"use client";

import { AdminDataTable, type AdminTableColumn } from "@/components/admin/admin-data-table";
import { AdminPageHeader } from "@/components/admin/admin-page-header";
import { ConfirmDeleteDialog } from "@/components/admin/confirm-delete-dialog";
import { ErrorState } from "@/components/common/error-state";
import { LoadingState } from "@/components/common/loading-state";
import { Badge } from "@/components/ui/badge";
import { adminListQuery } from "@/features/admin/access-control/list-query";
import {
  adminPermissionService,
  adminPermissionsQueryKey,
  adminRolesQueryKey,
} from "@/services/admin-access-control.service";
import type { AdminPermission } from "@/types/admin";
import { keepPreviousData, useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { useRouter, useSearchParams } from "next/navigation";
import { useMemo, useState } from "react";
import { toast } from "sonner";

const columns: AdminTableColumn<AdminPermission>[] = [
  { id: "name", label: "Name", sortable: true },
  { id: "guard_name", label: "Guard" },
  {
    id: "roles_count",
    label: "Roles",
    render: (permission) => <Badge variant="secondary">{permission.roles_count ?? 0}</Badge>,
  },
  { id: "created_at", label: "Created At", sortable: true },
];

export function PermissionsListView() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const serializedQuery = searchParams.toString();
  const queryClient = useQueryClient();
  const [selectedPermission, setSelectedPermission] = useState<AdminPermission | null>(null);
  const query = useMemo(
    () => adminListQuery(new URLSearchParams(serializedQuery), ["search", "sort", "direction", "per_page", "page"]),
    [serializedQuery],
  );
  const permissionsQuery = useQuery({
    queryKey: [...adminPermissionsQueryKey, serializedQuery],
    queryFn: () => adminPermissionService.list(query),
    placeholderData: keepPreviousData,
  });
  const deleteMutation = useMutation({
    mutationFn: adminPermissionService.destroy,
    onSuccess: async () => {
      setSelectedPermission(null);
      await Promise.all([
        queryClient.invalidateQueries({ queryKey: adminPermissionsQueryKey }),
        queryClient.invalidateQueries({ queryKey: adminRolesQueryKey }),
      ]);
      toast.success("Permission deleted successfully");
    },
    onError: (error: Error) => toast.error(error.message),
  });

  if (permissionsQuery.isLoading) return <LoadingState label="Loading permissions..." />;
  if (permissionsQuery.isError || !permissionsQuery.data) {
    return <div className="p-4"><ErrorState message="Unable to load permissions." onRetry={() => void permissionsQuery.refetch()} /></div>;
  }

  return (
    <div className="flex flex-1 flex-col gap-4 p-4 sm:gap-6">
      <AdminPageHeader
        title="Permissions"
        description="Manage your permissions here."
        createHref="/admin/permissions/create"
        createLabel="Add Permission"
      />
      <AdminDataTable
        data={permissionsQuery.data.data}
        columns={columns}
        meta={permissionsQuery.data.meta}
        isFetching={permissionsQuery.isFetching}
        onEdit={(permission) => router.push(`/admin/permissions/${permission.id}/edit`)}
        onDelete={setSelectedPermission}
      />
      <ConfirmDeleteDialog
        open={selectedPermission !== null}
        title="Delete Permission"
        description={`Are you sure you want to delete ${selectedPermission?.name ?? "this permission"}? This action cannot be undone.`}
        isPending={deleteMutation.isPending}
        onCancel={() => setSelectedPermission(null)}
        onConfirm={() => selectedPermission && deleteMutation.mutate(selectedPermission.id)}
      />
    </div>
  );
}
