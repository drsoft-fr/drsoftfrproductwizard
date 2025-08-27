<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\Domain\ValueObject\ProductChoice;

final class ReductionTax
{
    public function __construct(
        private readonly bool $value
    )
    {
    }

    public static function fromBool(bool $value): self
    {
        return new self($value);
    }

    final public function getValue(): bool
    {
        return $this->value;
    }

    public function equals(ReductionTax $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return (string)$this->value;
    }
}
