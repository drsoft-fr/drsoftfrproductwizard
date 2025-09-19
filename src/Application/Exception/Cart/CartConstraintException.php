<?php

namespace DrSoftFr\Module\ProductWizard\Application\Exception\Cart;

class CartConstraintException extends CartException
{
    public const INVALID_CONFIGURATOR_ID = 1;
    public const INVALID_CART_ITEMS_IS_EMPTY = 2;
}
