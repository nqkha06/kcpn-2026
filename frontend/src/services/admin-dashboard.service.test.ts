import { adminDashboardService } from "@/services/admin-dashboard.service";
import { beforeEach, describe, expect, it, vi } from "vitest";

describe("admin dashboard service", () => {
  beforeEach(() => {
    vi.stubEnv("NEXT_PUBLIC_API_URL", "http://localhost:8000/api/v1");
  });

  it("loads dashboard data through the centralized admin endpoint", async () => {
    const dashboard = {
      stats: {
        users: 10,
        wallets: 8,
        activeCategories: 4,
        activeBudgets: 2,
        postedIncomeThisMonth: 100,
        postedExpenseThisMonth: 50,
        netThisMonth: 50,
        pendingTransactions: 1,
      },
      monthlyFlow: [],
      topExpenseCategories: [],
      recentTransactions: [],
    };
    const fetchMock = vi.spyOn(globalThis, "fetch").mockResolvedValue(
      new Response(
        JSON.stringify({ success: true, message: "ok", data: dashboard }),
        { status: 200, headers: { "Content-Type": "application/json" } },
      ),
    );

    await expect(adminDashboardService.show()).resolves.toEqual(dashboard);
    expect(fetchMock.mock.calls[0][0]).toBe(
      "http://localhost:8000/api/v1/admin/dashboard",
    );
    expect(fetchMock.mock.calls[0][1]?.credentials).toBe("include");
  });
});
