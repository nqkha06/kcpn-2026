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
    adminCategoriesQueryKey,
    adminCategoryService,
} from "@/services/admin-finance.service";
import type { AdminCategory } from "@/types/admin";
import {
    keepPreviousData,
    useMutation,
    useQuery,
    useQueryClient,
} from "@tanstack/react-query";
import { useRouter, useSearchParams } from "next/navigation";
import { useMemo, useState } from "react";
import { toast } from "sonner";

const columns: AdminTableColumn<AdminCategory>[] = [
    { id: "name", label: "Name", sortable: true },
    {
        id: "color",
        label: "Color",
        render: (category) => (
            <div className="flex items-center gap-2">
                <span
                    className="size-3 rounded-full border border-slate-200"
                    style={{ backgroundColor: category.color }}
                />
                <span className="text-xs font-medium text-slate-600 dark:text-slate-300">
                    {category.color}
                </span>
            </div>
        ),
    },
    {
        id: "status",
        label: "Status",
        sortable: true,
        render: (category) => (
            <Badge
                variant={category.status === "active" ? "default" : "secondary"}
            >
                {category.status}
            </Badge>
        ),
    },
    { id: "created_at", label: "Created At", sortable: true },
];

export function CategoriesListView() {
    const router = useRouter();
    const searchParams = useSearchParams();
    const serialized = searchParams.toString();
    const queryClient = useQueryClient();
    const [selected, setSelected] = useState<AdminCategory | null>(null);
    const query = useMemo(
        () =>
            adminListQuery(new URLSearchParams(serialized), [
                "search",
                "status",
                "sort",
                "direction",
                "per_page",
                "page",
            ]),
        [serialized],
    );
    const categoriesQuery = useQuery({
        queryKey: [...adminCategoriesQueryKey, serialized],
        queryFn: () => adminCategoryService.list(query),
        placeholderData: keepPreviousData,
    });
    const deleteMutation = useMutation({
        mutationFn: adminCategoryService.destroy,
        onSuccess: async () => {
            setSelected(null);
            await queryClient.invalidateQueries({
                queryKey: adminCategoriesQueryKey,
            });
            toast.success("Category deleted successfully");
        },
        onError: (error: Error) => toast.error(error.message),
    });

    if (categoriesQuery.isLoading)
        return <LoadingState label="Loading categories..." />;
    if (categoriesQuery.isError || !categoriesQuery.data)
        return (
            <div className="p-4">
                <ErrorState
                    message="Unable to load categories."
                    onRetry={() => void categoriesQuery.refetch()}
                />
            </div>
        );

    return (
        <div className="flex flex-1 flex-col gap-4 p-4 sm:gap-6">
            <AdminPageHeader
                title="Categories"
                description="Manage transaction categories used in the expense tracker."
                createHref="/admin/categories/create"
                createLabel="Add Category"
            />
            <AdminDataTable
                data={categoriesQuery.data.data}
                columns={columns}
                meta={categoriesQuery.data.meta}
                isFetching={categoriesQuery.isFetching}
                filters={[
                    {
                        key: "status",
                        label: "Status",
                        type: "select",
                        placeholder: "All Statuses",
                        options: [
                            { value: "active", label: "Active" },
                            { value: "inactive", label: "Inactive" },
                        ],
                    },
                ]}
                onEdit={(category) =>
                    router.push(`/admin/categories/${category.id}/edit`)
                }
                onDelete={setSelected}
            />
            <ConfirmDeleteDialog
                open={selected !== null}
                title="Delete Category"
                description={`Are you sure you want to delete ${selected?.name ?? "this category"}? This action cannot be undone.`}
                isPending={deleteMutation.isPending}
                onCancel={() => setSelected(null)}
                onConfirm={() => selected && deleteMutation.mutate(selected.id)}
            />
        </div>
    );
}
