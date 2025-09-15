import { z } from 'zod'
import { ConditionSchema } from '@/js/admin/configurator/form/validation/conditionSchema'
import { QuantityRuleSchema } from '@/js/admin/configurator/form/validation/quantityRuleSchema'

export const ProductChoiceSchema = z
  .object({
    id: z.union([z.number().int(), z.string().min(1)]),
    label: z.string().trim().min(1, 'The wording of a choice is mandatory.'),
    description: z.string().optional().nullable(),
    active: z.boolean(),
    is_default: z.boolean().optional().default(false),
    product_id: z
      .union([z.number().int().nullable(), z.null()])
      .nullable()
      .optional(),
    reduction: z.number().nonnegative().optional().default(0),
    reduction_tax: z.boolean().optional().default(true),
    reduction_type: z
      .union([z.literal('amount'), z.literal('percentage')])
      .optional()
      .default('amount'),
    display_conditions: z
      .array(z.array(ConditionSchema))
      .optional()
      .default([]),
    quantity_rule: z.union([QuantityRuleSchema]),
    is_virtual: z.boolean().optional(),
  })
  .strict()
  .superRefine((choice, ctx) => {
    // > Reduction % range at choice level
    if (
      choice.reduction_type === 'percentage' &&
      (choice.reduction < 0 || choice.reduction > 100)
    ) {
      ctx.addIssue({
        code: z.ZodIssueCode.custom,
        message: `Step "${ctx.path[1]}" choice "${choice.label}": The percentage reduction must be between 0 and 100.`,
        path: ['reduction'],
      })
    }
    // < Reduction % range at choice level
  })
