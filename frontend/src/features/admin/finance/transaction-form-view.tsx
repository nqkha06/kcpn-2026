"use client";

import { ErrorState } from "@/components/common/error-state";
import { LoadingState } from "@/components/common/loading-state";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import {
    AdminFormActions,
    AdminFormField,
} from "@/features/admin/access-control/selection-grid";
import {
    adminTransactionSchema,
    type AdminTransactionFormValues,
} from "@/features/admin/finance/admin-finance-schema";
import { ApiError } from "@/lib/api/errors";
import {
    adminTransactionService,
    adminTransactionsQueryKey,
} from "@/services/admin-finance.service";
import type { AdminTransactionPayload } from "@/types/admin";
import { zodResolver } from "@hookform/resolvers/zod";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { useRouter } from "next/navigation";
import { useEffect } from "react";
import { useForm, useWatch } from "react-hook-form";
import { toast } from "sonner";

export function TransactionFormView({
    transactionId,
}: {
    transactionId?: number;
}) {
    const isEditing = transactionId !== undefined;
    const router = useRouter();
    const queryClient = useQueryClient();
    const optionsQuery = useQuery({
        queryKey: [...adminTransactionsQueryKey, "options"],
        queryFn: adminTransactionService.options,
        staleTime: 5 * 60 * 1000,
    });
    const itemQuery = useQuery({
        queryKey: [...adminTransactionsQueryKey, transactionId],
        queryFn: () => adminTransactionService.show(transactionId as number),
        enabled: isEditing,
    });
    const form = useForm<AdminTransactionFormValues>({
        resolver: zodResolver(adminTransactionSchema),
        defaultValues: {
            user_id: "",
            wallet_id: "",
            category_id: "",
            type: "expense",
            amount: "",
            transacted_at: new Date().toISOString().slice(0, 10),
            status: "posted",
            note: "",
            labels: "",
        },
    });
    const selectedUser = useWatch({ control: form.control, name: "user_id" });
    useEffect(() => {
        if (itemQuery.data)
            form.reset({
                user_id: String(itemQuery.data.user_id),
                wallet_id: String(itemQuery.data.wallet_id),
                category_id: itemQuery.data.category_id
                    ? String(itemQuery.data.category_id)
                    : "",
                type: itemQuery.data.type,
                amount: String(itemQuery.data.amount),
                transacted_at: itemQuery.data.transacted_at ?? "",
                status: itemQuery.data.status,
                note: itemQuery.data.note ?? "",
                labels: itemQuery.data.labels.join(", "),
            });
    }, [form, itemQuery.data]);
    const mutation = useMutation({
        mutationFn: (values: AdminTransactionFormValues) => {
            const payload: AdminTransactionPayload = {
                user_id: Number(values.user_id),
                wallet_id: Number(values.wallet_id),
                category_id: values.category_id
                    ? Number(values.category_id)
                    : null,
                type: values.type,
                amount: Number(values.amount),
                transacted_at: values.transacted_at,
                status: values.status,
                note: values.note || null,
                labels: values.labels,
            };
            return isEditing
                ? adminTransactionService.update(
                      transactionId as number,
                      payload,
                  )
                : adminTransactionService.create(payload);
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({
                queryKey: adminTransactionsQueryKey,
            });
            toast.success(
                isEditing
                    ? "Transaction updated successfully"
                    : "Transaction created successfully",
            );
            router.push("/admin/transactions");
        },
        onError: (error: Error) => {
            if (!(error instanceof ApiError))
                return form.setError("root", {
                    message: "Unable to save transaction.",
                });
            (
                [
                    "user_id",
                    "wallet_id",
                    "category_id",
                    "type",
                    "amount",
                    "transacted_at",
                    "status",
                    "note",
                    "labels",
                ] as const
            ).forEach((field) => {
                const message =
                    error.firstError(field) ?? error.firstError(`${field}.0`);
                if (message) form.setError(field, { message });
            });
            if (Object.keys(error.errors).length === 0)
                form.setError("root", { message: error.message });
        },
    });
    if (optionsQuery.isLoading || (isEditing && itemQuery.isLoading))
        return <LoadingState label="Loading transaction form..." />;
    if (
        optionsQuery.isError ||
        !optionsQuery.data ||
        (isEditing && (itemQuery.isError || !itemQuery.data))
    )
        return (
            <div className="p-4">
                <ErrorState message="Unable to load transaction form data." />
            </div>
        );
    const options = optionsQuery.data;
    const wallets = options.wallets.filter(
        (wallet) => !selectedUser || String(wallet.user_id) === selectedUser,
    );
    const categories = options.categories.filter(
        (category) =>
            category.user_id == null || String(category.user_id) === selectedUser,
    );
    const selectClass = "h-9 rounded-md border bg-background px-3 text-sm";
    const inputClass = "h-9 rounded-md border bg-background px-3 text-sm";
    return (
        <div className="flex flex-1 flex-col gap-4 p-4 sm:gap-6">
            <div>
                <h2 className="text-2xl font-bold tracking-tight">
                    {isEditing ? "Edit Transaction" : "Create Transaction"}
                </h2>
                <p className="text-muted-foreground">
                    {isEditing
                        ? "Update transaction details and status."
                        : "Add a new income or expense transaction for a user."}
                </p>
            </div>
            <Card>
                <CardHeader>
                    <CardTitle>Transaction Details</CardTitle>
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
                                        form.setValue(
                                            "user_id",
                                            event.target.value,
                                        );
                                        const firstWallet =
                                            options.wallets.find(
                                                (wallet) =>
                                                    String(wallet.user_id) ===
                                                    event.target.value,
                                            );
                                        form.setValue(
                                            "wallet_id",
                                            firstWallet
                                                ? String(firstWallet.id)
                                                : "",
                                        );
                                        form.setValue("category_id", "");
                                    }}
                                    className={selectClass}
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
                                label="Wallet"
                                htmlFor="wallet_id"
                                error={form.formState.errors.wallet_id?.message}
                            >
                                <select
                                    id="wallet_id"
                                    {...form.register("wallet_id")}
                                    className={selectClass}
                                >
                                    <option value="">Select wallet</option>
                                    {wallets.map((wallet) => (
                                        <option
                                            key={wallet.id}
                                            value={wallet.id}
                                        >
                                            {wallet.name} ({wallet.currency})
                                        </option>
                                    ))}
                                </select>
                            </AdminFormField>
                        </div>
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <AdminFormField
                                label="Type"
                                htmlFor="type"
                                error={form.formState.errors.type?.message}
                            >
                                <select
                                    id="type"
                                    {...form.register("type")}
                                    className={selectClass}
                                >
                                    <option value="income">Income</option>
                                    <option value="expense">Expense</option>
                                </select>
                            </AdminFormField>
                            <AdminFormField
                                label="Amount"
                                htmlFor="amount"
                                error={form.formState.errors.amount?.message}
                            >
                                <input
                                    id="amount"
                                    type="number"
                                    min="0.01"
                                    step="0.01"
                                    {...form.register("amount")}
                                    className={inputClass}
                                />
                            </AdminFormField>
                            <AdminFormField
                                label="Transaction Date"
                                htmlFor="transacted_at"
                                error={
                                    form.formState.errors.transacted_at?.message
                                }
                            >
                                <input
                                    id="transacted_at"
                                    type="date"
                                    {...form.register("transacted_at")}
                                    className={inputClass}
                                />
                            </AdminFormField>
                        </div>
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
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
                                    className={selectClass}
                                >
                                    <option value="">Uncategorized</option>
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
                            <AdminFormField
                                label="Status"
                                htmlFor="status"
                                error={form.formState.errors.status?.message}
                            >
                                <select
                                    id="status"
                                    {...form.register("status")}
                                    className={selectClass}
                                >
                                    <option value="posted">Posted</option>
                                    <option value="pending">Pending</option>
                                    <option value="cancelled">Cancelled</option>
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
                                {...form.register("note")}
                                className="min-h-20 rounded-md border bg-background px-3 py-2 text-sm"
                            />
                        </AdminFormField>
                        <AdminFormField
                            label="Labels"
                            htmlFor="labels"
                            error={form.formState.errors.labels?.message}
                        >
                            <input
                                id="labels"
                                placeholder="food, office"
                                {...form.register("labels")}
                                className={inputClass}
                            />
                        </AdminFormField>
                        {form.formState.errors.root?.message ? (
                            <p className="text-sm text-destructive">
                                {form.formState.errors.root.message}
                            </p>
                        ) : null}
                        <AdminFormActions
                            submitLabel={
                                isEditing
                                    ? "Save Changes"
                                    : "Create Transaction"
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
