<?php

namespace DrSoftFr\Module\ProductWizard\Dto;

use DrSoftFr\Module\ProductWizard\Entity\Configurator;

final class ConfiguratorDto
{
    public function __construct(
        public ?int   $id = null,
        public string $name = '',
        public bool   $active = true,
        /** @var StepDto[] */
        public array  $steps = []
    )
    {
    }

    public static function fromEntity(Configurator $configurator): self
    {
        $arr = [];

        foreach ($configurator->getSteps() as $step) {
            $arr[] = StepDto::fromEntity($step);
        }

        return new self(
            $configurator->getId(),
            $configurator->getName(),
            $configurator->isActive(),
            $arr
        );
    }
}
