import { apiClient } from "@/lib/api/client";
import type { ApiSuccess } from "@/types/api";
import type { PublicSiteConfiguration } from "@/types/site";

export const publicConfigurationQueryKey = ["public", "configuration"] as const;

export const publicSiteService = {
  configuration: async (): Promise<PublicSiteConfiguration> => {
    const response = await apiClient.get<ApiSuccess<PublicSiteConfiguration>>(
      "/public/configuration",
    );

    return response.data;
  },
};
