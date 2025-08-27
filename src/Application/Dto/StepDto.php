<?php

namespace DrSoftFr\Module\ProductWizard\Application\Dto;

use DrSoftFr\Module\ProductWizard\Domain\Entity\Step;

final class StepDto
{
    public function __construct(
        public ?string $id = null, // Peut être virtual-XXX
        public string  $label = '',
        public ?string $description = null,
        public int     $position = 0,
        public bool    $active = true,
        public float   $reduction = 0.0,
        public bool    $reductionTax = true,
        public string  $reductionType = 'amount',
        /** @var ProductChoiceDto[] */
        public array   $productChoices = []
    )
    {
    }

    public static function fromEntity(Step $step): self
    {
        $arr = [];

        foreach ($step->getProductChoices() as $productChoice) {
            $arr[] = ProductChoiceDto::fromEntity($productChoice);
        }

        return new self(
            $step->getId(),
            $step->getLabel(),
            $step->getDescription(),
            $step->getPosition(),
            $step->isActive(),
            $step->getReduction(),
            $step->isReductionTax(),
            $step->getReductionType(),
            $arr
        );
    }
}
