import { apiClient } from "@/lib/api/client";
import type { AdminDashboardData } from "@/types/admin";
import type { ApiSuccess } from "@/types/api";

export const adminDashboardQueryKey = ["admin", "dashboard"] as const;

export const adminDashboardService = {
  show: async (): Promise<AdminDashboardData> => {
    const response = await apiClient.get<ApiSuccess<AdminDashboardData>>("/admin/dashboard");

    return response.data;
  },
};
