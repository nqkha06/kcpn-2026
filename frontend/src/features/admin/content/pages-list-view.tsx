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
    adminPageService,
    adminPagesQueryKey,
} from "@/services/admin-content.service";
import type { AdminPage } from "@/types/admin";
import {
    keepPreviousData,
    useMutation,
    useQuery,
    useQueryClient,
} from "@tanstack/react-query";
import { useRouter, useSearchParams } from "next/navigation";
import { useMemo, useState } from "react";
import { toast } from "sonner";

const statusLabels = {
    published: "Published",
    draft: "Draft",
    pending: "Pending",
};
const columns: AdminTableColumn<AdminPage>[] = [
    { id: "title", label: "Title", sortable: true },
    { id: "slug", label: "Slug", sortable: true },
    {
        id: "status",
        label: "Status",
        sortable: true,
        render: (page) => (
            <Badge variant="secondary">{statusLabels[page.status]}</Badge>
        ),
    },
    { id: "created_at", label: "Created", sortable: true },
];

export function PagesListView() {
    const router = useRouter();
    const searchParams = useSearchParams();
    const serialized = searchParams.toString();
    const queryClient = useQueryClient();
    const [selected, setSelected] = useState<AdminPage | null>(null);
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
    const listQuery = useQuery({
        queryKey: [...adminPagesQueryKey, serialized],
        queryFn: () => adminPageService.list(query),
        placeholderData: keepPreviousData,
    });
    const deleteMutation = useMutation({
        mutationFn: adminPageService.destroy,
        onSuccess: async () => {
            setSelected(null);
            await queryClient.invalidateQueries({
                queryKey: adminPagesQueryKey,
            });
            toast.success("Page deleted successfully");
        },
        onError: (error: Error) => toast.error(error.message),
    });
    if (listQuery.isLoading) return <LoadingState label="Loading pages..." />;
    if (listQuery.isError || !listQuery.data)
        return (
            <div className="p-4">
                <ErrorState
                    message="Unable to load pages."
                    onRetry={() => void listQuery.refetch()}
                />
            </div>
        );
    return (
        <div className="flex flex-1 flex-col gap-4 p-4 sm:gap-6">
            <AdminPageHeader
                title="Pages"
                description="Manage static pages and metadata."
                createHref="/admin/pages/create"
                createLabel="Add Page"
            />
            <AdminDataTable
                data={listQuery.data.data}
                columns={columns}
                meta={listQuery.data.meta}
                isFetching={listQuery.isFetching}
                filters={[
                    {
                        key: "status",
                        label: "Status",
                        type: "select",
                        options: Object.entries(statusLabels).map(
                            ([value, label]) => ({ value, label }),
                        ),
                    },
                ]}
                onEdit={(page) => router.push(`/admin/pages/${page.id}/edit`)}
                onDelete={setSelected}
            />
            <ConfirmDeleteDialog
                open={selected !== null}
                title="Delete Page"
                description={`Are you sure you want to delete ${selected?.title ?? "this page"}? This action cannot be undone.`}
                isPending={deleteMutation.isPending}
                onCancel={() => setSelected(null)}
                onConfirm={() => selected && deleteMutation.mutate(selected.id)}
            />
        </div>
    );
}
