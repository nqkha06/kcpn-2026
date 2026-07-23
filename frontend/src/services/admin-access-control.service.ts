import { apiClient } from "@/lib/api/client";
import type {
  AdminPermission,
  AdminPermissionPayload,
  AdminRole,
  AdminRolePayload,
  AdminUser,
  AdminUserPayload,
} from "@/types/admin";
import type { ApiSuccess, PaginatedResponse, QueryParams } from "@/types/api";

export const adminUsersQueryKey = ["admin", "users"] as const;
export const adminRolesQueryKey = ["admin", "roles"] as const;
export const adminPermissionsQueryKey = ["admin", "permissions"] as const;

export const adminUserService = {
  list: (query: QueryParams = {}) =>
    apiClient.get<PaginatedResponse<AdminUser>>("/admin/users", { query }),
  show: async (id: number): Promise<AdminUser> => {
    const response = await apiClient.get<ApiSuccess<AdminUser>>(`/admin/users/${id}`);
    return response.data;
  },
  create: async (payload: AdminUserPayload): Promise<AdminUser> => {
    const response = await apiClient.post<ApiSuccess<AdminUser>>("/admin/users", payload);
    return response.data;
  },
  update: async (id: number, payload: AdminUserPayload): Promise<AdminUser> => {
    const response = await apiClient.patch<ApiSuccess<AdminUser>>(`/admin/users/${id}`, payload);
    return response.data;
  },
  destroy: async (id: number): Promise<void> => {
    await apiClient.delete<ApiSuccess<Record<string, never>>>(`/admin/users/${id}`);
  },
};

export const adminRoleService = {
  list: (query: QueryParams = {}) =>
    apiClient.get<PaginatedResponse<AdminRole>>("/admin/roles", { query }),
  options: async (): Promise<AdminRole[]> => {
    const response = await apiClient.get<ApiSuccess<AdminRole[]>>("/admin/roles/options");
    return response.data;
  },
  show: async (id: number): Promise<AdminRole> => {
    const response = await apiClient.get<ApiSuccess<AdminRole>>(`/admin/roles/${id}`);
    return response.data;
  },
  create: async (payload: AdminRolePayload): Promise<AdminRole> => {
    const response = await apiClient.post<ApiSuccess<AdminRole>>("/admin/roles", payload);
    return response.data;
  },
  update: async (id: number, payload: AdminRolePayload): Promise<AdminRole> => {
    const response = await apiClient.patch<ApiSuccess<AdminRole>>(`/admin/roles/${id}`, payload);
    return response.data;
  },
  destroy: async (id: number): Promise<void> => {
    await apiClient.delete<ApiSuccess<Record<string, never>>>(`/admin/roles/${id}`);
  },
};

export const adminPermissionService = {
  list: (query: QueryParams = {}) =>
    apiClient.get<PaginatedResponse<AdminPermission>>("/admin/permissions", { query }),
  options: async (): Promise<AdminPermission[]> => {
    const response = await apiClient.get<ApiSuccess<AdminPermission[]>>(
      "/admin/permissions/options",
    );
    return response.data;
  },
  show: async (id: number): Promise<AdminPermission> => {
    const response = await apiClient.get<ApiSuccess<AdminPermission>>(
      `/admin/permissions/${id}`,
    );
    return response.data;
  },
  create: async (payload: AdminPermissionPayload): Promise<AdminPermission> => {
    const response = await apiClient.post<ApiSuccess<AdminPermission>>(
      "/admin/permissions",
      payload,
    );
    return response.data;
  },
  update: async (id: number, payload: AdminPermissionPayload): Promise<AdminPermission> => {
    const response = await apiClient.patch<ApiSuccess<AdminPermission>>(
      `/admin/permissions/${id}`,
      payload,
    );
    return response.data;
  },
  destroy: async (id: number): Promise<void> => {
    await apiClient.delete<ApiSuccess<Record<string, never>>>(`/admin/permissions/${id}`);
  },
};
