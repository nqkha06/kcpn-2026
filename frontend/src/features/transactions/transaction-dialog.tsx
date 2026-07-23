"use client";

import {
  transactionSchema,
  type TransactionFormValues,
} from "@/features/transactions/transaction-schema";
import { ApiError } from "@/lib/api/errors";
import { resolveCurrencyCode } from "@/lib/finance/format";
import { budgetQueryKey } from "@/services/budget.service";
import { dashboardQueryKey } from "@/services/dashboard.service";
import { transactionQueryKey, transactionService } from "@/services/transaction.service";
import { walletQueryKey } from "@/services/wallet.service";
import type { FinanceCategory, FinanceWallet } from "@/types/finance";
import { zodResolver } from "@hookform/resolvers/zod";
import { useMutation, useQueryClient } from "@tanstack/react-query";
import { format } from "date-fns";
import { X } from "lucide-react";
import Link from "next/link";
import { useState, type ReactNode } from "react";
import { useForm, useWatch } from "react-hook-form";
import { toast } from "sonner";

interface TransactionDialogProps {
  categories: FinanceCategory[];
  wallets: FinanceWallet[];
  onClose: () => void;
}

export function TransactionDialog({ categories, wallets, onClose }: TransactionDialogProps) {
  const queryClient = useQueryClient();
  const [requestError, setRequestError] = useState<string | null>(null);
  const defaultWallet = wallets.find((wallet) => wallet.is_default) ?? wallets[0];
  const form = useForm<TransactionFormValues>({
    resolver: zodResolver(transactionSchema),
    defaultValues: {
      type: "expense",
      amount: "",
      wallet_id: defaultWallet ? String(defaultWallet.id) : "",
      category_id: "none",
      transacted_at: format(new Date(), "yyyy-MM-dd"),
      note: "",
      labels: "",
    },
  });
  const selectedWalletId = useWatch({ control: form.control, name: "wallet_id" });
  const selectedType = useWatch({ control: form.control, name: "type" });
  const selectedWallet = wallets.find((wallet) => String(wallet.id) === selectedWalletId);
  const systemCategories = categories.filter((category) => !category.is_private);
  const privateCategories = categories.filter((category) => category.is_private);
  const mutation = useMutation({
    mutationFn: transactionService.create,
    onSuccess: async () => {
      await Promise.all([
        queryClient.invalidateQueries({ queryKey: transactionQueryKey }),
        queryClient.invalidateQueries({ queryKey: dashboardQueryKey }),
        queryClient.invalidateQueries({ queryKey: walletQueryKey }),
        queryClient.invalidateQueries({ queryKey: budgetQueryKey }),
      ]);
      toast.success("Đã thêm giao dịch.");
      onClose();
    },
    onError: (error) => {
      if (!(error instanceof ApiError)) {
        setRequestError("Không thể lưu giao dịch. Vui lòng thử lại.");
        return;
      }

      setRequestError(error.message);
      Object.entries(error.errors).forEach(([field, messages]) => {
        if (
          field === "type" ||
          field === "amount" ||
          field === "wallet_id" ||
          field === "category_id" ||
          field === "transacted_at" ||
          field === "note" ||
          field === "labels"
        ) {
          form.setError(field, { type: "server", message: messages[0] });
        }
      });
    },
  });

  function submit(values: TransactionFormValues): void {
    const parsed = transactionSchema.parse(values);

    setRequestError(null);
    mutation.mutate({
      type: parsed.type,
      amount: Number(parsed.amount),
      wallet_id: Number(parsed.wallet_id),
      category_id: parsed.category_id === "none" ? null : Number(parsed.category_id),
      transacted_at: parsed.transacted_at,
      note: parsed.note || null,
      labels: parsed.labels,
    });
  }

  return (
    <div
      className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4 backdrop-blur-sm"
      onMouseDown={(event) => {
        if (event.currentTarget === event.target && !mutation.isPending) {
          onClose();
        }
      }}
    >
      <div
        role="dialog"
        aria-modal="true"
        aria-labelledby="transaction-dialog-title"
        className="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-xl"
      >
        <div className="flex items-center justify-between border-b border-slate-100 p-5">
          <h2 id="transaction-dialog-title" className="text-xl font-bold text-slate-900">
            Thêm giao dịch
          </h2>
          <button
            type="button"
            aria-label="Đóng"
            onClick={onClose}
            disabled={mutation.isPending}
            className="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600 disabled:opacity-50"
          >
            <X className="size-5" />
          </button>
        </div>

        <form onSubmit={form.handleSubmit(submit)} className="space-y-4 p-5">
          {requestError ? (
            <p role="alert" className="rounded-xl bg-danger-50 px-3 py-2 text-sm text-danger-600">
              {requestError}
            </p>
          ) : null}

          <div>
            <div className="grid grid-cols-2 gap-3">
              {(["expense", "income"] as const).map((type) => (
                <button
                  key={type}
                  type="button"
                  onClick={() => form.setValue("type", type, { shouldValidate: true })}
                  className={`rounded-xl border py-2.5 font-medium tracking-wide transition-colors ${
                    selectedType === type
                      ? type === "expense"
                        ? "border-danger-200 bg-danger-50 text-danger-600"
                        : "border-success-200 bg-success-50 text-success-600"
                      : "border-slate-200 bg-white text-slate-600 hover:bg-slate-50"
                  }`}
                >
                  {type === "expense" ? "Chi tiêu" : "Thu nhập"}
                </button>
              ))}
            </div>
            <FieldError message={form.formState.errors.type?.message} />
          </div>

          <Field label="Số tiền" error={form.formState.errors.amount?.message}>
            <div className="relative">
              <span className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm text-slate-500">
                {resolveCurrencyCode(selectedWallet?.currency)}
              </span>
              <input
                {...form.register("amount")}
                type="number"
                min="0.01"
                step="0.01"
                placeholder="0.00"
                className="block w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-14 text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
              />
            </div>
          </Field>

          <Field label="Ví" error={form.formState.errors.wallet_id?.message}>
            <select
              {...form.register("wallet_id")}
              className="block w-full rounded-xl border border-slate-200 bg-white py-2.5 text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
            >
              {wallets.map((wallet) => (
                <option key={wallet.id} value={wallet.id}>
                  {wallet.name}
                </option>
              ))}
            </select>
          </Field>

          <Field label="Danh mục" error={form.formState.errors.category_id?.message}>
            <select
              {...form.register("category_id")}
              className="block w-full rounded-xl border border-slate-200 bg-white py-2.5 text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
            >
              <option value="none">Không phân loại</option>
              {privateCategories.length > 0 ? <optgroup label="Danh mục riêng">{privateCategories.map((category) => <option key={category.id} value={category.id}>{category.name}</option>)}</optgroup> : null}
              <optgroup label="Danh mục hệ thống">{systemCategories.map((category) => <option key={category.id} value={category.id}>{category.name}</option>)}</optgroup>
            </select>
            <Link href="/categories" className="mt-2 inline-flex text-xs font-medium text-primary-700 hover:text-primary-800">Quản lý danh mục riêng →</Link>
          </Field>

          <Field label="Ngày" error={form.formState.errors.transacted_at?.message}>
            <input
              {...form.register("transacted_at")}
              type="date"
              className="block w-full rounded-xl border border-slate-200 bg-white py-2.5 text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
            />
          </Field>

          <Field label="Ghi chú (tùy chọn)" error={form.formState.errors.note?.message}>
            <input
              {...form.register("note")}
              placeholder="Khoản này dùng cho gì?"
              className="block w-full rounded-xl border border-slate-200 bg-white py-2.5 text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
            />
          </Field>

          <Field label="Nhãn (cách nhau bởi dấu phẩy)" error={form.formState.errors.labels?.message}>
            <input
              {...form.register("labels")}
              placeholder="an-uong, cong-viec"
              className="block w-full rounded-xl border border-slate-200 bg-white py-2.5 text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
            />
          </Field>

          <div className="flex justify-end gap-3 border-t border-slate-100 pt-4">
            <button
              type="button"
              onClick={onClose}
              disabled={mutation.isPending}
              className="rounded-xl border border-slate-200 bg-white px-4 py-2 font-medium text-slate-700 transition-colors hover:bg-slate-50 disabled:opacity-50"
            >
              Hủy
            </button>
            <button
              type="submit"
              disabled={mutation.isPending}
              className="rounded-xl bg-primary-600 px-6 py-2 font-medium text-white shadow-sm shadow-primary-500/30 transition-colors hover:bg-primary-700 disabled:cursor-not-allowed disabled:opacity-60"
            >
              {mutation.isPending ? "Đang lưu..." : "Lưu"}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}

function Field({
  label,
  error,
  children,
}: {
  label: string;
  error?: string;
  children: ReactNode;
}) {
  return (
    <label className="block">
      <span className="mb-1 block text-sm font-medium text-slate-700">{label}</span>
      {children}
      <FieldError message={error} />
    </label>
  );
}

function FieldError({ message }: { message?: string }) {
  return message ? <span className="mt-1 block text-xs text-danger-600">{message}</span> : null;
}
