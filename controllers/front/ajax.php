<?php

declare(strict_types=1);

use DrSoftFr\Module\ProductWizard\Application\Dto\CartDto;
use DrSoftFr\Module\ProductWizard\Application\Dto\CartItemDto;
use DrSoftFr\Module\ProductWizard\Application\Dto\ConfiguratorDto;
use DrSoftFr\Module\ProductWizard\Application\Dto\ProductChoiceDto;
use DrSoftFr\Module\ProductWizard\Application\Dto\StepDto;
use DrSoftFr\Module\ProductWizard\Application\Exception\Configurator\ConfiguratorNotFoundException;
use DrSoftFr\Module\ProductWizard\Domain\Service\PriceResolverService;
use DrSoftFr\Module\ProductWizard\Entity\Configurator;
use DrSoftFr\Module\ProductWizard\Domain\Exception\Configurator\ConfiguratorConstraintException;
use DrSoftFr\Module\ProductWizard\Domain\Repository\ConfiguratorRepositoryInterface;
use DrSoftFr\Module\ProductWizard\Domain\ValueObject\Configurator\ConfiguratorId;
use DrSoftFr\Module\ProductWizard\UI\Hook\Presenter\ConfiguratorPresenter;
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Presenter\Product\ProductPresenter;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;

// @TODO il faut aussi que le client est le droit de voir le produit, ou sinon on masque le choix ?

final class DrsoftfrproductwizardAjaxModuleFrontController extends ModuleFrontController
{
    private const PRESENTER_SERVICE = 'drsoft_fr.module.product_wizard.service.configurator_presenter';
    private const REPOSITORY_SERVICE = 'drsoft_fr.module.product_wizard.infrastructure.persistence.doctrine.configurator_repository';

    /**
     * @var null|string
     */
    private $action = null;

    /**
     * Process the POST request and validate the reCAPTCHA token.
     *
     * @return void
     */
    public function postProcess(): void
    {
        try {
            parent::postProcess();

            $this->action = Tools::getValue('action', null);

            if (empty($this->action)) {
                $this->sendErrorResponse(
                    $this
                        ->context
                        ->getTranslator()
                        ->trans(
                            'Action does not exist.',
                            [],
                            'Modules.Drsoftfrproductwizard.Error'
                        )
                );
            }
        } catch (Throwable $t) {
            $this->sendErrorResponse('An error occurred during Ajax processing. When retrieving the action parameter.');
        }
    }

    /**
     * Displays the response for an AJAX request.
     *
     * @return void
     */
    public function displayAjax(): void
    {
        try {
            switch ($this->action) {
                case 'add-to-cart':
                    $this->addToCartAction();

                    break;
                case 'get-configurator':
                    $this->getConfiguratorAction();

                    break;
                default:
                    $this->sendErrorResponse(
                        $this
                            ->context
                            ->getTranslator()
                            ->trans(
                                'Invalid action.',
                                [],
                                'Modules.Drsoftfrproductwizard.Error'
                            )
                    );
            }
        } catch (Throwable $t) {
            $this->sendErrorResponse('An error occurred during Ajax processing. When routing the action');
        }
    }

    /**
     * Redirects to the 404 error page.
     *
     * @return void
     */
    public function display(): void
    {
        Tools::redirect('/index.php?controller=404');
    }

    private function addToCartAction(): void
    {
        try {
            $data = json_decode(Tools::getValue('data', '{}'), true);

            $dto = CartDto::fromArray($data);
            $validationError = $this->validateCartDto($dto);

            if (null !== $validationError) {
                $this->sendErrorResponse($validationError);
            }

            $cart = $this->context->cart;

            if (!Validate::isLoadedObject($cart)) {
                $cart = new Cart();
                $cart->id_currency = (int)$this->context->currency->id;
                $cart->id_guest = (int)$this->context->cookie->id_guest;
                $cart->id_lang = (int)$this->context->language->id;
                $cart->id_shop_group = (int)$this->context->shop->id_shop_group;
                $cart->id_shop = $this->context->shop->id;

                if ($this->context->cookie->id_customer) {
                    $cart->id_customer = (int)$this->context->cookie->id_customer;
                    $cart->id_address_delivery = (int)Address::getFirstCustomerAddressId($cart->id_customer);
                    $cart->id_address_invoice = (int)$cart->id_address_delivery;
                } else {
                    $cart->id_address_delivery = 0;
                    $cart->id_address_invoice = 0;
                }

                $cart->save();

                $this->context->cart = $cart;
            }

            // Add products to cart
            $addedProducts = [];

            foreach ($dto->items as $itemDto) {
                $productId = $itemDto->productId;
                $combinationId = $itemDto->combinationId;
                $quantity = max(1, $itemDto->quantity);

                $result = $cart->updateQty(
                    $quantity,
                    $productId,
                    $combinationId
                );

                if (false === $result) {
                    $this->sendErrorResponse(
                        $this->trans(
                            sprintf(
                                'Product "%s" is currently unavailable and could not be added to your cart.',
                                var_export($itemDto->productName, true)
                            ),
                            [],
                            'Modules.Drsoftfrproductwizard.Error'
                        )
                    );
                }

                $addedProducts[] = [
                    'productId' => $productId,
                    'combinationId' => $combinationId,
                    'quantity' => $quantity,
                ];
            }

            // Apply discount dictated by configurator hierarchy (ProductChoice > Step > Configurator)
            $this->upsertCartRuleForSelections($cart, $dto);

            // Update cart cookie
            $this->context->cookie->__set('id_cart', (int)$cart->id);
            $this->context->cookie->write();

            $this->ajaxRender(json_encode([
                'success' => true,
                'message' => $this->trans('Products added to cart successfully', [], 'Modules.Drsoftfrproductwizard.Shop'),
                'cartUrl' => $this->context->link->getPageLink('cart', null, null, ['action' => 'show']),
                'addedProducts' => $addedProducts,
            ]));
        } catch (Throwable $t) {
            $this->sendErrorResponse('An error occurred while adding products to cart: ' . $t->getMessage());
        }
    }

    private function getConfiguratorAction(): void
    {
        try {
            $configuratorId = (int)Tools::getValue('slug', 0);

            if (empty($configuratorId)) {
                $this->sendErrorResponse(
                    $this
                        ->context
                        ->getTranslator()
                        ->trans(
                            'Invalid configurator ID.',
                            [],
                            'Modules.Drsoftfrproductwizard.Error'
                        )
                );
            }

            try {
                $presenter = $this->getPresenter();

                $this->ajaxRender(json_encode(
                    $presenter->present(new ConfiguratorId($configuratorId))
                ));

                return;
            } catch (ConfiguratorConstraintException $e) {
                $this->sendErrorResponse($e->getMessage());
            } catch (ConfiguratorNotFoundException $e) {
                $this->sendErrorResponse(
                    $this
                        ->context
                        ->getTranslator()
                        ->trans(
                            'Configurator not found',
                            [],
                            'Modules.Drsoftfrproductwizard.Error'
                        )
                );
            }
        } catch (Throwable $t) {
            $this->sendErrorResponse('Error retrieving configurator information.');
        }
    }

    /**
     * @throws Exception
     * @throws PrestaShopDatabaseException
     */
    private function addCartRuleRestrictions(int $cartRuleId, int $productId, int $qty): void
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
            if (!isset($selectionIndex[$itemDto->productId])) {
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

    /**
     * Sends an error response for an AJAX request.
     *
     * @param string $message The message key.
     *
     * @return void
     */
    private function sendErrorResponse(string $message): void
    {
        try {
            http_response_code(400);

            $this->ajaxRender(json_encode([
                'success' => false,
                'message' => $message,
            ]));
        } catch (Throwable $t) {
            http_response_code(400);
            header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');

            echo json_encode([
                'success' => false,
                'message' => 'An error occurred during Ajax processing.'
            ]);
        } finally {
            die;
        }
    }

    /**
     * Create or update a CartRule matching the exact selection requirements.
     * The rule will automatically become not applicable if cart content changes
     * (thanks to product restrictions with minimal quantities).
     *
     * @throws PrestaShopDatabaseException
     * @throws Exception
     */
    private function upsertCartRuleForSelections(Cart $cart, CartDto $cartDto): void
    {
        $this->cleanCartRules($cart);

        /** @var Configurator $configurator */
        $configurator = $this->getRepository()->findOneBy([
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
     * Validate given selections (products, quantities and combinations)
     *
     * @param CartDto $dto
     *
     * @return string|null Error message if invalid, null if ok
     */
    private function validateCartDto(CartDto $dto): ?string
    {
        if (true === empty($dto->configuratorId)) {
            return $this->trans('Invalid configuratorId.', [], 'Modules.Drsoftfrproductwizard.Error');
        }

        if (true === empty($dto->items)) {
            return $this->trans('No products selected.', [], 'Modules.Drsoftfrproductwizard.Error');
        }

        foreach ($dto->items as $itemDto) {
            if (true === empty($itemDto->productId)) {
                return $this->trans('Invalid product ID.', [], 'Modules.Drsoftfrproductwizard.Error');
            }

            if (true === empty($itemDto->quantity)) {
                return $this->trans('Invalid quantity.', [], 'Modules.Drsoftfrproductwizard.Error');
            }

            if (true === empty($itemDto->productChoiceId)) {
                return $this->trans('Invalid product choice ID.', [], 'Modules.Drsoftfrproductwizard.Error');
            }

            if (true === empty($itemDto->stepId)) {
                return $this->trans('Invalid step ID.', [], 'Modules.Drsoftfrproductwizard.Error');
            }

            $product = new Product($itemDto->productId, true, (int)$this->context->language->id);

            if (!Validate::isLoadedObject($product) || !$product->active) {
                return $this->trans('A selected product is not available anymore.', [], 'Modules.Drsoftfrproductwizard.Error');
            }

            if ($itemDto->combinationId > 0) {
                $attributes = $product->getAttributeCombinations($this->context->language->id);
                $valid = false;

                foreach ($attributes as $attr) {
                    if ((int)$attr['id_product_attribute'] !== $itemDto->combinationId) {
                        continue;
                    }

                    $valid = true;

                    break;
                }

                if (false === $valid) {
                    return $this->trans('Invalid product combination.', [], 'Modules.Drsoftfrproductwizard.Error');
                }
            }
        }

        return null;
    }

    /**
     * @throws Exception
     */
    private function getPresenter(): ConfiguratorPresenter
    {
        /** @type ConfiguratorPresenter */
        return $this->get(self::PRESENTER_SERVICE);
    }

    /**
     * @throws Exception
     */
    private function getRepository(): ConfiguratorRepositoryInterface
    {
        /** @type ConfiguratorRepositoryInterface */
        return $this->get(self::REPOSITORY_SERVICE);
    }
}
