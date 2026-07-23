"use client";

import { ErrorState } from "@/components/common/error-state";
import { LoadingState } from "@/components/common/loading-state";
import { PageHeader } from "@/components/layouts/page-header";
import { TransactionDialog } from "@/features/transactions/transaction-dialog";
import { formatCurrencyAmount } from "@/lib/finance/format";
import { categoryQueryKey, categoryService } from "@/services/category.service";
import { transactionQueryKey, transactionService } from "@/services/transaction.service";
import { walletQueryKey, walletService } from "@/services/wallet.service";
import type { TransactionType } from "@/types/finance";
import { keepPreviousData, useQuery } from "@tanstack/react-query";
import { format, parseISO } from "date-fns";
import { ArrowDownRight, DollarSign, Filter, Plus, Search } from "lucide-react";
import { useState } from "react";
import { toast } from "sonner";

type TransactionFilter = "all" | TransactionType;

export function TransactionsView() {
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [searchTerm, setSearchTerm] = useState("");
  const [filterType, setFilterType] = useState<TransactionFilter>("all");
  const filters = {
    search: searchTerm.trim() || undefined,
    type: filterType === "all" ? undefined : filterType,
    sort: "transacted_at" as const,
    direction: "desc" as const,
    page: 1,
    per_page: 100,
  };
  const transactionsQuery = useQuery({
    queryKey: [...transactionQueryKey, filters],
    queryFn: () => transactionService.list(filters),
    placeholderData: keepPreviousData,
  });
  const categoriesQuery = useQuery({
    queryKey: categoryQueryKey,
    queryFn: categoryService.list,
    staleTime: 10 * 60 * 1000,
  });
  const walletsQuery = useQuery({
    queryKey: walletQueryKey,
    queryFn: walletService.list,
    staleTime: 5 * 60 * 1000,
  });
  const categories = categoriesQuery.data ?? [];
  const wallets = walletsQuery.data ?? [];

  function openTransactionDialog(): void {
    if (wallets.length === 0) {
      toast.error("Bạn cần tạo ví trước khi thêm giao dịch.");
      return;
    }

    setIsModalOpen(true);
  }

  if (transactionsQuery.isLoading || categoriesQuery.isLoading || walletsQuery.isLoading) {
    return <LoadingState label="Đang tải giao dịch..." />;
  }

  if (
    transactionsQuery.isError ||
    categoriesQuery.isError ||
    walletsQuery.isError ||
    !transactionsQuery.data
  ) {
    return (
      <ErrorState
        onRetry={() => {
          void transactionsQuery.refetch();
          void categoriesQuery.refetch();
          void walletsQuery.refetch();
        }}
      />
    );
  }

  const transactions = transactionsQuery.data.data;
  const categoryMap = new Map(categories.map((category) => [category.id, category]));
  const walletMap = new Map(wallets.map((wallet) => [wallet.id, wallet]));

  return (
    <>
      <PageHeader
        title="Giao dịch"
        description="Ghi lại mọi khoản thu chi tại một nơi."
        action={
          <button
            type="button"
            onClick={openTransactionDialog}
            className="flex items-center gap-2 rounded-xl bg-primary-600 px-4 py-2 font-medium text-white shadow-sm shadow-primary-500/30 transition-colors hover:bg-primary-700"
          >
            <Plus className="size-4" />
            Thêm giao dịch
          </button>
        }
      />

      <div className="space-y-6">
        <section className="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
          <div className="flex flex-col justify-between gap-4 border-b border-slate-100 bg-slate-50/50 p-4 sm:flex-row">
            <div className="relative grow sm:max-w-md">
              <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <Search className="size-4 text-slate-400" />
              </div>
              <input
                type="text"
                placeholder="Tìm kiếm giao dịch..."
                value={searchTerm}
                onChange={(event) => setSearchTerm(event.target.value)}
                className="block w-full rounded-xl border border-slate-200 bg-white py-2 pl-10 text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
              />
            </div>
            <div className="flex items-center gap-2">
              <div className="relative">
                <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                  <Filter className="size-4 text-slate-400" />
                </div>
                <select
                  value={filterType}
                  onChange={(event) => setFilterType(event.target.value as TransactionFilter)}
                  className="block w-full rounded-xl border border-slate-200 bg-white py-2 pr-8 pl-10 font-medium text-slate-700 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                >
                  <option value="all">Tất cả loại</option>
                  <option value="income">Chỉ thu nhập</option>
                  <option value="expense">Chỉ chi tiêu</option>
                </select>
              </div>
            </div>
          </div>

          <div className="overflow-x-auto">
            <table className="w-full text-left text-sm whitespace-nowrap">
              <thead className="border-b border-slate-100 bg-slate-50/50 text-xs font-medium tracking-wider text-slate-500 uppercase">
                <tr>
                  <th scope="col" className="px-6 py-4">Giao dịch</th>
                  <th scope="col" className="px-6 py-4">Danh mục</th>
                  <th scope="col" className="px-6 py-4">Ngày</th>
                  <th scope="col" className="px-6 py-4">Ví</th>
                  <th scope="col" className="px-6 py-4 text-right">Số tiền</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100 text-slate-700">
                {transactions.map((transaction) => {
                  const category = transaction.category ??
                    (transaction.category_id ? categoryMap.get(transaction.category_id) : null);
                  const wallet = transaction.wallet ?? walletMap.get(transaction.wallet_id);
                  const isIncome = transaction.type === "income";

                  return (
                    <tr key={transaction.id} className="group cursor-pointer transition-colors hover:bg-slate-50/80">
                      <td className="flex items-center gap-3 px-6 py-4 font-medium text-slate-900">
                        <div
                          className={`rounded-lg p-2 ${
                            isIncome
                              ? "bg-success-50 text-success-600 group-hover:bg-success-100"
                              : "bg-slate-100 text-slate-600 group-hover:bg-slate-200"
                          } transition-colors`}
                        >
                          {isIncome ? <ArrowDownRight className="size-4" /> : <DollarSign className="size-4" />}
                        </div>
                        <div className="flex flex-col">
                          <span>{transaction.note ?? "Chuyển khoản"}</span>
                          {transaction.labels.length > 0 ? (
                            <div className="mt-1 flex gap-1">
                              {transaction.labels.map((label) => (
                                <span
                                  key={label}
                                  className="rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-bold tracking-wider text-slate-500 uppercase"
                                >
                                  {label}
                                </span>
                              ))}
                            </div>
                          ) : null}
                        </div>
                      </td>
                      <td className="px-6 py-4">
                        {category ? (
                          <span
                            className="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium"
                            style={{
                              backgroundColor: `${category.color}15`,
                              color: category.color ?? undefined,
                              borderColor: `${category.color}30`,
                            }}
                          >
                            {category.name}
                          </span>
                        ) : (
                          <span className="text-xs text-slate-400">Không phân loại</span>
                        )}
                      </td>
                      <td className="px-6 py-4 text-slate-500">
                        {format(parseISO(transaction.transacted_at), "dd/MM/yyyy")}
                      </td>
                      <td className="px-6 py-4 text-slate-500">{wallet?.name ?? "Ví không xác định"}</td>
                      <td className={`px-6 py-4 text-right font-bold ${isIncome ? "text-success-600" : "text-slate-900"}`}>
                        {isIncome ? "+" : "-"}
                        {formatCurrencyAmount(transaction.amount, wallet?.currency)}
                      </td>
                    </tr>
                  );
                })}
                {transactions.length === 0 ? (
                  <tr>
                    <td colSpan={5} className="px-6 py-12 text-center text-slate-500">
                      Không tìm thấy giao dịch. Hãy điều chỉnh bộ lọc.
                    </td>
                  </tr>
                ) : null}
              </tbody>
            </table>
          </div>
        </section>
      </div>

      {isModalOpen ? (
        <TransactionDialog
          categories={categories}
          wallets={wallets}
          onClose={() => setIsModalOpen(false)}
        />
      ) : null}
    </>
  );
}
