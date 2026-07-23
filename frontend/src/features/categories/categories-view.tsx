"use client";

import { ErrorState } from "@/components/common/error-state";
import { LoadingState } from "@/components/common/loading-state";
import { CategoryDialog } from "@/features/categories/category-dialog";
import { budgetQueryKey } from "@/services/budget.service";
import { categoryQueryKey, categoryService } from "@/services/category.service";
import { dashboardQueryKey } from "@/services/dashboard.service";
import { transactionQueryKey } from "@/services/transaction.service";
import type { FinanceCategory } from "@/types/finance";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { LockKeyhole, Pencil, Plus, Tags, Trash2 } from "lucide-react";
import { useState } from "react";
import { toast } from "sonner";

export function CategoriesView() {
  const queryClient = useQueryClient();
  const [editing, setEditing] = useState<FinanceCategory | null | "create">(null);
  const [deleting, setDeleting] = useState<FinanceCategory | null>(null);
  const categoriesQuery = useQuery({ queryKey: categoryQueryKey, queryFn: categoryService.list });
  const deleteMutation = useMutation({
    mutationFn: categoryService.destroy,
    onSuccess: async () => {
      setDeleting(null);
      await Promise.all([
        queryClient.invalidateQueries({ queryKey: categoryQueryKey }),
        queryClient.invalidateQueries({ queryKey: transactionQueryKey }),
        queryClient.invalidateQueries({ queryKey: budgetQueryKey }),
        queryClient.invalidateQueries({ queryKey: dashboardQueryKey }),
      ]);
      toast.success("Đã xóa danh mục riêng.");
    },
    onError: (error: Error) => toast.error(error.message),
  });
  if (categoriesQuery.isLoading) return <LoadingState label="Đang tải danh mục..." />;
  if (categoriesQuery.isError || !categoriesQuery.data) return <ErrorState message="Không thể tải danh mục." onRetry={() => void categoriesQuery.refetch()} />;
  const privateCategories = categoriesQuery.data.filter((category) => category.is_private);

  return <div className="space-y-6"><div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"><div><div className="mb-2 flex items-center gap-2 text-sm font-semibold text-primary-700"><LockKeyhole className="size-4" />Không chia sẻ với người dùng khác</div><h1 className="text-3xl font-bold tracking-tight text-slate-900">Danh mục riêng</h1><p className="mt-2 text-slate-500">Tạo các danh mục chỉ dành cho tài khoản của bạn.</p></div><button type="button" onClick={() => setEditing("create")} className="inline-flex items-center justify-center gap-2 rounded-xl bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-primary-500/30 hover:bg-primary-700"><Plus className="size-4" />Thêm danh mục</button></div>
    {privateCategories.length === 0 ? <div className="rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center"><span className="mx-auto flex size-14 items-center justify-center rounded-2xl bg-primary-50 text-primary-600"><Tags className="size-7" /></span><h2 className="mt-4 text-lg font-semibold text-slate-900">Chưa có danh mục riêng</h2><p className="mx-auto mt-2 max-w-md text-sm text-slate-500">Danh mục hệ thống vẫn dùng bình thường. Hãy tạo danh mục riêng cho nhu cầu đặc thù của bạn.</p><button type="button" onClick={() => setEditing("create")} className="mt-5 rounded-xl bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700">Tạo danh mục đầu tiên</button></div> : <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">{privateCategories.map((category) => <article key={category.id} className="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"><div className="flex items-start justify-between gap-4"><span className="flex size-11 items-center justify-center rounded-2xl" style={{ backgroundColor: `${category.color ?? "#94A3B8"}20`, color: category.color ?? "#64748B" }}><Tags className="size-5" /></span><div className="flex gap-1"><button type="button" aria-label={`Sửa ${category.name}`} onClick={() => setEditing(category)} className="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700"><Pencil className="size-4" /></button><button type="button" aria-label={`Xóa ${category.name}`} onClick={() => setDeleting(category)} className="rounded-lg p-2 text-slate-400 hover:bg-danger-50 hover:text-danger-600"><Trash2 className="size-4" /></button></div></div><div className="mt-4 flex items-center gap-2"><span className="size-2.5 rounded-full" style={{ backgroundColor: category.color ?? "#94A3B8" }} /><h2 className="font-semibold text-slate-900">{category.name}</h2></div><p className="mt-2 min-h-10 text-sm leading-5 text-slate-500">{category.description || "Danh mục riêng của bạn"}</p></article>)}</div>}
    {editing ? <CategoryDialog category={editing === "create" ? undefined : editing} onClose={() => setEditing(null)} /> : null}
    {deleting ? <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4 backdrop-blur-sm"><div role="dialog" aria-modal="true" className="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl"><h2 className="text-lg font-bold text-slate-900">Xóa danh mục “{deleting.name}”?</h2><p className="mt-2 text-sm leading-6 text-slate-500">Chỉ có thể xóa danh mục chưa được dùng trong giao dịch hoặc ngân sách. Dữ liệu tài chính của bạn sẽ không bị xóa theo.</p><div className="mt-6 flex justify-end gap-3"><button type="button" onClick={() => setDeleting(null)} disabled={deleteMutation.isPending} className="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700">Hủy</button><button type="button" onClick={() => deleteMutation.mutate(deleting.id)} disabled={deleteMutation.isPending} className="rounded-xl bg-danger-600 px-4 py-2 text-sm font-medium text-white disabled:opacity-60">{deleteMutation.isPending ? "Đang xóa..." : "Xóa danh mục"}</button></div></div></div> : null}
  </div>;
}
