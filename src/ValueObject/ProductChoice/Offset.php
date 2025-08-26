<?php

namespace DrSoftFr\Module\ProductWizard\ValueObject\ProductChoice;

final class Offset
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
