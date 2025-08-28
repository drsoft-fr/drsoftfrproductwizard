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
    public function save(Configurator $configurator): void
    {
        $this->_em->persist($configurator);
    }
}
