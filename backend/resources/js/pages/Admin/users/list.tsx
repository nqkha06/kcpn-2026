import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import { DataTable } from '@/components/data-table';
import AdminLayout from '@/layouts/admin';
import adminUsers from '@/routes/admin/users';
import type {BreadcrumbItem, User} from '@/types';
import { DeleteUserDialog } from './components/users-dialogs';
import { UsersPrimaryButtons } from './components/users-primary-buttons';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Users',
        href: adminUsers.index().url,
    },
];

interface Props {
    users: User[];
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

export default function UserList({ users = [], pagination, filters }: Props) {
    const [isDeleteOpen, setIsDeleteOpen] = useState(false);
    const [selectedUser, setSelectedUser] = useState<User | null>(null);

    const columns = [
        { id: 'name', label: 'Name', sortable: true },
        { id: 'email', label: 'Email', sortable: true },
        { id: 'created_at', label: 'Created At', sortable: true },
    ];

    const handleAdd = () => {
        router.visit(adminUsers.create().url);
    };

    const handleEdit = (user: User) => {
        router.visit(adminUsers.edit(user.id).url);
    };

    const handleDelete = (user: User) => {
        setSelectedUser(user);
        setIsDeleteOpen(true);
    };

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title={breadcrumbs[0].title} />
            <div className='flex flex-1 flex-col gap-4 sm:gap-6 p-4'>
                <div className='flex flex-wrap items-end justify-between gap-2'>
                    <div>
                        <h2 className='text-2xl font-bold tracking-tight'>Users</h2>
                        <p className='text-muted-foreground'>
                            Manage your users and their roles here.
                        </p>
                    </div>
                    <UsersPrimaryButtons onAdd={handleAdd} />
                </div>

                <DataTable
                    data={users}
                    columns={columns}
                    onEdit={handleEdit}
                    onDelete={handleDelete}
                    pagination={pagination}
                    filters={filters}
                    baseUrl={adminUsers.index().url}
                    advancedFilters={[
                        {
                            key: 'role',
                            label: 'Role',
                            type: 'select',
                            placeholder: 'All Roles',
                            options: [
                                { value: 'admin', label: 'Admin' },
                                { value: 'user', label: 'User' },
                            ]
                        },
                        {
                            key: 'status',
                            label: 'Status',
                            type: 'select',
                            options: [
                                { value: 'active', label: 'Active' },
                                { value: 'inactive', label: 'Inactive' },
                            ]
                        },
                        {
                            key: 'email',
                            label: 'Email',
                            type: 'input',
                            placeholder: 'Filter by email...'
                        },
                        {
                            key: 'created_date',
                            label: 'Created Date',
                            type: 'date'
                        }
                    ]}
                />
            </div>

            <DeleteUserDialog
                open={isDeleteOpen}
                onOpenChange={setIsDeleteOpen}
                user={selectedUser}
            />
        </AdminLayout>
    );
}
