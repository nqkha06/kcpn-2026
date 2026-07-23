import { apiClient } from "@/lib/api/client";
import type {
    AdminAppearanceData,
    AdminMenu,
    AdminMenuPayload,
    AdminPage,
    AdminPagePayload,
} from "@/types/admin";
import type { ApiSuccess, PaginatedResponse, QueryParams } from "@/types/api";

export const adminPagesQueryKey = ["admin", "pages"] as const;
export const adminMenusQueryKey = ["admin", "menus"] as const;
export const adminAppearanceQueryKey = ["admin", "appearance"] as const;

export const adminPageService = {
    list: (query: QueryParams = {}) =>
        apiClient.get<PaginatedResponse<AdminPage>>("/admin/pages", { query }),
    show: async (id: number) =>
        (await apiClient.get<ApiSuccess<AdminPage>>(`/admin/pages/${id}`)).data,
    create: async (payload: AdminPagePayload) =>
        (await apiClient.post<ApiSuccess<AdminPage>>("/admin/pages", payload))
            .data,
    update: async (id: number, payload: AdminPagePayload) =>
        (
            await apiClient.patch<ApiSuccess<AdminPage>>(
                `/admin/pages/${id}`,
                payload,
            )
        ).data,
    destroy: async (id: number): Promise<void> => {
        await apiClient.delete<ApiSuccess<Record<string, never>>>(
            `/admin/pages/${id}`,
        );
    },
};

export const adminMenuService = {
    list: (query: QueryParams = {}) =>
        apiClient.get<PaginatedResponse<AdminMenu>>("/admin/menus", { query }),
    parentOptions: async (exclude?: number) =>
        (
            await apiClient.get<ApiSuccess<AdminMenu[]>>(
                "/admin/menus/parent-options",
                { query: exclude ? { exclude } : {} },
            )
        ).data,
    show: async (id: number) =>
        (await apiClient.get<ApiSuccess<AdminMenu>>(`/admin/menus/${id}`)).data,
    create: async (payload: AdminMenuPayload) =>
        (await apiClient.post<ApiSuccess<AdminMenu>>("/admin/menus", payload))
            .data,
    update: async (id: number, payload: AdminMenuPayload) =>
        (
            await apiClient.patch<ApiSuccess<AdminMenu>>(
                `/admin/menus/${id}`,
                payload,
            )
        ).data,
    destroy: async (id: number): Promise<void> => {
        await apiClient.delete<ApiSuccess<Record<string, never>>>(
            `/admin/menus/${id}`,
        );
    },
};

export const adminAppearanceService = {
    show: async () =>
        (
            await apiClient.get<ApiSuccess<AdminAppearanceData>>(
                "/admin/appearance",
            )
        ).data,
    update: async (payload: FormData) =>
        (
            await apiClient.post<ApiSuccess<AdminAppearanceData>>(
                "/admin/appearance",
                payload,
            )
        ).data,
};
