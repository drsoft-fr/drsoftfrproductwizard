<?php

namespace DrSoftFr\Module\ProductWizard\ValueObject\ProductChoice;

use DrSoftFr\Module\ProductWizard\Exception\ProductChoice\ProductChoiceConstraintException;

final class DisplayCondition
{
    private int $step;
    private int $choice;

    /**
     * @throws ProductChoiceConstraintException
     */
    public function __construct(
        int $step,
        int $choice,
    )
    {
        $this->assertIntegerIsGreaterThanZero($step, 'step');
        $this->assertIntegerIsGreaterThanZero($choice, 'product choice');

        $this->step = $step;
        $this->choice = $choice;
    }

    /**
     * @throws ProductChoiceConstraintException
     */
    public static function fromArray(array $arr): self
    {
        return new self(
            $arr['step'],
            $arr['choice'],
        );
    }

    public function getValue(): array
    {
        return [
            'step' => $this->step,
            'choice' => $this->choice,
        ];
    }

    /**
     * @throws ProductChoiceConstraintException
     */
    private function assertIntegerIsGreaterThanZero(int $value, string $message = ''): void
    {
        if (0 >= $value) {
            if ('step' === $message) {
                throw new ProductChoiceConstraintException(
                    sprintf('Invalid step id "%s".', var_export($value, true)),
                    ProductChoiceConstraintException::INVALID_DISPLAY_CONDITION_STEP);
            } else {
                throw new ProductChoiceConstraintException(
                    sprintf('Invalid product choice id "%s".', var_export($value, true)),
                    ProductChoiceConstraintException::INVALID_DISPLAY_CONDITION_CHOICE);
            }
        }
    }
}
