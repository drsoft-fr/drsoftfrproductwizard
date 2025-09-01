<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\Infrastructure\Persistence\Doctrine;

use Doctrine\ORM\EntityRepository;
use DrSoftFr\Module\ProductWizard\Domain\Repository\ConfiguratorRepositoryInterface;
use DrSoftFr\Module\ProductWizard\Entity\Configurator;

/**
 * Class ConfiguratorRepository
 *
 * This class represents a repository for Configurator entities.
 * It extends the EntityRepository class to inherit common entity repository functionality.
 */
class ConfiguratorRepository extends EntityRepository implements ConfiguratorRepositoryInterface
{
    public function add(Configurator $configurator, bool $flush = true): void
    {
        $this->_em->persist($configurator);

        if (true === $flush) {
            $this->_em->flush();
        }
    }

    public function beginTransaction(): void
    {
        $this->_em->beginTransaction();
    }

    public function bulkRemove(array $configurators, bool $flush = true): void
    {
        foreach ($configurators as $configurator) {
            $this->_em->remove($configurator);
        }

        if (true === $flush) {
            $this->_em->flush();
        }
    }

    public function commit(): void
    {
        $this->_em->commit();
    }

    public function remove(Configurator $configurator, bool $flush = true): void
    {
        $this->_em->remove($configurator);

        if (true === $flush) {
            $this->_em->flush();
        }
    }

    public function rollback(): void
    {
        $this->_em->rollback();
    }

    public function save(): void
    {
        $this->_em->flush();
    }
}
