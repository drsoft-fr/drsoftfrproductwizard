<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\ValueObject\ProductChoice;

final class QuantityRuleOffset
{
    /**
     * @param int $value
     */
    public function __construct(
        private readonly int $value
    )
    {
    }

    public static function fromInt(int $value): self
    {
        return new self($value);
    }

    final public function getValue(): int
    {
        return $this->value;
    }

    public function equals(QuantityRuleOffset $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return (string)$this->value;
    }
}
