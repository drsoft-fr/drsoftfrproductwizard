<?php

declare(strict_types=1);

use DrSoftFr\Module\ProductWizard\Entity\Configurator;
use DrSoftFr\Module\ProductWizard\Entity\ProductChoice;
use DrSoftFr\Module\ProductWizard\Entity\Step;
use DrSoftFr\Module\ProductWizard\Repository\ConfiguratorRepository;
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Presenter\Product\ProductLazyArray;
use PrestaShop\PrestaShop\Adapter\Presenter\Product\ProductPresenter;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;
use PrestaShop\PrestaShop\Core\Product\ProductPresentationSettings;

// @TODO il faut aussi que le client est le droit de voir le produit, ou sinon on masque le choix ?

final class DrsoftfrproductwizardAjaxModuleFrontController extends ModuleFrontController
{
    private const CONFIGURATOR_REPOSITORY = 'drsoft_fr.module.product_wizard.repository.configurator_repository';

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

            $repository = $this->getRepository();

            /** @var Configurator $configurator */
            $configurator = $repository->findOneBy([
                'id' => $configuratorId,
                'active' => true
            ]);

            if (null === $configurator) {
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

            $steps = $this->retrieveSteps($configurator);

            $this->ajaxRender(json_encode([
                'success' => true,
                'slug' => $configurator->getId(),
                'id' => $configurator->getId(),
                'name' => $configurator->getName(),
                'steps' => $steps,
            ]));

            return;
        } catch (Throwable $t) {
            $this->sendErrorResponse('Error retrieving configurator information.');
        }
    }

    private function retrieveSteps(Configurator $configurator): array
    {
        $steps = [];

        /** @var Step $step */
        foreach ($configurator->getSteps() as $step) {
            if (false === $step->isActive()) {
                continue;
            }

            $steps[] = $this->retrieveStep($step);
        }

        usort($steps, function ($a, $b) {
            return $a['position'] <=> $b['position'];
        });


        return $steps;
    }

    private function retrieveStep(Step $step): array
    {
        $choices = $this->retrieveChoices($step);

        return [
            'id' => $step->getId(),
            'label' => $step->getLabel(),
            'choices' => $choices,
            'position' => $step->getPosition(),
        ];
    }

    private function retrieveChoices(Step $step): array
    {
        $choices = [];

        $assembler = new ProductAssembler($this->context);
        $presenterFactory = new ProductPresenterFactory($this->context);
        $presentationSettings = $presenterFactory->getPresentationSettings();

        $presenter = new ProductPresenter(
            new ImageRetriever(
                $this->context->link
            ),
            $this->context->link,
            new PriceFormatter(),
            new ProductColorsRetriever(),
            $this->context->getTranslator()
        );

        /** @var ProductChoice $choice */
        foreach ($step->getProductChoices() as $choice) {
            if (false === $choice->isActive()) {
                continue;
            }

            $choices[] = $this->retrieveChoice($choice, $presenter, $presentationSettings, $assembler);
        }

        usort($choices, function ($a, $b) {
            return ($b['isDefault'] <=> $a['isDefault']);
        });

        return $choices;
    }

    private function retrieveChoice(
        ProductChoice               $choice,
        ProductPresenter            $presenter,
        ProductPresentationSettings $presentationSettings,
        ProductAssembler            $assembler
    ): array
    {
        $productId = (int)$choice->getProductId();
        $productInfo = $this->retrieveProduct($productId, $presenter, $presentationSettings, $assembler);
        $combinations = $this->retrieveProductCombinations($productId);
        $variants = [];

        foreach ($combinations as $combination) {
            $variants[] = $this->retrieveProduct($productId, $presenter, $presentationSettings, $assembler, $combination['id']);;
        }

        return [
            'id' => $choice->getId(),
            'label' => $choice->getLabel(),
            'productId' => $choice->getProductId(),
            'isDefault' => $choice->isDefault(),
            'allowQuantity' => $choice->isAllowQuantity(),
            'forcedQuantity' => $choice->getForcedQuantity(),
            'displayConditions' => $choice->getDisplayConditions(),
            'product' => $productInfo,
            'variants' => $variants,
            'combinations' => $combinations,
        ];
    }

    private function retrieveProduct(
        int                         $productId,
        ProductPresenter            $presenter,
        ProductPresentationSettings $presentationSettings,
        ProductAssembler            $assembler,
        int                         $productAttributeId = null
    ): ?ProductLazyArray
    {
        if (0 >= $productId) {
            return null;
        }

        $props = [
            'id_product' => $productId,
        ];

        if (null !== $productAttributeId) {
            $props['id_product_attribute'] = $productAttributeId;
        }

        try {
            return $presenter->present(
                $presentationSettings,
                $assembler->assembleProduct($props),
                $this->context->language
            );
        } catch (Throwable $t) {
            return null;
        }
    }

    private function retrieveProductCombinations(int $productId): array
    {
        if (0 >= $productId) {
            return [];
        }

        $product = new Product($productId, true, $this->context->language->id);
        $images = $product->getImages($this->context->language->id);
        $imageUrl = null;

        if (!empty($images)) {
            $image = reset($images); // Get first image
            $imageUrl = $this->context->link->getImageLink($product->link_rewrite, $image['id_image'], 'home_default');
        }

        if (false === Validate::isLoadedObject($product)) {
            return [];
        }

        $combinations = [];

        foreach ($product->getAttributeCombinations($this->context->language->id) as $combination) {
            $this->retrieveProductCombination($combinations, $product, $combination, $imageUrl);
        }

        return array_values($combinations);
    }

    private function retrieveProductCombination(array &$combinations, Product $product, $combination, ?string $imageUrl): void
    {
        $key = $product->id . '-' . $combination['id_product_attribute'];

        if (false === empty($combinations[$key])) {
            $combinations[$key]['attributes'][] = $this->retrieveAttribute($combination);

            return;
        }

        $combinationImageUrl = null;
        $combinationImages = $product->getCombinationImages($this->context->language->id);

        if (false === empty($combinationImages) && false === empty($combinationImages[$combination['id_product_attribute']])) {
            $combinationImage = reset($combinationImages[$combination['id_product_attribute']]);
            $combinationImageUrl = $this->context->link->getImageLink(
                $product->link_rewrite,
                $combinationImage['id_image'],
                'home_default'
            );
        }

        $combinations[$key] = [
            'id' => $combination['id_product_attribute'],
            'reference' => $combination['reference'],
            'ean13' => $combination['ean13'],
            'price' => $combination['price'],
            'minimalQuantity' => $combination['minimal_quantity'],
            'imageUrl' => $combinationImageUrl ?: $imageUrl,
            'attributes' => [
                $this->retrieveAttribute($combination)
            ]
        ];
    }

    private function retrieveAttribute($combinations): array
    {
        return [
            'idAttributeGroup' => $combinations['id_attribute_group'],
            'groupName' => $combinations['group_name'],
            'attributeName' => $combinations['attribute_name'],
            'idAttribute' => $combinations['id_attribute'],
            'isColorGroup' => $combinations['is_color_group'],
        ];
    }

    /**
     * @throws Exception
     */
    private function getRepository(): ConfiguratorRepository
    {
        /** @type ConfiguratorRepository */
        return $this->get(self::CONFIGURATOR_REPOSITORY);
    }
}
