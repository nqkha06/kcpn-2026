"use client";

import { ErrorState } from "@/components/common/error-state";
import { LoadingState } from "@/components/common/loading-state";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import {
    AdminFormActions,
    AdminFormField,
} from "@/features/admin/access-control/selection-grid";
import {
    adminCategorySchema,
    type AdminCategoryFormValues,
} from "@/features/admin/finance/admin-finance-schema";
import { ApiError } from "@/lib/api/errors";
import {
    adminCategoriesQueryKey,
    adminCategoryService,
} from "@/services/admin-finance.service";
import { zodResolver } from "@hookform/resolvers/zod";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { useRouter } from "next/navigation";
import { useEffect } from "react";
import { useForm } from "react-hook-form";
import { toast } from "sonner";

export function CategoryFormView({ categoryId }: { categoryId?: number }) {
    const isEditing = categoryId !== undefined;
    const router = useRouter();
    const queryClient = useQueryClient();
    const categoryQuery = useQuery({
        queryKey: [...adminCategoriesQueryKey, categoryId],
        queryFn: () => adminCategoryService.show(categoryId as number),
        enabled: isEditing,
    });
    const form = useForm<AdminCategoryFormValues>({
        resolver: zodResolver(adminCategorySchema),
        defaultValues: {
            name: "",
            color: "#94A3B8",
            description: "",
            status: "active",
        },
    });

    useEffect(() => {
        if (categoryQuery.data)
            form.reset({
                name: categoryQuery.data.name,
                color: categoryQuery.data.color,
                description: categoryQuery.data.description ?? "",
                status: categoryQuery.data.status,
            });
    }, [categoryQuery.data, form]);

    const mutation = useMutation({
        mutationFn: (values: AdminCategoryFormValues) => {
            const payload = {
                ...values,
                description: values.description || null,
            };
            return isEditing
                ? adminCategoryService.update(categoryId as number, payload)
                : adminCategoryService.create(payload);
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({
                queryKey: adminCategoriesQueryKey,
            });
            toast.success(
                isEditing
                    ? "Category updated successfully"
                    : "Category created successfully",
            );
            router.push("/admin/categories");
        },
        onError: (error: Error) => {
            if (!(error instanceof ApiError))
                return form.setError("root", {
                    message: "Unable to save category.",
                });
            (["name", "color", "description", "status"] as const).forEach(
                (field) => {
                    const message = error.firstError(field);
                    if (message) form.setError(field, { message });
                },
            );
            if (Object.keys(error.errors).length === 0)
                form.setError("root", { message: error.message });
        },
    });

    if (isEditing && categoryQuery.isLoading)
        return <LoadingState label="Loading category..." />;
    if (isEditing && (categoryQuery.isError || !categoryQuery.data))
        return (
            <div className="p-4">
                <ErrorState message="Unable to load category." />
            </div>
        );

    return (
        <div className="flex flex-1 flex-col gap-4 p-4 sm:gap-6">
            <div>
                <h2 className="text-2xl font-bold tracking-tight">
                    {isEditing ? "Edit Category" : "Create Category"}
                </h2>
                <p className="text-muted-foreground">
                    {isEditing
                        ? "Update category details for manual transactions."
                        : "Add a new category for manual transaction entries."}
                </p>
            </div>
            <Card>
                <CardHeader>
                    <CardTitle>Category Details</CardTitle>
                </CardHeader>
                <CardContent>
                    <form
                        onSubmit={form.handleSubmit((values) =>
                            mutation.mutate(values),
                        )}
                        className="space-y-4"
                    >
                        <AdminFormField
                            label="Name"
                            htmlFor="name"
                            error={form.formState.errors.name?.message}
                        >
                            <input
                                id="name"
                                placeholder="e.g. Groceries"
                                {...form.register("name")}
                                className="h-9 rounded-md border bg-background px-3 text-sm"
                            />
                        </AdminFormField>
                        <AdminFormField
                            label="Color"
                            htmlFor="color"
                            error={form.formState.errors.color?.message}
                        >
                            <div className="flex flex-wrap items-center gap-3">
                                <input
                                    id="color"
                                    type="color"
                                    {...form.register("color")}
                                    className="h-10 w-16 rounded-md border bg-background p-1"
                                />
                                <input
                                    {...form.register("color")}
                                    className="h-9 max-w-xs rounded-md border bg-background px-3 text-sm"
                                />
                            </div>
                        </AdminFormField>
                        <AdminFormField
                            label="Description"
                            htmlFor="description"
                            error={form.formState.errors.description?.message}
                        >
                            <textarea
                                id="description"
                                placeholder="Optional note for admin use"
                                {...form.register("description")}
                                className="min-h-20 rounded-md border bg-background px-3 py-2 text-sm"
                            />
                        </AdminFormField>
                        <div className="max-w-xs">
                            <AdminFormField
                                label="Status"
                                htmlFor="status"
                                error={form.formState.errors.status?.message}
                            >
                                <select
                                    id="status"
                                    {...form.register("status")}
                                    className="h-9 rounded-md border bg-background px-3 text-sm"
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
                        <AdminFormActions
                            submitLabel={
                                isEditing ? "Save Changes" : "Create Category"
                            }
                            isPending={mutation.isPending}
                            onCancel={() => router.back()}
                        />
                    </form>
                </CardContent>
            </Card>
        </div>
    );
}
