"use client";

import { ErrorState } from "@/components/common/error-state";
import { LoadingState } from "@/components/common/loading-state";
import { PageHeader } from "@/components/layouts/page-header";
import { BudgetDialog } from "@/features/budgets/budget-dialog";
import { formatCurrencyAmount, resolveCurrencyCode } from "@/lib/finance/format";
import { budgetQueryKey, budgetService } from "@/services/budget.service";
import { categoryQueryKey, categoryService } from "@/services/category.service";
import { walletQueryKey, walletService } from "@/services/wallet.service";
import { useQuery } from "@tanstack/react-query";
import { AlertCircle, Plus, Target } from "lucide-react";
import { useState } from "react";
import { toast } from "sonner";

export function BudgetsView() {
  const [isModalOpen, setIsModalOpen] = useState(false);
  const budgetsQuery = useQuery({ queryKey: budgetQueryKey, queryFn: budgetService.list });
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

  if (budgetsQuery.isLoading || categoriesQuery.isLoading || walletsQuery.isLoading) {
    return <LoadingState label="Đang tải kế hoạch ngân sách..." />;
  }

  if (
    budgetsQuery.isError ||
    categoriesQuery.isError ||
    walletsQuery.isError ||
    !budgetsQuery.data
  ) {
    return (
      <ErrorState
        onRetry={() => {
          void budgetsQuery.refetch();
          void categoriesQuery.refetch();
          void walletsQuery.refetch();
        }}
      />
    );
  }

  const budgets = budgetsQuery.data;
  const categories = categoriesQuery.data ?? [];
  const wallets = walletsQuery.data ?? [];
  const displayCurrency = resolveCurrencyCode(
    wallets.find((wallet) => wallet.is_default)?.currency ?? wallets[0]?.currency,
  );
  const totalBudget = budgets.reduce((total, budget) => total + budget.amount_limit, 0);
  const totalSpent = budgets.reduce((total, budget) => total + budget.spent, 0);
  const overallPercentage =
    totalBudget > 0 ? Math.min((totalSpent / totalBudget) * 100, 100) : 0;

  function openBudgetDialog(): void {
    if (categories.length === 0) {
      toast.error("Chưa có danh mục hoạt động để tạo ngân sách.");
      return;
    }

    setIsModalOpen(true);
  }

  return (
    <>
      <PageHeader
        title="Kế hoạch ngân sách"
        description="Đặt giới hạn theo tháng và bám sát mục tiêu."
        action={
          <button
            type="button"
            onClick={openBudgetDialog}
            className="flex items-center gap-2 rounded-xl bg-primary-600 px-4 py-2 font-medium text-white shadow-sm shadow-primary-500/30 transition-colors hover:bg-primary-700"
          >
            <Plus className="size-4" />
            Tạo ngân sách
          </button>
        }
      />

      <div className="space-y-6">
        <section className="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm transition-shadow hover:shadow-md md:p-8">
          <h2 className="mb-6 text-lg font-bold text-slate-900">Tổng quan tháng</h2>
          <div className="flex flex-col items-center gap-8 md:flex-row">
            <div className="relative h-48 w-48 shrink-0">
              <svg className="h-full w-full -rotate-90 transform" viewBox="0 0 100 100" aria-hidden="true">
                <circle
                  className="stroke-current text-slate-100"
                  strokeWidth="10"
                  cx="50"
                  cy="50"
                  r="40"
                  fill="transparent"
                />
                <circle
                  className={`stroke-current transition-all duration-1000 ease-out ${
                    overallPercentage > 100
                      ? "text-danger-500"
                      : overallPercentage > 80
                        ? "text-amber-500"
                        : "text-primary-500"
                  }`}
                  strokeWidth="10"
                  strokeLinecap="round"
                  cx="50"
                  cy="50"
                  r="40"
                  fill="transparent"
                  strokeDasharray={`${overallPercentage * 2.51327} 251.327`}
                />
              </svg>
              <div className="absolute inset-0 flex flex-col items-center justify-center">
                <span className="text-3xl font-bold text-slate-900">{Math.round(overallPercentage)}%</span>
                <span className="text-sm font-medium text-slate-500">Đã dùng</span>
              </div>
            </div>

            <div className="w-full grow space-y-6">
              <div className="grid grid-cols-2 gap-4">
                <Summary label="Tổng hạn mức" value={formatCurrencyAmount(totalBudget, displayCurrency)} />
                <Summary label="Tổng đã chi" value={formatCurrencyAmount(totalSpent, displayCurrency)} />
              </div>
              <div className="flex items-start gap-3 rounded-xl border border-primary-100 bg-primary-50 p-4 text-primary-900">
                <Target className="mt-0.5 size-5 text-primary-500" />
                <div>
                  <p className="text-sm font-semibold">Bạn đang làm rất tốt!</p>
                  <p className="mt-1 text-sm text-primary-700/80">
                    Bạn vẫn còn{" "}
                    <strong>{formatCurrencyAmount(totalBudget - totalSpent, displayCurrency)}</strong>{" "}
                    cho phần còn lại của tháng.
                  </p>
                </div>
              </div>
            </div>
          </div>
        </section>

        <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
          {budgets.map((budget) => {
            const category = budget.category ??
              categories.find((item) => item.id === budget.category_id);
            const percentage = Math.min((budget.spent / budget.amount_limit) * 100, 100);
            const isWarning = percentage >= 80 && percentage < 100;
            const isDanger = percentage >= 100;

            return (
              <article key={budget.id} className="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm transition-shadow hover:shadow-md">
                <div className="mb-4 flex items-start justify-between">
                  <div className="flex items-center gap-3">
                    <span
                      className="flex size-10 items-center justify-center rounded-xl"
                      style={{
                        backgroundColor: `${category?.color}15`,
                        color: category?.color ?? undefined,
                      }}
                    >
                      <span
                        className="size-3 rounded-full"
                        style={{ backgroundColor: category?.color ?? undefined }}
                      />
                    </span>
                    <div>
                      <h3 className="font-bold text-slate-900">{category?.name}</h3>
                      <p className="text-xs font-medium tracking-wider text-slate-500 uppercase">
                        {budget.period === "monthly" ? "Hàng tháng" : "Hàng năm"}
                      </p>
                    </div>
                  </div>
                  <div className="text-right">
                    <p className="font-bold text-slate-900">
                      {formatCurrencyAmount(budget.spent, displayCurrency, 0)}
                    </p>
                    <p className="text-xs font-medium text-slate-500">
                      trên {formatCurrencyAmount(budget.amount_limit, displayCurrency, 0)}
                    </p>
                  </div>
                </div>

                <div className="space-y-2">
                  <div className="flex justify-between text-xs font-medium">
                    <span className={isDanger ? "text-danger-600" : isWarning ? "text-amber-600" : "text-slate-500"}>
                      {Math.round(percentage)}% đã dùng
                    </span>
                    <span className="text-slate-500">
                      {formatCurrencyAmount(budget.amount_limit - budget.spent, displayCurrency, 0)} còn lại
                    </span>
                  </div>
                  <div className="h-2.5 w-full overflow-hidden rounded-full bg-slate-100">
                    <div
                      className={`h-full rounded-full transition-all duration-500 ease-out ${
                        isDanger ? "bg-danger-500" : isWarning ? "bg-amber-500" : "bg-primary-500"
                      }`}
                      style={{ width: `${percentage}%` }}
                    />
                  </div>
                </div>

                {isDanger ? (
                  <div className="mt-4 flex items-center gap-2 rounded-lg bg-danger-50 px-3 py-2 text-xs font-medium text-danger-600">
                    <AlertCircle className="size-4" />
                    Bạn đã vượt quá hạn mức ngân sách này!
                  </div>
                ) : null}
              </article>
            );
          })}

          {budgets.length === 0 ? (
            <div className="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center text-sm text-slate-500 md:col-span-2">
              Chưa có ngân sách nào. Hãy tạo ngân sách đầu tiên của bạn.
            </div>
          ) : null}
        </div>
      </div>

      {isModalOpen ? (
        <BudgetDialog categories={categories} onClose={() => setIsModalOpen(false)} />
      ) : null}
    </>
  );
}

function Summary({ label, value }: { label: string; value: string }) {
  return (
    <div className="rounded-xl border border-slate-100 bg-slate-50 p-4">
      <p className="mb-1 text-sm font-medium text-slate-500">{label}</p>
      <p className="text-2xl font-bold text-slate-900">{value}</p>
    </div>
  );
}
