import { z } from "zod";

const maximumAmount = 9_999_999_999.99;

export const budgetSchema = z.object({
  category_id: z.string().min(1, "Vui lòng chọn danh mục."),
  amount_limit: z
    .string()
    .trim()
    .refine((value) => Number.isFinite(Number(value)) && Number(value) >= 0.01, "Hạn mức phải lớn hơn 0.")
    .refine(
      (value) => !Number.isFinite(Number(value)) || Number(value) <= maximumAmount,
      "Hạn mức nằm ngoài phạm vi cho phép.",
    ),
  period: z.enum(["monthly", "yearly"]),
  note: z.string().trim().max(255, "Ghi chú tối đa 255 ký tự."),
});

export type BudgetFormValues = z.infer<typeof budgetSchema>;
