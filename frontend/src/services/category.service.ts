import { apiClient } from "@/lib/api/client";
import type { ApiSuccess } from "@/types/api";
import type { CategoryPayload, FinanceCategory } from "@/types/finance";

export const categoryQueryKey = ["user", "categories"] as const;

export const categoryService = {
  list: async (): Promise<FinanceCategory[]> => {
    const response = await apiClient.get<ApiSuccess<FinanceCategory[]>>("/user/categories");

    return response.data;
  },
  create: async (payload: CategoryPayload): Promise<FinanceCategory> => {
    const response = await apiClient.post<ApiSuccess<FinanceCategory>>("/user/categories", payload);

    return response.data;
  },
  update: async (id: number, payload: CategoryPayload): Promise<FinanceCategory> => {
    const response = await apiClient.patch<ApiSuccess<FinanceCategory>>(
      `/user/categories/${id}`,
      payload,
    );

    return response.data;
  },
  destroy: async (id: number): Promise<void> => {
    await apiClient.delete<ApiSuccess<Record<string, never>>>(`/user/categories/${id}`);
  },
};
