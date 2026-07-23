"use client";

import { ErrorState } from "@/components/common/error-state";
import { LoadingState } from "@/components/common/loading-state";
import { PageHeader } from "@/components/layouts/page-header";
import {
  walletSchema,
  type WalletFormValues,
} from "@/features/wallets/wallet-schema";
import { ApiError } from "@/lib/api/errors";
import { formatCurrencyAmount, resolveCurrencyCode } from "@/lib/finance/format";
import { dashboardQueryKey, dashboardService } from "@/services/dashboard.service";
import { walletQueryKey, walletService } from "@/services/wallet.service";
import type { FinanceWallet, WalletPayload } from "@/types/finance";
import { zodResolver } from "@hookform/resolvers/zod";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { format, parseISO } from "date-fns";
import {
  ArrowDownRight,
  DollarSign,
  Pencil,
  Plus,
  Trash2,
  Wallet as WalletIcon,
  X,
} from "lucide-react";
import { useEffect, useMemo, useState } from "react";
import { useForm } from "react-hook-form";
import { toast } from "sonner";

interface SaveWalletVariables {
  walletId?: number;
  payload: WalletPayload;
}

const emptyForm: WalletFormValues = {
  name: "",
  currency: "VND",
  opening_balance: "0",
  is_default: false,
};

export function WalletsView() {
  const queryClient = useQueryClient();
  const dashboardQuery = useQuery({
    queryKey: dashboardQueryKey,
    queryFn: dashboardService.show,
  });
  const [selectedWalletId, setSelectedWalletId] = useState<number | null>(null);
  const [editingWallet, setEditingWallet] = useState<FinanceWallet | null>(null);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [requestError, setRequestError] = useState<string | null>(null);
  const form = useForm<WalletFormValues>({
    resolver: zodResolver(walletSchema),
    defaultValues: emptyForm,
  });
  const saveMutation = useMutation({
    mutationFn: ({ walletId, payload }: SaveWalletVariables) =>
      walletId ? walletService.update(walletId, payload) : walletService.create(payload),
    onSuccess: async (_, variables) => {
      await Promise.all([
        queryClient.invalidateQueries({ queryKey: dashboardQueryKey }),
        queryClient.invalidateQueries({ queryKey: walletQueryKey }),
      ]);
      closeModal();
      toast.success(variables.walletId ? "Đã cập nhật ví." : "Đã tạo ví mới.");
    },
    onError: (error) => applyApiError(error),
  });
  const deleteMutation = useMutation({
    mutationFn: walletService.destroy,
    onSuccess: async () => {
      setSelectedWalletId(null);
      await Promise.all([
        queryClient.invalidateQueries({ queryKey: dashboardQueryKey }),
        queryClient.invalidateQueries({ queryKey: walletQueryKey }),
      ]);
      toast.success("Đã xóa ví.");
    },
    onError: (error) => {
      toast.error(error instanceof ApiError ? error.message : "Không thể xóa ví.");
    },
  });
  const wallets = useMemo(() => dashboardQuery.data?.wallets ?? [], [dashboardQuery.data]);
  const transactions = useMemo(
    () => dashboardQuery.data?.transactions ?? [],
    [dashboardQuery.data],
  );
  const categories = useMemo(
    () => dashboardQuery.data?.categories ?? [],
    [dashboardQuery.data],
  );
  const selectedWallet =
    wallets.find((wallet) => wallet.id === selectedWalletId) ?? wallets[0] ?? null;
  const walletTransactions = useMemo(
    () =>
      transactions
        .filter((transaction) => transaction.wallet_id === selectedWallet?.id)
        .sort(
          (left, right) =>
            new Date(right.transacted_at).getTime() - new Date(left.transacted_at).getTime(),
        ),
    [selectedWallet?.id, transactions],
  );
  const categoryMap = useMemo(
    () => new Map(categories.map((category) => [category.id, category])),
    [categories],
  );

  useEffect(() => {
    function closeOnEscape(event: KeyboardEvent): void {
      if (event.key === "Escape" && isModalOpen && !saveMutation.isPending) {
        setIsModalOpen(false);
        setEditingWallet(null);
        setRequestError(null);
        form.reset(emptyForm);
      }
    }

    document.addEventListener("keydown", closeOnEscape);
    return () => document.removeEventListener("keydown", closeOnEscape);
  }, [form, isModalOpen, saveMutation.isPending]);

  function openCreateModal(): void {
    const currency = resolveCurrencyCode(
      wallets.find((wallet) => wallet.is_default)?.currency ?? wallets[0]?.currency,
    );

    setEditingWallet(null);
    setRequestError(null);
    form.reset({ ...emptyForm, currency });
    setIsModalOpen(true);
  }

  function openEditModal(wallet: FinanceWallet): void {
    setEditingWallet(wallet);
    setRequestError(null);
    form.reset({
      name: wallet.name,
      currency: wallet.currency,
      opening_balance: String(wallet.opening_balance),
      is_default: wallet.is_default,
    });
    setIsModalOpen(true);
  }

  function closeModal(): void {
    setIsModalOpen(false);
    setEditingWallet(null);
    setRequestError(null);
    form.reset(emptyForm);
  }

  function applyApiError(error: Error): void {
    if (!(error instanceof ApiError)) {
      setRequestError("Không thể lưu ví. Vui lòng thử lại.");
      return;
    }

    setRequestError(error.message);
    Object.entries(error.errors).forEach(([field, messages]) => {
      if (field === "name" || field === "currency" || field === "opening_balance" || field === "is_default") {
        form.setError(field, { type: "server", message: messages[0] });
      }
    });
  }

  function submitWallet(values: WalletFormValues): void {
    const parsed = walletSchema.parse(values);

    setRequestError(null);
    saveMutation.mutate({
      walletId: editingWallet?.id,
      payload: {
        name: parsed.name,
        currency: parsed.currency,
        opening_balance: Number(parsed.opening_balance),
        is_default: parsed.is_default,
      },
    });
  }

  function deleteWallet(wallet: FinanceWallet): void {
    if (window.confirm(`Xoá ví "${wallet.name}"?`)) {
      deleteMutation.mutate(wallet.id);
    }
  }

  if (dashboardQuery.isLoading) {
    return <LoadingState label="Đang tải danh sách ví..." />;
  }

  if (dashboardQuery.isError || !dashboardQuery.data) {
    return <ErrorState onRetry={() => void dashboardQuery.refetch()} />;
  }

  return (
    <>
      <PageHeader
        title="Ví tiền"
        description="Quản lý số dư tiền mặt, ngân hàng và thẻ tại một nơi."
        action={
          <button
            type="button"
            onClick={openCreateModal}
            className="flex items-center gap-2 rounded-xl bg-primary-600 px-4 py-2 font-medium text-white shadow-sm shadow-primary-500/30 transition-colors hover:bg-primary-700"
          >
            <Plus className="size-4" />
            Thêm ví
          </button>
        }
      />

      <div className="space-y-6">
        <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
          {wallets.length === 0 ? (
            <div className="col-span-full rounded-2xl border border-dashed border-slate-200 bg-white p-10 text-center text-slate-500">
              <p className="text-lg font-semibold text-slate-800">Chưa có ví nào</p>
              <p className="mt-1 text-sm">Thêm ví đầu tiên để bắt đầu theo dõi số dư.</p>
            </div>
          ) : (
            wallets.map((wallet) => {
              const isSelected = wallet.id === selectedWallet?.id;
              const isNegative = wallet.current_balance < 0;

              return (
                <article
                  key={wallet.id}
                  role="button"
                  tabIndex={0}
                  onClick={() => setSelectedWalletId(wallet.id)}
                  onKeyDown={(event) => {
                    if (event.key === "Enter" || event.key === " ") {
                      setSelectedWalletId(wallet.id);
                    }
                  }}
                  className={`relative cursor-pointer overflow-hidden rounded-2xl border-2 bg-white p-6 transition-all duration-200 ${
                    isSelected
                      ? "border-primary-500 shadow-md ring-4 ring-primary-500/10"
                      : "border-slate-100 shadow-sm hover:border-primary-200 hover:shadow-md"
                  }`}
                >
                  {isSelected ? (
                    <span className="absolute top-0 right-0 -mt-8 -mr-8 size-16 rounded-full bg-linear-to-bl from-primary-100 to-transparent" />
                  ) : null}
                  <div className="relative mb-6 flex items-start justify-between">
                    <span className="rounded-xl border border-emerald-100 bg-emerald-50 p-3 text-emerald-600">
                      <WalletIcon className="size-6" />
                    </span>
                    <div className="flex items-center gap-1">
                      <button
                        type="button"
                        aria-label={`Sửa ${wallet.name}`}
                        onClick={(event) => {
                          event.stopPropagation();
                          openEditModal(wallet);
                        }}
                        className="rounded-lg p-2 text-slate-400 transition-colors hover:bg-slate-100 hover:text-primary-600"
                      >
                        <Pencil className="size-4" />
                      </button>
                      <button
                        type="button"
                        aria-label={`Xóa ${wallet.name}`}
                        disabled={deleteMutation.isPending}
                        onClick={(event) => {
                          event.stopPropagation();
                          deleteWallet(wallet);
                        }}
                        className="rounded-lg p-2 text-slate-400 transition-colors hover:bg-danger-50 hover:text-danger-600 disabled:opacity-50"
                      >
                        <Trash2 className="size-4" />
                      </button>
                    </div>
                  </div>
                  <p className="mb-1 text-sm font-medium text-slate-500">{wallet.name}</p>
                  <p className={`text-2xl font-bold ${isNegative ? "text-danger-600" : "text-slate-900"}`}>
                    {formatCurrencyAmount(wallet.current_balance, wallet.currency)}
                  </p>
                  <p className="mt-2 text-xs tracking-wider text-slate-400 uppercase">
                    cash tài khoản
                  </p>
                </article>
              );
            })
          )}
        </div>

        <section className="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
          <div className="flex items-center justify-between border-b border-slate-100 bg-slate-50/50 p-6">
            <h2 className="text-lg font-bold tracking-tight text-slate-900">
              Lịch sử giao dịch
              <span className="ml-2 text-sm font-normal text-slate-500">
                ({selectedWallet?.name ?? "Chưa chọn ví"})
              </span>
            </h2>
            <button className="text-sm font-medium text-primary-600 transition-colors hover:text-primary-700">
              Xem thống kê
            </button>
          </div>

          {walletTransactions.length > 0 ? (
            <div className="overflow-x-auto">
              <table className="w-full text-left text-sm whitespace-nowrap">
                <thead className="border-b border-slate-100 bg-slate-50/50 text-xs font-medium tracking-wider text-slate-500 uppercase">
                  <tr>
                    <th className="px-6 py-4">Giao dịch</th>
                    <th className="px-6 py-4">Danh mục</th>
                    <th className="px-6 py-4">Ngày</th>
                    <th className="px-6 py-4 text-right">Số tiền</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100 text-slate-700">
                  {walletTransactions.map((transaction) => {
                    const category = transaction.category_id
                      ? categoryMap.get(transaction.category_id)
                      : null;
                    const isIncome = transaction.type === "income";

                    return (
                      <tr key={transaction.id} className="transition-colors hover:bg-slate-50/80">
                        <td className="flex items-center gap-3 px-6 py-4 font-medium text-slate-900">
                          <span className={isIncome ? "rounded-lg bg-success-50 p-2 text-success-600" : "rounded-lg bg-slate-100 p-2 text-slate-600"}>
                            {isIncome ? <ArrowDownRight className="size-4" /> : <DollarSign className="size-4" />}
                          </span>
                          {transaction.note || "Chuyển khoản"}
                        </td>
                        <td className="px-6 py-4">
                          <span
                            className="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium"
                            style={{
                              backgroundColor: `${category?.color || "#94a3b8"}15`,
                              color: category?.color || "#64748b",
                              borderColor: `${category?.color || "#94a3b8"}30`,
                            }}
                          >
                            {category?.name || "Không phân loại"}
                          </span>
                        </td>
                        <td className="px-6 py-4 text-slate-500">
                          {format(parseISO(transaction.transacted_at), "dd/MM/yyyy")}
                        </td>
                        <td className={`px-6 py-4 text-right font-bold ${isIncome ? "text-success-600" : "text-slate-900"}`}>
                          {isIncome ? "+" : "-"}
                          {formatCurrencyAmount(transaction.amount, selectedWallet?.currency)}
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>
            </div>
          ) : (
            <div className="p-12 text-center text-slate-500">
              <span className="mx-auto mb-4 flex size-16 items-center justify-center rounded-full bg-slate-100">
                <DollarSign className="size-8 text-slate-400" />
              </span>
              <p className="mb-1 text-lg font-medium text-slate-900">Chưa có giao dịch</p>
              <p className="text-sm">Ví này chưa có giao dịch nào.</p>
            </div>
          )}
        </section>
      </div>

      {isModalOpen ? (
        <div
          className="fixed inset-0 z-50 flex animate-in items-center justify-center bg-slate-900/40 p-4 backdrop-blur-sm duration-200 fade-in"
          onMouseDown={(event) => {
            if (event.currentTarget === event.target && !saveMutation.isPending) {
              closeModal();
            }
          }}
        >
          <div
            role="dialog"
            aria-modal="true"
            aria-labelledby="wallet-dialog-title"
            className="w-full max-w-lg animate-in overflow-hidden rounded-2xl bg-white shadow-xl duration-300 slide-in-from-bottom-4"
          >
            <div className="flex items-center justify-between border-b border-slate-100 p-5">
              <h2 id="wallet-dialog-title" className="text-xl font-bold text-slate-900">
                {editingWallet ? "Sửa ví" : "Thêm ví"}
              </h2>
              <button
                type="button"
                aria-label="Đóng"
                onClick={closeModal}
                disabled={saveMutation.isPending}
                className="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600 disabled:opacity-50"
              >
                <X className="size-5" />
              </button>
            </div>

            <form onSubmit={form.handleSubmit(submitWallet)} className="space-y-4 p-5">
              {requestError ? (
                <p role="alert" className="rounded-xl bg-danger-50 px-3 py-2 text-sm text-danger-600">
                  {requestError}
                </p>
              ) : null}
              <FormField label="Tên ví" error={form.formState.errors.name?.message}>
                <input
                  {...form.register("name")}
                  autoFocus
                  placeholder="Ví tiền mặt"
                  className="block w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                />
              </FormField>
              <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <FormField
                  label="Tiền tệ"
                  error={form.formState.errors.currency?.message}
                  hint="Dùng mã ISO 4217 cho đơn vị tiền tệ."
                >
                  <input
                    {...form.register("currency")}
                    maxLength={3}
                    placeholder="Mã tiền tệ"
                    className="block w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-slate-900 uppercase shadow-sm focus:border-primary-500 focus:ring-primary-500"
                  />
                </FormField>
                <FormField
                  label="Số dư ban đầu"
                  error={form.formState.errors.opening_balance?.message}
                >
                  <input
                    {...form.register("opening_balance")}
                    type="number"
                    step="0.01"
                    className="block w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                  />
                </FormField>
              </div>
              <label className="flex items-center gap-2 text-sm text-slate-600">
                <input
                  {...form.register("is_default")}
                  type="checkbox"
                  className="size-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                />
                Đặt làm ví mặc định
              </label>
              <div className="flex justify-end gap-3 border-t border-slate-100 pt-4">
                <button
                  type="button"
                  onClick={closeModal}
                  disabled={saveMutation.isPending}
                  className="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-50 disabled:opacity-50"
                >
                  Hủy
                </button>
                <button
                  type="submit"
                  disabled={saveMutation.isPending}
                  className="rounded-xl bg-primary-600 px-5 py-2 text-sm font-medium text-white shadow-sm shadow-primary-500/30 transition-colors hover:bg-primary-700 disabled:cursor-not-allowed disabled:opacity-60"
                >
                  {saveMutation.isPending
                    ? "Đang lưu..."
                    : editingWallet
                      ? "Cập nhật ví"
                      : "Lưu ví"}
                </button>
              </div>
            </form>
          </div>
        </div>
      ) : null}
    </>
  );
}

function FormField({
  label,
  error,
  hint,
  children,
}: {
  label: string;
  error?: string;
  hint?: string;
  children: React.ReactNode;
}) {
  return (
    <label className="block">
      <span className="mb-1 block text-sm font-medium text-slate-700">{label}</span>
      {children}
      {hint ? <span className="mt-1 block text-xs text-slate-500">{hint}</span> : null}
      {error ? <span className="mt-1 block text-xs text-danger-600">{error}</span> : null}
    </label>
  );
}
