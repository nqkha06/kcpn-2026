"use client";

import { ErrorState } from "@/components/common/error-state";
import { LoadingState } from "@/components/common/loading-state";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import {
    AdminFormActions,
    AdminFormField,
} from "@/features/admin/access-control/selection-grid";
import {
    adminBudgetSchema,
    type AdminBudgetFormValues,
} from "@/features/admin/finance/admin-finance-schema";
import { ApiError } from "@/lib/api/errors";
import {
    adminBudgetService,
    adminBudgetsQueryKey,
} from "@/services/admin-finance.service";
import type { AdminBudgetPayload } from "@/types/admin";
import { zodResolver } from "@hookform/resolvers/zod";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { useRouter } from "next/navigation";
import { useEffect } from "react";
import { useForm, useWatch } from "react-hook-form";
import { toast } from "sonner";

export function BudgetFormView({ budgetId }: { budgetId?: number }) {
    const isEditing = budgetId !== undefined;
    const router = useRouter();
    const queryClient = useQueryClient();
    const optionsQuery = useQuery({
        queryKey: [...adminBudgetsQueryKey, "options"],
        queryFn: adminBudgetService.options,
        staleTime: 5 * 60 * 1000,
    });
    const itemQuery = useQuery({
        queryKey: [...adminBudgetsQueryKey, budgetId],
        queryFn: () => adminBudgetService.show(budgetId as number),
        enabled: isEditing,
    });
    const form = useForm<AdminBudgetFormValues>({
        resolver: zodResolver(adminBudgetSchema),
        defaultValues: {
            user_id: "",
            category_id: "",
            amount_limit: "",
            period: "monthly",
            status: "active",
            note: "",
        },
    });
    const selectedUser = useWatch({
        control: form.control,
        name: "user_id",
    });
    useEffect(() => {
        if (itemQuery.data)
            form.reset({
                user_id: String(itemQuery.data.user_id),
                category_id: String(itemQuery.data.category_id),
                amount_limit: String(itemQuery.data.amount_limit),
                period: itemQuery.data.period,
                status: itemQuery.data.status,
                note: itemQuery.data.note ?? "",
            });
    }, [form, itemQuery.data]);
    const mutation = useMutation({
        mutationFn: (values: AdminBudgetFormValues) => {
            const payload: AdminBudgetPayload = {
                user_id: Number(values.user_id),
                category_id: Number(values.category_id),
                amount_limit: Number(values.amount_limit),
                period: values.period,
                status: values.status,
                note: values.note || null,
            };
            return isEditing
                ? adminBudgetService.update(budgetId as number, payload)
                : adminBudgetService.create(payload);
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({
                queryKey: adminBudgetsQueryKey,
            });
            toast.success(
                isEditing
                    ? "Budget updated successfully"
                    : "Budget created successfully",
            );
            router.push("/admin/budgets");
        },
        onError: (error: Error) => {
            if (!(error instanceof ApiError))
                return form.setError("root", {
                    message: "Unable to save budget.",
                });
            (
                [
                    "user_id",
                    "category_id",
                    "amount_limit",
                    "period",
                    "status",
                    "note",
                ] as const
            ).forEach((field) => {
                const message = error.firstError(field);
                if (message) form.setError(field, { message });
            });
            if (Object.keys(error.errors).length === 0)
                form.setError("root", { message: error.message });
        },
    });
    if (optionsQuery.isLoading || (isEditing && itemQuery.isLoading))
        return <LoadingState label="Loading budget form..." />;
    if (
        optionsQuery.isError ||
        !optionsQuery.data ||
        (isEditing && (itemQuery.isError || !itemQuery.data))
    )
        return (
            <div className="p-4">
                <ErrorState message="Unable to load budget form data." />
            </div>
        );
    const options = optionsQuery.data;
    const categories = options.categories.filter(
        (category) =>
            category.user_id == null || String(category.user_id) === selectedUser,
    );
    const inputClass = "h-9 rounded-md border bg-background px-3 text-sm";
    return (
        <div className="flex flex-1 flex-col gap-4 p-4 sm:gap-6">
            <div>
                <h2 className="text-2xl font-bold tracking-tight">
                    {isEditing ? "Edit Budget" : "Create Budget"}
                </h2>
                <p className="text-muted-foreground">
                    {isEditing
                        ? "Update budget details and status."
                        : "Add a budget for a user and category."}
                </p>
            </div>
            <Card>
                <CardHeader>
                    <CardTitle>Budget Details</CardTitle>
                </CardHeader>
                <CardContent>
                    <form
                        onSubmit={form.handleSubmit((values) =>
                            mutation.mutate(values),
                        )}
                        className="space-y-4"
                    >
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <AdminFormField
                                label="User"
                                htmlFor="user_id"
                                error={form.formState.errors.user_id?.message}
                            >
                                <select
                                    id="user_id"
                                    {...form.register("user_id")}
                                    onChange={(event) => {
                                        form.setValue("user_id", event.target.value, {
                                            shouldValidate: true,
                                        });
                                        form.setValue("category_id", "");
                                    }}
                                    className={inputClass}
                                >
                                    <option value="">Select user</option>
                                    {options.users.map((user) => (
                                        <option key={user.id} value={user.id}>
                                            {user.name} ({user.email})
                                        </option>
                                    ))}
                                </select>
                            </AdminFormField>
                            <AdminFormField
                                label="Category"
                                htmlFor="category_id"
                                error={
                                    form.formState.errors.category_id?.message
                                }
                            >
                                <select
                                    id="category_id"
                                    {...form.register("category_id")}
                                    className={inputClass}
                                >
                                    <option value="">Select category</option>
                                    {categories.map((category) => (
                                        <option
                                            key={category.id}
                                            value={category.id}
                                        >
                                            {category.name}
                                            {category.user_id
                                                ? " (Private)"
                                                : ""}
                                        </option>
                                    ))}
                                </select>
                            </AdminFormField>
                        </div>
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <AdminFormField
                                label="Limit"
                                htmlFor="amount_limit"
                                error={
                                    form.formState.errors.amount_limit?.message
                                }
                            >
                                <input
                                    id="amount_limit"
                                    type="number"
                                    min="0.01"
                                    step="0.01"
                                    {...form.register("amount_limit")}
                                    className={inputClass}
                                />
                            </AdminFormField>
                            <AdminFormField
                                label="Period"
                                htmlFor="period"
                                error={form.formState.errors.period?.message}
                            >
                                <select
                                    id="period"
                                    {...form.register("period")}
                                    className={inputClass}
                                >
                                    <option value="monthly">Monthly</option>
                                    <option value="yearly">Yearly</option>
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
                        <AdminFormField
                            label="Note"
                            htmlFor="note"
                            error={form.formState.errors.note?.message}
                        >
                            <textarea
                                id="note"
                                placeholder="Optional budget note"
                                {...form.register("note")}
                                className="min-h-20 rounded-md border bg-background px-3 py-2 text-sm"
                            />
                        </AdminFormField>
                        {form.formState.errors.root?.message ? (
                            <p className="text-sm text-destructive">
                                {form.formState.errors.root.message}
                            </p>
                        ) : null}
                        <AdminFormActions
                            submitLabel={
                                isEditing ? "Save Changes" : "Create Budget"
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
