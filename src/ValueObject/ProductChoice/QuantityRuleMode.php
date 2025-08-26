<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\ValueObject\ProductChoice;

use DrSoftFr\Module\ProductWizard\Exception\ProductChoice\ProductChoiceConstraintException;

final class QuantityRuleMode
{
    public const NONE = 'none';
    public const FIXED = 'fixed';
    public const EXPRESSION = 'expression';
    public const DEFAULT_MODE = self::FIXED;
    public const ALLOWED_MODES = [
        self::NONE,
        self::FIXED,
        self::EXPRESSION,
    ];

    private readonly string $value;

    /**
     * @throws ProductChoiceConstraintException
     */
    public function __construct(
        string $value = self::DEFAULT_MODE
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

    public static function none(): self
    {
        return new self(self::NONE);
    }

    public static function fixed(): self
    {
        return new self(self::FIXED);
    }

    public static function expression(): self
    {
        return new self(self::EXPRESSION);
    }

    final public function getValue(): string
    {
        return $this->__toString();
    }

    public function equals(QuantityRuleMode $other): bool
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
        if (false === in_array($value, self::ALLOWED_MODES, true)) {
            throw new ProductChoiceConstraintException(
                sprintf('Invalid product choice quantity rule mode "%s".', var_export($value, true)),
                ProductChoiceConstraintException::INVALID_QUANTITY_RULE_MODE);
        }
    }
}
