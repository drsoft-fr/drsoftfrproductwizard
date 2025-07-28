<?php

namespace DrSoftFr\Module\ProductWizard\Exception\Configurator;

/**
 * Is thrown when Configurator constraints are violated
 */
class ConfiguratorConstraintException extends ConfiguratorException
{
    public const INVALID_ID = 1;
}
