<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\Domain\Service;

use DrSoftFr\Module\ProductWizard\Application\Dto\ConfiguratorDto;
use DrSoftFr\Module\ProductWizard\Application\Dto\ProductChoiceDto;
use DrSoftFr\Module\ProductWizard\Application\Dto\StepDto;
use DrSoftFr\Module\ProductWizard\Domain\Exception\ProductChoice\ProductChoiceConstraintException;
use DrSoftFr\Module\ProductWizard\Domain\ValueObject\ProductChoice\Reduction;
use DrSoftFr\Module\ProductWizard\Domain\ValueObject\ProductChoice\ReductionTax;
use DrSoftFr\Module\ProductWizard\Domain\ValueObject\ProductChoice\ReductionType;

final class ReductionPickerService
{
    /**
     * @return array{reduction: Reduction, reductionTax: ReductionTax, reductionType: ReductionType, hasReduction: bool}
     *
     * @throws ProductChoiceConstraintException
     */
    final public static function pick(
        ProductChoiceDto $productChoiceDto,
        StepDto          $stepDto,
        ConfiguratorDto  $configuratorDto
    ): array
    {
        // Search for the first strictly positive reduction in order of priority
        $candidates = [$productChoiceDto, $stepDto, $configuratorDto];
        $first = self::firstPositiveReduction($candidates);

        if (null !== $first) {
            [$reduction, $reductionTax, $reductionType] = $first;

            return self::buildResponse(
                $reduction,
                $reductionTax,
                $reductionType,
                true
            );
        }

        return self::buildResponse(
            $productChoiceDto->reduction,
            $productChoiceDto->reductionTax,
            $productChoiceDto->reductionType,
            false
        );
    }


    /**
     * @param array<int, object> $dtoList
     *
     * @return array{0: float, 1: bool, 2: string}|null
     */
    private static function firstPositiveReduction(array $dtoList): ?array
    {
        foreach ($dtoList as $dto) {
            if (false === isset($dto->reduction) || 0 < $dto->reduction) {
                continue;
            }

            return [$dto->reduction, $dto->reductionTax, $dto->reductionType];
        }

        return null;
    }


    /**
     * @return array{reduction: Reduction, reductionTax: ReductionTax, reductionType: ReductionType, hasReduction: bool}
     *
     * @throws ProductChoiceConstraintException
     */
    private static function buildResponse(
        float  $reduction,
        bool   $reductionTax,
        string $reductionType,
        bool   $hasReduction,
    ): array
    {
        return [
            'reduction' => Reduction::fromFloat($reduction),
            'reductionTax' => ReductionTax::fromBool($reductionTax),
            'reductionType' => ReductionType::fromString($reductionType),
            'hasReduction' => $hasReduction,
        ];
    }
}
