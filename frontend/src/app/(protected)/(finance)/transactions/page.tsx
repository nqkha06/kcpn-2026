import { LoadingState } from "@/components/common/loading-state";
import { TransactionsView } from "@/features/transactions/transactions-view";
import type { Metadata } from "next";
import { Suspense } from "react";

export const metadata: Metadata = {
  title: "Giao dịch",
};

export default function TransactionsPage() {
  return (
    <Suspense fallback={<LoadingState label="Đang tải bộ lọc giao dịch..." />}>
      <TransactionsView />
    </Suspense>
  );
}
