<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\Domain\Repository;

use DrSoftFr\Module\ProductWizard\Entity\Configurator;

interface ConfiguratorRepositoryInterface
{
    /**
     * @param Configurator[] $configurators
     *
     * @return void
     */
    public function bulkRemove(array $configurators): void;

    public function flush(): void;

    public function save(Configurator $configurator): void;
}
