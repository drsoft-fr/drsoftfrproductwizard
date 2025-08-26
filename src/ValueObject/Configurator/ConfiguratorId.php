<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\ValueObject\Configurator;

use DrSoftFr\Module\ProductWizard\Exception\Configurator\ConfiguratorConstraintException;

/**
 * Class provides configurator id
 */
final class ConfiguratorId
{
    private readonly int $value;

    /**
     * @throws ConfiguratorConstraintException
     */
    public function __construct(int $value)
    {
        $this->assertIntegerIsGreaterThanZero($value);

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
    private function assertIntegerIsGreaterThanZero(int $value): void
    {
        if (0 >= $value) {
            throw new ConfiguratorConstraintException(
                sprintf('Invalid configurator id "%s".', var_export($value, true)),
                ConfiguratorConstraintException::INVALID_ID);
        }
    }
}
