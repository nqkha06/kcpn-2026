import { TransactionFormView } from "@/features/admin/finance/transaction-form-view";
import type { Metadata } from "next";
import { notFound } from "next/navigation";

export const metadata: Metadata = { title: "Edit Transaction" };

export default async function EditAdminTransactionPage({
    params,
}: {
    params: Promise<{ id: string }>;
}) {
    const { id } = await params;
    const transactionId = Number(id);

    if (!Number.isInteger(transactionId) || transactionId < 1) {
        notFound();
    }

    return <TransactionFormView transactionId={transactionId} />;
}
