<?php

namespace DrSoftFr\Module\ProductWizard\ValueObject\ProductChoice;

final class ReductionTax
{
    public function __construct(
        private readonly bool $value
    )
    {
    }

    public function getValue(): bool
    {
        return $this->value;
    }
}
