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
    switch (v.mode) {
      case 'none':
        if (v.offset !== 0) {
          ctx.addIssue({
            code: z.ZodIssueCode.custom,
            message: 'In NONE mode, offset should be 0.',
            path: ['offset'],
          })
        }

        if (v.min !== null || v.max !== null) {
          ctx.addIssue({
            code: z.ZodIssueCode.custom,
            message: 'In NONE mode, min/max should not be defined.',
            path: ['min', 'max'],
          })
        }

        if (v.sources.length > 0) {
          ctx.addIssue({
            code: z.ZodIssueCode.custom,
            message: 'In NONE mode, no source should be defined.',
            path: ['sources'],
          })
        }

        if (true === v.locked) {
          ctx.addIssue({
            code: z.ZodIssueCode.custom,
            message: 'In NONE mode, locked should not be defined.',
            path: ['locked'],
          })
        }

        if (v.round !== 'none') {
          ctx.addIssue({
            code: z.ZodIssueCode.custom,
            message: 'In NONE mode, round should not be defined.',
            path: ['round'],
          })
        }

        break
      case 'fixed':
        if (v.sources.length > 0) {
          ctx.addIssue({
            code: z.ZodIssueCode.custom,
            message: 'In FIXED mode, no source should be defined.',
            path: ['sources'],
          })
        }

        if (true === v.locked && (v.min !== null || v.max !== null)) {
          ctx.addIssue({
            code: z.ZodIssueCode.custom,
            message:
              'In FIXED mode, min/max should not be defined when locked.',
            path: ['min', 'max'],
          })
        }

        if (v.round !== 'none') {
          ctx.addIssue({
            code: z.ZodIssueCode.custom,
            message: 'In FIXED mode, round should not be defined.',
            path: ['round'],
          })
        }

        break
      case 'expression':
        if (false === v.locked) {
          ctx.addIssue({
            code: z.ZodIssueCode.custom,
            message: 'In EXPRESSION mode, locked should be defined.',
            path: ['locked'],
          })
        }

        if (v.sources.length === 0) {
          ctx.addIssue({
            code: z.ZodIssueCode.custom,
            message: 'In EXPRESSION mode, at least one source is required.',
            path: ['sources'],
          })
        }

        if (v.min !== null || v.max !== null) {
          ctx.addIssue({
            code: z.ZodIssueCode.custom,
            message: 'In EXPRESSION mode, min/max should not be defined.',
            path: ['min', 'max'],
          })
        }
    }
  })
