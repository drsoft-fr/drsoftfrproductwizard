<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\ValueObject\ProductChoice;

use DrSoftFr\Module\ProductWizard\Exception\ProductChoice\ProductChoiceConstraintException;

/**
 * Class provides product choice id
 */
final class ProductChoiceId
{
    private readonly int $value;

    /**
     * @throws ProductChoiceConstraintException
     */
    public function __construct(int $value)
    {
        $this->assertIntegerIsGreaterThanZero($value);

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

    public function equals(ProductChoiceId $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return (string)$this->value;
    }

    /**
     * @param int $value
     *
     * @throws ProductChoiceConstraintException
     */
    private function assertIntegerIsGreaterThanZero(int $value): void
    {
        if (0 >= $value) {
            throw new ProductChoiceConstraintException(
                sprintf('Invalid product choice id "%s".', var_export($value, true)),
                ProductChoiceConstraintException::INVALID_ID);
        }
    }
}
