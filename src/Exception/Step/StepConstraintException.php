<?php

namespace DrSoftFr\Module\ProductWizard\Exception\Step;

/**
 * Is thrown when Step constraints are violated
 */
class StepConstraintException extends StepException
{
    public const INVALID_LABEL = 1;
    public const INVALID_POSITION = 2;
    public const INVALID_PRODUCT_CHOICES = 3;
}
