<?php

namespace DrSoftFr\Module\ProductWizard\ValueObject\ProductChoice;

use DrSoftFr\Module\ProductWizard\Exception\ProductChoice\ProductChoiceConstraintException;

final class Quantity
{
    private int $value;

    /**
     * @param int $value
     *
     * @throws ProductChoiceConstraintException
     */
    public function __construct(int $value)
    {
        $this->assertIntegerIsPositive($value);
        $this->value = $value;
    }

    /**
     * @return int
     */
    public function getValue(): int
    {
        return $this->value;
    }

    /**
     * @param int $value
     *
     * @throws ProductChoiceConstraintException
     */
    private function assertIntegerIsPositive(int $value): void
    {
        if (0 >= $value) {
            throw new ProductChoiceConstraintException(
                sprintf('Invalid product choice quantity "%s".', var_export($value, true)),
                ProductChoiceConstraintException::INVALID_QUANTITY);
        }
    }
}
