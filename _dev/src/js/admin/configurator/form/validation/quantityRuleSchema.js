import { z } from 'zod'

export const QuantityRuleSchema = z
  .object({
    mode: z.enum(['none', 'fixed', 'expression']),
    locked: z.boolean().default(false),
    sources: z
      .array(
        z.object({
          step: z.union([z.number().int().positive(), z.string().min(1)]),
          choice: z.union([z.number().int().positive(), z.string().min(1)]),
          coeff: z.number(),
        }),
      )
      .default([]),
    offset: z.number().default(0),
    min: z.number().int().nullable().optional(),
    max: z.number().int().nullable().optional(),
    round: z.enum(['none', 'floor', 'ceil', 'round']).default('none'),
  })
  .strict()
  .superRefine((v, ctx) => {
    if (v.mode === 'fixed' && v.sources.length > 0) {
      ctx.addIssue({
        code: z.ZodIssueCode.custom,
        message: 'In FIXED mode, no source should be defined.',
        path: ['sources'],
      })
    }
    if (v.mode === 'expression' && v.sources.length === 0) {
      ctx.addIssue({
        code: z.ZodIssueCode.custom,
        message: 'In EXPRESSION mode, at least one source is required.',
        path: ['sources'],
      })
    }
  })
