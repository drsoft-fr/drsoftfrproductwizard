<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\Infrastructure\Persistence\Doctrine;

use Doctrine\ORM\EntityRepository;
use DrSoftFr\Module\ProductWizard\Domain\Repository\ConfiguratorRepositoryInterface;

/**
 * Class ConfiguratorRepository
 *
 * This class represents a repository for Configurator entities.
 * It extends the EntityRepository class to inherit common entity repository functionality.
 */
class ConfiguratorRepository extends EntityRepository implements ConfiguratorRepositoryInterface
{
}
