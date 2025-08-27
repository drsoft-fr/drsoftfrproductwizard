<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\Domain\ValueObject\ProductChoice;

use DrSoftFr\Module\ProductWizard\Domain\ValueObject\Step\StepId;
use DrSoftFr\Module\ProductWizard\Domain\Exception\ProductChoice\ProductChoiceConstraintException;
use DrSoftFr\Module\ProductWizard\Domain\Exception\Step\StepConstraintException;

final class QuantityRuleSource
{
    public const DEFAULT_COEFF = 1.0;

    private readonly float $coeff;

    /**
     * @throws ProductChoiceConstraintException
     */
    public function __construct(
        private readonly StepId $step,
        float                   $coeff = self::DEFAULT_COEFF
    )
    {
        self::assertPositiveFloat($coeff);

        $this->coeff = $coeff;
    }

    /**
     * @throws ProductChoiceConstraintException
     * @throws StepConstraintException
     */
    public static function fromArray(array $data): self
    {
        if (false === isset($data['step'])) {
            throw new ProductChoiceConstraintException(
                'Missing step in quantity source',
                ProductChoiceConstraintException::INVALID_QUANTITY_RULE_SOURCES
            );
        }

        $coeff = isset($data['coeff']) ? (float)$data['coeff'] : self::DEFAULT_COEFF;

        return new self(
            StepId::fromInt($data['step']),
            $coeff
        );
    }

    final public function getStep(): StepId
    {
        return $this->step;
    }

    final public function getCoeff(): float
    {
        return $this->coeff;
    }

    /**
     * @return array{step:int, coeff:float}
     */
    final public function toArray(): array
    {
        return [
            'step' => $this->step->getValue(),
            'coeff' => $this->coeff,
        ];
    }

    public function equals(QuantityRuleSource $other): bool
    {
        return $this->step->equals($other->step) && $this->coeff === $other->coeff;
    }

    public function __toString(): string
    {
        return sprintf('step:%d;coeff:%s', $this->step->getValue(), rtrim(rtrim(sprintf('%.12F', $this->coeff), '0'), '.'));
    }

    /**
     * @throws ProductChoiceConstraintException
     */
    private static function assertPositiveFloat(float $value): void
    {
        if (0.0 >= $value) {
            throw new ProductChoiceConstraintException(
                sprintf('Invalid product choice quantity rule source coeff "%g".', $value),
                ProductChoiceConstraintException::INVALID_QUANTITY_RULE_SOURCES);
        }
    }
}
