import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import { DataTable } from '@/components/data-table';
import { Badge } from '@/components/ui/badge';
import AdminLayout from '@/layouts/admin';
import adminRoles from '@/routes/admin/roles';
import type {BreadcrumbItem} from '@/types';
import { DeleteRoleDialog } from './components/roles-dialogs';
import { RolesPrimaryButtons } from './components/roles-primary-buttons';

interface Permission {
    id: number;
    name: string;
}

interface Role {
    id: number;
    name: string;
    permissions: Permission[];
    created_at: string;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Roles',
        href: adminRoles.index().url,
    },
];

interface Props {
    roles: Role[];
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

export default function RoleList({ roles = [], pagination, filters }: Props) {
    const [isDeleteOpen, setIsDeleteOpen] = useState(false);
    const [selectedRole, setSelectedRole] = useState<Role | null>(null);

    const columns = [
        { id: 'name', label: 'Name', sortable: true },
        {
            id: 'permissions',
            label: 'Permissions',
            sortable: false,
            render: (role: Role) => (
                <div className="flex flex-wrap gap-1 max-w-xs">
                    {role.permissions?.length > 0 ? (
                        role.permissions.map((p) => (
                            <Badge key={p.id} variant="secondary" className="text-xs font-normal">
                                {p.name}
                            </Badge>
                        ))
                    ) : (
                        <span className="text-sm text-muted-foreground">None</span>
                    )}
                </div>
            ),
        },
        { id: 'created_at', label: 'Created At', sortable: true },
    ];

    const handleAdd = () => {
        router.visit(adminRoles.create().url);
    };

    const handleEdit = (role: Role) => {
        router.visit(adminRoles.edit(role.id).url);
    };

    const handleDelete = (role: Role) => {
        setSelectedRole(role);
        setIsDeleteOpen(true);
    };

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title={breadcrumbs[0].title} />
            <div className='flex flex-1 flex-col gap-4 sm:gap-6 p-4'>
                <div className='flex flex-wrap items-end justify-between gap-2'>
                    <div>
                        <h2 className='text-2xl font-bold tracking-tight'>Roles</h2>
                        <p className='text-muted-foreground'>
                            Manage your roles and their permissions here.
                        </p>
                    </div>
                    <RolesPrimaryButtons onAdd={handleAdd} />
                </div>

                <DataTable
                    data={roles}
                    columns={columns}
                    onEdit={handleEdit}
                    onDelete={handleDelete}
                    pagination={pagination}
                    filters={filters}
                    baseUrl={adminRoles.index().url}
                />
            </div>

            <DeleteRoleDialog
                open={isDeleteOpen}
                onOpenChange={setIsDeleteOpen}
                role={selectedRole}
            />
        </AdminLayout>
    );
}
