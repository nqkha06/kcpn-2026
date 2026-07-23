import { useForm, usePage } from '@inertiajs/react';
import { format, parseISO } from 'date-fns';
import {
    ArrowDownRight,
    DollarSign,
    Filter,
    Plus,
    Search,
    X,
} from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';
import type { FormEvent } from 'react';
import TrackerLayout from '@/components/expense-tracker/layout';

import { formatCurrencyAmount, resolveCurrencyCode } from '@/lib/utils';
import expense from '@/routes/expense';
import type {
    TrackerCategory,
    TrackerNavigationItem,
    TrackerProfile,
    TrackerTransaction,
    TrackerWallet,
} from '@/types/expense-tracker';

interface TransactionsProps {
    navigation: TrackerNavigationItem[];
    profile?: TrackerProfile;
    data?: {
        transactions?: TrackerTransaction[];
        categories?: TrackerCategory[];
        wallets?: TrackerWallet[];
    };
}

export default function Transactions({
    navigation,
    profile,
    data,
}: TransactionsProps) {
    const page = usePage<{ userPreferenceCurrency?: string }>();
    const preferredCurrency = resolveCurrencyCode(page.props.userPreferenceCurrency);
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [searchTerm, setSearchTerm] = useState('');
    const [filterType, setFilterType] = useState<'all' | 'income' | 'expense'>(
        'all',
    );

    const transactions = useMemo(
        () => data?.transactions ?? [],
        [data?.transactions],
    );
    const categoryOptions =
        data?.categories && data.categories.length > 0
            ? data.categories
            : [];
    const walletOptions =
        data?.wallets && data.wallets.length > 0 ? data.wallets : [];

    const {
        data: formData,
        setData,
        post,
        processing,
        errors,
        reset,
    } = useForm({
        type: 'expense',
        amount: '',
        wallet_id: walletOptions[0]?.id ?? '',
        category_id: 'none',
        transacted_at: format(new Date(), 'yyyy-MM-dd'),
        note: '',
        labels: '',
    });

    useEffect(() => {
        if (
            formData.wallet_id === '' &&
            walletOptions.length > 0 &&
            walletOptions[0] !== undefined
        ) {
            setData('wallet_id', walletOptions[0].id);
        }
    }, [formData.wallet_id, setData, walletOptions]);

    const filteredTransactions = useMemo(() => {
        return transactions
            .filter((transaction) => {
                const note = (transaction.note ?? '').toLowerCase();
                const matchesSearch = note.includes(searchTerm.toLowerCase());
                const matchesType =
                    filterType === 'all' || transaction.type === filterType;

                return matchesSearch && matchesType;
            })
            .sort(
                (a, b) =>
                    new Date(b.date).getTime() - new Date(a.date).getTime(),
            );
    }, [transactions, searchTerm, filterType]);

    const closeModal = (): void => {
        setIsModalOpen(false);
        reset();
        setData('type', 'expense');
        setData('wallet_id', walletOptions[0]?.id ?? '');
        setData('category_id', 'none');
        setData('transacted_at', format(new Date(), 'yyyy-MM-dd'));
    };

    const submitTransaction = (event: FormEvent): void => {
        event.preventDefault();

        post(expense.transactions().url, {
            preserveScroll: true,
            onSuccess: () => {
                closeModal();
            },
        });
    };

    return (
        <TrackerLayout
            title="Giao dịch"
            heading="Giao dịch"
            description="Ghi lại mọi khoản thu chi tại một nơi."
            activePath="/user/transactions"
            navigation={navigation}
            profile={profile}
            action={
                <button
                    type="button"
                    onClick={() => setIsModalOpen(true)}
                    className="hover:bg-primary-700 flex items-center gap-2 rounded-xl bg-primary-600 px-4 py-2 font-medium text-white shadow-sm shadow-primary-500/30 transition-colors"
                >
                    <Plus className="h-4 w-4" />
                    Thêm giao dịch
                </button>
            }
        >
            <div className="space-y-6">
                <div className="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
                    <div className="flex flex-col justify-between gap-4 border-b border-slate-100 bg-slate-50/50 p-4 sm:flex-row">
                        <div className="relative grow sm:max-w-md">
                            <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <Search className="h-4 w-4 text-slate-400" />
                            </div>
                            <input
                                type="text"
                                placeholder="Tìm kiếm giao dịch..."
                                value={searchTerm}
                                onChange={(event) =>
                                    setSearchTerm(event.target.value)
                                }
                                className="block w-full rounded-xl border border-slate-200 bg-white py-2 pl-10 text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                            />
                        </div>
                        <div className="flex items-center gap-2">
                            <div className="relative">
                                <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <Filter className="h-4 w-4 text-slate-400" />
                                </div>
                                <select
                                    value={filterType}
                                    onChange={(event) =>
                                        setFilterType(
                                            event.target.value as
                                                | 'all'
                                                | 'income'
                                                | 'expense',
                                        )
                                    }
                                    className="block w-full rounded-xl border border-slate-200 bg-white py-2 pr-8 pl-10 font-medium text-slate-700 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                                >
                                    <option value="all">Tất cả loại</option>
                                    <option value="income">Chỉ thu nhập</option>
                                    <option value="expense">
                                        Chỉ chi tiêu
                                    </option>
                                </select>
                            </div>
                        </div>
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
                                {filteredTransactions.map((transaction) => {
                                    const category = categoryOptions.find(
                                        (item) =>
                                            item.id === transaction.categoryId,
                                    );
                                    const wallet = walletOptions.find(
                                        (item) =>
                                            item.id === transaction.walletId,
                                    );
                                    const isIncome =
                                        transaction.type === 'income';

                                    return (
                                        <tr
                                            key={transaction.id}
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
                                                <div className="flex flex-col">
                                                    <span>
                                                        {transaction.note ??
                                                            'Chuyển khoản'}
                                                    </span>
                                                    {transaction.labels &&
                                                    transaction.labels.length >
                                                        0 ? (
                                                        <div className="mt-1 flex gap-1">
                                                            {transaction.labels.map(
                                                                (label) => (
                                                                    <span
                                                                        key={
                                                                            label
                                                                        }
                                                                        className="rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-bold tracking-wider text-slate-500 uppercase"
                                                                    >
                                                                        {label}
                                                                    </span>
                                                                ),
                                                            )}
                                                        </div>
                                                    ) : null}
                                                </div>
                                            </td>
                                            <td className="px-6 py-4">
                                                {category ? (
                                                    <span
                                                        className="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium"
                                                        style={{
                                                            backgroundColor: `${category.color}15`,
                                                            color: category.color,
                                                            borderColor: `${category.color}30`,
                                                        }}
                                                    >
                                                        {category.name}
                                                    </span>
                                                ) : (
                                                    <span className="text-xs text-slate-400">
                                                        Không phân loại
                                                    </span>
                                                )}
                                            </td>
                                            <td className="px-6 py-4 text-slate-500">
                                                {format(
                                                    parseISO(transaction.date),
                                                    'dd/MM/yyyy',
                                                )}
                                            </td>
                                            <td className="px-6 py-4 text-slate-500">
                                                {wallet?.name ??
                                                    'Ví không xác định'}
                                            </td>
                                            <td
                                                className={`px-6 py-4 text-right font-bold ${isIncome ? 'text-success-600' : 'text-slate-900'}`}
                                            >
                                                {isIncome ? '+' : '-'}
                                                {formatCurrencyAmount(
                                                    transaction.amount,
                                                    wallet?.currency ?? preferredCurrency,
                                                )}
                                            </td>
                                        </tr>
                                    );
                                })}
                                {filteredTransactions.length === 0 ? (
                                    <tr>
                                        <td
                                            colSpan={5}
                                            className="px-6 py-12 text-center text-slate-500"
                                        >
                                            Không tìm thấy giao dịch. Hãy điều
                                            chỉnh bộ lọc.
                                        </td>
                                    </tr>
                                ) : null}
                            </tbody>
                        </table>
                    </div>
                </div>

                {isModalOpen ? (
                    <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4 backdrop-blur-sm">
                        <div className="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-xl">
                            <div className="flex items-center justify-between border-b border-slate-100 p-5">
                                <h2 className="text-xl font-bold text-slate-900">
                                    Thêm giao dịch
                                </h2>
                                <button
                                    type="button"
                                    onClick={closeModal}
                                    className="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600"
                                >
                                    <X className="h-5 w-5" />
                                </button>
                            </div>

                            <form
                                onSubmit={submitTransaction}
                                className="space-y-4 p-5"
                            >
                                <div className="grid grid-cols-2 gap-3">
                                    <button
                                        type="button"
                                        onClick={() =>
                                            setData('type', 'expense')
                                        }
                                        className={`rounded-xl border py-2.5 font-medium tracking-wide transition-colors ${
                                            formData.type === 'expense'
                                                ? 'border-danger-200 bg-danger-50 text-danger-600'
                                                : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'
                                        }`}
                                    >
                                        Chi tiêu
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() =>
                                            setData('type', 'income')
                                        }
                                        className={`rounded-xl border py-2.5 font-medium tracking-wide transition-colors ${
                                            formData.type === 'income'
                                                ? 'border-success-200 bg-success-50 text-success-600'
                                                : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'
                                        }`}
                                    >
                                        Thu nhập
                                    </button>
                                </div>
                                {errors.type ? (
                                    <p className="text-xs text-danger-600">
                                        {errors.type}
                                    </p>
                                ) : null}

                                <div>
                                    <label className="mb-1 block text-sm font-medium text-slate-700">
                                        Số tiền
                                    </label>
                                    <div className="relative">
                                        <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                            <span className="text-sm text-slate-500">
                                                {resolveCurrencyCode(
                                                    walletOptions.find((wallet) => wallet.id === formData.wallet_id)?.currency,
                                                ) ?? preferredCurrency ?? '---'}
                                            </span>
                                        </div>
                                        <input
                                            type="number"
                                            min="0.01"
                                            step="0.01"
                                            value={formData.amount}
                                            onChange={(event) =>
                                                setData(
                                                    'amount',
                                                    event.target.value,
                                                )
                                            }
                                            placeholder="0.00"
                                            className="block w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-14 text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                                        />
                                    </div>
                                    {errors.amount ? (
                                        <p className="mt-1 text-xs text-danger-600">
                                            {errors.amount}
                                        </p>
                                    ) : null}
                                </div>

                                <div>
                                    <label className="mb-1 block text-sm font-medium text-slate-700">
                                        Ví
                                    </label>
                                    <select
                                        value={formData.wallet_id}
                                        onChange={(event) =>
                                            setData(
                                                'wallet_id',
                                                event.target.value,
                                            )
                                        }
                                        className="block w-full rounded-xl border border-slate-200 bg-white py-2.5 text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                                    >
                                        {walletOptions.map((wallet) => (
                                            <option
                                                key={wallet.id}
                                                value={wallet.id}
                                            >
                                                {wallet.name}
                                            </option>
                                        ))}
                                    </select>
                                    {errors.wallet_id ? (
                                        <p className="mt-1 text-xs text-danger-600">
                                            {errors.wallet_id}
                                        </p>
                                    ) : null}
                                </div>

                                <div>
                                    <label className="mb-1 block text-sm font-medium text-slate-700">
                                        Danh mục
                                    </label>
                                    <select
                                        value={formData.category_id}
                                        onChange={(event) =>
                                            setData(
                                                'category_id',
                                                event.target.value,
                                            )
                                        }
                                        className="block w-full rounded-xl border border-slate-200 bg-white py-2.5 text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                                    >
                                        <option value="none">
                                            Không phân loại
                                        </option>
                                        {categoryOptions.map((category) => (
                                            <option
                                                key={category.id}
                                                value={category.id}
                                            >
                                                {category.name}
                                            </option>
                                        ))}
                                    </select>
                                    {errors.category_id ? (
                                        <p className="mt-1 text-xs text-danger-600">
                                            {errors.category_id}
                                        </p>
                                    ) : null}
                                </div>

                                <div>
                                    <label className="mb-1 block text-sm font-medium text-slate-700">
                                        Ngày
                                    </label>
                                    <input
                                        type="date"
                                        value={formData.transacted_at}
                                        onChange={(event) =>
                                            setData(
                                                'transacted_at',
                                                event.target.value,
                                            )
                                        }
                                        className="block w-full rounded-xl border border-slate-200 bg-white py-2.5 text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                                    />
                                    {errors.transacted_at ? (
                                        <p className="mt-1 text-xs text-danger-600">
                                            {errors.transacted_at}
                                        </p>
                                    ) : null}
                                </div>

                                <div>
                                    <label className="mb-1 block text-sm font-medium text-slate-700">
                                        Ghi chú (tùy chọn)
                                    </label>
                                    <input
                                        type="text"
                                        value={formData.note}
                                        onChange={(event) =>
                                            setData('note', event.target.value)
                                        }
                                        placeholder="Khoản này dùng cho gì?"
                                        className="block w-full rounded-xl border border-slate-200 bg-white py-2.5 text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                                    />
                                    {errors.note ? (
                                        <p className="mt-1 text-xs text-danger-600">
                                            {errors.note}
                                        </p>
                                    ) : null}
                                </div>

                                <div>
                                    <label className="mb-1 block text-sm font-medium text-slate-700">
                                        Nhãn (cách nhau bởi dấu phẩy)
                                    </label>
                                    <input
                                        type="text"
                                        value={formData.labels}
                                        onChange={(event) =>
                                            setData(
                                                'labels',
                                                event.target.value,
                                            )
                                        }
                                        placeholder="an-uong, cong-viec"
                                        className="block w-full rounded-xl border border-slate-200 bg-white py-2.5 text-slate-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                                    />
                                    {errors.labels ? (
                                        <p className="mt-1 text-xs text-danger-600">
                                            {errors.labels}
                                        </p>
                                    ) : null}
                                </div>

                                <div className="flex justify-end gap-3 border-t border-slate-100 pt-4">
                                    <button
                                        type="button"
                                        onClick={closeModal}
                                        className="rounded-xl border border-slate-200 bg-white px-4 py-2 font-medium text-slate-700 transition-colors hover:bg-slate-50"
                                    >
                                        Hủy
                                    </button>
                                    <button
                                        type="submit"
                                        disabled={processing}
                                        className="hover:bg-primary-700 rounded-xl bg-primary-600 px-6 py-2 font-medium text-white shadow-sm shadow-primary-500/30 transition-colors disabled:cursor-not-allowed disabled:opacity-60"
                                    >
                                        {processing ? 'Đang lưu...' : 'Lưu'}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                ) : null}
            </div>
        </TrackerLayout>
    );
}
