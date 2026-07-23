import { apiClient } from "@/lib/api/client";
import type { PaginatedResponse, QueryParams } from "@/types/api";
import type {
  FinanceTransaction,
  TransactionFilters,
  TransactionPayload,
} from "@/types/finance";
import type { ApiSuccess } from "@/types/api";

export const transactionQueryKey = ["user", "transactions"] as const;

export const transactionService = {
  list: (filters: TransactionFilters): Promise<PaginatedResponse<FinanceTransaction>> =>
    apiClient.get<PaginatedResponse<FinanceTransaction>>("/user/transactions", {
      query: filters as unknown as QueryParams,
    }),
  create: async (payload: TransactionPayload): Promise<FinanceTransaction> => {
    const response = await apiClient.post<ApiSuccess<FinanceTransaction>>(
      "/user/transactions",
      payload,
    );

    return response.data;
  },
};
