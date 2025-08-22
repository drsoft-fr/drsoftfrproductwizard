<?php

namespace DrSoftFr\Module\ProductWizard\Service;

use Context;
use DrSoftFr\Module\ProductWizard\Dto\ConfiguratorDto;
use DrSoftFr\Module\ProductWizard\Dto\ProductChoiceDto;
use DrSoftFr\Module\ProductWizard\Dto\StepDto;
use DrSoftFr\Module\ProductWizard\Entity\Configurator;
use DrSoftFr\Module\ProductWizard\Exception\Configurator\ConfiguratorNotFoundException;
use DrSoftFr\Module\ProductWizard\Repository\ConfiguratorRepository;
use DrSoftFr\Module\ProductWizard\ValueObject\Configurator\ConfiguratorId;
use DrSoftFr\Module\ProductWizard\ValueObject\ProductChoice\QuantityRule;
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Presenter\Product\ProductLazyArray;
use PrestaShop\PrestaShop\Adapter\Presenter\Product\ProductPresenter;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;
use PrestaShop\PrestaShop\Core\Product\ProductPresentationSettings;
use Product;
use ProductAssembler;
use ProductPresenterFactory;
use Throwable;
use Validate;

final class ConfiguratorPresenterService
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var ConfiguratorRepository
     */
    private $repository;

    public function __construct(
        ConfiguratorRepository $repository
    )
    {
        $this->context = Context::getContext();
        $this->repository = $repository;
    }

    /**
     * @throws ConfiguratorNotFoundException
     */
    public function present(ConfiguratorId $configuratorId): array
    {
        /** @var Configurator $configurator */
        $configurator = $this->repository->findOneBy([
            'id' => $configuratorId->getValue(),
            'active' => true
        ]);

        if (null === $configurator) {
            throw new ConfiguratorNotFoundException();
        }

        $dto = ConfiguratorDto::fromEntity($configurator);

        return [
            'success' => true,
            'slug' => $dto->id,
            'configurator' => [
                'id' => $dto->id,
                'name' => $dto->name,
                'description' => $dto->description,
                'steps' => $this->retrieveSteps($dto),
            ],
        ];
    }

    private function retrieveSteps(ConfiguratorDto $configurator): array
    {
        $steps = [];

        foreach ($configurator->steps as $step) {
            if (false === $step->active) {
                continue;
            }

            $steps[] = $this->retrieveStep($step);
        }

        usort($steps, function ($a, $b) {
            return $a['position'] <=> $b['position'];
        });


        return $steps;
    }

    private function retrieveStep(StepDto $step): array
    {
        $choices = $this->retrieveChoices($step);

        return [
            'id' => $step->id,
            'label' => $step->label,
            'description' => $step->description,
            'choices' => $choices,
            'position' => $step->position,
        ];
    }

    private function retrieveChoices(StepDto $step): array
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

        foreach ($step->productChoices as $choice) {
            if (false === $choice->active) {
                continue;
            }

            $choices[] = $this->retrieveChoice($choice, $step, $presenter, $presentationSettings, $assembler);
        }

        usort($choices, function ($a, $b) {
            return ($b['isDefault'] <=> $a['isDefault']);
        });

        return $choices;
    }

    private function retrieveChoice(
        ProductChoiceDto            $choice,
        StepDto                     $step,
        ProductPresenter            $presenter,
        ProductPresentationSettings $presentationSettings,
        ProductAssembler            $assembler
    ): array
    {
        $productId = (int)$choice->productId;
        $productInfo = $this->retrieveProduct($productId, $presenter, $presentationSettings, $assembler);
        $combinations = $this->retrieveProductCombinations($productId);
        $variants = [];

        foreach ($combinations as $combination) {
            $variants[] = $this->retrieveProduct($productId, $presenter, $presentationSettings, $assembler, $combination['id']);;
        }

        $quantityRuleApplier = new QuantityRuleApplier();

        return [
            'id' => $choice->id,
            'label' => $choice->label,
            'description' => $choice->description,
            'productId' => $choice->productId,
            'isDefault' => $choice->isDefault,
            'displayConditions' => $choice->displayConditions,
            'quantityRule' => $choice->quantityRule,
            'product' => $productInfo,
            'variants' => $variants,
            'combinations' => $combinations,
            'quantity' => $quantityRuleApplier->resolveQuantity(QuantityRule::fromArray($choice->quantityRule), [], $choice->quantityRule['offset']),
            'stepId' => $step->id,
            'stepLabel' => $step->label,
            'stepPosition' => $step->position,
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
}
