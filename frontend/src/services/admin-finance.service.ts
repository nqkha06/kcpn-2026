import { apiClient } from "@/lib/api/client";
import type {
    AdminBudget,
    AdminBudgetOptions,
    AdminBudgetPayload,
    AdminCategory,
    AdminCategoryPayload,
    AdminTransaction,
    AdminTransactionOptions,
    AdminTransactionPayload,
} from "@/types/admin";
import type { ApiSuccess, PaginatedResponse, QueryParams } from "@/types/api";

export const adminCategoriesQueryKey = ["admin", "categories"] as const;
export const adminTransactionsQueryKey = ["admin", "transactions"] as const;
export const adminBudgetsQueryKey = ["admin", "budgets"] as const;

export const adminCategoryService = {
    list: (query: QueryParams = {}) =>
        apiClient.get<PaginatedResponse<AdminCategory>>("/admin/categories", {
            query,
        }),
    show: async (id: number) =>
        (
            await apiClient.get<ApiSuccess<AdminCategory>>(
                `/admin/categories/${id}`,
            )
        ).data,
    create: async (payload: AdminCategoryPayload) =>
        (
            await apiClient.post<ApiSuccess<AdminCategory>>(
                "/admin/categories",
                payload,
            )
        ).data,
    update: async (id: number, payload: AdminCategoryPayload) =>
        (
            await apiClient.patch<ApiSuccess<AdminCategory>>(
                `/admin/categories/${id}`,
                payload,
            )
        ).data,
    destroy: async (id: number): Promise<void> => {
        await apiClient.delete<ApiSuccess<Record<string, never>>>(
            `/admin/categories/${id}`,
        );
    },
};

export const adminTransactionService = {
    list: (query: QueryParams = {}) =>
        apiClient.get<PaginatedResponse<AdminTransaction>>(
            "/admin/transactions",
            { query },
        ),
    options: async () =>
        (
            await apiClient.get<ApiSuccess<AdminTransactionOptions>>(
                "/admin/transactions/options",
            )
        ).data,
    show: async (id: number) =>
        (
            await apiClient.get<ApiSuccess<AdminTransaction>>(
                `/admin/transactions/${id}`,
            )
        ).data,
    create: async (payload: AdminTransactionPayload) =>
        (
            await apiClient.post<ApiSuccess<AdminTransaction>>(
                "/admin/transactions",
                payload,
            )
        ).data,
    update: async (id: number, payload: AdminTransactionPayload) =>
        (
            await apiClient.patch<ApiSuccess<AdminTransaction>>(
                `/admin/transactions/${id}`,
                payload,
            )
        ).data,
    destroy: async (id: number): Promise<void> => {
        await apiClient.delete<ApiSuccess<Record<string, never>>>(
            `/admin/transactions/${id}`,
        );
    },
};

export const adminBudgetService = {
    list: (query: QueryParams = {}) =>
        apiClient.get<PaginatedResponse<AdminBudget>>("/admin/budgets", {
            query,
        }),
    options: async () =>
        (
            await apiClient.get<ApiSuccess<AdminBudgetOptions>>(
                "/admin/budgets/options",
            )
        ).data,
    show: async (id: number) =>
        (await apiClient.get<ApiSuccess<AdminBudget>>(`/admin/budgets/${id}`))
            .data,
    create: async (payload: AdminBudgetPayload) =>
        (
            await apiClient.post<ApiSuccess<AdminBudget>>(
                "/admin/budgets",
                payload,
            )
        ).data,
    update: async (id: number, payload: AdminBudgetPayload) =>
        (
            await apiClient.patch<ApiSuccess<AdminBudget>>(
                `/admin/budgets/${id}`,
                payload,
            )
        ).data,
    destroy: async (id: number): Promise<void> => {
        await apiClient.delete<ApiSuccess<Record<string, never>>>(
            `/admin/budgets/${id}`,
        );
    },
};
