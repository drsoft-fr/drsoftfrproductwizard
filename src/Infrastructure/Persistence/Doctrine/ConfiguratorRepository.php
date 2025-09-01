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
    /**
     * {@inerhitDoc}
     */
    public function bulkRemove(array $configurators): void
    {
        foreach ($configurators as $configurator) {
            $this->_em->remove($configurator);
        }
    }

    public function flush(): void
    {
        $this->_em->flush();
    }

    public function save(Configurator $configurator): void
    {
        $this->_em->persist($configurator);
    }
}
