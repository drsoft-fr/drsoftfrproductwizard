<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\Domain\ValueObject\Configurator;

use DrSoftFr\Module\ProductWizard\Exception\Configurator\ConfiguratorConstraintException;

final class ConfiguratorId
{
    private readonly int $value;

    /**
     * @throws ConfiguratorConstraintException
     */
    public function __construct(int $value)
    {
        self::assertIntegerIsGreaterThanZero($value);

        $this->value = $value;
    }

    /**
     * @throws ConfiguratorConstraintException
     */
    public static function fromInt(int $value): self
    {
        return new self($value);
    }

    final public function getValue(): int
    {
        return $this->value;
    }

    public function equals(ConfiguratorId $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return (string)$this->value;
    }

    /**
     * @throws ConfiguratorConstraintException
     */
    private static function assertIntegerIsGreaterThanZero(int $value): void
    {
        if (0 >= $value) {
            throw new ConfiguratorConstraintException(
                sprintf('Invalid configurator id "%s".', var_export($value, true)),
                ConfiguratorConstraintException::INVALID_ID);
        }
    }
}
