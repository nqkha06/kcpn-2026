import { apiClient } from "@/lib/api/client";
import type { ApiSuccess } from "@/types/api";
import type { UserDashboardData } from "@/types/finance";

export const dashboardQueryKey = ["user", "dashboard"] as const;

export const dashboardService = {
  show: async (): Promise<UserDashboardData> => {
    const response = await apiClient.get<ApiSuccess<UserDashboardData>>("/user/dashboard");

    return response.data;
  },
};
