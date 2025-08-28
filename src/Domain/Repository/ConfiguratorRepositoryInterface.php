<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\Domain\Repository;

use DrSoftFr\Module\ProductWizard\Entity\Configurator;

interface ConfiguratorRepositoryInterface
{
    public function save(Configurator $configurator): void;
}
