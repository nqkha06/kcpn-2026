"use client";

import { ErrorState } from "@/components/common/error-state";
import { LoadingState } from "@/components/common/loading-state";
import { PageHeader } from "@/components/layouts/page-header";
import {
  buildDashboardMetrics,
  type DashboardPeriod,
} from "@/lib/finance/dashboard-metrics";
import { formatCurrencyAmount, resolveCurrencyCode } from "@/lib/finance/format";
import { dashboardQueryKey, dashboardService } from "@/services/dashboard.service";
import { useQuery } from "@tanstack/react-query";
import { format, parseISO } from "date-fns";
import {
  ArrowDownRight,
  ArrowUpRight,
  DollarSign,
  TrendingUp,
  Wallet,
} from "lucide-react";
import Link from "next/link";
import { useMemo, useState } from "react";
import {
  Area,
  AreaChart,
  CartesianGrid,
  Cell,
  Pie,
  PieChart,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis,
} from "recharts";

function formatChange(change: number | null): string {
  if (change === null) {
    return "Chưa có dữ liệu so sánh";
  }

  return `${change >= 0 ? "+" : ""}${change.toFixed(1)}%`;
}

export function DashboardView() {
  const [period, setPeriod] = useState<DashboardPeriod>("this-month");
  const dashboardQuery = useQuery({
    queryKey: dashboardQueryKey,
    queryFn: dashboardService.show,
  });
  const metrics = useMemo(
    () => (dashboardQuery.data ? buildDashboardMetrics(dashboardQuery.data, period) : null),
    [dashboardQuery.data, period],
  );

  if (dashboardQuery.isLoading) {
    return <LoadingState label="Đang tải tổng quan tài chính..." />;
  }

  if (dashboardQuery.isError || !dashboardQuery.data || !metrics) {
    return <ErrorState onRetry={() => void dashboardQuery.refetch()} />;
  }

  const { wallets, categories } = dashboardQuery.data;
  const walletMap = new Map(wallets.map((wallet) => [wallet.id, wallet]));
  const categoryMap = new Map(categories.map((category) => [category.id, category]));
  const displayCurrency = resolveCurrencyCode(
    wallets.find((wallet) => wallet.is_default)?.currency ?? wallets[0]?.currency,
  );
  const periodLabel =
    period === "this-month"
      ? "so với tháng trước"
      : period === "last-month"
        ? "so với tháng liền trước"
        : "so với năm trước";

  return (
    <>
      <PageHeader
        title="Tổng quan tài chính"
        description="Theo dõi số dư, dòng tiền và chi tiêu trong nháy mắt."
        action={
          <div>
            <label className="sr-only" htmlFor="dashboard-period">
              Kỳ thống kê
            </label>
            <select
              id="dashboard-period"
              value={period}
              onChange={(event) => setPeriod(event.target.value as DashboardPeriod)}
              className="block rounded-lg border border-slate-200 bg-white p-2 text-sm font-medium text-slate-700 shadow-sm focus:border-primary-500 focus:ring-primary-500"
            >
              <option value="this-month">Tháng này</option>
              <option value="last-month">Tháng trước</option>
              <option value="this-year">Năm nay</option>
            </select>
          </div>
        }
      />

      <div className="space-y-6">
        <div className="grid grid-cols-1 gap-6 md:grid-cols-3">
          <SummaryCard
            label="Tổng số dư"
            value={formatCurrencyAmount(metrics.totalBalance, displayCurrency)}
            detail="Cập nhật theo số dư ví hiện tại"
            icon={<Wallet className="size-6" />}
            iconClassName="bg-primary-50 text-primary-600"
          />
          <SummaryCard
            label="Tổng thu nhập"
            value={formatCurrencyAmount(metrics.totalIncome, displayCurrency)}
            change={formatChange(metrics.incomeChange)}
            periodLabel={periodLabel}
            icon={<ArrowDownRight className="size-6" />}
            iconClassName="bg-success-50 text-success-600"
            changeClassName="text-success-500"
          />
          <SummaryCard
            label="Tổng chi tiêu"
            value={formatCurrencyAmount(metrics.totalExpense, displayCurrency)}
            change={formatChange(metrics.expenseChange)}
            periodLabel={periodLabel}
            icon={<ArrowUpRight className="size-6" />}
            iconClassName="bg-danger-50 text-danger-600"
            changeClassName="text-danger-500"
          />
        </div>

        <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
          <section className="flex flex-col rounded-2xl border border-slate-100 bg-white p-6 shadow-sm transition-shadow hover:shadow-md lg:col-span-2">
            <h2 className="mb-6 text-lg font-bold tracking-tight text-slate-900">Dòng tiền</h2>
            <div className="h-[300px] w-full grow">
              <ResponsiveContainer width="100%" height="100%">
                <AreaChart data={metrics.chartData} margin={{ top: 10, right: 10, left: -20, bottom: 0 }}>
                  <defs>
                    <linearGradient id="cashFlowColor" x1="0" y1="0" x2="0" y2="1">
                      <stop offset="5%" stopColor="#3b82f6" stopOpacity={0.3} />
                      <stop offset="95%" stopColor="#3b82f6" stopOpacity={0} />
                    </linearGradient>
                  </defs>
                  <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#e2e8f0" />
                  <XAxis dataKey="name" axisLine={false} tickLine={false} tick={{ fill: "#64748b", fontSize: 12 }} dy={10} />
                  <YAxis
                    axisLine={false}
                    tickLine={false}
                    tick={{ fill: "#64748b", fontSize: 12 }}
                    tickFormatter={(value) => formatCurrencyAmount(Number(value), displayCurrency, 0)}
                  />
                  <Tooltip
                    contentStyle={{ borderRadius: 12, border: "none", boxShadow: "0 10px 15px -3px rgb(0 0 0 / 0.1)" }}
                    itemStyle={{ color: "#0f172a", fontWeight: 600 }}
                  />
                  <Area
                    type="monotone"
                    dataKey="value"
                    stroke="#3b82f6"
                    strokeWidth={3}
                    fillOpacity={1}
                    fill="url(#cashFlowColor)"
                  />
                </AreaChart>
              </ResponsiveContainer>
            </div>
          </section>

          <section className="flex flex-col rounded-2xl border border-slate-100 bg-white p-6 shadow-sm transition-shadow hover:shadow-md">
            <h2 className="mb-6 text-lg font-bold tracking-tight text-slate-900">
              Chi tiêu theo danh mục
            </h2>
            <div className="relative flex min-h-[220px] w-full grow items-center justify-center">
              <ResponsiveContainer width="100%" height="100%">
                <PieChart>
                  <Pie
                    data={metrics.categoryData}
                    cx="50%"
                    cy="50%"
                    innerRadius={60}
                    outerRadius={80}
                    paddingAngle={5}
                    dataKey="value"
                    stroke="none"
                  >
                    {metrics.categoryData.map((entry, index) => (
                      <Cell key={`cell-${index}`} fill={entry.color} />
                    ))}
                  </Pie>
                  <Tooltip
                    contentStyle={{ borderRadius: 12, border: "none", boxShadow: "0 4px 6px -1px rgb(0 0 0 / 0.1)" }}
                    itemStyle={{ fontWeight: 500 }}
                    formatter={(value) => [
                      formatCurrencyAmount(Number(value ?? 0), displayCurrency),
                      "Số tiền",
                    ]}
                  />
                </PieChart>
              </ResponsiveContainer>
              <div className="pointer-events-none absolute inset-0 flex flex-col items-center justify-center">
                <span className="text-2xl font-bold text-slate-900">
                  {formatCurrencyAmount(metrics.totalExpense, displayCurrency, 0)}
                </span>
                <span className="text-xs font-medium tracking-wider text-slate-500 uppercase">
                  Tổng
                </span>
              </div>
            </div>
            <div className="mt-4 flex flex-col gap-3">
              {metrics.categoryData.slice(0, 4).map((category, index) => (
                <div key={index} className="flex items-center justify-between text-sm">
                  <div className="flex items-center gap-2">
                    <div className="h-3 w-3 rounded-full" style={{ backgroundColor: category.color }} />
                    <span className="font-medium text-slate-600">{category.name}</span>
                  </div>
                  <span className="font-bold text-slate-900">
                    {formatCurrencyAmount(category.value, displayCurrency)}
                  </span>
                </div>
              ))}
              {metrics.categoryData.length === 0 ? (
                <p className="text-sm text-slate-500">Chưa có dữ liệu chi tiêu trong kỳ đã chọn.</p>
              ) : null}
            </div>
          </section>
        </div>

        <section className="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm transition-shadow hover:shadow-md">
          <div className="flex items-center justify-between border-b border-slate-100 bg-slate-50/50 p-6">
            <h2 className="text-lg font-bold tracking-tight text-slate-900">Giao dịch gần đây</h2>
            <Link href="/transactions" className="text-sm font-medium text-primary-600 hover:text-primary-700">
              Xem tất cả
            </Link>
          </div>
          <div className="overflow-x-auto">
            <table className="w-full text-left text-sm whitespace-nowrap">
              <thead className="border-b border-slate-100 bg-slate-50/50 text-xs font-medium tracking-wider text-slate-500 uppercase">
                <tr>
                  <th className="px-6 py-4">Giao dịch</th>
                  <th className="px-6 py-4">Danh mục</th>
                  <th className="px-6 py-4">Ngày</th>
                  <th className="px-6 py-4">Ví</th>
                  <th className="px-6 py-4 text-right">Số tiền</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100 text-slate-700">
                {metrics.recentTransactions.map((transaction) => {
                  const wallet = walletMap.get(transaction.wallet_id);
                  const category = transaction.category_id
                    ? categoryMap.get(transaction.category_id)
                    : null;
                  const isIncome = transaction.type === "income";

                  return (
                    <tr key={transaction.id} className="group cursor-pointer transition-colors hover:bg-slate-50/80">
                      <td className="flex items-center gap-3 px-6 py-4 font-medium text-slate-900">
                        <span className={isIncome ? "rounded-lg bg-success-50 p-2 text-success-600 transition-colors group-hover:bg-success-100" : "rounded-lg bg-slate-100 p-2 text-slate-600 transition-colors group-hover:bg-slate-200"}>
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
                      <td className="px-6 py-4 text-slate-500">{wallet?.name || "Không xác định"}</td>
                      <td className={`px-6 py-4 text-right font-bold ${isIncome ? "text-success-600" : "text-slate-900"}`}>
                        {isIncome ? "+" : "-"}
                        {formatCurrencyAmount(transaction.amount, wallet?.currency || displayCurrency)}
                      </td>
                    </tr>
                  );
                })}
                {metrics.recentTransactions.length === 0 ? (
                  <tr>
                    <td colSpan={5} className="px-6 py-12 text-center text-slate-500">
                      Chưa có giao dịch nào.
                    </td>
                  </tr>
                ) : null}
              </tbody>
            </table>
          </div>
        </section>
      </div>
    </>
  );
}

type SummaryCardProps = {
  label: string;
  value: string;
  detail: string;
  change?: never;
  periodLabel?: never;
  changeClassName?: never;
  icon: React.ReactNode;
  iconClassName: string;
} | {
  label: string;
  value: string;
  detail?: never;
  change: string;
  periodLabel: string;
  changeClassName: string;
  icon: React.ReactNode;
  iconClassName: string;
};

function SummaryCard(props: SummaryCardProps) {
  const { label, value, icon, iconClassName } = props;

  return (
    <section className="flex flex-col justify-between rounded-2xl border border-slate-100 bg-white p-6 shadow-sm transition-shadow hover:shadow-md">
      <div className="flex items-start justify-between gap-4">
        <div>
          <p className="mb-1 text-sm font-medium text-slate-500">{label}</p>
          <p className="text-3xl font-bold text-slate-900">{value}</p>
        </div>
        <span className={`rounded-xl p-3 ${iconClassName}`}>{icon}</span>
      </div>
      {props.detail ? (
        <div className="mt-4 text-sm text-slate-400">{props.detail}</div>
      ) : (
        <div className="mt-4 flex items-center text-sm">
          <TrendingUp className={`mr-1 size-4 ${props.changeClassName}`} />
          <span className={`font-medium ${props.changeClassName}`}>{props.change}</span>
          <span className="ml-2 text-slate-400">{props.periodLabel}</span>
        </div>
      )}
    </section>
  );
}
