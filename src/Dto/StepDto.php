<?php

namespace DrSoftFr\Module\ProductWizard\Dto;

use DrSoftFr\Module\ProductWizard\Entity\Step;

final class StepDto
{
    public function __construct(
        public ?string $id = null, // Peut être virtual-XXX
        public string  $label = '',
        public int     $position = 0,
        public bool    $active = true,
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
            $step->getPosition(),
            $step->isActive(),
            $arr
        );
    }
}
