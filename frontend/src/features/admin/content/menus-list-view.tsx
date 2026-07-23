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
    adminMenuService,
    adminMenusQueryKey,
} from "@/services/admin-content.service";
import type { AdminMenu } from "@/types/admin";
import {
    keepPreviousData,
    useMutation,
    useQuery,
    useQueryClient,
} from "@tanstack/react-query";
import { useRouter, useSearchParams } from "next/navigation";
import { useMemo, useState } from "react";
import { toast } from "sonner";

const columns: AdminTableColumn<AdminMenu>[] = [
    { id: "title", label: "Title", sortable: true },
    { id: "url", label: "URL" },
    {
        id: "canonical",
        label: "Canonical",
        sortable: true,
        render: (menu) => <Badge variant="outline">{menu.canonical}</Badge>,
    },
    {
        id: "parent",
        label: "Parent",
        render: (menu) => menu.parent?.title ?? "—",
    },
    { id: "sort_order", label: "Order", sortable: true },
    {
        id: "status",
        label: "Status",
        sortable: true,
        render: (menu) => (
            <Badge variant={menu.status === "active" ? "default" : "secondary"}>
                {menu.status}
            </Badge>
        ),
    },
    { id: "created_at", label: "Created At", sortable: true },
];

export function MenusListView() {
    const router = useRouter();
    const searchParams = useSearchParams();
    const serialized = searchParams.toString();
    const queryClient = useQueryClient();
    const [selected, setSelected] = useState<AdminMenu | null>(null);
    const query = useMemo(
        () =>
            adminListQuery(new URLSearchParams(serialized), [
                "search",
                "status",
                "canonical",
                "parent_id",
                "sort",
                "direction",
                "per_page",
                "page",
            ]),
        [serialized],
    );
    const listQuery = useQuery({
        queryKey: [...adminMenusQueryKey, serialized],
        queryFn: () => adminMenuService.list(query),
        placeholderData: keepPreviousData,
    });
    const deleteMutation = useMutation({
        mutationFn: adminMenuService.destroy,
        onSuccess: async () => {
            setSelected(null);
            await queryClient.invalidateQueries({
                queryKey: adminMenusQueryKey,
            });
            toast.success("Menu deleted successfully");
        },
        onError: (error: Error) => toast.error(error.message),
    });
    if (listQuery.isLoading) return <LoadingState label="Loading menus..." />;
    if (listQuery.isError || !listQuery.data)
        return (
            <div className="p-4">
                <ErrorState
                    message="Unable to load menus."
                    onRetry={() => void listQuery.refetch()}
                />
            </div>
        );
    return (
        <div className="flex flex-1 flex-col gap-4 p-4 sm:gap-6">
            <AdminPageHeader
                title="Menus"
                description="Manage dynamic navigation menus by canonical slot."
                createHref="/admin/menus/create"
                createLabel="Add Menu"
            />
            <AdminDataTable
                data={listQuery.data.data}
                columns={columns}
                meta={listQuery.data.meta}
                isFetching={listQuery.isFetching}
                filters={[
                    {
                        key: "canonical",
                        label: "Canonical",
                        type: "select",
                        placeholder: "All Canonical Slots",
                        options: [
                            { value: "home.header", label: "Home Header" },
                            { value: "home.footer", label: "Home Footer" },
                            { value: "user.header", label: "User Header" },
                        ],
                    },
                    {
                        key: "status",
                        label: "Status",
                        type: "select",
                        options: [
                            { value: "active", label: "Active" },
                            { value: "inactive", label: "Inactive" },
                        ],
                    },
                ]}
                onEdit={(menu) => router.push(`/admin/menus/${menu.id}/edit`)}
                onDelete={setSelected}
            />
            <ConfirmDeleteDialog
                open={selected !== null}
                title="Delete Menu"
                description={`Are you sure you want to delete ${selected?.title ?? "this menu"}? This action cannot be undone.`}
                isPending={deleteMutation.isPending}
                onCancel={() => setSelected(null)}
                onConfirm={() => selected && deleteMutation.mutate(selected.id)}
            />
        </div>
    );
}
