<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\Infrastructure\Persistence\Doctrine;

use Doctrine\ORM\EntityRepository;
use DrSoftFr\Module\ProductWizard\Domain\Repository\StepRepositoryInterface;

/**
 * Class StepRepository
 *
 * This class represents a repository for Step entities.
 * It extends the EntityRepository class to inherit common entity repository functionality.
 */
class StepRepository extends EntityRepository implements StepRepositoryInterface
{
}
