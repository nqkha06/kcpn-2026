import { Link, usePage } from '@inertiajs/react';
import {
    eachDayOfInterval,
    eachMonthOfInterval,
    endOfMonth,
    endOfYear,
    format,
    isWithinInterval,
    parseISO,
    startOfMonth,
    startOfYear,
    subMonths,
    subYears,
} from 'date-fns';
import {
    ArrowDownRight,
    ArrowUpRight,
    TrendingUp,
    Wallet,
    DollarSign,
} from 'lucide-react';
import { useMemo, useState } from 'react';
import {
    AreaChart,
    Area,
    XAxis,
    YAxis,
    CartesianGrid,
    Tooltip,
    ResponsiveContainer,
    PieChart,
    Pie,
    Cell,
} from 'recharts';
import TrackerLayout from '@/components/expense-tracker/layout';
import { formatCurrencyAmount, resolveCurrencyCode } from '@/lib/utils';
import expense from '@/routes/expense';
import type {
    TrackerCategory,
    TrackerTransaction,
    TrackerNavigationItem,
    TrackerProfile,
    TrackerWallet,
} from '@/types/expense-tracker';

type DashboardPeriod = 'this-month' | 'last-month' | 'this-year';

const CATEGORY_FALLBACK_COLOR = '#94a3b8';

const calculateChange = (currentValue: number, previousValue: number): number | null => {
    if (previousValue === 0) {
        return currentValue === 0 ? 0 : null;
    }

    return ((currentValue - previousValue) / previousValue) * 100;
};

const isValidDate = (date: Date): boolean => !Number.isNaN(date.getTime());

interface DashboardProps {
    navigation: TrackerNavigationItem[];
    profile?: TrackerProfile;
    data?: {
        categories?: TrackerCategory[];
        wallets?: TrackerWallet[];
        transactions?: TrackerTransaction[];
    };
}

export default function Dashboard({ navigation, profile, data: pageData }: DashboardProps) {
    const page = usePage<{ userPreferenceCurrency?: string }>();
    const preferredCurrency = resolveCurrencyCode(page.props.userPreferenceCurrency);
    const [period, setPeriod] = useState<DashboardPeriod>('this-month');

    const categories = useMemo(
        () => pageData?.categories ?? [],
        [pageData?.categories],
    );
    const wallets = useMemo(
        () => pageData?.wallets ?? [],
        [pageData?.wallets],
    );
    const transactions = useMemo(
        () => pageData?.transactions ?? [],
        [pageData?.transactions],
    );

    const categoryMap = useMemo(
        () => new Map(categories.map((category) => [category.id, category])),
        [categories],
    );

    const walletMap = useMemo(
        () => new Map(wallets.map((wallet) => [wallet.id, wallet])),
        [wallets],
    );

    const displayCurrency = useMemo(
        () => preferredCurrency ?? resolveCurrencyCode(wallets[0]?.currency),
        [preferredCurrency, wallets],
    );

    const periodRange = useMemo(() => {
        const now = new Date();

        if (period === 'this-year') {
            return {
                start: startOfYear(now),
                end: endOfYear(now),
            };
        }

        if (period === 'last-month') {
            const previousMonth = subMonths(now, 1);

            return {
                start: startOfMonth(previousMonth),
                end: endOfMonth(previousMonth),
            };
        }

        return {
            start: startOfMonth(now),
            end: endOfMonth(now),
        };
    }, [period]);

    const previousPeriodRange = useMemo(() => {
        const now = new Date();

        if (period === 'this-year') {
            const previousYear = subYears(now, 1);

            return {
                start: startOfYear(previousYear),
                end: endOfYear(previousYear),
            };
        }

        if (period === 'last-month') {
            const twoMonthsAgo = subMonths(now, 2);

            return {
                start: startOfMonth(twoMonthsAgo),
                end: endOfMonth(twoMonthsAgo),
            };
        }

        const previousMonth = subMonths(now, 1);

        return {
            start: startOfMonth(previousMonth),
            end: endOfMonth(previousMonth),
        };
    }, [period]);

    const transactionsInPeriod = useMemo(() => {
        return transactions.filter((transaction) => {
            const transactionDate = parseISO(transaction.date);

            if (!isValidDate(transactionDate)) {
                return false;
            }

            return isWithinInterval(transactionDate, periodRange);
        });
    }, [periodRange, transactions]);

    const previousTransactionsInPeriod = useMemo(() => {
        return transactions.filter((transaction) => {
            const transactionDate = parseISO(transaction.date);

            if (!isValidDate(transactionDate)) {
                return false;
            }

            return isWithinInterval(transactionDate, previousPeriodRange);
        });
    }, [previousPeriodRange, transactions]);

    const totalBalance = useMemo(
        () => wallets.reduce((accumulator, wallet) => accumulator + wallet.balance, 0),
        [wallets],
    );

    const totalIncome = useMemo(
        () =>
            transactionsInPeriod
                .filter((transaction) => transaction.type === 'income')
                .reduce((accumulator, transaction) => accumulator + transaction.amount, 0),
        [transactionsInPeriod],
    );

    const totalExpense = useMemo(
        () =>
            transactionsInPeriod
                .filter((transaction) => transaction.type === 'expense')
                .reduce((accumulator, transaction) => accumulator + transaction.amount, 0),
        [transactionsInPeriod],
    );

    const previousTotalIncome = useMemo(
        () =>
            previousTransactionsInPeriod
                .filter((transaction) => transaction.type === 'income')
                .reduce((accumulator, transaction) => accumulator + transaction.amount, 0),
        [previousTransactionsInPeriod],
    );

    const previousTotalExpense = useMemo(
        () =>
            previousTransactionsInPeriod
                .filter((transaction) => transaction.type === 'expense')
                .reduce((accumulator, transaction) => accumulator + transaction.amount, 0),
        [previousTransactionsInPeriod],
    );

    const incomeChange = useMemo(
        () => calculateChange(totalIncome, previousTotalIncome),
        [totalIncome, previousTotalIncome],
    );

    const expenseChange = useMemo(
        () => calculateChange(totalExpense, previousTotalExpense),
        [totalExpense, previousTotalExpense],
    );

    const chartData = useMemo(() => {
        if (period === 'this-year') {
            const months = eachMonthOfInterval(periodRange);

            return months.map((month) => {
                const key = format(month, 'yyyy-MM');

                const value = transactionsInPeriod.reduce(
                    (accumulator, transaction) => {
                        const transactionDate = parseISO(transaction.date);

                        if (!isValidDate(transactionDate)) {
                            return accumulator;
                        }

                        if (format(transactionDate, 'yyyy-MM') !== key) {
                            return accumulator;
                        }

                        const sign = transaction.type === 'income' ? 1 : -1;

                        return accumulator + transaction.amount * sign;
                    },
                    0,
                );

                return {
                    name: `T${format(month, 'M')}`,
                    value,
                };
            });
        }

        const days = eachDayOfInterval(periodRange);

        return days.map((day) => {
            const key = format(day, 'yyyy-MM-dd');

            const value = transactionsInPeriod.reduce((accumulator, transaction) => {
                const transactionDate = parseISO(transaction.date);

                if (!isValidDate(transactionDate)) {
                    return accumulator;
                }

                if (format(transactionDate, 'yyyy-MM-dd') !== key) {
                    return accumulator;
                }

                const sign = transaction.type === 'income' ? 1 : -1;

                return accumulator + transaction.amount * sign;
            }, 0);

            return {
                name: format(day, 'dd/MM'),
                value,
            };
        });
    }, [period, periodRange, transactionsInPeriod]);

    const categoryData = useMemo(() => {
        const expenseTotalsByCategory = new Map<string, number>();

        transactionsInPeriod
            .filter((transaction) => transaction.type === 'expense')
            .forEach((transaction) => {
                const key = transaction.categoryId || 'uncategorized';
                const currentAmount = expenseTotalsByCategory.get(key) ?? 0;

                expenseTotalsByCategory.set(key, currentAmount + transaction.amount);
            });

        const dataset = [...expenseTotalsByCategory.entries()]
            .map(([categoryId, value]) => {
                const category = categoryMap.get(categoryId);

                if (categoryId === 'uncategorized' || category === undefined) {
                    return {
                        name: 'Không phân loại',
                        value,
                        color: CATEGORY_FALLBACK_COLOR,
                    };
                }

                return {
                    name: category.name,
                    value,
                    color: category.color,
                };
            })
            .sort((a, b) => b.value - a.value);

        return dataset;
    }, [categoryMap, transactionsInPeriod]);

    const recentTransactions = useMemo(
        () =>
            transactions
                .slice()
                .sort(
                    (a, b) =>
                        new Date(b.date).getTime() -
                        new Date(a.date).getTime(),
                )
                .slice(0, 10),
        [transactions],
    );

    const periodLabel =
        period === 'this-month'
            ? 'so với tháng trước'
            : period === 'last-month'
              ? 'so với tháng liền trước'
              : 'so với năm trước';

    const formatChangeLabel = (change: number | null): string => {
        if (change === null) {
            return 'Chưa có dữ liệu so sánh';
        }

        const sign = change >= 0 ? '+' : '';

        return `${sign}${change.toFixed(1)}%`;
    };

    const formatDate = (value: string): string => {
        const date = parseISO(value);

        if (!isValidDate(date)) {
            return value;
        }

        return format(date, 'dd/MM/yyyy');
    };

    return (
        <TrackerLayout
            title="Tổng quan"
            heading="Tổng quan tài chính"
            description="Theo dõi số dư, dòng tiền và chi tiêu trong nháy mắt."
            activePath="/user/dashboard"
            navigation={navigation}
            profile={profile}
            action={
                <div>
                    <select
                        value={period}
                        onChange={(event) =>
                            setPeriod(event.target.value as DashboardPeriod)
                        }
                        className="block rounded-lg border border-slate-200 bg-white p-2 text-sm font-medium text-slate-700 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                    >
                        <option value="this-month">Tháng này</option>
                        <option value="last-month">Tháng trước</option>
                        <option value="this-year">Năm nay</option>
                    </select>
                </div>
            }
        >
            <div className="space-y-6">
                {/* Overview Cards */}
                <div className="grid grid-cols-1 gap-6 md:grid-cols-3">
                    <div className="flex flex-col justify-between rounded-2xl border border-slate-100 bg-white p-6 shadow-sm transition-shadow hover:shadow-md">
                        <div className="flex items-start justify-between">
                            <div>
                                <p className="mb-1 text-sm font-medium text-slate-500">
                                    Tổng số dư
                                </p>
                                <h3 className="text-3xl font-bold text-slate-900">
                                    {formatCurrencyAmount(totalBalance, displayCurrency)}
                                </h3>
                            </div>
                            <div className="rounded-xl bg-primary-50 p-3 text-primary-600">
                                <Wallet className="h-6 w-6" />
                            </div>
                        </div>
                        <div className="mt-4 text-sm text-slate-400">
                            Cập nhật theo số dư ví hiện tại
                        </div>
                    </div>

                    <div className="flex flex-col justify-between rounded-2xl border border-slate-100 bg-white p-6 shadow-sm transition-shadow hover:shadow-md">
                        <div className="flex items-start justify-between">
                            <div>
                                <p className="mb-1 text-sm font-medium text-slate-500">
                                    Tổng thu nhập
                                </p>
                                <h3 className="text-3xl font-bold text-slate-900">
                                    {formatCurrencyAmount(totalIncome, displayCurrency)}
                                </h3>
                            </div>
                            <div className="rounded-xl bg-success-50 p-3 text-success-600">
                                <ArrowDownRight className="h-6 w-6" />
                            </div>
                        </div>
                        <div className="mt-4 flex items-center text-sm">
                            <TrendingUp className="mr-1 h-4 w-4 text-success-500" />
                            <span className="font-medium text-success-500">
                                {formatChangeLabel(incomeChange)}
                            </span>
                            <span className="ml-2 text-slate-400">
                                {periodLabel}
                            </span>
                        </div>
                    </div>

                    <div className="flex flex-col justify-between rounded-2xl border border-slate-100 bg-white p-6 shadow-sm transition-shadow hover:shadow-md">
                        <div className="flex items-start justify-between">
                            <div>
                                <p className="mb-1 text-sm font-medium text-slate-500">
                                    Tổng chi tiêu
                                </p>
                                <h3 className="text-3xl font-bold text-slate-900">
                                    {formatCurrencyAmount(totalExpense, displayCurrency)}
                                </h3>
                            </div>
                            <div className="rounded-xl bg-danger-50 p-3 text-danger-600">
                                <ArrowUpRight className="h-6 w-6" />
                            </div>
                        </div>
                        <div className="mt-4 flex items-center text-sm">
                            <TrendingUp className="mr-1 h-4 w-4 text-danger-500" />
                            <span className="font-medium text-danger-500">
                                {formatChangeLabel(expenseChange)}
                            </span>
                            <span className="ml-2 text-slate-400">
                                {periodLabel}
                            </span>
                        </div>
                    </div>
                </div>

                <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    {/* Main Chart */}
                    <div className="flex flex-col rounded-2xl border border-slate-100 bg-white p-6 shadow-sm transition-shadow hover:shadow-md lg:col-span-2">
                        <h3 className="mb-6 text-lg font-bold tracking-tight text-slate-900">
                            Dòng tiền
                        </h3>
                        <div className="h-[300px] w-full grow">
                            <ResponsiveContainer width="100%" height="100%">
                                <AreaChart
                                    data={chartData}
                                    margin={{
                                        top: 10,
                                        right: 10,
                                        left: -20,
                                        bottom: 0,
                                    }}
                                >
                                    <defs>
                                        <linearGradient
                                            id="colorValue"
                                            x1="0"
                                            y1="0"
                                            x2="0"
                                            y2="1"
                                        >
                                            <stop
                                                offset="5%"
                                                stopColor="#3b82f6"
                                                stopOpacity={0.3}
                                            />
                                            <stop
                                                offset="95%"
                                                stopColor="#3b82f6"
                                                stopOpacity={0}
                                            />
                                        </linearGradient>
                                    </defs>
                                    <CartesianGrid
                                        strokeDasharray="3 3"
                                        vertical={false}
                                        stroke="#e2e8f0"
                                    />
                                    <XAxis
                                        dataKey="name"
                                        axisLine={false}
                                        tickLine={false}
                                        tick={{ fill: '#64748b', fontSize: 12 }}
                                        dy={10}
                                    />
                                    <YAxis
                                        axisLine={false}
                                        tickLine={false}
                                        tick={{ fill: '#64748b', fontSize: 12 }}
                                        tickFormatter={(value: number) =>
                                            formatCurrencyAmount(
                                                Number(value),
                                                displayCurrency,
                                                0,
                                            )
                                        }
                                    />
                                    <Tooltip
                                        contentStyle={{
                                            borderRadius: '12px',
                                            border: 'none',
                                            boxShadow:
                                                '0 10px 15px -3px rgb(0 0 0 / 0.1)',
                                        }}
                                        itemStyle={{
                                            color: '#0f172a',
                                            fontWeight: 600,
                                        }}
                                    />
                                    <Area
                                        type="monotone"
                                        dataKey="value"
                                        stroke="#3b82f6"
                                        strokeWidth={3}
                                        fillOpacity={1}
                                        fill="url(#colorValue)"
                                    />
                                </AreaChart>
                            </ResponsiveContainer>
                        </div>
                    </div>

                    {/* Expenses by Category */}
                    <div className="flex flex-col rounded-2xl border border-slate-100 bg-white p-6 shadow-sm transition-shadow hover:shadow-md">
                        <h3 className="mb-6 text-lg font-bold tracking-tight text-slate-900">
                            Chi tiêu theo danh mục
                        </h3>
                        <div className="relative flex min-h-[220px] w-full grow items-center justify-center">
                            <ResponsiveContainer width="100%" height="100%">
                                <PieChart>
                                    <Pie
                                        data={categoryData}
                                        cx="50%"
                                        cy="50%"
                                        innerRadius={60}
                                        outerRadius={80}
                                        paddingAngle={5}
                                        dataKey="value"
                                        stroke="none"
                                    >
                                        {categoryData.map((entry, index) => (
                                            <Cell
                                                key={`cell-${index}`}
                                                fill={entry.color}
                                            />
                                        ))}
                                    </Pie>
                                    <Tooltip
                                        contentStyle={{
                                            borderRadius: '12px',
                                            border: 'none',
                                            boxShadow:
                                                '0 4px 6px -1px rgb(0 0 0 / 0.1)',
                                        }}
                                        itemStyle={{ fontWeight: 500 }}
                                        formatter={(value: number | string | undefined) => [
                                            formatCurrencyAmount(
                                                Number(value ?? 0),
                                                displayCurrency,
                                            ),
                                            'Số tiền',
                                        ]}
                                    />
                                </PieChart>
                            </ResponsiveContainer>
                            <div className="pointer-events-none absolute inset-0 flex flex-col items-center justify-center">
                                <span className="text-2xl font-bold text-slate-900">
                                    {formatCurrencyAmount(totalExpense, displayCurrency, 0)}
                                </span>
                                <span className="text-xs font-medium tracking-wider text-slate-500 uppercase">
                                    Tổng
                                </span>
                            </div>
                        </div>
                        <div className="mt-4 flex flex-col gap-3">
                            {categoryData.slice(0, 4).map((category, index) => (
                                <div
                                    key={index}
                                    className="flex items-center justify-between text-sm"
                                >
                                    <div className="flex items-center gap-2">
                                        <div
                                            className="h-3 w-3 rounded-full"
                                            style={{
                                                backgroundColor: category.color,
                                            }}
                                        ></div>
                                        <span className="font-medium text-slate-600">
                                            {category.name}
                                        </span>
                                    </div>
                                    <span className="font-bold text-slate-900">
                                        {formatCurrencyAmount(category.value, displayCurrency)}
                                    </span>
                                </div>
                            ))}
                            {categoryData.length === 0 ? (
                                <p className="text-sm text-slate-500">
                                    Chưa có dữ liệu chi tiêu trong kỳ đã chọn.
                                </p>
                            ) : null}
                        </div>
                    </div>
                </div>

                {/* Recent Transactions */}
                <div className="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm transition-shadow hover:shadow-md">
                    <div className="flex items-center justify-between border-b border-slate-100 bg-slate-50/50 p-6">
                        <h3 className="text-lg font-bold tracking-tight text-slate-900">
                            Giao dịch gần đây
                        </h3>
                        <Link
                            href={expense.transactions().url}
                            className="hover:text-primary-700 text-sm font-medium text-primary-600 transition-colors"
                        >
                            Xem tất cả
                        </Link>
                    </div>
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-sm whitespace-nowrap">
                            <thead className="border-b border-slate-100 bg-slate-50/50 text-xs font-medium tracking-wider text-slate-500 uppercase">
                                <tr>
                                    <th scope="col" className="px-6 py-4">
                                        Giao dịch
                                    </th>
                                    <th scope="col" className="px-6 py-4">
                                        Danh mục
                                    </th>
                                    <th scope="col" className="px-6 py-4">
                                        Ngày
                                    </th>
                                    <th scope="col" className="px-6 py-4">
                                        Ví
                                    </th>
                                    <th
                                        scope="col"
                                        className="px-6 py-4 text-right"
                                    >
                                        Số tiền
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100 text-slate-700">
                                {recentTransactions.map((tx) => {
                                        const category = categoryMap.get(
                                            tx.categoryId,
                                        );
                                        const wallet = walletMap.get(
                                            tx.walletId,
                                        );
                                        const isIncome = tx.type === 'income';
                                        return (
                                            <tr
                                                key={tx.id}
                                                className="group cursor-pointer transition-colors hover:bg-slate-50/80"
                                            >
                                                <td className="flex items-center gap-3 px-6 py-4 font-medium text-slate-900">
                                                    <div
                                                        className={`rounded-lg p-2 ${isIncome ? 'group-hover:bg-success-100 bg-success-50 text-success-600' : 'bg-slate-100 text-slate-600 group-hover:bg-slate-200'} transition-colors`}
                                                    >
                                                        {isIncome ? (
                                                            <ArrowDownRight className="h-4 w-4" />
                                                        ) : (
                                                            <DollarSign className="h-4 w-4" />
                                                        )}
                                                    </div>
                                                    {tx.note || 'Chuyển khoản'}
                                                </td>
                                                <td className="px-6 py-4">
                                                    <span
                                                        className="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium"
                                                        style={{
                                                            backgroundColor: `${category?.color}15`,
                                                            color: category?.color,
                                                            borderColor: `${category?.color}30`,
                                                        }}
                                                    >
                                                        {category?.name}
                                                    </span>
                                                </td>
                                                <td className="px-6 py-4 text-slate-500">
                                                    {formatDate(tx.date)}
                                                </td>
                                                <td className="px-6 py-4 text-slate-500">
                                                    {wallet?.name ??
                                                        'Không xác định'}
                                                </td>
                                                <td
                                                    className={`px-6 py-4 text-right font-bold ${isIncome ? 'text-success-600' : 'text-slate-900'}`}
                                                >
                                                    {isIncome ? '+' : '-'}
                                                    {formatCurrencyAmount(
                                                        tx.amount,
                                                        resolveCurrencyCode(wallet?.currency) ?? displayCurrency,
                                                    )}
                                                </td>
                                            </tr>
                                        );
                                    })}
                                {recentTransactions.length === 0 ? (
                                    <tr>
                                        <td
                                            colSpan={5}
                                            className="px-6 py-12 text-center text-slate-500"
                                        >
                                            Chưa có giao dịch nào.
                                        </td>
                                    </tr>
                                ) : null}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </TrackerLayout>
    );
}
