import { budgetService } from "@/services/budget.service";
import { transactionService } from "@/services/transaction.service";
import type { BudgetPayload, TransactionPayload } from "@/types/finance";
import { beforeEach, describe, expect, it, vi } from "vitest";

describe("finance module services", () => {
  beforeEach(() => {
    vi.stubEnv("NEXT_PUBLIC_API_URL", "http://localhost:8000/api/v1");
  });

  it("sends transaction filters to the paginated endpoint", async () => {
    const fetchMock = vi.spyOn(globalThis, "fetch").mockResolvedValue(
      jsonResponse({
        success: true,
        message: "ok",
        data: [],
        meta: { current_page: 2, last_page: 3, per_page: 15, total: 31 },
        links: { first: "", last: "", prev: "", next: "" },
      }),
    );

    await transactionService.list({
      search: "lunch",
      type: "expense",
      page: 2,
      per_page: 15,
      sort: "amount",
      direction: "asc",
    });

    const url = new URL(String(fetchMock.mock.calls[0][0]));
    expect(url.pathname).toBe("/api/v1/user/transactions");
    expect(url.searchParams.get("search")).toBe("lunch");
    expect(url.searchParams.get("type")).toBe("expense");
    expect(url.searchParams.get("page")).toBe("2");
    expect(url.searchParams.get("sort")).toBe("amount");
  });

  it("creates a transaction through the centralized endpoint", async () => {
    const payload: TransactionPayload = {
      wallet_id: 4,
      category_id: null,
      type: "expense",
      amount: 250000,
      transacted_at: "2026-07-22",
      note: "Lunch",
      labels: "food, office",
    };
    const fetchMock = vi.spyOn(globalThis, "fetch").mockResolvedValue(
      jsonResponse({ success: true, message: "created", data: { id: 10 } }, 201),
    );

    await transactionService.create(payload);

    expect(fetchMock.mock.calls[0][1]?.method).toBe("POST");
    expect(fetchMock.mock.calls[0][1]?.body).toBe(JSON.stringify(payload));
  });

  it("loads and creates budgets through the user API", async () => {
    const fetchMock = vi.spyOn(globalThis, "fetch");
    fetchMock
      .mockResolvedValueOnce(jsonResponse({ success: true, message: "ok", data: [] }))
      .mockResolvedValueOnce(
        jsonResponse({ success: true, message: "created", data: { id: 5 } }, 201),
      );
    const payload: BudgetPayload = {
      category_id: 2,
      amount_limit: 1_500_000,
      period: "monthly",
      note: null,
    };

    await budgetService.list();
    await budgetService.create(payload);

    expect(fetchMock.mock.calls[0][0]).toBe("http://localhost:8000/api/v1/user/budgets");
    expect(fetchMock.mock.calls[0][1]?.method).toBe("GET");
    expect(fetchMock.mock.calls[1][1]?.method).toBe("POST");
    expect(fetchMock.mock.calls[1][1]?.body).toBe(JSON.stringify(payload));
  });
});

function jsonResponse(body: unknown, status = 200): Response {
  return new Response(JSON.stringify(body), {
    status,
    headers: { "Content-Type": "application/json" },
  });
}
