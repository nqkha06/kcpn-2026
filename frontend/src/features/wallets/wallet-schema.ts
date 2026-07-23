import { z } from "zod";

const maximumBalance = 9_999_999_999.99;

export const walletSchema = z.object({
  name: z.string().trim().min(1, "Vui lòng nhập tên ví.").max(100, "Tên ví tối đa 100 ký tự."),
  currency: z
    .string()
    .trim()
    .length(3, "Tiền tệ phải là mã gồm 3 ký tự.")
    .transform((value) => value.toUpperCase()),
  opening_balance: z
    .string()
    .trim()
    .refine((value) => value !== "" && Number.isFinite(Number(value)), "Số dư phải là một số.")
    .refine(
      (value) => !Number.isFinite(Number(value)) || Math.abs(Number(value)) <= maximumBalance,
      "Số dư nằm ngoài phạm vi cho phép.",
    ),
  is_default: z.boolean(),
});

export type WalletFormValues = z.input<typeof walletSchema>;
