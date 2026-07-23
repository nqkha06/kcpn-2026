"use client";

import { categorySchema, type CategoryFormValues } from "@/features/categories/category-schema";
import { ApiError } from "@/lib/api/errors";
import { budgetQueryKey } from "@/services/budget.service";
import { categoryQueryKey, categoryService } from "@/services/category.service";
import { dashboardQueryKey } from "@/services/dashboard.service";
import { transactionQueryKey } from "@/services/transaction.service";
import type { FinanceCategory } from "@/types/finance";
import { zodResolver } from "@hookform/resolvers/zod";
import { useMutation, useQueryClient } from "@tanstack/react-query";
import { X } from "lucide-react";
import { useState } from "react";
import { useForm, useWatch } from "react-hook-form";
import { toast } from "sonner";

export function CategoryDialog({ category, onClose }: { category?: FinanceCategory; onClose: () => void }) {
  const queryClient = useQueryClient();
  const [requestError, setRequestError] = useState<string | null>(null);
  const form = useForm<CategoryFormValues>({
    resolver: zodResolver(categorySchema),
    defaultValues: {
      name: category?.name ?? "",
      color: category?.color ?? "#0EA5E9",
      description: category?.description ?? "",
    },
  });
  const selectedColor = useWatch({ control: form.control, name: "color" });
  const mutation = useMutation({
    mutationFn: (values: CategoryFormValues) => {
      const payload = { ...values, description: values.description || null };
      return category ? categoryService.update(category.id, payload) : categoryService.create(payload);
    },
    onSuccess: async () => {
      await Promise.all([
        queryClient.invalidateQueries({ queryKey: categoryQueryKey }),
        queryClient.invalidateQueries({ queryKey: transactionQueryKey }),
        queryClient.invalidateQueries({ queryKey: budgetQueryKey }),
        queryClient.invalidateQueries({ queryKey: dashboardQueryKey }),
      ]);
      toast.success(category ? "Đã cập nhật danh mục riêng." : "Đã tạo danh mục riêng.");
      onClose();
    },
    onError: (error: Error) => {
      if (!(error instanceof ApiError)) {
        setRequestError("Không thể lưu danh mục. Vui lòng thử lại.");
        return;
      }
      setRequestError(error.message);
      (["name", "color", "description"] as const).forEach((field) => {
        const message = error.firstError(field);
        if (message) form.setError(field, { message });
      });
    },
  });

  return <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4 backdrop-blur-sm" onMouseDown={(event) => { if (event.currentTarget === event.target && !mutation.isPending) onClose(); }}>
    <div role="dialog" aria-modal="true" aria-labelledby="category-dialog-title" className="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-xl">
      <div className="flex items-center justify-between border-b border-slate-100 p-5"><div><h2 id="category-dialog-title" className="text-xl font-bold text-slate-900">{category ? "Sửa danh mục riêng" : "Thêm danh mục riêng"}</h2><p className="mt-1 text-sm text-slate-500">Chỉ tài khoản của bạn nhìn thấy danh mục này.</p></div><button type="button" aria-label="Đóng" onClick={onClose} disabled={mutation.isPending} className="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600"><X className="size-5" /></button></div>
      <form onSubmit={form.handleSubmit((values) => mutation.mutate(values))} className="space-y-4 p-5">
        {requestError ? <p role="alert" className="rounded-xl bg-danger-50 px-3 py-2 text-sm text-danger-600">{requestError}</p> : null}
        <CategoryField label="Tên danh mục" error={form.formState.errors.name?.message}><input {...form.register("name")} placeholder="Ví dụ: Thú cưng" className="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-primary-500 focus:ring-primary-500" /></CategoryField>
        <CategoryField label="Màu sắc" error={form.formState.errors.color?.message}><div className="flex gap-3"><input type="color" aria-label="Chọn màu" value={selectedColor} onChange={(event) => form.setValue("color", event.target.value, { shouldValidate: true })} className="h-11 w-14 cursor-pointer rounded-xl border border-slate-200 bg-white p-1" /><input {...form.register("color")} className="h-11 flex-1 rounded-xl border border-slate-200 px-3 text-sm uppercase focus:border-primary-500 focus:ring-primary-500" /></div></CategoryField>
        <CategoryField label="Mô tả (tùy chọn)" error={form.formState.errors.description?.message}><textarea {...form.register("description")} rows={3} placeholder="Danh mục này dùng cho khoản nào?" className="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-primary-500 focus:ring-primary-500" /></CategoryField>
        <div className="flex justify-end gap-3 border-t border-slate-100 pt-4"><button type="button" onClick={onClose} disabled={mutation.isPending} className="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Hủy</button><button type="submit" disabled={mutation.isPending} className="rounded-xl bg-primary-600 px-5 py-2 text-sm font-medium text-white hover:bg-primary-700 disabled:opacity-60">{mutation.isPending ? "Đang lưu..." : category ? "Lưu thay đổi" : "Tạo danh mục"}</button></div>
      </form>
    </div>
  </div>;
}

function CategoryField({ label, error, children }: { label: string; error?: string; children: React.ReactNode }) {
  return <label className="block"><span className="mb-2 block text-sm font-medium text-slate-700">{label}</span>{children}{error ? <span className="mt-1 block text-xs text-danger-600">{error}</span> : null}</label>;
}
