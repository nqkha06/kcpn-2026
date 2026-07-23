import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import { DataTable } from '@/components/data-table';
import { Badge } from '@/components/ui/badge';
import AdminLayout from '@/layouts/admin';
import adminTransactions from '@/routes/admin/transactions';
import type { BreadcrumbItem } from '@/types';
import { DeleteTransactionDialog } from './components/transactions-dialogs';
import { TransactionsPrimaryButtons } from './components/transactions-primary-buttons';

interface TransactionRow {
    id: number;
    user_id: number;
    wallet_id: number;
    category_id: number | null;
    type: 'income' | 'expense';
    amount: number;
    status: 'posted' | 'pending' | 'cancelled';
    note: string | null;
    labels: string[];
    transacted_at: string | null;
    created_at: string | null;
    user: {
        id: number;
        name: string;
        email: string;
    } | null;
    wallet: {
        id: number;
        name: string;
        currency: string;
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
    wallets: Array<{
        id: number;
        user_id: number;
        name: string;
        currency: string;
        user_name?: string | null;
    }>;
    categories: Array<{
        id: number;
        name: string;
        color: string;
        status: string;
    }>;
}

interface Props {
    transactions: TransactionRow[];
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
        type?: string;
        status?: string;
        user_id?: string | number;
        wallet_id?: string | number;
        category_id?: string | number;
        from_date?: string;
        to_date?: string;
        sort_by?: string;
        sort_direction?: 'asc' | 'desc';
    };
    formOptions: FormOptions;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Transactions',
        href: adminTransactions.index().url,
    },
];

const amountFormatter = new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
});

export default function TransactionList({
    transactions = [],
    pagination,
    filters,
    formOptions,
}: Props) {
    const [isDeleteOpen, setIsDeleteOpen] = useState(false);
    const [selectedTransaction, setSelectedTransaction] =
        useState<TransactionRow | null>(null);

    const columns = [
        { id: 'id', label: 'ID', sortable: true },
        {
            id: 'user',
            label: 'User',
            sortable: false,
            render: (transaction: TransactionRow) => (
                <div className="flex flex-col">
                    <span className="font-medium text-slate-900">
                        {transaction.user?.name ?? 'N/A'}
                    </span>
                    <span className="text-xs text-slate-500">
                        {transaction.user?.email ?? ''}
                    </span>
                </div>
            ),
        },
        {
            id: 'wallet',
            label: 'Wallet',
            sortable: false,
            render: (transaction: TransactionRow) => (
                <span className="font-medium text-slate-700">
                    {transaction.wallet?.name ?? 'N/A'}
                </span>
            ),
        },
        {
            id: 'category',
            label: 'Category',
            sortable: false,
            render: (transaction: TransactionRow) =>
                transaction.category ? (
                    <div className="flex items-center gap-2">
                        <span
                            className="h-2.5 w-2.5 rounded-full border border-slate-200"
                            style={{
                                backgroundColor: transaction.category.color,
                            }}
                        />
                        <span>{transaction.category.name}</span>
                    </div>
                ) : (
                    <span className="text-slate-400">Uncategorized</span>
                ),
        },
        {
            id: 'type',
            label: 'Type',
            sortable: true,
            render: (transaction: TransactionRow) => (
                <Badge
                    variant={
                        transaction.type === 'income' ? 'default' : 'secondary'
                    }
                >
                    {transaction.type}
                </Badge>
            ),
        },
        {
            id: 'amount',
            label: 'Amount',
            sortable: true,
            render: (transaction: TransactionRow) => (
                <span
                    className={
                        transaction.type === 'income'
                            ? 'font-semibold text-emerald-600'
                            : 'font-semibold text-slate-900'
                    }
                >
                    {transaction.type === 'income' ? '+' : '-'}$
                    {amountFormatter.format(transaction.amount)}
                </span>
            ),
        },
        { id: 'transacted_at', label: 'Transaction Date', sortable: true },
        {
            id: 'status',
            label: 'Status',
            sortable: true,
            render: (transaction: TransactionRow) => (
                <Badge
                    variant={
                        transaction.status === 'posted'
                            ? 'default'
                            : transaction.status === 'pending'
                              ? 'secondary'
                              : 'destructive'
                    }
                >
                    {transaction.status}
                </Badge>
            ),
        },
    ];

    const handleAdd = () => {
        router.visit(adminTransactions.create().url);
    };

    const handleEdit = (transaction: TransactionRow) => {
        router.visit(adminTransactions.edit(transaction.id).url);
    };

    const handleDelete = (transaction: TransactionRow) => {
        setSelectedTransaction(transaction);
        setIsDeleteOpen(true);
    };

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title={breadcrumbs[0].title} />
            <div className="flex flex-1 flex-col gap-4 p-4 sm:gap-6">
                <div className="flex flex-wrap items-end justify-between gap-2">
                    <div>
                        <h2 className="text-2xl font-bold tracking-tight">
                            Transactions
                        </h2>
                        <p className="text-muted-foreground">
                            Manage user income and expense transactions.
                        </p>
                    </div>
                    <TransactionsPrimaryButtons onAdd={handleAdd} />
                </div>

                <DataTable
                    data={transactions}
                    columns={columns}
                    onEdit={handleEdit}
                    onDelete={handleDelete}
                    pagination={pagination}
                    filters={filters}
                    baseUrl={adminTransactions.index().url}
                    advancedFilters={[
                        {
                            key: 'type',
                            label: 'Type',
                            type: 'select',
                            placeholder: 'All Types',
                            options: [
                                { value: 'income', label: 'Income' },
                                { value: 'expense', label: 'Expense' },
                            ],
                        },
                        {
                            key: 'status',
                            label: 'Status',
                            type: 'select',
                            placeholder: 'All Statuses',
                            options: [
                                { value: 'posted', label: 'Posted' },
                                { value: 'pending', label: 'Pending' },
                                { value: 'cancelled', label: 'Cancelled' },
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
                            key: 'wallet_id',
                            label: 'Wallet',
                            type: 'select',
                            placeholder: 'All Wallets',
                            options: formOptions.wallets.map((wallet) => ({
                                value: String(wallet.id),
                                label: `${wallet.name} (${wallet.user_name ?? 'N/A'})`,
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
                        {
                            key: 'from_date',
                            label: 'From Date',
                            type: 'date',
                        },
                        {
                            key: 'to_date',
                            label: 'To Date',
                            type: 'date',
                        },
                    ]}
                />
            </div>

            <DeleteTransactionDialog
                open={isDeleteOpen}
                onOpenChange={setIsDeleteOpen}
                transaction={selectedTransaction}
            />
        </AdminLayout>
    );
}
