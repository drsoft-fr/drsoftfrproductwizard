<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\Domain\Repository;

use DrSoftFr\Module\ProductWizard\Entity\Configurator;

interface ConfiguratorRepositoryInterface
{
    public function add(Configurator $configurator, bool $flush = true): void;

    public function beginTransaction(): void;

    /**
     * @param Configurator[] $configurators
     * @param bool $flush
     *
     * @return void
     */
    public function bulkRemove(array $configurators, bool $flush = true): void;

    public function commit(): void;

    public function remove(Configurator $configurator, bool $flush = true): void;

    public function rollback(): void;

    public function save(): void;
}
