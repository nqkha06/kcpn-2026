"use client";

import { ErrorState } from "@/components/common/error-state";
import { LoadingState } from "@/components/common/loading-state";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import {
  AdminFormActions,
  AdminFormField,
  SelectionGrid,
} from "@/features/admin/access-control/selection-grid";
import {
  createUserSchema,
  updateUserSchema,
  type UserFormValues,
} from "@/features/admin/access-control/access-control-schema";
import { ApiError } from "@/lib/api/errors";
import {
  adminRoleService,
  adminRolesQueryKey,
  adminUserService,
  adminUsersQueryKey,
} from "@/services/admin-access-control.service";
import type { AdminUserPayload } from "@/types/admin";
import { zodResolver } from "@hookform/resolvers/zod";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { useRouter } from "next/navigation";
import { useEffect } from "react";
import { useForm, useWatch } from "react-hook-form";
import { toast } from "sonner";

export function UserFormView({ userId }: { userId?: number }) {
  const isEditing = userId !== undefined;
  const router = useRouter();
  const queryClient = useQueryClient();
  const rolesQuery = useQuery({
    queryKey: [...adminRolesQueryKey, "options"],
    queryFn: adminRoleService.options,
    staleTime: 5 * 60 * 1000,
  });
  const userQuery = useQuery({
    queryKey: [...adminUsersQueryKey, userId],
    queryFn: () => adminUserService.show(userId as number),
    enabled: isEditing,
  });
  const form = useForm<UserFormValues>({
    resolver: zodResolver(isEditing ? updateUserSchema : createUserSchema),
    defaultValues: {
      name: "",
      email: "",
      password: "",
      password_confirmation: "",
      roles: [],
    },
  });

  useEffect(() => {
    if (userQuery.data) {
      form.reset({
        name: userQuery.data.name,
        email: userQuery.data.email,
        password: "",
        password_confirmation: "",
        roles: userQuery.data.roles.map((role) => role.id),
      });
    }
  }, [form, userQuery.data]);

  const mutation = useMutation({
    mutationFn: (values: UserFormValues) => {
      const payload: AdminUserPayload = {
        name: values.name,
        email: values.email,
        password: values.password || undefined,
        password_confirmation: values.password ? values.password_confirmation : undefined,
        roles: values.roles,
      };

      return isEditing
        ? adminUserService.update(userId, payload)
        : adminUserService.create(payload);
    },
    onSuccess: async () => {
      await queryClient.invalidateQueries({ queryKey: adminUsersQueryKey });
      toast.success(isEditing ? "User updated successfully" : "User created successfully");
      router.push("/admin/users");
    },
    onError: (error: Error) => {
      if (!(error instanceof ApiError)) {
        form.setError("root", { message: "Unable to save user." });
        return;
      }

      const fieldMap = ["name", "email", "password", "roles"] as const;
      let hasFieldError = false;
      fieldMap.forEach((field) => {
        const message = error.firstError(field) ?? error.firstError(`${field}.0`);
        if (message) {
          hasFieldError = true;
          form.setError(field, { message });
        }
      });
      if (!hasFieldError) form.setError("root", { message: error.message });
    },
  });
  const selectedRoles = useWatch({ control: form.control, name: "roles" });

  if (rolesQuery.isLoading || (isEditing && userQuery.isLoading)) {
    return <LoadingState label="Loading user form..." />;
  }
  if (rolesQuery.isError || (isEditing && (userQuery.isError || !userQuery.data))) {
    return <div className="p-4"><ErrorState message="Unable to load user form data." /></div>;
  }

  return (
    <div className="flex flex-1 flex-col gap-4 p-4 sm:gap-6">
      <div>
        <h2 className="text-2xl font-bold tracking-tight">{isEditing ? "Edit User" : "Create User"}</h2>
        <p className="text-muted-foreground">
          {isEditing ? "Update user information." : "Add a new user to the system."}
        </p>
      </div>
      <Card>
        <CardHeader><CardTitle>User Information</CardTitle></CardHeader>
        <CardContent>
          <form onSubmit={form.handleSubmit((values) => mutation.mutate(values))} className="space-y-4">
            <AdminFormField label="Name" htmlFor="name" error={form.formState.errors.name?.message}>
              <input id="name" {...form.register("name")} className="h-9 rounded-md border bg-background px-3 text-sm" />
            </AdminFormField>
            <AdminFormField label="Email" htmlFor="email" error={form.formState.errors.email?.message}>
              <input id="email" type="email" {...form.register("email")} className="h-9 rounded-md border bg-background px-3 text-sm" />
            </AdminFormField>
            <AdminFormField
              label={isEditing ? "Password (leave blank to keep current)" : "Password"}
              htmlFor="password"
              error={form.formState.errors.password?.message}
            >
              <input id="password" type="password" autoComplete="new-password" {...form.register("password")} className="h-9 rounded-md border bg-background px-3 text-sm" />
            </AdminFormField>
            <AdminFormField label="Confirm Password" htmlFor="password_confirmation" error={form.formState.errors.password_confirmation?.message}>
              <input id="password_confirmation" type="password" autoComplete="new-password" {...form.register("password_confirmation")} className="h-9 rounded-md border bg-background px-3 text-sm" />
            </AdminFormField>
            <AdminFormField label="Assign Roles" error={form.formState.errors.roles?.message}>
              <SelectionGrid
                items={(rolesQuery.data ?? []).map((role) => ({ id: role.id, label: role.name }))}
                selected={selectedRoles}
                emptyMessage="No roles available. Please create roles first."
                onToggle={(roleId, checked) =>
                  form.setValue(
                    "roles",
                    checked
                      ? [...selectedRoles, roleId]
                      : selectedRoles.filter((id) => id !== roleId),
                    { shouldDirty: true },
                  )
                }
              />
            </AdminFormField>
            {form.formState.errors.root?.message ? (
              <p className="text-sm text-destructive">{form.formState.errors.root.message}</p>
            ) : null}
            <AdminFormActions
              submitLabel={isEditing ? "Update User" : "Create User"}
              isPending={mutation.isPending}
              onCancel={() => router.back()}
            />
          </form>
        </CardContent>
      </Card>
    </div>
  );
}
