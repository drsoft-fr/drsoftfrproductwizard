import { z } from 'zod'
import { StepSchema } from '@/js/admin/configurator/form/validation/stepSchema'

export const ConfiguratorSchema = z
  .object({
    id: z.union([z.number().int().nullable(), z.null()]).nullable(),
    name: z.string().trim().min(1, 'The name of the scenario is mandatory.'),
    description: z.string().optional().nullable(),
    active: z.boolean(),
    reduction: z.number().nonnegative().optional().default(0),
    reduction_tax: z.boolean().optional().default(true),
    reduction_type: z
      .union([z.literal('amount'), z.literal('percentage')])
      .optional()
      .default('amount'),
    steps: z.array(StepSchema).min(1, 'At least one step is required.'),
  })
  .strict()
  .superRefine((cfg, ctx) => {
    // Steps must have unique, gapless positions starting at 0
    const seenPositions = new Set()
    cfg.steps.forEach((step, idx) => {
      const currentPos = step.position

      if (seenPositions.has(currentPos)) {
        ctx.addIssue({
          code: z.ZodIssueCode.custom,
          message: `Two Steps share the same position (${currentPos}).`,
          path: ['steps', idx, 'position'],
        })
      }

      seenPositions.add(currentPos)

      step.product_choices.forEach((choice, cIdx) => {
        // > Quantity logic
        if (
          null !== choice.product_id &&
          'none' === choice.quantity_rule.mode
        ) {
          ctx.addIssue({
            code: z.ZodIssueCode.custom,
            message: `Step "${step.label}" choice "${choice.label}": Quantity rule must be set when product is selected.`,
            path: ['steps', idx, 'product_choices', cIdx, 'quantity_rule'],
          })
        }
        // < Quantity logic

        // > Condition logic
        const conditions = choice.display_conditions || []

        conditions.forEach((cond, k) => {
          // Find referenced step
          const refStep = cfg.steps.find(
            (s) => String(s.id) === String(cond.step),
          )

          if (!refStep) {
            ctx.addIssue({
              code: z.ZodIssueCode.custom,
              message: `Step "${step.label}" choice "${choice.label}": The condition #${k + 1} reference a non-existent step.`,
              path: [
                'steps',
                idx,
                'product_choices',
                cIdx,
                'display_conditions',
                k,
                'step',
              ],
            })

            return
          }

          if (Number(refStep.position) >= Number(currentPos)) {
            ctx.addIssue({
              code: z.ZodIssueCode.custom,
              message: `Step "${step.label}" choice "${choice.label}": The condition #${k + 1} must reference a previous step.`,
              path: [
                'steps',
                idx,
                'product_choices',
                cIdx,
                'display_conditions',
                k,
                'step',
              ],
            })
          }

          const refChoice = Array.isArray(refStep.product_choices)
            ? refStep.product_choices.find(
              (c) => String(c.id) === String(cond.choice),
            )
            : null

          if (!refChoice) {
            ctx.addIssue({
              code: z.ZodIssueCode.custom,
              message: `Step "${step.label}" choice "${choice.label}": The condition #${k + 1} reference a choice that does not exist in the target step.`,
              path: [
                'steps',
                idx,
                'product_choices',
                cIdx,
                'display_conditions',
                k,
                'choice',
              ],
            })
          }
        })
        // < Condition logic
      })
    })

    // > Positions should cover 0..n-1 without gaps
    if (cfg.steps.length > 0) {
      for (let i = 0; i < cfg.steps.length; i++) {
        if (!seenPositions.has(i)) {
          ctx.addIssue({
            code: z.ZodIssueCode.custom,
            message:
              'The positions of the steps must be continuous starting from 0 (no gaps).',
            path: ['steps'],
          })

          break
        }
      }
    }
    // < Positions should cover 0..n-1 without gaps

    // > Validate reduction percentage ranges based on type
    if (
      cfg.reduction_type === 'percentage' &&
      (cfg.reduction < 0 || cfg.reduction > 100)
    ) {
      ctx.addIssue({
        code: z.ZodIssueCode.custom,
        message:
          'Configurator: The percentage reduction must be between 0 and 100.',
        path: ['reduction'],
      })
    }
    // < Validate reduction percentage ranges based on type
  })
