<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\ValueObject\ProductChoice;

use DrSoftFr\Module\ProductWizard\Exception\ProductChoice\ProductChoiceConstraintException;

final class ReductionType
{
    public const AMOUNT = 'amount';
    public const PERCENTAGE = 'percentage';

    private readonly string $value;

    /**
     * @throws ProductChoiceConstraintException
     */
    public function __construct(
        string $value
    )
    {
        $value = strtolower($value);
        $value = trim($value);

        self::assertIsValidType($value);

        $this->value = $value;
    }

    /**
     * @throws ProductChoiceConstraintException
     */
    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public static function amount(): self
    {
        return new self(self::AMOUNT);
    }

    public static function percentage(): self
    {
        return new self(self::PERCENTAGE);
    }

    final public function getValue(): string
    {
        return $this->__toString();
    }

    public function equals(ReductionType $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * @throws ProductChoiceConstraintException
     */
    private static function assertIsValidType(string $value): void
    {
        if (false === in_array($value, [self::AMOUNT, self::PERCENTAGE], true)) {
            throw new ProductChoiceConstraintException(
                sprintf('Invalid product choice reduction type "%s".', var_export($value, true)),
                ProductChoiceConstraintException::INVALID_REDUCTION_TYPE);
        }
    }
}
