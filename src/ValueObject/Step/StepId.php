<?php

namespace DrSoftFr\Module\ProductWizard\ValueObject\Step;

use DrSoftFr\Module\ProductWizard\Exception\Step\StepConstraintException;

/**
 * Class provides step id
 */
final class StepId
{
    private int $value;

    /**
     * @param int $value
     *
     * @throws StepConstraintException
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
     * @throws StepConstraintException
     */
    private function assertIntegerIsGreaterThanZero(int $value): void
    {
        if (0 >= $value) {
            throw new StepConstraintException(
                sprintf('Invalid step id "%s".', var_export($value, true)),
                StepConstraintException::INVALID_ID);
        }
    }
}
