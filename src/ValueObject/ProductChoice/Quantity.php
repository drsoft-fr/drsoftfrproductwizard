<?php

namespace DrSoftFr\Module\ProductWizard\ValueObject\ProductChoice;

final class Quantity
{
    /**
     * @param int $value
     */
    public function __construct(
        private readonly int $value
    )
    {
    }

    public function getValue(): int
    {
        return $this->value;
    }
}
