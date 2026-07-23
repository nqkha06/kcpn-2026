import { buildDashboardMetrics } from "@/lib/finance/dashboard-metrics";
import type { UserDashboardData } from "@/types/finance";
import { describe, expect, it } from "vitest";

const data: UserDashboardData = {
  categories: [{ id: 1, is_private: false, name: "Ăn uống", color: "#ef4444", description: null, status: "active" }],
  wallets: [
    {
      id: 1,
      name: "Tiền mặt",
      currency: "VND",
      opening_balance: 1_000_000,
      current_balance: 1_200_000,
      is_default: true,
      created_at: null,
      updated_at: null,
    },
  ],
  transactions: [
    {
      id: 1,
      wallet_id: 1,
      category_id: null,
      type: "income",
      amount: 500_000,
      transacted_at: "2026-07-05",
      status: "posted",
      note: null,
      labels: [],
      created_at: null,
      updated_at: null,
    },
    {
      id: 2,
      wallet_id: 1,
      category_id: 1,
      type: "expense",
      amount: 200_000,
      transacted_at: "2026-07-10",
      status: "posted",
      note: null,
      labels: [],
      created_at: null,
      updated_at: null,
    },
    {
      id: 3,
      wallet_id: 1,
      category_id: 1,
      type: "expense",
      amount: 100_000,
      transacted_at: "2026-06-10",
      status: "posted",
      note: null,
      labels: [],
      created_at: null,
      updated_at: null,
    },
  ],
};

describe("buildDashboardMetrics", () => {
  it("calculates balances, period totals, comparison and category totals", () => {
    const result = buildDashboardMetrics(data, "this-month", new Date("2026-07-22T08:00:00Z"));

    expect(result.totalBalance).toBe(1_200_000);
    expect(result.totalIncome).toBe(500_000);
    expect(result.totalExpense).toBe(200_000);
    expect(result.expenseChange).toBe(100);
    expect(result.categoryData).toEqual([
      { name: "Ăn uống", value: 200_000, color: "#ef4444" },
    ]);
    expect(result.chartData).toHaveLength(31);
  });
});
