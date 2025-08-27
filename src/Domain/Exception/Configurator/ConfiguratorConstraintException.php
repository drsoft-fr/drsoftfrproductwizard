<?php

namespace DrSoftFr\Module\ProductWizard\Domain\Exception\Configurator;

/**
 * Is thrown when Configurator constraints are violated
 */
class ConfiguratorConstraintException extends ConfiguratorException
{
    public const INVALID_ID = 1;

    public const INVALID_NAME = 2;
    public const INVALID_STEPS = 3;
    public const INVALID_REDUCTION = 4;
    public const INVALID_REDUCTION_TAX = 5;
    public const INVALID_REDUCTION_TYPE = 6;
}
