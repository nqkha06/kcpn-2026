import { Head, router } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import { DataTable } from '@/components/data-table';
import { Badge } from '@/components/ui/badge';
import AdminLayout from '@/layouts/admin';
import adminMenus from '@/routes/admin/menus';
import type { BreadcrumbItem } from '@/types';
import { DeleteMenuDialog } from './components/menus-dialogs';
import { MenusPrimaryButtons } from './components/menus-primary-buttons';

interface MenuItem {
    id: number;
    title: string;
    url: string | null;
    canonical: string;
    sort_order: number;
    target: '_self' | '_blank';
    status: 'active' | 'inactive';
    parent_id: number | null;
    parent_title: string | null;
    created_at: string;
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
    canonical?: string;
    sort_by?: string;
    sort_direction?: 'asc' | 'desc';
}

interface Props {
    menus: MenuItem[];
    pagination: Pagination;
    filters: Filters;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Menus', href: adminMenus.index().url },
];

export default function MenuList({ menus = [], pagination, filters }: Props) {
    const [isDeleteOpen, setIsDeleteOpen] = useState(false);
    const [selectedMenu, setSelectedMenu] = useState<MenuItem | null>(null);

    const columns = useMemo(
        () => [
            { id: 'title', label: 'Title', sortable: true },
            { id: 'url', label: 'URL', sortable: false },
            {
                id: 'canonical',
                label: 'Canonical',
                sortable: true,
                render: (menu: MenuItem) => (
                    <Badge variant="outline">{menu.canonical}</Badge>
                ),
            },
            {
                id: 'parent_title',
                label: 'Parent',
                sortable: false,
                render: (menu: MenuItem) => menu.parent_title || '-',
            },
            { id: 'sort_order', label: 'Order', sortable: true },
            {
                id: 'status',
                label: 'Status',
                sortable: true,
                render: (menu: MenuItem) => (
                    <Badge variant={menu.status === 'active' ? 'default' : 'secondary'}>
                        {menu.status}
                    </Badge>
                ),
            },
            { id: 'created_at', label: 'Created At', sortable: true },
        ],
        []
    );

    const handleAdd = () => {
        router.visit(adminMenus.create().url);
    };

    const handleEdit = (menu: MenuItem) => {
        router.visit(adminMenus.edit(menu.id).url);
    };

    const handleDelete = (menu: MenuItem) => {
        setSelectedMenu(menu);
        setIsDeleteOpen(true);
    };

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title={breadcrumbs[0].title} />
            <div className="flex flex-1 flex-col gap-4 p-4 sm:gap-6">
                <div className="flex flex-wrap items-end justify-between gap-2">
                    <div>
                        <h2 className="text-2xl font-bold tracking-tight">Menus</h2>
                        <p className="text-muted-foreground">Manage dynamic navigation menus by canonical slot.</p>
                    </div>
                    <MenusPrimaryButtons onAdd={handleAdd} />
                </div>

                <DataTable<MenuItem>
                    data={menus}
                    columns={columns}
                    onEdit={handleEdit}
                    onDelete={handleDelete}
                    pagination={pagination}
                    filters={filters}
                    baseUrl={adminMenus.index().url}
                    advancedFilters={[
                        {
                            key: 'canonical',
                            label: 'Canonical',
                            type: 'select',
                            placeholder: 'All Canonical Slots',
                            options: [
                                { value: 'home.header', label: 'Home Header' },
                                { value: 'home.footer', label: 'Home Footer' },
                                { value: 'user.header', label: 'User Header' },
                            ],
                        },
                        {
                            key: 'status',
                            label: 'Status',
                            type: 'select',
                            placeholder: 'All Statuses',
                            options: [
                                { value: 'active', label: 'Active' },
                                { value: 'inactive', label: 'Inactive' },
                            ],
                        },
                    ]}
                />
            </div>

            <DeleteMenuDialog
                open={isDeleteOpen}
                onOpenChange={setIsDeleteOpen}
                menu={selectedMenu}
            />
        </AdminLayout>
    );
}
