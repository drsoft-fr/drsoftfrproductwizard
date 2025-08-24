export function useQuantityRule() {
  const _findStepIndexByChoiceId = (steps, choiceId) => {
    for (let i = 0; i < steps.length; i++) {
      const list = steps[i].choices || []

      if (false === list.some((c) => c.id === choiceId)) {
        continue
      }

      return i
    }

    return -1
  }

  const _selectedQty = (selections, choiceId) => {
    const sel = selections.find((s) => Number(s.id) === Number(choiceId))

    return sel ? Number(sel.quantity || 0) : 0
  }

  const _clampAndRound = (val, rule) => {
    let v = Number(val || 0)

    if (!rule) {
      return Math.max(0, Math.floor(v))
    }

    switch (rule.round) {
      case 'floor':
        v = Math.floor(v)

        break
      case 'ceil':
        v = Math.ceil(v)

        break
      case 'round':
        v = Math.round(v)

        break
    }

    if (rule.min !== null) {
      v = Math.max(rule.min, v)
    }

    if (rule.max !== null) {
      v = Math.min(rule.max, v)
    }

    return Math.max(0, v)
  }

  const _computeRuleQty = (rule, selections) => {
    if (!rule) {
      return null
    }

    switch (rule.mode) {
      case 'fixed':
        if (false === rule.locked) {
          return null
        }

        return rule.offset
      case 'expression':
        // expression: Σ(coeff_i * qty(selected source_i)) + offset
        const sum = (rule.sources || []).reduce((acc, s) => {
          const q = _selectedQty(selections, s.choice)

          return acc + (s.coeff ?? 1) * q
        }, 0)

        return _clampAndRound(sum + (rule.offset ?? 0), rule)
      case 'none':
      default:
        return null
    }
  }

  const applyRulesFromStep = (steps, selections) => {
    for (const step of steps) {
      for (const choice of step.choices || []) {
        const rule = choice.quantityRule || null

        if (null === rule) {
          continue
        }

        const q = _computeRuleQty(rule, selections)

        if (q === null) {
          continue
        }

        choice.quantity = q
      }
    }
  }

  const onSelectedQuantityChanged = (steps, choice, selections) => {
    const k = _findStepIndexByChoiceId(steps, choice.id)
    const sel = selections.find((s) => s.id === choice.id)

    if (sel) {
      sel.quantity = Number(choice.quantity || 0)
    }

    if (k >= 0) {
      applyRulesFromStep(steps, selections)
    }
  }

  return {
    applyRulesFromStep,
    onSelectedQuantityChanged,
  }
}
