<?php

namespace DrSoftFr\Module\ProductWizard\Application\Exception\CartItem;

class CartItemConstraintException extends CartItemException
{
    public const INVALID_PRODUCT_CHOICE_ID = 1;
    public const INVALID_STEP_ID = 2;
    public const INVALID_PRODUCT_ID = 3;
    public const INVALID_COMBINATION_ID = 4;
    public const INVALID_QUANTITY = 5;
    public const INVALID_PRODUCT_NAME = 6;
    public const INVALID_PRODUCT_AVAILABILITY = 7;
}
