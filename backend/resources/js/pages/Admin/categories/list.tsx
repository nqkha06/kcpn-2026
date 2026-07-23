import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import { DataTable } from '@/components/data-table';
import { Badge } from '@/components/ui/badge';
import AdminLayout from '@/layouts/admin';
import adminCategories from '@/routes/admin/categories';
import type {BreadcrumbItem} from '@/types';
import { DeleteCategoryDialog } from './components/categories-dialogs';
import { CategoriesPrimaryButtons } from './components/categories-primary-buttons';

interface Category {
    id: number;
    name: string;
    color: string;
    description: string | null;
    status: string;
    created_at: string;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Categories',
        href: adminCategories.index().url,
    },
];

interface Props {
    categories: Category[];
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
        status?: string;
        sort_by?: string;
        sort_direction?: 'asc' | 'desc';
    };
}

export default function CategoryList({ categories = [], pagination, filters }: Props) {
    const [isDeleteOpen, setIsDeleteOpen] = useState(false);
    const [selectedCategory, setSelectedCategory] = useState<Category | null>(null);

    const columns = [
        { id: 'name', label: 'Name', sortable: true },
        {
            id: 'color',
            label: 'Color',
            sortable: false,
            render: (category: Category) => (
                <div className="flex items-center gap-2">
                    <span
                        className="h-3 w-3 rounded-full border border-slate-200"
                        style={{ backgroundColor: category.color }}
                    />
                    <span className="text-xs font-medium text-slate-600">{category.color}</span>
                </div>
            ),
        },
        {
            id: 'status',
            label: 'Status',
            sortable: true,
            render: (category: Category) => (
                <Badge variant={category.status === 'active' ? 'default' : 'secondary'}>
                    {category.status}
                </Badge>
            ),
        },
        { id: 'created_at', label: 'Created At', sortable: true },
    ];

    const handleAdd = () => {
        router.visit(adminCategories.create().url);
    };

    const handleEdit = (category: Category) => {
        router.visit(adminCategories.edit(category.id).url);
    };

    const handleDelete = (category: Category) => {
        setSelectedCategory(category);
        setIsDeleteOpen(true);
    };

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title={breadcrumbs[0].title} />
            <div className="flex flex-1 flex-col gap-4 p-4 sm:gap-6">
                <div className="flex flex-wrap items-end justify-between gap-2">
                    <div>
                        <h2 className="text-2xl font-bold tracking-tight">Categories</h2>
                        <p className="text-muted-foreground">Manage transaction categories used in the expense tracker.</p>
                    </div>
                    <CategoriesPrimaryButtons onAdd={handleAdd} />
                </div>

                <DataTable
                    data={categories}
                    columns={columns}
                    onEdit={handleEdit}
                    onDelete={handleDelete}
                    pagination={pagination}
                    filters={filters}
                    baseUrl={adminCategories.index().url}
                    advancedFilters={[
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

            <DeleteCategoryDialog
                open={isDeleteOpen}
                onOpenChange={setIsDeleteOpen}
                category={selectedCategory}
            />
        </AdminLayout>
    );
}
