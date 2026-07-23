"use client";

import { ErrorState } from "@/components/common/error-state";
import { LoadingState } from "@/components/common/loading-state";
import { Card, CardContent } from "@/components/ui/card";
import {
    AdminFormActions,
    AdminFormField,
} from "@/features/admin/access-control/selection-grid";
import {
    adminMenuSchema,
    type AdminMenuFormValues,
} from "@/features/admin/content/admin-content-schema";
import { ApiError } from "@/lib/api/errors";
import {
    adminMenuService,
    adminMenusQueryKey,
} from "@/services/admin-content.service";
import type { AdminMenuPayload } from "@/types/admin";
import { zodResolver } from "@hookform/resolvers/zod";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { useRouter } from "next/navigation";
import { useEffect } from "react";
import { useForm, useWatch } from "react-hook-form";
import { toast } from "sonner";

const fields = [
    "title",
    "url",
    "canonical",
    "parent_id",
    "sort_order",
    "target",
    "status",
] as const;
export function MenuFormView({ menuId }: { menuId?: number }) {
    const isEditing = menuId !== undefined;
    const router = useRouter();
    const queryClient = useQueryClient();
    const optionsQuery = useQuery({
        queryKey: [...adminMenusQueryKey, "parent-options", menuId],
        queryFn: () => adminMenuService.parentOptions(menuId),
    });
    const itemQuery = useQuery({
        queryKey: [...adminMenusQueryKey, menuId],
        queryFn: () => adminMenuService.show(menuId as number),
        enabled: isEditing,
    });
    const form = useForm<AdminMenuFormValues>({
        resolver: zodResolver(adminMenuSchema),
        defaultValues: {
            title: "",
            url: "",
            canonical: "home.header",
            parent_id: "",
            sort_order: "0",
            target: "_self",
            status: "active",
        },
    });
    const canonical = useWatch({ control: form.control, name: "canonical" });
    useEffect(() => {
        if (itemQuery.data)
            form.reset({
                title: itemQuery.data.title,
                url: itemQuery.data.url ?? "",
                canonical: itemQuery.data.canonical,
                parent_id: itemQuery.data.parent_id
                    ? String(itemQuery.data.parent_id)
                    : "",
                sort_order: String(itemQuery.data.sort_order),
                target: itemQuery.data.target,
                status: itemQuery.data.status,
            });
    }, [form, itemQuery.data]);
    const mutation = useMutation({
        mutationFn: (values: AdminMenuFormValues) => {
            const payload: AdminMenuPayload = {
                title: values.title,
                url: values.url || null,
                canonical: values.canonical,
                parent_id: values.parent_id ? Number(values.parent_id) : null,
                sort_order: Number(values.sort_order),
                target: values.target,
                status: values.status,
            };
            return isEditing
                ? adminMenuService.update(menuId as number, payload)
                : adminMenuService.create(payload);
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({
                queryKey: adminMenusQueryKey,
            });
            toast.success(
                isEditing
                    ? "Menu updated successfully"
                    : "Menu created successfully",
            );
            router.push("/admin/menus");
        },
        onError: (error: Error) => {
            if (error instanceof ApiError)
                fields.forEach((field) => {
                    const message = error.firstError(field);
                    if (message) form.setError(field, { message });
                });
            else form.setError("root", { message: "Unable to save menu." });
        },
    });
    if (optionsQuery.isLoading || (isEditing && itemQuery.isLoading))
        return <LoadingState label="Loading menu form..." />;
    if (
        optionsQuery.isError ||
        !optionsQuery.data ||
        (isEditing && (itemQuery.isError || !itemQuery.data))
    )
        return (
            <div className="p-4">
                <ErrorState message="Unable to load menu form." />
            </div>
        );
    const parents = optionsQuery.data.filter(
        (item) => item.canonical === canonical,
    );
    const inputClass = "h-9 rounded-md border bg-background px-3 text-sm";
    return (
        <div className="flex flex-1 flex-col gap-4 p-4 sm:gap-6">
            <div>
                <h2 className="text-2xl font-bold tracking-tight">
                    {isEditing ? "Edit Menu" : "Create Menu"}
                </h2>
                <p className="text-muted-foreground">
                    {isEditing
                        ? "Update menu details and canonical slot assignment."
                        : "Add a menu item for a canonical slot with optional sub menu support."}
                </p>
            </div>
            <form
                onSubmit={form.handleSubmit((values) =>
                    mutation.mutate(values),
                )}
                className="grid gap-4 xl:grid-cols-12"
            >
                <Card className="xl:col-span-9">
                    <CardContent className="space-y-5 pt-6">
                        <AdminFormField
                            label="Title"
                            htmlFor="title"
                            error={form.formState.errors.title?.message}
                        >
                            <input
                                id="title"
                                {...form.register("title")}
                                className={inputClass}
                            />
                        </AdminFormField>
                        <div className="grid gap-4 md:grid-cols-2">
                            <AdminFormField
                                label="URL"
                                htmlFor="url"
                                error={form.formState.errors.url?.message}
                            >
                                <input
                                    id="url"
                                    placeholder="/about or https://example.com"
                                    {...form.register("url")}
                                    className={inputClass}
                                />
                            </AdminFormField>
                            <AdminFormField
                                label="Sort Order"
                                htmlFor="sort_order"
                                error={
                                    form.formState.errors.sort_order?.message
                                }
                            >
                                <input
                                    id="sort_order"
                                    type="number"
                                    min="0"
                                    max="9999"
                                    {...form.register("sort_order")}
                                    className={inputClass}
                                />
                            </AdminFormField>
                        </div>
                        <div className="grid gap-4 md:grid-cols-2">
                            <AdminFormField
                                label="Canonical"
                                htmlFor="canonical"
                                error={form.formState.errors.canonical?.message}
                            >
                                <input
                                    id="canonical"
                                    placeholder="home.header"
                                    {...form.register("canonical")}
                                    onChange={(event) => {
                                        form.setValue(
                                            "canonical",
                                            event.target.value,
                                            { shouldValidate: true },
                                        );
                                        form.setValue("parent_id", "");
                                    }}
                                    className={inputClass}
                                />
                            </AdminFormField>
                            <AdminFormField
                                label="Parent Menu"
                                htmlFor="parent_id"
                                error={form.formState.errors.parent_id?.message}
                            >
                                <select
                                    id="parent_id"
                                    {...form.register("parent_id")}
                                    className={inputClass}
                                >
                                    <option value="">No parent</option>
                                    {parents.map((parent) => (
                                        <option
                                            key={parent.id}
                                            value={parent.id}
                                        >
                                            {parent.title}
                                        </option>
                                    ))}
                                </select>
                            </AdminFormField>
                        </div>
                        <div className="grid gap-4 md:grid-cols-2">
                            <AdminFormField
                                label="Target"
                                htmlFor="target"
                                error={form.formState.errors.target?.message}
                            >
                                <select
                                    id="target"
                                    {...form.register("target")}
                                    className={inputClass}
                                >
                                    <option value="_self">
                                        Same Tab (_self)
                                    </option>
                                    <option value="_blank">
                                        New Tab (_blank)
                                    </option>
                                </select>
                            </AdminFormField>
                            <AdminFormField
                                label="Status"
                                htmlFor="status"
                                error={form.formState.errors.status?.message}
                            >
                                <select
                                    id="status"
                                    {...form.register("status")}
                                    className={inputClass}
                                >
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </AdminFormField>
                        </div>
                        {form.formState.errors.root?.message ? (
                            <p className="text-sm text-destructive">
                                {form.formState.errors.root.message}
                            </p>
                        ) : null}
                    </CardContent>
                </Card>
                <Card className="h-fit xl:col-span-3">
                    <CardContent className="pt-6">
                        <AdminFormActions
                            submitLabel="Save"
                            isPending={mutation.isPending}
                            onCancel={() => router.back()}
                        />
                    </CardContent>
                </Card>
            </form>
        </div>
    );
}
