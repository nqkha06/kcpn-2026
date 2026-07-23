"use client";

import { ErrorState } from "@/components/common/error-state";
import { LoadingState } from "@/components/common/loading-state";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import {
  permissionSchema,
  type PermissionFormValues,
} from "@/features/admin/access-control/access-control-schema";
import { AdminFormActions, AdminFormField } from "@/features/admin/access-control/selection-grid";
import { ApiError } from "@/lib/api/errors";
import {
  adminPermissionService,
  adminPermissionsQueryKey,
  adminRolesQueryKey,
} from "@/services/admin-access-control.service";
import { zodResolver } from "@hookform/resolvers/zod";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { useRouter } from "next/navigation";
import { useEffect } from "react";
import { useForm } from "react-hook-form";
import { toast } from "sonner";

export function PermissionFormView({ permissionId }: { permissionId?: number }) {
  const isEditing = permissionId !== undefined;
  const router = useRouter();
  const queryClient = useQueryClient();
  const permissionQuery = useQuery({
    queryKey: [...adminPermissionsQueryKey, permissionId],
    queryFn: () => adminPermissionService.show(permissionId as number),
    enabled: isEditing,
  });
  const form = useForm<PermissionFormValues>({
    resolver: zodResolver(permissionSchema),
    defaultValues: { name: "" },
  });

  useEffect(() => {
    if (permissionQuery.data) form.reset({ name: permissionQuery.data.name });
  }, [form, permissionQuery.data]);

  const mutation = useMutation({
    mutationFn: (values: PermissionFormValues) =>
      isEditing
        ? adminPermissionService.update(permissionId as number, values)
        : adminPermissionService.create(values),
    onSuccess: async () => {
      await Promise.all([
        queryClient.invalidateQueries({ queryKey: adminPermissionsQueryKey }),
        queryClient.invalidateQueries({ queryKey: adminRolesQueryKey }),
      ]);
      toast.success(isEditing ? "Permission updated successfully" : "Permission created successfully");
      router.push("/admin/permissions");
    },
    onError: (error: Error) => {
      if (error instanceof ApiError) {
        form.setError("name", { message: error.firstError("name") ?? error.message });
      } else {
        form.setError("root", { message: "Unable to save permission." });
      }
    },
  });

  if (isEditing && permissionQuery.isLoading) return <LoadingState label="Loading permission..." />;
  if (isEditing && (permissionQuery.isError || !permissionQuery.data)) {
    return <div className="p-4"><ErrorState message="Unable to load permission." /></div>;
  }

  return (
    <div className="flex flex-1 flex-col gap-4 p-4 sm:gap-6">
      <div>
        <h2 className="text-2xl font-bold tracking-tight">{isEditing ? "Edit Permission" : "Create Permission"}</h2>
        <p className="text-muted-foreground">
          {isEditing ? "Update permission information." : "Add a new permission to the system."}
        </p>
      </div>
      <Card>
        <CardHeader><CardTitle>Permission Information</CardTitle></CardHeader>
        <CardContent>
          <form onSubmit={form.handleSubmit((values) => mutation.mutate(values))} className="space-y-4">
            <div className="max-w-xl">
              <AdminFormField label="Permission Name" htmlFor="name" error={form.formState.errors.name?.message}>
                <input id="name" placeholder="e.g. edit articles" {...form.register("name")} className="h-9 w-full rounded-md border bg-background px-3 text-sm" />
              </AdminFormField>
            </div>
            {form.formState.errors.root?.message ? <p className="text-sm text-destructive">{form.formState.errors.root.message}</p> : null}
            <AdminFormActions
              submitLabel={isEditing ? "Save Changes" : "Create Permission"}
              isPending={mutation.isPending}
              onCancel={() => router.back()}
            />
          </form>
        </CardContent>
      </Card>
    </div>
  );
}
