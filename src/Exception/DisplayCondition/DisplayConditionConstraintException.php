<?php

namespace DrSoftFr\Module\ProductWizard\Exception\DisplayCondition;

/**
 * Is thrown when DisplayCondition constraints are violated
 */
class DisplayConditionConstraintException extends DisplayConditionException
{
    public const INVALID_STEP = 1;
    public const INVALID_CHOICE = 2;
}
