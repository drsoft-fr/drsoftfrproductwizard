import { z } from 'zod'
import { ProductChoiceSchema } from '@/js/admin/configurator/form/validation/productChoiceSchema'

export const StepSchema = z
  .object({
    id: z.union([z.number().int(), z.string().min(1)]),
    label: z.string().trim().min(1, 'The wording is mandatory.'),
    description: z.string().optional().nullable(),
    position: z.number().int().nonnegative({ message: 'Invalid position.' }),
    active: z.boolean(),
    reduction: z.number().nonnegative().optional().default(0),
    reduction_tax: z.boolean().optional().default(true),
    reduction_type: z
      .union([z.literal('amount'), z.literal('percentage')])
      .optional()
      .default('amount'),
    product_choices: z
      .array(ProductChoiceSchema)
      .min(1, 'At least one choice by step is required.'),
    is_virtual: z.boolean().optional(),
  })
  .strict()
  .superRefine((step, ctx) => {
    // > Validate default choice count
    const defaultCount = step.product_choices.reduce(
      (acc, c) => acc + (c.is_default ? 1 : 0),
      0,
    )

    if (defaultCount > 1) {
      ctx.addIssue({
        code: z.ZodIssueCode.custom,
        message: `Step "${step.label}": There can only be one default choice..`,
        path: ['product_choices', 'default_choice'],
      })
    }
    // < Validate default choice count

    // > Step-level reduction % range
    if (
      step.reduction_type === 'percentage' &&
      (step.reduction < 0 || step.reduction > 100)
    ) {
      ctx.addIssue({
        code: z.ZodIssueCode.custom,
        message: `Step "${step.label}": The percentage reduction must be between 0 and 100.`,
        path: ['reduction'],
      })
    }
    // < Step-level reduction % range
  })
