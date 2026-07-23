import { Head } from '@inertiajs/react';
import {
    Activity,
    ArrowDownRight,
    ArrowUpRight,
    CircleDollarSign,
    Clock3,
    CreditCard,
    Tags,
    Users,
    Wallet,
} from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AdminLayout from '@/layouts/admin';
import admin from '@/routes/admin';
import type { BreadcrumbItem } from '@/types';

interface DashboardStats {
    users: number;
    wallets: number;
    activeCategories: number;
    activeBudgets: number;
    postedIncomeThisMonth: number;
    postedExpenseThisMonth: number;
    netThisMonth: number;
    pendingTransactions: number;
}

interface MonthlyFlowItem {
    month: string;
    income: number;
    expense: number;
    net: number;
}

interface TopExpenseCategory {
    id: number;
    name: string;
    color: string;
    amount: number;
}

interface RecentTransaction {
    id: number;
    type: 'income' | 'expense';
    amount: number;
    status: 'posted' | 'pending' | 'cancelled';
    transactedAt: string | null;
    userName: string | null;
    walletName: string | null;
    currency: string | null;
    categoryName: string | null;
    categoryColor: string | null;
}

interface Props {
    stats: DashboardStats;
    monthlyFlow: MonthlyFlowItem[];
    topExpenseCategories: TopExpenseCategory[];
    recentTransactions: RecentTransaction[];
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: admin.dashboard().url,
    },
];

const numberFormatter = new Intl.NumberFormat('en-US');

function formatAmount(amount: number, currency = 'VND'): string {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency,
        maximumFractionDigits: 0,
    }).format(amount);
}

export default function AdminDashboard({
    stats,
    monthlyFlow,
    topExpenseCategories,
    recentTransactions,
}: Props) {
    const maxMonthlyAmount = Math.max(
        ...monthlyFlow.map((item) => Math.max(item.income, item.expense)),
        1,
    );
    const maxCategoryAmount = Math.max(
        ...topExpenseCategories.map((category) => category.amount),
        1,
    );

    const statCards = [
        {
            title: 'Users',
            value: numberFormatter.format(stats.users),
            icon: Users,
            tone: 'text-sky-600 bg-sky-50',
        },
        {
            title: 'Wallets',
            value: numberFormatter.format(stats.wallets),
            icon: Wallet,
            tone: 'text-emerald-600 bg-emerald-50',
        },
        {
            title: 'Active Categories',
            value: numberFormatter.format(stats.activeCategories),
            icon: Tags,
            tone: 'text-violet-600 bg-violet-50',
        },
        {
            title: 'Active Budgets',
            value: numberFormatter.format(stats.activeBudgets),
            icon: CreditCard,
            tone: 'text-amber-600 bg-amber-50',
        },
    ];

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title="Admin Dashboard" />

            <div className="flex flex-1 flex-col gap-4 p-4 sm:gap-6">
                <div>
                    <h2 className="text-2xl font-bold tracking-tight">
                        Dashboard
                    </h2>
                    <p className="text-muted-foreground">
                        Overview of users, wallets, budgets, and posted cash
                        flow.
                    </p>
                </div>

                <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    {statCards.map((card) => {
                        const Icon = card.icon;

                        return (
                            <Card key={card.title} className="rounded-lg">
                                <CardContent className="flex items-center justify-between gap-4">
                                    <div>
                                        <p className="text-sm font-medium text-muted-foreground">
                                            {card.title}
                                        </p>
                                        <p className="mt-2 text-3xl font-bold tracking-tight">
                                            {card.value}
                                        </p>
                                    </div>
                                    <div
                                        className={`rounded-lg p-3 ${card.tone}`}
                                    >
                                        <Icon className="h-5 w-5" />
                                    </div>
                                </CardContent>
                            </Card>
                        );
                    })}
                </div>

                <div className="grid gap-4 lg:grid-cols-3">
                    <Card className="rounded-lg">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 text-base">
                                <ArrowUpRight className="h-4 w-4 text-emerald-600" />
                                Income This Month
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p className="text-3xl font-bold text-emerald-600">
                                {formatAmount(stats.postedIncomeThisMonth)}
                            </p>
                        </CardContent>
                    </Card>

                    <Card className="rounded-lg">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 text-base">
                                <ArrowDownRight className="h-4 w-4 text-rose-600" />
                                Expense This Month
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p className="text-3xl font-bold text-rose-600">
                                {formatAmount(stats.postedExpenseThisMonth)}
                            </p>
                        </CardContent>
                    </Card>

                    <Card className="rounded-lg">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 text-base">
                                <CircleDollarSign className="h-4 w-4 text-slate-700" />
                                Net This Month
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p
                                className={`text-3xl font-bold ${
                                    stats.netThisMonth >= 0
                                        ? 'text-slate-900'
                                        : 'text-rose-600'
                                }`}
                            >
                                {formatAmount(stats.netThisMonth)}
                            </p>
                            <div className="mt-3 flex items-center gap-2 text-sm text-muted-foreground">
                                <Clock3 className="h-4 w-4" />
                                {numberFormatter.format(
                                    stats.pendingTransactions,
                                )}{' '}
                                pending transactions
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <div className="grid gap-4 xl:grid-cols-5">
                    <Card className="rounded-lg xl:col-span-3">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 text-base">
                                <Activity className="h-4 w-4" />
                                Six-Month Cash Flow
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {monthlyFlow.map((item) => (
                                    <div
                                        key={item.month}
                                        className="grid gap-2 sm:grid-cols-[88px_1fr_140px]"
                                    >
                                        <div className="text-sm font-medium text-slate-600">
                                            {item.month}
                                        </div>
                                        <div className="space-y-1.5">
                                            <div className="h-2 rounded-full bg-slate-100">
                                                <div
                                                    className="h-2 rounded-full bg-emerald-500"
                                                    style={{
                                                        width: `${Math.max((item.income / maxMonthlyAmount) * 100, item.income > 0 ? 4 : 0)}%`,
                                                    }}
                                                />
                                            </div>
                                            <div className="h-2 rounded-full bg-slate-100">
                                                <div
                                                    className="h-2 rounded-full bg-rose-500"
                                                    style={{
                                                        width: `${Math.max((item.expense / maxMonthlyAmount) * 100, item.expense > 0 ? 4 : 0)}%`,
                                                    }}
                                                />
                                            </div>
                                        </div>
                                        <div className="text-sm text-slate-600 sm:text-right">
                                            {formatAmount(item.net)}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="rounded-lg xl:col-span-2">
                        <CardHeader>
                            <CardTitle className="text-base">
                                Top Expense Categories
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            {topExpenseCategories.length > 0 ? (
                                <div className="space-y-4">
                                    {topExpenseCategories.map((category) => (
                                        <div
                                            key={category.id}
                                            className="space-y-2"
                                        >
                                            <div className="flex items-center justify-between gap-3 text-sm">
                                                <div className="flex min-w-0 items-center gap-2">
                                                    <span
                                                        className="h-2.5 w-2.5 shrink-0 rounded-full"
                                                        style={{
                                                            backgroundColor:
                                                                category.color,
                                                        }}
                                                    />
                                                    <span className="truncate font-medium">
                                                        {category.name}
                                                    </span>
                                                </div>
                                                <span className="shrink-0 text-slate-600">
                                                    {formatAmount(
                                                        category.amount,
                                                    )}
                                                </span>
                                            </div>
                                            <div className="h-2 rounded-full bg-slate-100">
                                                <div
                                                    className="h-2 rounded-full bg-slate-700"
                                                    style={{
                                                        width: `${Math.max((category.amount / maxCategoryAmount) * 100, 4)}%`,
                                                    }}
                                                />
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <p className="text-sm text-muted-foreground">
                                    No posted expenses yet.
                                </p>
                            )}
                        </CardContent>
                    </Card>
                </div>

                <Card className="rounded-lg">
                    <CardHeader>
                        <CardTitle className="text-base">
                            Recent Transactions
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="overflow-x-auto">
                            <table className="w-full text-left text-sm">
                                <thead className="border-b text-xs text-muted-foreground uppercase">
                                    <tr>
                                        <th className="py-3 pr-4 font-medium">
                                            User
                                        </th>
                                        <th className="py-3 pr-4 font-medium">
                                            Category
                                        </th>
                                        <th className="py-3 pr-4 font-medium">
                                            Wallet
                                        </th>
                                        <th className="py-3 pr-4 font-medium">
                                            Status
                                        </th>
                                        <th className="py-3 text-right font-medium">
                                            Amount
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y">
                                    {recentTransactions.map((transaction) => (
                                        <tr key={transaction.id}>
                                            <td className="py-3 pr-4 font-medium">
                                                {transaction.userName ?? 'N/A'}
                                                <div className="text-xs font-normal text-muted-foreground">
                                                    {transaction.transactedAt ??
                                                        ''}
                                                </div>
                                            </td>
                                            <td className="py-3 pr-4">
                                                <div className="flex items-center gap-2">
                                                    {transaction.categoryColor ? (
                                                        <span
                                                            className="h-2.5 w-2.5 rounded-full"
                                                            style={{
                                                                backgroundColor:
                                                                    transaction.categoryColor,
                                                            }}
                                                        />
                                                    ) : null}
                                                    <span>
                                                        {transaction.categoryName ??
                                                            'Uncategorized'}
                                                    </span>
                                                </div>
                                            </td>
                                            <td className="py-3 pr-4 text-slate-600">
                                                {transaction.walletName ??
                                                    'N/A'}
                                            </td>
                                            <td className="py-3 pr-4">
                                                <Badge
                                                    variant={
                                                        transaction.status ===
                                                        'posted'
                                                            ? 'default'
                                                            : transaction.status ===
                                                                'pending'
                                                              ? 'secondary'
                                                              : 'destructive'
                                                    }
                                                >
                                                    {transaction.status}
                                                </Badge>
                                            </td>
                                            <td
                                                className={`py-3 text-right font-semibold ${
                                                    transaction.type ===
                                                    'income'
                                                        ? 'text-emerald-600'
                                                        : 'text-rose-600'
                                                }`}
                                            >
                                                {transaction.type === 'income'
                                                    ? '+'
                                                    : '-'}
                                                {formatAmount(
                                                    transaction.amount,
                                                    transaction.currency ??
                                                        'VND',
                                                )}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                            {recentTransactions.length === 0 ? (
                                <div className="py-8 text-center text-sm text-muted-foreground">
                                    No transactions yet.
                                </div>
                            ) : null}
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AdminLayout>
    );
}
