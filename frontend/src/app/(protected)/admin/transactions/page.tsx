import { TransactionsListView } from "@/features/admin/finance/transactions-list-view";
import type { Metadata } from "next";

export const metadata: Metadata = { title: "Transactions" };

export default function AdminTransactionsPage() {
    return <TransactionsListView />;
}
