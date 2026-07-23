import { BudgetsView } from "@/features/budgets/budgets-view";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Ngân sách",
};

export default function BudgetsPage() {
  return <BudgetsView />;
}
