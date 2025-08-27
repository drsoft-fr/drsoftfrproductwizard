<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\Domain\ValueObject\ProductChoice;

use DrSoftFr\Module\ProductWizard\Exception\ProductChoice\ProductChoiceConstraintException;
use DrSoftFr\Module\ProductWizard\Exception\Step\StepConstraintException;

final class QuantityRule
{
    private readonly QuantityRuleRange $min;
    private readonly QuantityRuleRange $max;

    /**
     * @throws ProductChoiceConstraintException
     */
    private function __construct(
        private readonly QuantityRuleMode   $mode,
        private readonly QuantityRuleLocked $locked,
        /** @var QuantityRuleSource[] */
        private readonly array              $sources,
        private readonly QuantityRuleOffset $offset,
        QuantityRuleRange                   $min,
        QuantityRuleRange                   $max,
        private readonly QuantityRuleRound  $round
    )
    {
        self::assertRangeIsValid($min->getValue(), $max->getValue());

        $this->min = $min;
        $this->max = $max;
    }

    /**
     * @throws StepConstraintException
     * @throws ProductChoiceConstraintException
     */
    public static function fromArray(array $data): self
    {
        $mode = QuantityRuleMode::fromString((string)($data['mode'] ?? QuantityRuleMode::DEFAULT_MODE));
        $locked = QuantityRuleLocked::fromBool((bool)($data['locked'] ?? QuantityRuleLocked::DEFAULT_LOCKED));

        $sources = [];
        $rawSources = is_array($data['sources'] ?? null) ? $data['sources'] : [];
        foreach ($rawSources as $item) {
            if (false === is_array($item)) {
                continue;
            }

            $sources[] = QuantityRuleSource::fromArray($item);
        }

        $offset = QuantityRuleOffset::fromInt((int)($data['offset'] ?? QuantityRuleOffset::DEFAULT_OFFSET));
        $min = QuantityRuleRange::fromNullOrInt(isset($data['min']) && is_numeric($data['min']) ? (int)$data['min'] : null);
        $max = QuantityRuleRange::fromNullOrInt(isset($data['max']) && is_numeric($data['max']) ? (int)$data['max'] : null);
        $round = QuantityRuleRound::fromString((string)($data['round'] ?? QuantityRuleRound::DEFAULT_ROUND));

        return new self(
            $mode,
            $locked,
            $sources,
            $offset,
            $min,
            $max,
            $round
        );
    }

    final public function getValue(): array
    {
        return [
            'mode' => $this->mode->getValue(),
            'locked' => $this->locked->getValue(),
            'sources' => array_values(array_map(
                fn(QuantityRuleSource $s) => $this->normalizeSourceForExport($s),
                $this->sources
            )),
            'offset' => $this->offset->getValue(),
            'min' => $this->min->getValue(),
            'max' => $this->max->getValue(),
            'round' => $this->round->getValue(),
        ];
    }

    /**
     * @return array{step:int, coeff:float}
     */
    private function normalizeSourceForExport(QuantityRuleSource $s): array
    {
        return $s->toArray();
    }

    /**
     * @throws ProductChoiceConstraintException
     */
    private static function assertRangeIsValid(?int $min, ?int $max): void
    {
        if (null !== $min && null !== $max && $min > $max) {
            throw new ProductChoiceConstraintException(
                sprintf('Invalid quantity range: min "%s" is greater than max "%s".', var_export($min, true), var_export($max, true)),
                ProductChoiceConstraintException::INVALID_QUANTITY_RULE_MIN_MAX_LOGIC
            );
        }
    }
}
