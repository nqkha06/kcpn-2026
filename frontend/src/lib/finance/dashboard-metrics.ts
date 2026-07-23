import type { UserDashboardData } from "@/types/finance";
import {
  eachDayOfInterval,
  eachMonthOfInterval,
  endOfMonth,
  endOfYear,
  format,
  isWithinInterval,
  parseISO,
  startOfMonth,
  startOfYear,
  subMonths,
  subYears,
} from "date-fns";

export type DashboardPeriod = "this-month" | "last-month" | "this-year";

const fallbackCategoryColor = "#94a3b8";

function calculateChange(currentValue: number, previousValue: number): number | null {
  if (previousValue === 0) {
    return currentValue === 0 ? 0 : null;
  }

  return ((currentValue - previousValue) / previousValue) * 100;
}

function validDate(value: string): Date | null {
  const date = parseISO(value);

  return Number.isNaN(date.getTime()) ? null : date;
}

function ranges(period: DashboardPeriod, now: Date) {
  if (period === "this-year") {
    const previousYear = subYears(now, 1);

    return {
      current: { start: startOfYear(now), end: endOfYear(now) },
      previous: { start: startOfYear(previousYear), end: endOfYear(previousYear) },
    };
  }

  if (period === "last-month") {
    const previousMonth = subMonths(now, 1);
    const twoMonthsAgo = subMonths(now, 2);

    return {
      current: { start: startOfMonth(previousMonth), end: endOfMonth(previousMonth) },
      previous: { start: startOfMonth(twoMonthsAgo), end: endOfMonth(twoMonthsAgo) },
    };
  }

  const previousMonth = subMonths(now, 1);

  return {
    current: { start: startOfMonth(now), end: endOfMonth(now) },
    previous: { start: startOfMonth(previousMonth), end: endOfMonth(previousMonth) },
  };
}

export function buildDashboardMetrics(
  data: UserDashboardData,
  period: DashboardPeriod,
  now = new Date(),
) {
  const periodRanges = ranges(period, now);
  const currentTransactions = data.transactions.filter((transaction) => {
    const date = validDate(transaction.transacted_at);

    return date ? isWithinInterval(date, periodRanges.current) : false;
  });
  const previousTransactions = data.transactions.filter((transaction) => {
    const date = validDate(transaction.transacted_at);

    return date ? isWithinInterval(date, periodRanges.previous) : false;
  });
  const sumByType = (transactions: typeof data.transactions, type: "income" | "expense") =>
    transactions
      .filter((transaction) => transaction.type === type)
      .reduce((total, transaction) => total + transaction.amount, 0);
  const totalIncome = sumByType(currentTransactions, "income");
  const totalExpense = sumByType(currentTransactions, "expense");
  const previousIncome = sumByType(previousTransactions, "income");
  const previousExpense = sumByType(previousTransactions, "expense");
  const chartIntervals =
    period === "this-year"
      ? eachMonthOfInterval(periodRanges.current)
      : eachDayOfInterval(periodRanges.current);
  const chartData = chartIntervals.map((interval) => {
    const keyFormat = period === "this-year" ? "yyyy-MM" : "yyyy-MM-dd";
    const key = format(interval, keyFormat);
    const value = currentTransactions.reduce((total, transaction) => {
      const date = validDate(transaction.transacted_at);

      if (!date || format(date, keyFormat) !== key) {
        return total;
      }

      return total + transaction.amount * (transaction.type === "income" ? 1 : -1);
    }, 0);

    return {
      name: period === "this-year" ? `T${format(interval, "M")}` : format(interval, "dd/MM"),
      value,
    };
  });
  const categoryMap = new Map(data.categories.map((category) => [category.id, category]));
  const totalsByCategory = new Map<number | "uncategorized", number>();

  currentTransactions
    .filter((transaction) => transaction.type === "expense")
    .forEach((transaction) => {
      const categoryId = transaction.category_id ?? "uncategorized";
      totalsByCategory.set(categoryId, (totalsByCategory.get(categoryId) ?? 0) + transaction.amount);
    });

  const categoryData = [...totalsByCategory.entries()]
    .map(([categoryId, value]) => {
      const category = categoryId === "uncategorized" ? undefined : categoryMap.get(categoryId);

      return {
        name: category?.name ?? "Không phân loại",
        value,
        color: category?.color || fallbackCategoryColor,
      };
    })
    .sort((left, right) => right.value - left.value);

  return {
    totalBalance: data.wallets.reduce((total, wallet) => total + wallet.current_balance, 0),
    totalIncome,
    totalExpense,
    incomeChange: calculateChange(totalIncome, previousIncome),
    expenseChange: calculateChange(totalExpense, previousExpense),
    chartData,
    categoryData,
    recentTransactions: [...data.transactions]
      .sort(
        (left, right) =>
          new Date(right.transacted_at).getTime() - new Date(left.transacted_at).getTime(),
      )
      .slice(0, 10),
  };
}
