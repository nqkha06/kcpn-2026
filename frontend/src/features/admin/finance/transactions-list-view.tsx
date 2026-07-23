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
    adminTransactionService,
    adminTransactionsQueryKey,
} from "@/services/admin-finance.service";
import type { AdminTransaction } from "@/types/admin";
import {
    keepPreviousData,
    useMutation,
    useQuery,
    useQueryClient,
} from "@tanstack/react-query";
import { useRouter, useSearchParams } from "next/navigation";
import { useMemo, useState } from "react";
import { toast } from "sonner";

const amountFormatter = new Intl.NumberFormat("en-US", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
});
const columns: AdminTableColumn<AdminTransaction>[] = [
    { id: "id", label: "ID", sortable: true },
    {
        id: "user",
        label: "User",
        render: (item) => (
            <div>
                <p className="font-medium">{item.user?.name ?? "N/A"}</p>
                <p className="text-xs text-muted-foreground">
                    {item.user?.email ?? ""}
                </p>
            </div>
        ),
    },
    {
        id: "wallet",
        label: "Wallet",
        render: (item) => (
            <div>
                <p>{item.wallet?.name ?? "N/A"}</p>
                <p className="text-xs text-muted-foreground">
                    {item.wallet?.currency ?? ""}
                </p>
            </div>
        ),
    },
    {
        id: "category",
        label: "Category",
        render: (item) =>
            item.category ? (
                <div className="flex items-center gap-2">
                    <span
                        className="size-2.5 rounded-full"
                        style={{ backgroundColor: item.category.color }}
                    />
                    {item.category.name}
                </div>
            ) : (
                <span className="text-muted-foreground">Uncategorized</span>
            ),
    },
    {
        id: "type",
        label: "Type",
        sortable: true,
        render: (item) => (
            <Badge variant={item.type === "income" ? "default" : "secondary"}>
                {item.type}
            </Badge>
        ),
    },
    {
        id: "amount",
        label: "Amount",
        sortable: true,
        render: (item) => (
            <span
                className={
                    item.type === "income"
                        ? "font-semibold text-emerald-600"
                        : "font-semibold text-rose-600"
                }
            >
                {item.type === "income" ? "+" : "-"}
                {amountFormatter.format(item.amount)}
            </span>
        ),
    },
    {
        id: "status",
        label: "Status",
        sortable: true,
        render: (item) => (
            <Badge
                variant={
                    item.status === "posted"
                        ? "default"
                        : item.status === "pending"
                          ? "secondary"
                          : "destructive"
                }
            >
                {item.status}
            </Badge>
        ),
    },
    { id: "transacted_at", label: "Transaction Date", sortable: true },
];

export function TransactionsListView() {
    const router = useRouter();
    const searchParams = useSearchParams();
    const serialized = searchParams.toString();
    const queryClient = useQueryClient();
    const [selected, setSelected] = useState<AdminTransaction | null>(null);
    const query = useMemo(
        () =>
            adminListQuery(new URLSearchParams(serialized), [
                "search",
                "type",
                "status",
                "user_id",
                "wallet_id",
                "category_id",
                "from_date",
                "to_date",
                "sort",
                "direction",
                "per_page",
                "page",
            ]),
        [serialized],
    );
    const listQuery = useQuery({
        queryKey: [...adminTransactionsQueryKey, serialized],
        queryFn: () => adminTransactionService.list(query),
        placeholderData: keepPreviousData,
    });
    const optionsQuery = useQuery({
        queryKey: [...adminTransactionsQueryKey, "options"],
        queryFn: adminTransactionService.options,
        staleTime: 5 * 60 * 1000,
    });
    const deleteMutation = useMutation({
        mutationFn: adminTransactionService.destroy,
        onSuccess: async () => {
            setSelected(null);
            await queryClient.invalidateQueries({
                queryKey: adminTransactionsQueryKey,
            });
            toast.success("Transaction deleted successfully");
        },
        onError: (error: Error) => toast.error(error.message),
    });
    if (listQuery.isLoading || optionsQuery.isLoading)
        return <LoadingState label="Loading transactions..." />;
    if (
        listQuery.isError ||
        optionsQuery.isError ||
        !listQuery.data ||
        !optionsQuery.data
    )
        return (
            <div className="p-4">
                <ErrorState
                    message="Unable to load transactions."
                    onRetry={() => {
                        void listQuery.refetch();
                        void optionsQuery.refetch();
                    }}
                />
            </div>
        );
    const options = optionsQuery.data;
    return (
        <div className="flex flex-1 flex-col gap-4 p-4 sm:gap-6">
            <AdminPageHeader
                title="Transactions"
                description="Manage user income and expense transactions."
                createHref="/admin/transactions/create"
                createLabel="Add Transaction"
            />
            <AdminDataTable
                data={listQuery.data.data}
                columns={columns}
                meta={listQuery.data.meta}
                isFetching={listQuery.isFetching}
                filters={[
                    {
                        key: "type",
                        label: "Type",
                        type: "select",
                        placeholder: "All Types",
                        options: options.types.map((value) => ({
                            value,
                            label: value,
                        })),
                    },
                    {
                        key: "status",
                        label: "Status",
                        type: "select",
                        placeholder: "All Statuses",
                        options: options.statuses.map((value) => ({
                            value,
                            label: value,
                        })),
                    },
                    {
                        key: "user_id",
                        label: "User",
                        type: "select",
                        placeholder: "All Users",
                        options: options.users.map((user) => ({
                            value: String(user.id),
                            label: user.name,
                        })),
                    },
                    {
                        key: "wallet_id",
                        label: "Wallet",
                        type: "select",
                        placeholder: "All Wallets",
                        options: options.wallets.map((wallet) => ({
                            value: String(wallet.id),
                            label: `${wallet.name} (${wallet.user_name ?? "N/A"})`,
                        })),
                    },
                    {
                        key: "category_id",
                        label: "Category",
                        type: "select",
                        placeholder: "All Categories",
                        options: options.categories.map((category) => ({
                            value: String(category.id),
                            label: category.name,
                        })),
                    },
                    { key: "from_date", label: "From Date", type: "date" },
                    { key: "to_date", label: "To Date", type: "date" },
                ]}
                onEdit={(item) =>
                    router.push(`/admin/transactions/${item.id}/edit`)
                }
                onDelete={setSelected}
            />
            <ConfirmDeleteDialog
                open={selected !== null}
                title="Delete Transaction"
                description={`Are you sure you want to delete transaction #${selected?.id ?? ""}? This action cannot be undone.`}
                isPending={deleteMutation.isPending}
                onCancel={() => setSelected(null)}
                onConfirm={() => selected && deleteMutation.mutate(selected.id)}
            />
        </div>
    );
}
