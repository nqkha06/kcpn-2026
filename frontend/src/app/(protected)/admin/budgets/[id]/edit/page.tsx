import { BudgetFormView } from "@/features/admin/finance/budget-form-view";
import type { Metadata } from "next";
import { notFound } from "next/navigation";

export const metadata: Metadata = { title: "Edit Budget" };

export default async function EditAdminBudgetPage({
    params,
}: {
    params: Promise<{ id: string }>;
}) {
    const { id } = await params;
    const budgetId = Number(id);

    if (!Number.isInteger(budgetId) || budgetId < 1) {
        notFound();
    }

    return <BudgetFormView budgetId={budgetId} />;
}
