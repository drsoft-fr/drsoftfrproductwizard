<?php

namespace DrSoftFr\Module\ProductWizard\ValueObject\ProductChoice;

use DrSoftFr\Module\ProductWizard\Exception\ProductChoice\ProductChoiceConstraintException;

/**
 * Class provides product choice id
 */
final class ProductChoiceId
{
    private int $value;

    /**
     * @param int $value
     *
     * @throws ProductChoiceConstraintException
     */
    public function __construct(int $value)
    {
        $this->assertIntegerIsGreaterThanZero($value);
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
    private function assertIntegerIsGreaterThanZero(int $value): void
    {
        if (0 >= $value) {
            throw new ProductChoiceConstraintException(
                sprintf('Invalid configurator id "%s".', var_export($value, true)),
                ProductChoiceConstraintException::INVALID_ID);
        }
    }
}
