import { z } from 'zod'

export const ConditionSchema = z
  .object({
    step: z.union([z.number().int().nonnegative(), z.string().min(1)]),
    choice: z.union([z.number().int().nonnegative(), z.string().min(1)]),
    is_virtual: z.boolean().optional(),
  })
  .strict()
