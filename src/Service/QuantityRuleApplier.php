<?php

declare(strict_types=1);

namespace DrSoftFr\Module\ProductWizard\Service;

use DrSoftFr\Module\ProductWizard\ValueObject\ProductChoice\QuantityRule;

final class QuantityRuleApplier
{
    /**
     * @param array<int,int|float> $quantitiesByStepId ex: [stepId => qty]
     */
    public function resolveQuantity(QuantityRule $rule, array $quantitiesByStepId, int $userQtyFallback = 1): int
    {
        $rule = $rule->getValue();

        if ($rule['mode'] === QuantityRule::MODE_NONE) {
            return $userQtyFallback;
        }

        if ($rule['mode'] === QuantityRule::MODE_FIXED) {
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
            QuantityRule::ROUND_FLOOR => (int)floor($qty),
            QuantityRule::ROUND_CEIL => (int)ceil($qty),
            QuantityRule::ROUND_ROUND => (int)round($qty),
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
