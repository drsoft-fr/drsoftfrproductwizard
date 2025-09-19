<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\Infrastructure\Cart;

use Cart;
use CartRule;
use Context;
use Db;
use DrSoftFr\Module\ProductWizard\Application\Dto\CartDto;
use DrSoftFr\Module\ProductWizard\Application\Dto\CartItemDto;
use DrSoftFr\Module\ProductWizard\Application\Dto\ConfiguratorDto;
use DrSoftFr\Module\ProductWizard\Application\Dto\ProductChoiceDto;
use DrSoftFr\Module\ProductWizard\Application\Dto\StepDto;
use DrSoftFr\Module\ProductWizard\Domain\Repository\ConfiguratorRepositoryInterface;
use DrSoftFr\Module\ProductWizard\Domain\Service\PriceResolverService;
use DrSoftFr\Module\ProductWizard\Entity\Configurator;
use Exception;
use Group;
use Language;
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Presenter\Product\ProductPresenter;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;
use PrestaShopDatabaseException;
use PrestaShopException;
use ProductAssembler;
use ProductPresenterFactory;
use Tools;

final class DiscountApplier
{
    public function __construct(
        private readonly ConfiguratorRepositoryInterface $repository,
        private readonly Context                         $context,
    )
    {
    }

    /**
     * Create or update a CartRule matching the exact selection requirements.
     * The rule will automatically become not applicable if cart content changes
     * (thanks to product restrictions with minimal quantities).
     *
     * @throws PrestaShopDatabaseException
     * @throws Exception
     */
    public function apply(
        Cart    $cart,
        CartDto $cartDto
    ): void
    {
        $this->cleanCartRules($cart);

        /** @var Configurator $configurator */
        $configurator = $this->repository->findOneBy([
            'id' => $cartDto->configuratorId,
            'active' => true
        ]);

        if (null === $configurator) {
            throw new Exception('Cannot find active Configurator for ID ' . $cartDto->configuratorId);
        }

        $configuratorDto = ConfiguratorDto::fromEntity($configurator);

        /**
         * @var array<int, int> $requirements
         * @var array<int, CartItemDto> $selectionIndex
         */
        [$requirements, $selectionIndex] = $this->prepareRequirementsForCartRuleCreation($cartDto);

        if (true === empty($requirements)) {
            return;
        }

        ksort($requirements);

        $hash = md5(json_encode($requirements));

        // Prepare presenters to retrieve product lazy arrays (price_amount, reduction, etc.)
        $assembler = new ProductAssembler($this->context);
        $presenterFactory = new ProductPresenterFactory($this->context);
        $presentationSettings = $presenterFactory->getPresentationSettings();
        $presenter = new ProductPresenter(
            new ImageRetriever($this->context->link),
            $this->context->link,
            new PriceFormatter(),
            new ProductColorsRetriever(),
            $this->context->getTranslator()
        );
        $code = sprintf('WIZ-%d-%s', $cartDto->configuratorId, $hash);
        $reduction = 0;
        $cartRule = $this->createCartRule($cart, $code);
        $cartRuleId = (int)$cartRule->id;

        foreach ($requirements as $productId => $qtyRequired) {
            $itemDto = $selectionIndex[$productId] ?? null;

            if (null === $itemDto) {
                throw new Exception('Cannot find representative selection for product ' . $productId);
            }

            /**
             * @var StepDto $stepDto
             * @var ProductChoiceDto $choiceDto
             */
            [$stepDto, $choiceDto] = $this->retrieveStepDtoAndChoiceDtoFromConfiguratorDtoAndCartDto(
                $itemDto,
                $configuratorDto
            );

            if (null === $stepDto || null === $choiceDto) {
                throw new Exception('Cannot find matching Step/ProductChoice for product ' . $productId);
            }

            // Present product to get current price and existing reduction
            $props = [
                'id_product' => $productId,
                'id_product_attribute' => $itemDto->combinationId,
            ];
            $productLazy = $presenter->present(
                $presentationSettings,
                $assembler->assembleProduct($props),
                $this->context->language
            );

            $priceResolver = PriceResolverService::get($choiceDto, $stepDto, $configuratorDto, $productLazy);

            if (false === $priceResolver['has_discount'] || true === $priceResolver['is_product_discount']) {
                continue;
            }

            $reduction += $priceResolver['reduction'] * $qtyRequired;

            $this->addCartRuleRestrictions($cartRuleId, $productId, $qtyRequired);
        }

        if (0 >= $reduction) {
            $cartRule->delete();

            return;
        }

        $cartRule->reduction_amount = max(0, (float)Tools::ps_round($reduction, 6));
        $cartRule->update();

        // Apply to current cart
        $cart->addCartRule($cartRuleId);

        // Clean caches at the end
        CartRule::cleanCache();
    }

    /**
     * @throws Exception
     * @throws PrestaShopDatabaseException
     */
    private function addCartRuleRestrictions(
        int $cartRuleId,
        int $productId,
        int $qty
    ): void
    {
        Db::getInstance()->insert('cart_rule_product_rule_group', [
            'id_cart_rule' => $cartRuleId,
            'quantity' => $qty,
        ]);

        $groupId = (int)Db::getInstance()->Insert_ID();

        if (true === empty($groupId)) {
            throw new Exception('Cannot create CartRuleProductRuleGroup for CartRule ' . $cartRuleId);
        }

        Db::getInstance()->insert('cart_rule_product_rule', [
            'id_product_rule_group' => $groupId,
            'type' => pSQL('products'),
        ]);

        $ruleRowId = (int)Db::getInstance()->Insert_ID();

        if (true === empty($ruleRowId)) {
            throw new Exception('Cannot create CartRuleProductRule for CartRule ' . $cartRuleId);
        }

        Db::getInstance()->insert('cart_rule_product_rule_value', [
            'id_product_rule' => $ruleRowId,
            'id_item' => $productId,
        ]);
    }

    private function cleanCartRules(Cart $cart): void
    {
        /** @var array[] $cartRules */
        $cartRules = $cart->getCartRules();

        foreach ($cartRules as $cartRule) {
            $cart->removeCartRule($cartRule['id_cart_rule']);
        }
    }

    /**
     * @throws Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function createCartRule(
        Cart   $cart,
        string $code
    ): CartRule
    {
        // Resolve user's tax display mode to set reduction_tax consistently
        $useTax = !Group::getCurrent()->price_display_method; // true means tax-included prices are shown

        $now = date('Y-m-d H:i:s');
        $to = date('Y-m-d H:i:s', strtotime('+1 day'));
        $rule = new CartRule();
        $rule->code = $code;
        $rule->quantity = 1;
        $rule->quantity_per_user = 1;
        $rule->highlight = false;
        $rule->partial_use = false;
        $rule->active = 1;
        $rule->date_from = $now;
        $rule->date_to = $to;
        $rule->minimum_amount = $cart->getOrderTotal($useTax, Cart::ONLY_PRODUCTS);
        $rule->minimum_amount_tax = $useTax;
        $rule->minimum_amount_currency = (int)$cart->id_currency;
        $rule->minimum_amount_shipping = 0;
        $rule->id_customer = (int)$this->context->customer->id ?: 0;
        $rule->reduction_tax = $useTax; // amount expressed in the same tax mode as shown
        $rule->free_shipping = false;
        $rule->reduction_product = 0;
        $rule->reduction_exclude_special = true; // we apply a delta, so no double-discounting beyond target
        $rule->reduction_currency = (int)$cart->id_currency;
        $rule->cart_rule_restriction = true;
        $rule->product_restriction = true;
        $rule->shop_restriction = true;
        $rule->gift_product = false;
        $rule->gift_product_attribute = false;

        foreach (Language::getIDs(false) as $idLang) {
            $rule->name[(int)$idLang] = 'Configurator Discount';
        }

        $rule->add();
        $cartRuleId = (int)$rule->id;

        if (true === empty($cartRuleId)) {
            throw new Exception('Cannot create CartRule for product.');
        }

        return $rule;
    }

    /**
     * Build normalized requirements and a stable hash for the whole selection (used in codes)
     *
     * @return array{0: array<int, int>, 1: <int, CartItemDto>} An array containing two elements:
     *               - The first element is an associative array mapping product IDs to their required quantities.
     *               - The second element is an associative array mapping product IDs to a representative item DTO
     *                 for accessing stepId, id, or combinationId.
     */
    private function prepareRequirementsForCartRuleCreation(CartDto $cartDto): array
    {
        $requirements = [];
        $selectionIndex = [];

        foreach ($cartDto->items as $itemDto) {
            $qty = max(1, ($itemDto->quantity ?? 1));

            if (false === isset($requirements[$itemDto->productId])) {
                $requirements[$itemDto->productId] = 0;
            }

            $requirements[$itemDto->productId] += $qty;

            // Keep a representative selection row for this product (to access stepId/id/combinationId)
            if (false === isset($selectionIndex[$itemDto->productId])) {
                $selectionIndex[$itemDto->productId] = $itemDto;
            }
        }

        return [$requirements, $selectionIndex];
    }

    /**
     * Retrieves the step and choice DTOs from the provided ConfiguratorDto and CartItemDto.
     *
     * @return array{0: StepDto|null, 1: ProductChoiceDto|null} An array containing the matched step DTO and choice DTO. If no matches are found, null values are returned in the array.
     */
    private function retrieveStepDtoAndChoiceDtoFromConfiguratorDtoAndCartDto(
        CartItemDto     $itemDto,
        ConfiguratorDto $configuratorDto
    ): array
    {
        $stepDto = null;
        $choiceDto = null;

        foreach ($configuratorDto->steps as $step) {
            if ((int)$step->id !== $itemDto->stepId) {
                continue;
            }

            $stepDto = $step;

            foreach ($stepDto->productChoices as $pc) {
                if ((int)$pc->id !== $itemDto->productChoiceId) {
                    continue;
                }

                $choiceDto = $pc;

                break;
            }

            break;
        }

        return [$stepDto, $choiceDto];
    }
}
