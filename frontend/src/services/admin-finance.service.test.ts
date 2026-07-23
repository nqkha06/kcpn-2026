import {
    adminBudgetService,
    adminCategoryService,
    adminTransactionService,
} from "@/services/admin-finance.service";
import type {
    AdminBudgetPayload,
    AdminCategoryPayload,
    AdminTransactionPayload,
} from "@/types/admin";
import { beforeEach, describe, expect, it, vi } from "vitest";

describe("admin finance services", () => {
    beforeEach(() => {
        vi.stubEnv("NEXT_PUBLIC_API_URL", "http://localhost:8000/api/v1");
    });

    it("loads category filters through the centralized API client", async () => {
        const fetchMock = vi.spyOn(globalThis, "fetch").mockResolvedValue(
            jsonResponse({
                success: true,
                message: "ok",
                data: [],
                meta: {
                    current_page: 2,
                    last_page: 3,
                    per_page: 15,
                    total: 31,
                },
                links: { first: "", last: "", prev: null, next: "" },
            }),
        );

        await adminCategoryService.list({
            search: "food",
            status: "active",
            sort: "name",
            direction: "asc",
            page: 2,
        });

        const url = new URL(String(fetchMock.mock.calls[0][0]));
        expect(url.pathname).toBe("/api/v1/admin/categories");
        expect(url.searchParams.get("search")).toBe("food");
        expect(url.searchParams.get("status")).toBe("active");
        expect(url.searchParams.get("sort")).toBe("name");
        expect(url.searchParams.get("page")).toBe("2");
    });

    it("creates and updates transactions with the complete admin payload", async () => {
        const fetchMock = vi.spyOn(globalThis, "fetch");
        fetchMock
            .mockResolvedValueOnce(
                jsonResponse(
                    { success: true, message: "created", data: { id: 8 } },
                    201,
                ),
            )
            .mockResolvedValueOnce(
                jsonResponse({
                    success: true,
                    message: "updated",
                    data: { id: 8 },
                }),
            );
        const payload: AdminTransactionPayload = {
            user_id: 3,
            wallet_id: 4,
            category_id: 2,
            type: "expense",
            amount: 250000,
            transacted_at: "2026-07-22",
            status: "posted",
            note: "Lunch",
            labels: "food, office",
        };

        await adminTransactionService.create(payload);
        await adminTransactionService.update(8, payload);

        expect(fetchMock.mock.calls[0][0]).toBe(
            "http://localhost:8000/api/v1/admin/transactions",
        );
        expect(fetchMock.mock.calls[0][1]?.method).toBe("POST");
        expect(fetchMock.mock.calls[0][1]?.body).toBe(JSON.stringify(payload));
        expect(fetchMock.mock.calls[1][0]).toBe(
            "http://localhost:8000/api/v1/admin/transactions/8",
        );
        expect(fetchMock.mock.calls[1][1]?.method).toBe("PATCH");
    });

    it("loads budget options and deletes budgets", async () => {
        const fetchMock = vi.spyOn(globalThis, "fetch");
        fetchMock
            .mockResolvedValueOnce(
                jsonResponse({
                    success: true,
                    message: "ok",
                    data: {
                        users: [],
                        categories: [],
                        periods: ["monthly"],
                        statuses: ["active"],
                    },
                }),
            )
            .mockResolvedValueOnce(
                jsonResponse({ success: true, message: "deleted", data: {} }),
            );

        await adminBudgetService.options();
        await adminBudgetService.destroy(7);

        expect(fetchMock.mock.calls[0][0]).toBe(
            "http://localhost:8000/api/v1/admin/budgets/options",
        );
        expect(fetchMock.mock.calls[1][0]).toBe(
            "http://localhost:8000/api/v1/admin/budgets/7",
        );
        expect(fetchMock.mock.calls[1][1]?.method).toBe("DELETE");
    });

    it("sends category and budget mutations without changing their domain fields", async () => {
        const fetchMock = vi.spyOn(globalThis, "fetch");
        fetchMock
            .mockResolvedValueOnce(
                jsonResponse(
                    { success: true, message: "created", data: { id: 1 } },
                    201,
                ),
            )
            .mockResolvedValueOnce(
                jsonResponse(
                    { success: true, message: "created", data: { id: 2 } },
                    201,
                ),
            );
        const category: AdminCategoryPayload = {
            name: "Food",
            color: "#22c55e",
            description: "Daily meals",
            status: "active",
        };
        const budget: AdminBudgetPayload = {
            user_id: 3,
            category_id: 1,
            amount_limit: 1_500_000,
            period: "monthly",
            status: "active",
            note: null,
        };

        await adminCategoryService.create(category);
        await adminBudgetService.create(budget);

        expect(fetchMock.mock.calls[0][1]?.body).toBe(JSON.stringify(category));
        expect(fetchMock.mock.calls[1][1]?.body).toBe(JSON.stringify(budget));
    });
});

function jsonResponse(body: unknown, status = 200): Response {
    return new Response(JSON.stringify(body), {
        status,
        headers: { "Content-Type": "application/json" },
    });
}
