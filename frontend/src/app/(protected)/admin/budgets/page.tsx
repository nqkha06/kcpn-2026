import { BudgetsListView } from "@/features/admin/finance/budgets-list-view";
import type { Metadata } from "next";

export const metadata: Metadata = { title: "Budgets" };

export default function AdminBudgetsPage() {
    return <BudgetsListView />;
}
