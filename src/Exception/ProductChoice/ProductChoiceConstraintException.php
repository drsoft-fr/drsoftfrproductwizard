<?php

namespace DrSoftFr\Module\ProductWizard\Exception\ProductChoice;

/**
 * Is thrown when ProductChoice constraints are violated
 */
class ProductChoiceConstraintException extends ProductChoiceException
{
    public const INVALID_LABEL = 1;
    public const INVALID_FORCED_QUANTITY = 2;
    public const INVALID_IS_DEFAULT = 3;
    public const INVALID_REDUCTION = 4;
    public const INVALID_REDUCTION_TAX = 5;
    public const INVALID_REDUCTION_TYPE = 6;
}
