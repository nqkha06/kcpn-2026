"use client";

import { budgetSchema, type BudgetFormValues } from "@/features/budgets/budget-schema";
import { ApiError } from "@/lib/api/errors";
import { budgetQueryKey, budgetService } from "@/services/budget.service";
import { dashboardQueryKey } from "@/services/dashboard.service";
import type { FinanceCategory } from "@/types/finance";
import { zodResolver } from "@hookform/resolvers/zod";
import { useMutation, useQueryClient } from "@tanstack/react-query";
import { X } from "lucide-react";
import Link from "next/link";
import { useState, type ReactNode } from "react";
import { useForm } from "react-hook-form";
import { toast } from "sonner";

interface BudgetDialogProps {
  categories: FinanceCategory[];
  onClose: () => void;
}

export function BudgetDialog({ categories, onClose }: BudgetDialogProps) {
  const queryClient = useQueryClient();
  const [requestError, setRequestError] = useState<string | null>(null);
  const form = useForm<BudgetFormValues>({
    resolver: zodResolver(budgetSchema),
    defaultValues: {
      category_id: categories[0] ? String(categories[0].id) : "",
      amount_limit: "",
      period: "monthly",
      note: "",
    },
  });
  const systemCategories = categories.filter((category) => !category.is_private);
  const privateCategories = categories.filter((category) => category.is_private);
  const mutation = useMutation({
    mutationFn: budgetService.create,
    onSuccess: async () => {
      await Promise.all([
        queryClient.invalidateQueries({ queryKey: budgetQueryKey }),
        queryClient.invalidateQueries({ queryKey: dashboardQueryKey }),
      ]);
      toast.success("Đã tạo ngân sách.");
      onClose();
    },
    onError: (error) => {
      if (!(error instanceof ApiError)) {
        setRequestError("Không thể tạo ngân sách. Vui lòng thử lại.");
        return;
      }

      setRequestError(error.message);
      Object.entries(error.errors).forEach(([field, messages]) => {
        if (
          field === "category_id" ||
          field === "amount_limit" ||
          field === "period" ||
          field === "note"
        ) {
          form.setError(field, { type: "server", message: messages[0] });
        }
      });
    },
  });

  function submit(values: BudgetFormValues): void {
    const parsed = budgetSchema.parse(values);

    setRequestError(null);
    mutation.mutate({
      category_id: Number(parsed.category_id),
      amount_limit: Number(parsed.amount_limit),
      period: parsed.period,
      note: parsed.note || null,
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
        aria-labelledby="budget-dialog-title"
        className="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-xl"
      >
        <div className="flex items-center justify-between border-b border-slate-100 p-5">
          <h2 id="budget-dialog-title" className="text-xl font-bold text-slate-900">
            Tạo ngân sách
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

          <Field label="Danh mục" error={form.formState.errors.category_id?.message}>
            <select
              {...form.register("category_id")}
              className="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"
            >
              {privateCategories.length > 0 ? <optgroup label="Danh mục riêng">{privateCategories.map((category) => <option key={category.id} value={category.id}>{category.name}</option>)}</optgroup> : null}
              <optgroup label="Danh mục hệ thống">{systemCategories.map((category) => <option key={category.id} value={category.id}>{category.name}</option>)}</optgroup>
            </select>
            <Link href="/categories" className="mt-2 inline-flex text-xs font-medium text-primary-700 hover:text-primary-800">Quản lý danh mục riêng →</Link>
          </Field>

          <Field label="Hạn mức" error={form.formState.errors.amount_limit?.message}>
            <input
              {...form.register("amount_limit")}
              type="number"
              min="0.01"
              step="0.01"
              placeholder="0.00"
              className="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"
            />
          </Field>

          <Field label="Chu kỳ" error={form.formState.errors.period?.message}>
            <select
              {...form.register("period")}
              className="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"
            >
              <option value="monthly">Hàng tháng</option>
              <option value="yearly">Hàng năm</option>
            </select>
          </Field>

          <Field label="Ghi chú" error={form.formState.errors.note?.message}>
            <textarea
              {...form.register("note")}
              rows={3}
              placeholder="Ghi chú ngân sách (tùy chọn)"
              className="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"
            />
          </Field>

          <div className="flex gap-3 pt-2">
            <button
              type="submit"
              disabled={mutation.isPending}
              className="rounded-xl bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700 disabled:opacity-60"
            >
              Lưu ngân sách
            </button>
            <button
              type="button"
              onClick={onClose}
              disabled={mutation.isPending}
              className="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-50"
            >
              Hủy
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
      <span className="mb-2 block text-sm font-medium text-slate-700">{label}</span>
      {children}
      {error ? <span className="mt-1 block text-xs text-danger-600">{error}</span> : null}
    </label>
  );
}
