import { BudgetFormView } from "@/features/admin/finance/budget-form-view";
import type { Metadata } from "next";

export const metadata: Metadata = { title: "Create Budget" };

export default function CreateAdminBudgetPage() {
    return <BudgetFormView />;
}
