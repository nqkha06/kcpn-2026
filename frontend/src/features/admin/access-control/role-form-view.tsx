"use client";

import { ErrorState } from "@/components/common/error-state";
import { LoadingState } from "@/components/common/loading-state";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { roleSchema, type RoleFormValues } from "@/features/admin/access-control/access-control-schema";
import { AdminFormActions, AdminFormField, SelectionGrid } from "@/features/admin/access-control/selection-grid";
import { ApiError } from "@/lib/api/errors";
import {
  adminPermissionService,
  adminPermissionsQueryKey,
  adminRoleService,
  adminRolesQueryKey,
} from "@/services/admin-access-control.service";
import { zodResolver } from "@hookform/resolvers/zod";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { useRouter } from "next/navigation";
import { useEffect } from "react";
import { useForm, useWatch } from "react-hook-form";
import { toast } from "sonner";

export function RoleFormView({ roleId }: { roleId?: number }) {
  const isEditing = roleId !== undefined;
  const router = useRouter();
  const queryClient = useQueryClient();
  const permissionsQuery = useQuery({
    queryKey: [...adminPermissionsQueryKey, "options"],
    queryFn: adminPermissionService.options,
    staleTime: 5 * 60 * 1000,
  });
  const roleQuery = useQuery({
    queryKey: [...adminRolesQueryKey, roleId],
    queryFn: () => adminRoleService.show(roleId as number),
    enabled: isEditing,
  });
  const form = useForm<RoleFormValues>({
    resolver: zodResolver(roleSchema),
    defaultValues: { name: "", permissions: [] },
  });

  useEffect(() => {
    if (roleQuery.data) {
      form.reset({
        name: roleQuery.data.name,
        permissions: roleQuery.data.permissions.map((permission) => permission.id),
      });
    }
  }, [form, roleQuery.data]);

  const mutation = useMutation({
    mutationFn: (values: RoleFormValues) =>
      isEditing
        ? adminRoleService.update(roleId as number, values)
        : adminRoleService.create(values),
    onSuccess: async () => {
      await queryClient.invalidateQueries({ queryKey: adminRolesQueryKey });
      toast.success(isEditing ? "Role updated successfully" : "Role created successfully");
      router.push("/admin/roles");
    },
    onError: (error: Error) => {
      if (!(error instanceof ApiError)) {
        form.setError("root", { message: "Unable to save role." });
        return;
      }
      const nameError = error.firstError("name");
      const permissionsError = error.firstError("permissions") ?? error.firstError("permissions.0");
      if (nameError) form.setError("name", { message: nameError });
      if (permissionsError) form.setError("permissions", { message: permissionsError });
      if (!nameError && !permissionsError) form.setError("root", { message: error.message });
    },
  });
  const selectedPermissions = useWatch({ control: form.control, name: "permissions" });

  if (permissionsQuery.isLoading || (isEditing && roleQuery.isLoading)) {
    return <LoadingState label="Loading role form..." />;
  }
  if (permissionsQuery.isError || (isEditing && (roleQuery.isError || !roleQuery.data))) {
    return <div className="p-4"><ErrorState message="Unable to load role form data." /></div>;
  }

  return (
    <div className="flex flex-1 flex-col gap-4 p-4 sm:gap-6">
      <div>
        <h2 className="text-2xl font-bold tracking-tight">{isEditing ? "Edit Role" : "Create Role"}</h2>
        <p className="text-muted-foreground">
          {isEditing ? "Update role information and permissions." : "Add a new role to the system."}
        </p>
      </div>
      <Card>
        <CardHeader><CardTitle>Role Information</CardTitle></CardHeader>
        <CardContent>
          <form onSubmit={form.handleSubmit((values) => mutation.mutate(values))} className="space-y-6">
            <div className="max-w-xl">
              <AdminFormField label="Role Name" htmlFor="name" error={form.formState.errors.name?.message}>
                <input id="name" placeholder="e.g. Editor" {...form.register("name")} className="h-9 rounded-md border bg-background px-3 text-sm" />
              </AdminFormField>
            </div>
            <AdminFormField label="Assign Permissions" error={form.formState.errors.permissions?.message}>
              <SelectionGrid
                items={(permissionsQuery.data ?? []).map((permission) => ({ id: permission.id, label: permission.name }))}
                selected={selectedPermissions}
                columns="md:grid-cols-3 lg:grid-cols-4"
                emptyMessage="No permissions available. Please create permissions first."
                onToggle={(permissionId, checked) =>
                  form.setValue(
                    "permissions",
                    checked
                      ? [...selectedPermissions, permissionId]
                      : selectedPermissions.filter((id) => id !== permissionId),
                    { shouldDirty: true },
                  )
                }
              />
            </AdminFormField>
            {form.formState.errors.root?.message ? <p className="text-sm text-destructive">{form.formState.errors.root.message}</p> : null}
            <AdminFormActions
              submitLabel={isEditing ? "Save Changes" : "Create Role"}
              isPending={mutation.isPending}
              onCancel={() => router.back()}
            />
          </form>
        </CardContent>
      </Card>
    </div>
  );
}
