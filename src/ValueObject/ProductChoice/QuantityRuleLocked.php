<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\ValueObject\ProductChoice;

final class QuantityRuleLocked
{
    public const DEFAULT_LOCKED = false;

    public function __construct(
        private readonly bool $value = self::DEFAULT_LOCKED
    )
    {
    }

    public static function fromBool(
        bool $value = self::DEFAULT_LOCKED
    ): self
    {
        return new self($value);
    }

    final public function getValue(): bool
    {
        return $this->value;
    }

    public function equals(QuantityRuleLocked $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return (string)$this->value;
    }
}
