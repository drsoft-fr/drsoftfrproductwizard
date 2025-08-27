<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\Service;

use DrSoftFr\Module\ProductWizard\ValueObject\ProductChoice\QuantityRule;
use DrSoftFr\Module\ProductWizard\ValueObject\ProductChoice\QuantityRuleMode;
use DrSoftFr\Module\ProductWizard\ValueObject\ProductChoice\QuantityRuleRound;

final class QuantityRuleApplier
{
    /**
     * @param array<int,int|float> $quantitiesByStepId ex: [stepId => qty]
     */
    public function resolveQuantity(QuantityRule $rule, array $quantitiesByStepId, int $userQtyFallback = 1): int
    {
        $rule = $rule->getValue();

        if ($rule['mode'] === QuantityRuleMode::NONE) {
            return $userQtyFallback;
        }

        if ($rule['mode'] === QuantityRuleMode::FIXED) {
            $qty = $rule['offset'];
        } else {
            $sum = 0.0;

            foreach ($rule['sources'] as $term) {
                $srcQty = (float)($quantitiesByStepId[$term['step']] ?? 0);
                $sum += ((float)($term['coeff'] ?? 1.0)) * $srcQty;
            }
            $qty = $sum + (float)$rule['offset'];
        }

        $qty = match ($rule['round']) {
            QuantityRuleRound::FLOOR => (int)floor($qty),
            QuantityRuleRound::CEIL => (int)ceil($qty),
            QuantityRuleRound::ROUND => (int)round($qty),
            default => (int)$qty,
        };

        if ($rule['min'] !== null) {
            $qty = max($rule['min'], $qty);
        }

        if ($rule['max'] !== null) {
            $qty = min($rule['max'], $qty);
        }

        return (int)max($userQtyFallback, $qty);
    }
}
