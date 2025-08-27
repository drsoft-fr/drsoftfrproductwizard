<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\Domain\ValueObject\Step;

use DrSoftFr\Module\ProductWizard\Exception\Step\StepConstraintException;

final class StepId
{
    private readonly int $value;

    /**
     * @throws StepConstraintException
     */
    public function __construct(int $value)
    {
        self::assertIntegerIsGreaterThanZero($value);

        $this->value = $value;
    }

    /**
     * @throws StepConstraintException
     */
    public static function fromInt(int $value): self
    {
        return new self($value);
    }

    final public function getValue(): int
    {
        return $this->value;
    }

    public function equals(StepId $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return (string)$this->value;
    }

    /**
     * @throws StepConstraintException
     */
    private static function assertIntegerIsGreaterThanZero(int $value): void
    {
        if (0 >= $value) {
            throw new StepConstraintException(
                sprintf('Invalid step id "%s".', var_export($value, true)),
                StepConstraintException::INVALID_ID);
        }
    }
}
