import { z } from 'zod'

export const QuantityRuleSchema = z
  .object({
    mode: z.enum(['none', 'fixed', 'expression']),
    locked: z.boolean().default(false),
    sources: z
      .array(
        z.object({
          step: z.union([z.number().int().positive(), z.string().min(1)]),
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
            message: `Step "${ctx.path[1]}" choice "${ctx.path[3]}": In NONE mode, offset should be 0.`,
            path: ['offset'],
          })
        }

        if (v.min !== null || v.max !== null) {
          ctx.addIssue({
            code: z.ZodIssueCode.custom,
            message: `Step "${ctx.path[1]}" choice "${ctx.path[3]}": In NONE mode, min/max should not be defined.`,
            path: ['min', 'max'],
          })
        }

        if (v.sources.length > 0) {
          ctx.addIssue({
            code: z.ZodIssueCode.custom,
            message: `Step "${ctx.path[1]}" choice "${ctx.path[3]}": In NONE mode, no source should be defined.`,
            path: ['sources'],
          })
        }

        if (false === v.locked) {
          ctx.addIssue({
            code: z.ZodIssueCode.custom,
            message: `Step "${ctx.path[1]}" choice "${ctx.path[3]}": In NONE mode, locked should be defined.`,
            path: ['locked'],
          })
        }

        if (v.round !== 'none') {
          ctx.addIssue({
            code: z.ZodIssueCode.custom,
            message: `Step "${ctx.path[1]}" choice "${ctx.path[3]}": In NONE mode, round should not be defined.`,
            path: ['round'],
          })
        }

        break
      case 'fixed':
        if (v.sources.length > 0) {
          ctx.addIssue({
            code: z.ZodIssueCode.custom,
            message: `Step "${ctx.path[1]}" choice "${ctx.path[3]}": In FIXED mode, no source should be defined.`,
            path: ['sources'],
          })
        }

        if (v.round !== 'none') {
          ctx.addIssue({
            code: z.ZodIssueCode.custom,
            message: `Step "${ctx.path[1]}" choice "${ctx.path[3]}": In FIXED mode, round should not be defined.`,
            path: ['round'],
          })
        }

        if (true === v.locked) {
          if (v.min !== null || v.max !== null) {
            ctx.addIssue({
              code: z.ZodIssueCode.custom,
              message: `Step "${ctx.path[1]}" choice "${ctx.path[3]}": In FIXED mode, min/max should not be defined when locked.`,
              path: ['min', 'max'],
            })
          }

          if (v.offset < 1) {
            ctx.addIssue({
              code: z.ZodIssueCode.custom,
              message: `Step "${ctx.path[1]}" choice "${ctx.path[3]}": In FIXED mode, offset should be at least 1 when locked.`,
              path: ['offset'],
            })
          }
        } else {
          const minQ = v.min
          const maxQ = v.max

          const hasMin = minQ !== null
          const hasMax = maxQ !== null

          if (hasMin) {
            const validMin = Number.isInteger(minQ) && Number(minQ) >= 1
            if (!validMin) {
              ctx.addIssue({
                code: z.ZodIssueCode.custom,
                message: `Step "${ctx.path[1]}" choice "${ctx.path[3]}": Minimal quantity must be an integer >= 1.`,
                path: ['min'],
              })
            }
          }

          if (hasMax) {
            const validMax = Number.isInteger(maxQ) && Number(maxQ) >= 1
            if (!validMax) {
              ctx.addIssue({
                code: z.ZodIssueCode.custom,
                message: `Step "${ctx.path[1]}" choice "${ctx.path[3]}": Maximal quantity must be an integer >= 1.`,
                path: ['max'],
              })
            }
          }

          if (hasMin && hasMax && Number(minQ) > Number(maxQ)) {
            ctx.addIssue({
              code: z.ZodIssueCode.custom,
              message: `Step "${ctx.path[1]}" choice "${ctx.path[3]}": Minimal quantity cannot be greater than maximal quantity.`,
              path: ['min_'],
            })
          }
        }

        break
      case 'expression':
        if (false === v.locked) {
          ctx.addIssue({
            code: z.ZodIssueCode.custom,
            message: `Step "${ctx.path[1]}" choice "${ctx.path[3]}": In EXPRESSION mode, locked should be defined.`,
            path: ['locked'],
          })
        }

        if (v.sources.length === 0) {
          ctx.addIssue({
            code: z.ZodIssueCode.custom,
            message: `Step "${ctx.path[1]}" choice "${ctx.path[3]}": In EXPRESSION mode, at least one source is required.`,
            path: ['sources'],
          })
        }

        if (v.min !== null || v.max !== null) {
          ctx.addIssue({
            code: z.ZodIssueCode.custom,
            message: `Step "${ctx.path[1]}" choice "${ctx.path[3]}": In EXPRESSION mode, min/max should not be defined.`,
            path: ['min', 'max'],
          })
        }
    }
  })
