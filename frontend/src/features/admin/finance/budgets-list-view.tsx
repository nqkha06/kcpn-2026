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
    adminBudgetService,
    adminBudgetsQueryKey,
} from "@/services/admin-finance.service";
import type { AdminBudget } from "@/types/admin";
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
const columns: AdminTableColumn<AdminBudget>[] = [
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
        id: "category",
        label: "Category",
        render: (item) =>
            item.category ? (
                <div className="flex items-center gap-2">
                    <span
                        className="size-2.5 rounded-full border"
                        style={{ backgroundColor: item.category.color }}
                    />
                    {item.category.name}
                </div>
            ) : (
                <span className="text-muted-foreground">N/A</span>
            ),
    },
    {
        id: "amount_limit",
        label: "Limit",
        sortable: true,
        render: (item) => (
            <span className="font-semibold">
                ${amountFormatter.format(item.amount_limit)}
            </span>
        ),
    },
    {
        id: "spent",
        label: "Spent",
        render: (item) => (
            <span
                className={
                    item.spent > item.amount_limit
                        ? "font-semibold text-red-600"
                        : "font-semibold text-emerald-600"
                }
            >
                ${amountFormatter.format(item.spent)}
            </span>
        ),
    },
    {
        id: "period",
        label: "Period",
        sortable: true,
        render: (item) => <Badge variant="secondary">{item.period}</Badge>,
    },
    {
        id: "status",
        label: "Status",
        sortable: true,
        render: (item) => (
            <Badge variant={item.status === "active" ? "default" : "secondary"}>
                {item.status}
            </Badge>
        ),
    },
];

export function BudgetsListView() {
    const router = useRouter();
    const searchParams = useSearchParams();
    const serialized = searchParams.toString();
    const queryClient = useQueryClient();
    const [selected, setSelected] = useState<AdminBudget | null>(null);
    const query = useMemo(
        () =>
            adminListQuery(new URLSearchParams(serialized), [
                "search",
                "period",
                "status",
                "user_id",
                "category_id",
                "sort",
                "direction",
                "per_page",
                "page",
            ]),
        [serialized],
    );
    const listQuery = useQuery({
        queryKey: [...adminBudgetsQueryKey, serialized],
        queryFn: () => adminBudgetService.list(query),
        placeholderData: keepPreviousData,
    });
    const optionsQuery = useQuery({
        queryKey: [...adminBudgetsQueryKey, "options"],
        queryFn: adminBudgetService.options,
        staleTime: 5 * 60 * 1000,
    });
    const deleteMutation = useMutation({
        mutationFn: adminBudgetService.destroy,
        onSuccess: async () => {
            setSelected(null);
            await queryClient.invalidateQueries({
                queryKey: adminBudgetsQueryKey,
            });
            toast.success("Budget deleted successfully");
        },
        onError: (error: Error) => toast.error(error.message),
    });
    if (listQuery.isLoading || optionsQuery.isLoading)
        return <LoadingState label="Loading budgets..." />;
    if (
        listQuery.isError ||
        optionsQuery.isError ||
        !listQuery.data ||
        !optionsQuery.data
    )
        return (
            <div className="p-4">
                <ErrorState
                    message="Unable to load budgets."
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
                title="Budgets"
                description="Manage monthly and yearly budgets for users."
                createHref="/admin/budgets/create"
                createLabel="Add Budget"
            />
            <AdminDataTable
                data={listQuery.data.data}
                columns={columns}
                meta={listQuery.data.meta}
                isFetching={listQuery.isFetching}
                filters={[
                    {
                        key: "period",
                        label: "Period",
                        type: "select",
                        placeholder: "All Periods",
                        options: options.periods.map((value) => ({
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
                        key: "category_id",
                        label: "Category",
                        type: "select",
                        placeholder: "All Categories",
                        options: options.categories.map((category) => ({
                            value: String(category.id),
                            label: category.name,
                        })),
                    },
                ]}
                onEdit={(item) => router.push(`/admin/budgets/${item.id}/edit`)}
                onDelete={setSelected}
            />
            <ConfirmDeleteDialog
                open={selected !== null}
                title="Delete Budget"
                description={`Are you sure you want to delete budget ${selected?.user?.name ?? "User"} - ${selected?.category?.name ?? "Category"}? This action cannot be undone.`}
                isPending={deleteMutation.isPending}
                onCancel={() => setSelected(null)}
                onConfirm={() => selected && deleteMutation.mutate(selected.id)}
            />
        </div>
    );
}
