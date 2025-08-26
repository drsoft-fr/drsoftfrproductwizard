<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\ValueObject\ProductChoice;

use DrSoftFr\Module\ProductWizard\Exception\ProductChoice\ProductChoiceConstraintException;

final class Quantity
{
    private readonly int $value;

    /**
     * @throws ProductChoiceConstraintException
     */
    public function __construct(int $value)
    {
        self::assertIntegerIsGreaterThanZero($value);

        $this->value = $value;
    }

    /**
     * @throws ProductChoiceConstraintException
     */
    public static function fromInt(int $value): self
    {
        return new self($value);
    }

    final public function getValue(): int
    {
        return $this->value;
    }

    public function equals(Quantity $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return (string)$this->value;
    }

    /**
     * @throws ProductChoiceConstraintException
     */
    private static function assertIntegerIsGreaterThanZero(int $value): void
    {
        if (0 >= $value) {
            throw new ProductChoiceConstraintException(
                sprintf('Invalid product choice quantity "%s".', var_export($value, true)),
                ProductChoiceConstraintException::INVALID_QUANTITY);
        }
    }
}
