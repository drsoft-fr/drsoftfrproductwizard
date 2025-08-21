import { z } from 'zod'

// Zod schema for a Condition
const ConditionSchema = z
  .object({
    step: z.union([z.number().int().nonnegative(), z.string().min(1)]),
    choice: z.union([z.number().int().nonnegative(), z.string().min(1)]),
    is_virtual: z.boolean().optional(),
  })
  .strict()

// Zod schema for a Product Choice
const ProductChoiceSchema = z
  .object({
    id: z.union([z.number().int(), z.string().min(1)]),
    label: z.string().trim().min(1, 'The wording of a choice is mandatory.'),
    active: z.boolean(),
    is_default: z.boolean().optional().default(false),
    product_id: z
      .union([z.number().int().nullable(), z.null()])
      .nullable()
      .optional(),
    allow_quantity: z.boolean().optional().default(true),
    forced_quantity: z
      .union([z.number().int().min(1), z.null(), z.undefined()])
      .optional(),
    min_quantity: z
      .union([z.number().int().min(1), z.null(), z.undefined()])
      .optional(),
    max_quantity: z
      .union([z.number().int().min(1), z.null(), z.undefined()])
      .optional(),
    reduction: z.number().nonnegative().optional().default(0),
    reduction_tax: z.boolean().optional().default(true),
    reduction_type: z.union([z.literal('amount'), z.literal('percentage')]).optional().default('amount'),
    display_conditions: z.array(ConditionSchema).optional().default([]),
    is_virtual: z.boolean().optional(),
  })
  .strict()

// Zod schema for a Step
const StepSchema = z
  .object({
    id: z.union([z.number().int(), z.string().min(1)]),
    label: z.string().trim().min(1, 'The wording is mandatory.'),
    position: z.number().int().nonnegative({ message: 'Invalid position.' }),
    active: z.boolean(),
    reduction: z.number().nonnegative().optional().default(0),
    reduction_tax: z.boolean().optional().default(true),
    reduction_type: z.union([z.literal('amount'), z.literal('percentage')]).optional().default('amount'),
    product_choices: z
      .array(ProductChoiceSchema)
      .min(1, 'At least one choice by step is required.'),
    is_virtual: z.boolean().optional(),
  })
  .strict()

// Main Configurator Zod schema
export const ConfiguratorSchema = z
  .object({
    id: z.union([z.number().int().nullable(), z.null()]).nullable(),
    name: z.string().trim().min(1, 'The name of the scenario is mandatory.'),
    active: z.boolean(),
    reduction: z.number().nonnegative().optional().default(0),
    reduction_tax: z.boolean().optional().default(true),
    reduction_type: z.union([z.literal('amount'), z.literal('percentage')]).optional().default('amount'),
    steps: z.array(StepSchema).min(1, 'At least one step is required.'),
  })
  .strict()
  .superRefine((cfg, ctx) => {
    // Steps must have unique, gapless positions starting at 0
    const seenPositions = new Set()
    cfg.steps.forEach((step, idx) => {
      if (seenPositions.has(step.position)) {
        ctx.addIssue({
          code: z.ZodIssueCode.custom,
          message: `Two Steps share the same position (${step.position}).`,
          path: ['steps', idx, 'position'],
        })
      }

      seenPositions.add(step.position)

      // Validate default choice count
      const defaultCount = step.product_choices.reduce(
        (acc, c) => acc + (c.is_default ? 1 : 0),
        0,
      )

      // Step-level reduction % range
      if (step.reduction_type === 'percentage' && (step.reduction < 0 || step.reduction > 100)) {
        ctx.addIssue({
          code: z.ZodIssueCode.custom,
          message: `Step "${step.label}": The percentage reduction must be between 0 and 100.`,
          path: ['steps', idx, 'reduction'],
        })
      }

      if (defaultCount > 1) {
        ctx.addIssue({
          code: z.ZodIssueCode.custom,
          message: `Step "${step.label}": There can only be one default choice..`,
          path: ['steps', idx, 'product_choices'],
        })
      }

      // Quantity logic
      step.product_choices.forEach((choice, cIdx) => {
        // Case 1: quantity selection disabled => forced_quantity is required (int >= 1)
        if (choice.allow_quantity === false) {
          const fq = choice.forced_quantity
          const isValid = Number.isInteger(fq) && Number(fq) >= 1

          if (!isValid) {
            ctx.addIssue({
              code: z.ZodIssueCode.custom,
              message: `Step "${step.label}": The choice "${choice.label}" must have a valid quantity (integer >= 1) when quantity selection is disabled.`,
              path: ['steps', idx, 'product_choices', cIdx, 'forced_quantity'],
            })
          }
        } else {
          // Case 2: quantity selection allowed => optional min/max constraints
          const minQ = choice.min_quantity
          const maxQ = choice.max_quantity

          const hasMin = minQ !== null && minQ !== undefined
          const hasMax = maxQ !== null && maxQ !== undefined

          if (hasMin) {
            const validMin = Number.isInteger(minQ) && Number(minQ) >= 1
            if (!validMin) {
              ctx.addIssue({
                code: z.ZodIssueCode.custom,
                message: `Step "${step.label}" choice "${choice.label}": Minimal quantity must be an integer >= 1.`,
                path: ['steps', idx, 'product_choices', cIdx, 'min_quantity'],
              })
            }
          }

          if (hasMax) {
            const validMax = Number.isInteger(maxQ) && Number(maxQ) >= 1
            if (!validMax) {
              ctx.addIssue({
                code: z.ZodIssueCode.custom,
                message: `Step "${step.label}" choice "${choice.label}": Maximal quantity must be an integer >= 1.`,
                path: ['steps', idx, 'product_choices', cIdx, 'max_quantity'],
              })
            }
          }

          if (hasMin && hasMax && Number(minQ) > Number(maxQ)) {
            ctx.addIssue({
              code: z.ZodIssueCode.custom,
              message: `Step "${step.label}" choice "${choice.label}": Minimal quantity cannot be greater than maximal quantity.`,
              path: ['steps', idx, 'product_choices', cIdx, 'min_quantity'],
            })
          }
        }

        // Reduction % range at choice level
        if (choice.reduction_type === 'percentage' && (choice.reduction < 0 || choice.reduction > 100)) {
          ctx.addIssue({
            code: z.ZodIssueCode.custom,
            message: `Step "${step.label}" choice "${choice.label}": The percentage reduction must be between 0 and 100.`,
            path: ['steps', idx, 'product_choices', cIdx, 'reduction'],
          })
        }
      })
    })

    // Positions should cover 0..n-1 without gaps
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

    // Validate reduction percentage ranges based on type
    if (cfg.reduction_type === 'percentage' && (cfg.reduction < 0 || cfg.reduction > 100)) {
      ctx.addIssue({
        code: z.ZodIssueCode.custom,
        message: 'Configurator: The percentage reduction must be between 0 and 100.',
        path: ['reduction'],
      })
    }

    cfg.steps.forEach((step, idx) => {
      const currentPos = step.position
      const stepIdStr = String(step.id)

      step.product_choices.forEach((choice, cIdx) => {
        const conditions = choice.display_conditions || []

        conditions.forEach((cond, k) => {
          // Find referenced step
          const refStep = cfg.steps.find(
            (s) => String(s.id) === String(cond.step),
          )

          if (!refStep) {
            ctx.addIssue({
              code: z.ZodIssueCode.custom,
              message: `Step "${step.label}": The condition #${k + 1} reference a non-existent step.`,
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
              message: `Step "${step.label}": The condition #${k + 1} must reference a previous step.`,
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
              message: `Step "${step.label}": The condition #${k + 1} reference a choice that does not exist in the target step.`,
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
      })
    })
  })
