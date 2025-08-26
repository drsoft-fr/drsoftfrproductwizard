<?php

namespace DrSoftFr\Module\ProductWizard\ValueObject\ProductChoice;

final class Reduction
{
    public function __construct(
        private readonly float $value
    )
    {
    }

    public function getValue(): float
    {
        return $this->value;
    }
}
