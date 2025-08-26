<?php

namespace DrSoftFr\Module\ProductWizard\Exception\ProductChoice;

/**
 * Is thrown when ProductChoice constraints are violated
 */
class ProductChoiceConstraintException extends ProductChoiceException
{
    public const INVALID_LABEL = 1;
    public const INVALID_QUANTITY_RULE = 2;
    public const INVALID_IS_DEFAULT = 3;
    public const INVALID_REDUCTION = 4;
    public const INVALID_REDUCTION_TAX = 5;
    public const INVALID_REDUCTION_TYPE = 6;
    public const INVALID_QUANTITY_RULE_SOURCES = 7;
    public const INVALID_QUANTITY_RULE_OFFSET = 8;
    public const INVALID_QUANTITY_RULE_MIN = 9;
    public const INVALID_QUANTITY_RULE_MAX = 10;
    public const INVALID_QUANTITY_RULE_MIN_MAX_LOGIC = 11;
    public const INVALID_DISPLAY_CONDITION_STEP = 12;
    public const INVALID_DISPLAY_CONDITION_CHOICE = 13;
    public const INVALID_QUANTITY_RULE_MODE = 14;
    public const INVALID_QUANTITY_RULE_LOCKED = 15;
    public const INVALID_QUANTITY_RULE_ROUND = 16;
    public const INVALID_ID = 17;
    public const INVALID_QUANTITY = 18;
}
