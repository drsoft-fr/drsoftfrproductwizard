<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\ValueObject\ProductChoice;

use DrSoftFr\Module\ProductWizard\Exception\ProductChoice\ProductChoiceConstraintException;
use DrSoftFr\Module\ProductWizard\Exception\Step\StepConstraintException;
use DrSoftFr\Module\ProductWizard\ValueObject\Step\StepId;

final class DisplayCondition
{
    public function __construct(
        private readonly StepId          $step,
        private readonly ProductChoiceId $choice,
    )
    {
    }

    /**
     * @throws ProductChoiceConstraintException
     * @throws StepConstraintException
     */
    public static function fromArray(array $arr): self
    {
        return new self(
            StepId::fromInt((int)$arr['step']),
            ProductChoiceId::fromInt((int)$arr['choice']),
        );
    }

    final public function getValue(): array
    {
        return [
            'step' => $this->step->getValue(),
            'choice' => $this->choice->getValue(),
        ];
    }

    public function equals(DisplayCondition $other): bool
    {
        return $this->step->equals($other->step) && $this->choice->equals($other->choice);
    }

    public function __toString(): string
    {
        return sprintf('step:%d;choice:%d', $this->step->getValue(), $this->choice->getValue());
    }
}
