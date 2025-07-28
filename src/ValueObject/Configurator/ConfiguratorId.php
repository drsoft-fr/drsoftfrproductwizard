<?php

namespace DrSoftFr\Module\ProductWizard\ValueObject\Configurator;

use DrSoftFr\Module\ProductWizard\Exception\Configurator\ConfiguratorConstraintException;

/**
 * Class provides configurator id
 */
final class ConfiguratorId
{
    /**
     * @var int
     */
    private $value;

    /**
     * @param int $value
     *
     * @throws ConfiguratorConstraintException
     */
    public function __construct(int $value)
    {
        $this->assertIntegerIsGreaterThanZero($value);
        $this->value = $value;
    }

    /**
     * @return int
     */
    public function getValue(): int
    {
        return $this->value;
    }

    /**
     * @param int $value
     *
     * @throws ConfiguratorConstraintException
     */
    private function assertIntegerIsGreaterThanZero(int $value)
    {
        if (0 >= $value) {
            throw new ConfiguratorConstraintException(
                sprintf('Invalid configurator id "%s".', var_export($value, true)),
                ConfiguratorConstraintException::INVALID_ID);
        }
    }
}
