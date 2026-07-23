"use client";

import { AdminDataTable, type AdminTableColumn } from "@/components/admin/admin-data-table";
import { AdminPageHeader } from "@/components/admin/admin-page-header";
import { ConfirmDeleteDialog } from "@/components/admin/confirm-delete-dialog";
import { ErrorState } from "@/components/common/error-state";
import { LoadingState } from "@/components/common/loading-state";
import { Badge } from "@/components/ui/badge";
import { adminListQuery } from "@/features/admin/access-control/list-query";
import { adminRoleService, adminRolesQueryKey } from "@/services/admin-access-control.service";
import type { AdminRole } from "@/types/admin";
import { keepPreviousData, useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { useRouter, useSearchParams } from "next/navigation";
import { useMemo, useState } from "react";
import { toast } from "sonner";

const columns: AdminTableColumn<AdminRole>[] = [
  { id: "name", label: "Name", sortable: true },
  {
    id: "permissions",
    label: "Permissions",
    render: (role) => role.permissions.length > 0 ? (
      <div className="flex max-w-xl flex-wrap gap-1">
        {role.permissions.map((permission) => (
          <Badge key={permission.id} variant="secondary" className="font-normal">{permission.name}</Badge>
        ))}
      </div>
    ) : <span className="text-muted-foreground">None</span>,
  },
  { id: "created_at", label: "Created At", sortable: true },
];

export function RolesListView() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const serializedQuery = searchParams.toString();
  const queryClient = useQueryClient();
  const [selectedRole, setSelectedRole] = useState<AdminRole | null>(null);
  const query = useMemo(
    () => adminListQuery(new URLSearchParams(serializedQuery), ["search", "sort", "direction", "per_page", "page"]),
    [serializedQuery],
  );
  const rolesQuery = useQuery({
    queryKey: [...adminRolesQueryKey, serializedQuery],
    queryFn: () => adminRoleService.list(query),
    placeholderData: keepPreviousData,
  });
  const deleteMutation = useMutation({
    mutationFn: adminRoleService.destroy,
    onSuccess: async () => {
      setSelectedRole(null);
      await queryClient.invalidateQueries({ queryKey: adminRolesQueryKey });
      toast.success("Role deleted successfully");
    },
    onError: (error: Error) => toast.error(error.message),
  });

  if (rolesQuery.isLoading) return <LoadingState label="Loading roles..." />;
  if (rolesQuery.isError || !rolesQuery.data) {
    return <div className="p-4"><ErrorState message="Unable to load roles." onRetry={() => void rolesQuery.refetch()} /></div>;
  }

  return (
    <div className="flex flex-1 flex-col gap-4 p-4 sm:gap-6">
      <AdminPageHeader
        title="Roles"
        description="Manage your roles and their permissions here."
        createHref="/admin/roles/create"
        createLabel="Add Role"
      />
      <AdminDataTable
        data={rolesQuery.data.data}
        columns={columns}
        meta={rolesQuery.data.meta}
        isFetching={rolesQuery.isFetching}
        onEdit={(role) => router.push(`/admin/roles/${role.id}/edit`)}
        onDelete={setSelectedRole}
      />
      <ConfirmDeleteDialog
        open={selectedRole !== null}
        title="Delete Role"
        description={`Are you sure you want to delete ${selectedRole?.name ?? "this role"}? This action cannot be undone.`}
        isPending={deleteMutation.isPending}
        onCancel={() => setSelectedRole(null)}
        onConfirm={() => selectedRole && deleteMutation.mutate(selectedRole.id)}
      />
    </div>
  );
}
