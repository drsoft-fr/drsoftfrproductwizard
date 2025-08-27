<?php

namespace DrSoftFr\Module\ProductWizard\Application\Dto;

use DrSoftFr\Module\ProductWizard\Entity\ProductChoice;
use DrSoftFr\Module\ProductWizard\Exception\ProductChoice\ProductChoiceConstraintException;

final class ProductChoiceDto
{
    public function __construct(
        public ?string $id = null, // Peut être virtual-XXX
        public string  $label = '',
        public ?string $description = null,
        public ?int    $productId = null,
        public bool    $isDefault = false,
        public bool    $active = true,
        public float   $reduction = 0.0,
        public bool    $reductionTax = true,
        public string  $reductionType = 'amount',
        /** @var array<int, array{step:int, choice:int}> */
        public array   $displayConditions = [],
        public array   $quantityRule = [],
    )
    {
    }

    /**
     * @throws ProductChoiceConstraintException
     */
    public static function fromEntity(ProductChoice $productChoice): self
    {
        $arr = [];

        foreach ($productChoice->getDisplayConditions() as $displayCondition) {
            $arr[] = $displayCondition->getValue();
        }

        return new self(
            $productChoice->getId(),
            $productChoice->getLabel(),
            $productChoice->getDescription(),
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
