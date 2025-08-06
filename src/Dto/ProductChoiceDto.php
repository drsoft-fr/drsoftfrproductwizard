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
        public bool    $allowQuantity = true,
        public ?int    $forcedQuantity = null,
        public bool    $active = true,
        public array   $displayConditions = []
    )
    {
    }

    public static function fromEntity(ProductChoice $productChoice): self
    {
        return new self(
            $productChoice->getId(),
            $productChoice->getLabel(),
            $productChoice->getProductId(),
            $productChoice->isDefault(),
            $productChoice->isAllowQuantity(),
            $productChoice->getForcedQuantity(),
            $productChoice->isActive(),
            $productChoice->getDisplayConditions()
        );
    }
}
