import { z } from "zod";

const maximumAmount = 9_999_999_999.99;

export const transactionSchema = z.object({
  type: z.enum(["income", "expense"]),
  amount: z
    .string()
    .trim()
    .refine((value) => Number.isFinite(Number(value)) && Number(value) >= 0.01, "Số tiền phải lớn hơn 0.")
    .refine(
      (value) => !Number.isFinite(Number(value)) || Number(value) <= maximumAmount,
      "Số tiền nằm ngoài phạm vi cho phép.",
    ),
  wallet_id: z.string().min(1, "Vui lòng chọn ví."),
  category_id: z.string(),
  transacted_at: z.string().min(1, "Vui lòng chọn ngày giao dịch."),
  note: z.string().trim().max(255, "Ghi chú tối đa 255 ký tự."),
  labels: z.string().refine(
    (value) =>
      value
        .split(",")
        .map((label) => label.trim())
        .filter(Boolean)
        .every((label) => label.length <= 30),
    "Mỗi nhãn tối đa 30 ký tự.",
  ),
});

export type TransactionFormValues = z.infer<typeof transactionSchema>;
