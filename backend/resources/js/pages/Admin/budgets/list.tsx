import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import { DataTable } from '@/components/data-table';
import { Badge } from '@/components/ui/badge';
import AdminLayout from '@/layouts/admin';
import adminBudgets from '@/routes/admin/budgets';
import type { BreadcrumbItem } from '@/types';
import { DeleteBudgetDialog } from './components/budgets-dialogs';
import { BudgetsPrimaryButtons } from './components/budgets-primary-buttons';

interface BudgetRow {
    id: number;
    user_id: number;
    category_id: number;
    amount_limit: number;
    spent: number;
    period: 'monthly' | 'yearly';
    status: 'active' | 'inactive';
    note: string | null;
    created_at: string | null;
    user: {
        id: number;
        name: string;
        email: string;
    } | null;
    category: {
        id: number;
        name: string;
        color: string;
    } | null;
}

interface FormOptions {
    users: Array<{
        id: number;
        name: string;
        email: string;
    }>;
    categories: Array<{
        id: number;
        name: string;
        color: string;
    }>;
}

interface Props {
    budgets: BudgetRow[];
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
        period?: string;
        status?: string;
        user_id?: string | number;
        category_id?: string | number;
        sort_by?: string;
        sort_direction?: 'asc' | 'desc';
    };
    formOptions: FormOptions;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Budgets',
        href: adminBudgets.index().url,
    },
];

const amountFormatter = new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
});

export default function BudgetList({
    budgets = [],
    pagination,
    filters,
    formOptions,
}: Props) {
    const [isDeleteOpen, setIsDeleteOpen] = useState(false);
    const [selectedBudget, setSelectedBudget] = useState<BudgetRow | null>(null);

    const columns = [
        { id: 'id', label: 'ID', sortable: true },
        {
            id: 'user',
            label: 'User',
            sortable: false,
            render: (budget: BudgetRow) => (
                <div className="flex flex-col">
                    <span className="font-medium text-slate-900">
                        {budget.user?.name ?? 'N/A'}
                    </span>
                    <span className="text-xs text-slate-500">
                        {budget.user?.email ?? ''}
                    </span>
                </div>
            ),
        },
        {
            id: 'category',
            label: 'Category',
            sortable: false,
            render: (budget: BudgetRow) =>
                budget.category ? (
                    <div className="flex items-center gap-2">
                        <span
                            className="h-2.5 w-2.5 rounded-full border border-slate-200"
                            style={{
                                backgroundColor: budget.category.color,
                            }}
                        />
                        <span>{budget.category.name}</span>
                    </div>
                ) : (
                    <span className="text-slate-400">N/A</span>
                ),
        },
        {
            id: 'amount_limit',
            label: 'Limit',
            sortable: true,
            render: (budget: BudgetRow) => (
                <span className="font-semibold text-slate-900">
                    ${amountFormatter.format(budget.amount_limit)}
                </span>
            ),
        },
        {
            id: 'spent',
            label: 'Spent',
            sortable: false,
            render: (budget: BudgetRow) => (
                <span
                    className={
                        budget.spent > budget.amount_limit
                            ? 'font-semibold text-red-600'
                            : 'font-semibold text-emerald-600'
                    }
                >
                    ${amountFormatter.format(budget.spent)}
                </span>
            ),
        },
        {
            id: 'period',
            label: 'Period',
            sortable: true,
            render: (budget: BudgetRow) => (
                <Badge variant="secondary">{budget.period}</Badge>
            ),
        },
        {
            id: 'status',
            label: 'Status',
            sortable: true,
            render: (budget: BudgetRow) => (
                <Badge variant={budget.status === 'active' ? 'default' : 'secondary'}>
                    {budget.status}
                </Badge>
            ),
        },
    ];

    const handleAdd = () => {
        router.visit(adminBudgets.create().url);
    };

    const handleEdit = (budget: BudgetRow) => {
        router.visit(adminBudgets.edit(budget.id).url);
    };

    const handleDelete = (budget: BudgetRow) => {
        setSelectedBudget(budget);
        setIsDeleteOpen(true);
    };

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title={breadcrumbs[0].title} />
            <div className="flex flex-1 flex-col gap-4 p-4 sm:gap-6">
                <div className="flex flex-wrap items-end justify-between gap-2">
                    <div>
                        <h2 className="text-2xl font-bold tracking-tight">Budgets</h2>
                        <p className="text-muted-foreground">
                            Manage monthly and yearly budgets for users.
                        </p>
                    </div>
                    <BudgetsPrimaryButtons onAdd={handleAdd} />
                </div>

                <DataTable
                    data={budgets}
                    columns={columns}
                    onEdit={handleEdit}
                    onDelete={handleDelete}
                    pagination={pagination}
                    filters={filters}
                    baseUrl={adminBudgets.index().url}
                    advancedFilters={[
                        {
                            key: 'period',
                            label: 'Period',
                            type: 'select',
                            placeholder: 'All Periods',
                            options: [
                                { value: 'monthly', label: 'Monthly' },
                                { value: 'yearly', label: 'Yearly' },
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
                        {
                            key: 'user_id',
                            label: 'User',
                            type: 'select',
                            placeholder: 'All Users',
                            options: formOptions.users.map((user) => ({
                                value: String(user.id),
                                label: user.name,
                            })),
                        },
                        {
                            key: 'category_id',
                            label: 'Category',
                            type: 'select',
                            placeholder: 'All Categories',
                            options: formOptions.categories.map((category) => ({
                                value: String(category.id),
                                label: category.name,
                            })),
                        },
                    ]}
                />
            </div>

            <DeleteBudgetDialog
                open={isDeleteOpen}
                onOpenChange={setIsDeleteOpen}
                budget={selectedBudget}
            />
        </AdminLayout>
    );
}
