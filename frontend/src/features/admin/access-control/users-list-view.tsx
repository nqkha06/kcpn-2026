"use client";

import {
  AdminDataTable,
  type AdminTableColumn,
} from "@/components/admin/admin-data-table";
import { AdminPageHeader } from "@/components/admin/admin-page-header";
import { ConfirmDeleteDialog } from "@/components/admin/confirm-delete-dialog";
import { ErrorState } from "@/components/common/error-state";
import { LoadingState } from "@/components/common/loading-state";
import { Badge } from "@/components/ui/badge";
import { adminListQuery } from "@/features/admin/access-control/list-query";
import {
  adminRoleService,
  adminRolesQueryKey,
  adminUserService,
  adminUsersQueryKey,
} from "@/services/admin-access-control.service";
import type { AdminUser } from "@/types/admin";
import { keepPreviousData, useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { useRouter, useSearchParams } from "next/navigation";
import { useMemo, useState } from "react";
import { toast } from "sonner";

const columns: AdminTableColumn<AdminUser>[] = [
  { id: "name", label: "Name", sortable: true },
  { id: "email", label: "Email", sortable: true },
  {
    id: "roles",
    label: "Roles",
    render: (user) =>
      user.roles.length > 0 ? (
        <div className="flex flex-wrap gap-1">
          {user.roles.map((role) => <Badge key={role.id} variant="secondary">{role.name}</Badge>)}
        </div>
      ) : (
        <span className="text-muted-foreground">None</span>
      ),
  },
  { id: "created_at", label: "Created At", sortable: true },
];

export function UsersListView() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const serializedQuery = searchParams.toString();
  const queryClient = useQueryClient();
  const [selectedUser, setSelectedUser] = useState<AdminUser | null>(null);
  const query = useMemo(
    () => adminListQuery(new URLSearchParams(serializedQuery), [
      "search", "email", "role", "created_date", "sort", "direction", "per_page", "page",
    ]),
    [serializedQuery],
  );
  const usersQuery = useQuery({
    queryKey: [...adminUsersQueryKey, serializedQuery],
    queryFn: () => adminUserService.list(query),
    placeholderData: keepPreviousData,
  });
  const rolesQuery = useQuery({
    queryKey: [...adminRolesQueryKey, "options"],
    queryFn: adminRoleService.options,
    staleTime: 5 * 60 * 1000,
  });
  const deleteMutation = useMutation({
    mutationFn: adminUserService.destroy,
    onSuccess: async () => {
      setSelectedUser(null);
      await queryClient.invalidateQueries({ queryKey: adminUsersQueryKey });
      toast.success("User deleted successfully");
    },
    onError: (error: Error) => toast.error(error.message),
  });

  if (usersQuery.isLoading) return <LoadingState label="Loading users..." />;
  if (usersQuery.isError || !usersQuery.data) {
    return <div className="p-4"><ErrorState message="Unable to load users." onRetry={() => void usersQuery.refetch()} /></div>;
  }

  return (
    <div className="flex flex-1 flex-col gap-4 p-4 sm:gap-6">
      <AdminPageHeader
        title="Users"
        description="Manage your users and their roles here."
        createHref="/admin/users/create"
        createLabel="Add User"
      />
      <AdminDataTable
        data={usersQuery.data.data}
        columns={columns}
        meta={usersQuery.data.meta}
        isFetching={usersQuery.isFetching}
        filters={[
          {
            key: "role",
            label: "Role",
            type: "select",
            placeholder: "All Roles",
            options: (rolesQuery.data ?? []).map((role) => ({ value: role.name, label: role.name })),
          },
          { key: "email", label: "Email", type: "input", placeholder: "Filter by email..." },
          { key: "created_date", label: "Created Date", type: "date" },
        ]}
        onEdit={(user) => router.push(`/admin/users/${user.id}/edit`)}
        onDelete={setSelectedUser}
      />
      <ConfirmDeleteDialog
        open={selectedUser !== null}
        title="Delete User"
        description={`Are you sure you want to delete ${selectedUser?.name ?? "this user"}? This action cannot be undone.`}
        isPending={deleteMutation.isPending}
        onCancel={() => setSelectedUser(null)}
        onConfirm={() => selectedUser && deleteMutation.mutate(selectedUser.id)}
      />
    </div>
  );
}
