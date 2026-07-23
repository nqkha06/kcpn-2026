import { apiClient } from "@/lib/api/client";
import type { ApiSuccess } from "@/types/api";
import type { AuthUser } from "@/types/auth";
import type { UserSettingsData } from "@/types/finance";

export const settingsQueryKey = ["user", "settings"] as const;

export const settingsService = {
  show: async (): Promise<UserSettingsData> => {
    const response = await apiClient.get<ApiSuccess<UserSettingsData>>("/user/settings");

    return response.data;
  },
  updateProfile: async (payload: { name: string; email: string }): Promise<AuthUser> => {
    const response = await apiClient.patch<ApiSuccess<AuthUser>>("/user/settings/profile", payload);

    return response.data;
  },
  updatePreferences: async (payload: { currency: string }): Promise<UserSettingsData> => {
    const response = await apiClient.patch<ApiSuccess<UserSettingsData>>(
      "/user/settings/preferences",
      payload,
    );

    return response.data;
  },
};
