import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import { DataTable } from '@/components/data-table';
import AdminLayout from '@/layouts/admin';
import adminPermissions from '@/routes/admin/permissions';
import type {BreadcrumbItem} from '@/types';
import { DeletePermissionDialog } from './components/permissions-dialogs';
import { PermissionsPrimaryButtons } from './components/permissions-primary-buttons';

interface Permission {
    id: number;
    name: string;
    created_at: string;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Permissions',
        href: adminPermissions.index().url,
    },
];

interface Props {
    permissions: Permission[];
    pagination: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number;
        to: number;
    };
    filters: {
        search?: string;
        sort_by?: string;
        sort_direction?: 'asc' | 'desc';
    };
}

export default function PermissionList({ permissions = [], pagination, filters }: Props) {
    const [isDeleteOpen, setIsDeleteOpen] = useState(false);
    const [selectedPermission, setSelectedPermission] = useState<Permission | null>(null);

    const columns = [
        { id: 'name', label: 'Name', sortable: true },
        { id: 'created_at', label: 'Created At', sortable: true },
    ];

    const handleAdd = () => {
        router.visit(adminPermissions.create().url);
    };

    const handleEdit = (permission: Permission) => {
        router.visit(adminPermissions.edit(permission.id).url);
    };

    const handleDelete = (permission: Permission) => {
        setSelectedPermission(permission);
        setIsDeleteOpen(true);
    };

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title={breadcrumbs[0].title} />
            <div className='flex flex-1 flex-col gap-4 sm:gap-6 p-4'>
                <div className='flex flex-wrap items-end justify-between gap-2'>
                    <div>
                        <h2 className='text-2xl font-bold tracking-tight'>Permissions</h2>
                        <p className='text-muted-foreground'>
                            Manage your permissions here.
                        </p>
                    </div>
                    <PermissionsPrimaryButtons onAdd={handleAdd} />
                </div>

                <DataTable
                    data={permissions}
                    columns={columns}
                    onEdit={handleEdit}
                    onDelete={handleDelete}
                    pagination={pagination}
                    filters={filters}
                    baseUrl={adminPermissions.index().url}
                />
            </div>

            <DeletePermissionDialog
                open={isDeleteOpen}
                onOpenChange={setIsDeleteOpen}
                permission={selectedPermission}
            />
        </AdminLayout>
    );
}
