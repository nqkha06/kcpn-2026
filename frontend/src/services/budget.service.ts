import { apiClient } from "@/lib/api/client";
import type { ApiSuccess } from "@/types/api";
import type { BudgetPayload, FinanceBudget } from "@/types/finance";

export const budgetQueryKey = ["user", "budgets"] as const;

export const budgetService = {
  list: async (): Promise<FinanceBudget[]> => {
    const response = await apiClient.get<ApiSuccess<FinanceBudget[]>>("/user/budgets");

    return response.data;
  },
  create: async (payload: BudgetPayload): Promise<FinanceBudget> => {
    const response = await apiClient.post<ApiSuccess<FinanceBudget>>("/user/budgets", payload);

    return response.data;
  },
};
