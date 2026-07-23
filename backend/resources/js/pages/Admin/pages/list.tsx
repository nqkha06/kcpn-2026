import { Head, router } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import { DataTable } from '@/components/data-table';
import { Badge } from '@/components/ui/badge';
import AdminLayout from '@/layouts/admin';
import adminPages from '@/routes/admin/pages';
import type {BreadcrumbItem} from '@/types';
import { DeletePageDialog } from './components/users-dialogs';
import { PagesPrimaryButtons } from './components/users-primary-buttons';

interface PageItem {
    id: number;
    title: string;
    slug?: string;
    status: string;
    created_at?: string;
}

interface Pagination {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
}

interface Filters {
    search?: string;
    status?: string;
    sort_by?: string;
    sort_direction?: 'asc' | 'desc';
}

interface Props {
    pages: PageItem[];
    pagination: Pagination;
    filters: Filters;
}

const STATUS_LABELS: Record<string, string> = {
    published: 'Published',
    draft: 'Draft',
    pending: 'Pending',
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Pages', href: adminPages.index().url },
];

export default function PageList({ pages = [], pagination, filters }: Props) {
    const [isDeleteOpen, setIsDeleteOpen] = useState(false);
    const [selectedPage, setSelectedPage] = useState<PageItem | null>(null);

    const columns = useMemo(
        () => [
            { id: 'title', label: 'Title' },
            { id: 'slug', label: 'Slug', sortable: true },
            {
                id: 'status',
                label: 'Status',
                sortable: true,
                render: (page: PageItem) => (
                    <Badge variant="secondary">{STATUS_LABELS[page.status] || page.status}</Badge>
                ),
            },
            { id: 'created_at', label: 'Created', sortable: true },
        ],
        []
    );

    const handleAdd = () => router.visit(adminPages.create().url);
    const handleEdit = (page: PageItem) => router.visit(adminPages.edit(page.id).url);
    const handleDelete = (page: PageItem) => {
        setSelectedPage(page);
        setIsDeleteOpen(true);
    };

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title={breadcrumbs[0].title} />
            <div className="flex flex-1 flex-col gap-4 sm:gap-6 p-4">
                <div className="flex flex-wrap items-end justify-between gap-2">
                    <div>
                        <h2 className="text-2xl font-bold tracking-tight">Pages</h2>
                        <p className="text-muted-foreground">Manage static pages and metadata.</p>
                    </div>
                    <PagesPrimaryButtons onAdd={handleAdd} />
                </div>

                <DataTable<PageItem>
                    data={pages}
                    columns={columns}
                    onEdit={handleEdit}
                    onDelete={handleDelete}
                    pagination={pagination}
                    filters={filters}
                    baseUrl={adminPages.index().url}
                    advancedFilters={[
                        {
                            key: 'status',
                            label: 'Status',
                            type: 'select',
                            options: [
                                { value: 'published', label: 'Published' },
                                { value: 'draft', label: 'Draft' },
                                { value: 'pending', label: 'Pending' },
                            ],
                        },
                    ]}
                />
            </div>

            <DeletePageDialog open={isDeleteOpen} onOpenChange={setIsDeleteOpen} page={selectedPage} />
        </AdminLayout>
    );
}
