import { TransactionFormView } from "@/features/admin/finance/transaction-form-view";
import type { Metadata } from "next";

export const metadata: Metadata = { title: "Create Transaction" };

export default function CreateAdminTransactionPage() {
    return <TransactionFormView />;
}
