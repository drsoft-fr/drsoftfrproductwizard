<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\Domain\ValueObject\ProductChoice;

final class Reduction
{
    public function __construct(
        private readonly float $value
    )
    {
    }

    public static function fromFloat(float $value): self
    {
        return new self($value);
    }

    final public function getValue(): float
    {
        return $this->value;
    }

    public function equals(Reduction $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return (string)$this->value;
    }
}
