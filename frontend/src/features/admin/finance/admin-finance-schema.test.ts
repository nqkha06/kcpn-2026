import {
    adminBudgetSchema,
    adminCategorySchema,
    adminTransactionSchema,
} from "@/features/admin/finance/admin-finance-schema";
import { describe, expect, it } from "vitest";

describe("admin finance schemas", () => {
    it("validates category colors and accepts a complete category", () => {
        expect(
            adminCategorySchema.safeParse({
                name: "Food",
                color: "green",
                description: "Daily meals",
                status: "active",
            }).success,
        ).toBe(false);

        expect(
            adminCategorySchema.safeParse({
                name: "Food",
                color: "#22c55e",
                description: "Daily meals",
                status: "active",
            }).success,
        ).toBe(true);
    });

    it("validates transaction amount, required relations and label lengths", () => {
        const validTransaction = {
            user_id: "3",
            wallet_id: "4",
            category_id: "",
            type: "expense" as const,
            amount: "250000",
            transacted_at: "2026-07-22",
            status: "posted" as const,
            note: "Lunch",
            labels: "food, office",
        };

        expect(adminTransactionSchema.safeParse(validTransaction).success).toBe(
            true,
        );
        expect(
            adminTransactionSchema.safeParse({
                ...validTransaction,
                amount: "0",
            }).success,
        ).toBe(false);
        expect(
            adminTransactionSchema.safeParse({
                ...validTransaction,
                labels: "a".repeat(31),
            }).success,
        ).toBe(false);
    });

    it("requires a user and category for budgets", () => {
        const validBudget = {
            user_id: "3",
            category_id: "2",
            amount_limit: "1500000",
            period: "monthly" as const,
            status: "active" as const,
            note: "Food budget",
        };

        expect(adminBudgetSchema.safeParse(validBudget).success).toBe(true);
        expect(
            adminBudgetSchema.safeParse({ ...validBudget, user_id: "" })
                .success,
        ).toBe(false);
        expect(
            adminBudgetSchema.safeParse({ ...validBudget, category_id: "" })
                .success,
        ).toBe(false);
    });
});
