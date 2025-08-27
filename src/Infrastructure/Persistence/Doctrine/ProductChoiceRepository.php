<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\Infrastructure\Persistence\Doctrine;

use Doctrine\ORM\EntityRepository;
use DrSoftFr\Module\ProductWizard\Domain\Repository\ProductChoiceRepositoryInterface;

/**
 * Class ProductChoiceRepository
 *
 * This class represents a repository for ProductChoice entities.
 * It extends the EntityRepository class to inherit common entity repository functionality.
 */
class ProductChoiceRepository extends EntityRepository implements ProductChoiceRepositoryInterface
{
}
