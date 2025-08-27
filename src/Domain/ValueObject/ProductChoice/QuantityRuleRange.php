<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\Domain\ValueObject\ProductChoice;

use DrSoftFr\Module\ProductWizard\Exception\ProductChoice\ProductChoiceConstraintException;

final class QuantityRuleRange
{
    public const DEFAULT_RANGE = null;

    private readonly ?int $value;

    /**
     * @throws ProductChoiceConstraintException
     */
    public function __construct(
        ?int $value = self::DEFAULT_RANGE
    )
    {
        self::assertRangeIsValid($value);

        $this->value = $value;
    }

    /**
     * @throws ProductChoiceConstraintException
     */
    public static function fromNullOrInt(
        ?int $value = self::DEFAULT_RANGE
    ): self
    {
        return new self($value);
    }

    final public function getValue(): ?int
    {
        return $this->value;
    }

    public function equals(QuantityRuleRange $other): bool
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
    private static function assertRangeIsValid(?int $value): void
    {
        if (null !== $value && $value < 0) {
            throw new ProductChoiceConstraintException(
                sprintf('Invalid range quantity "%s". Must be >= 0 or null.', var_export($value, true)),
                ProductChoiceConstraintException::INVALID_QUANTITY_RULE_RANGE
            );
        }
    }
}
