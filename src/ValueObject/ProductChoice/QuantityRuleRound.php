<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\ValueObject\ProductChoice;

use DrSoftFr\Module\ProductWizard\Exception\ProductChoice\ProductChoiceConstraintException;

final class QuantityRuleRound
{
    public const NONE = 'none';
    public const FLOOR = 'floor';
    public const CEIL = 'ceil';
    public const ROUND = 'round';
    public const DEFAULT_ROUND = self::NONE;
    public const ALLOWED_ROUNDS = [
        self::NONE,
        self::FLOOR,
        self::CEIL,
        self::ROUND,
    ];

    private readonly string $value;

    /**
     * @throws ProductChoiceConstraintException
     */
    public function __construct(
        string $value = self::DEFAULT_ROUND
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

    public static function floor(): self
    {
        return new self(self::FLOOR);
    }

    public static function ceil(): self
    {
        return new self(self::CEIL);
    }

    public static function round(): self
    {
        return new self(self::ROUND);
    }

    final public function getValue(): string
    {
        return $this->__toString();
    }

    public function equals(QuantityRuleRound $other): bool
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
        if (false === in_array($value, self::ALLOWED_ROUNDS, true)) {
            throw new ProductChoiceConstraintException(
                sprintf('Invalid product choice quantity rule round "%s".', var_export($value, true)),
                ProductChoiceConstraintException::INVALID_QUANTITY_RULE_ROUND);
        }
    }
}
