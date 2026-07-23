import { z } from "zod";

const amountString = z
    .string()
    .trim()
    .refine(
        (value) => Number.isFinite(Number(value)) && Number(value) >= 0.01,
        "Amount must be greater than zero.",
    )
    .refine(
        (value) => Number(value) <= 9_999_999_999.99,
        "Amount is out of range.",
    );

export const adminCategorySchema = z.object({
    name: z.string().trim().min(1, "Please provide a category name.").max(120),
    color: z
        .string()
        .regex(
            /^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/,
            "Color must be a valid hex value like #94A3B8.",
        ),
    description: z.string().trim().max(500),
    status: z.enum(["active", "inactive"]),
});

export const adminTransactionSchema = z.object({
    user_id: z.string().min(1, "Please select a user."),
    wallet_id: z.string().min(1, "Please select a wallet."),
    category_id: z.string(),
    type: z.enum(["income", "expense"]),
    amount: amountString,
    transacted_at: z.string().min(1, "Please select a transaction date."),
    status: z.enum(["posted", "pending", "cancelled"]),
    note: z.string().trim().max(255),
    labels: z.string().refine(
        (value) =>
            value
                .split(",")
                .map((label) => label.trim())
                .filter(Boolean)
                .every((label) => label.length <= 30),
        "Each label may not be greater than 30 characters.",
    ),
});

export const adminBudgetSchema = z.object({
    user_id: z.string().min(1, "Please select a user."),
    category_id: z.string().min(1, "Please select a category."),
    amount_limit: amountString,
    period: z.enum(["monthly", "yearly"]),
    status: z.enum(["active", "inactive"]),
    note: z.string().trim().max(255),
});

export type AdminCategoryFormValues = z.infer<typeof adminCategorySchema>;
export type AdminTransactionFormValues = z.infer<typeof adminTransactionSchema>;
export type AdminBudgetFormValues = z.infer<typeof adminBudgetSchema>;
