<?php

namespace DrSoftFr\Module\ProductWizard\Dto;

use DrSoftFr\Module\ProductWizard\Entity\ProductChoice;

final class ProductChoiceDto
{
    public function __construct(
        public ?string $id = null, // Peut être virtual-XXX
        public string  $label = '',
        public ?int    $productId = null,
        public bool    $isDefault = false,
        public bool    $active = true,
        public float   $reduction = 0.0,
        public bool    $reductionTax = true,
        public string  $reductionType = 'amount',
        /** @var DisplayConditionDto[] */
        public array   $displayConditions = [],
        public array   $quantityRule,
    )
    {
    }

    public static function fromEntity(ProductChoice $productChoice): self
    {
        $arr = [];

        foreach ($productChoice->getDisplayConditions() as $displayCondition) {
            $arr[] = DisplayConditionDto::fromArray($displayCondition);
        }

        return new self(
            $productChoice->getId(),
            $productChoice->getLabel(),
            $productChoice->getProductId(),
            $productChoice->isDefault(),
            $productChoice->isActive(),
            $productChoice->getReduction(),
            $productChoice->isReductionTax(),
            $productChoice->getReductionType(),
            $arr,
            $productChoice->getQuantityRule()->getValue()
        );
    }
}
