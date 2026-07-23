import { useForm, usePage } from '@inertiajs/react';
import { Plus, Target, AlertCircle, X } from 'lucide-react';
import { useState } from 'react';
import type { FormEvent } from 'react';
import TrackerLayout from '@/components/expense-tracker/layout';
import { formatCurrencyAmount, resolveCurrencyCode } from '@/lib/utils';
import expense from '@/routes/expense';
import type {
    TrackerCategory,
    TrackerBudget,
    TrackerNavigationItem,
    TrackerProfile,
} from '@/types/expense-tracker';

interface BudgetsProps {
    navigation: TrackerNavigationItem[];
    profile?: TrackerProfile;
    data?: {
        categories?: TrackerCategory[];
        budgets?: TrackerBudget[];
    };
}

export default function Budgets({ navigation, profile, data }: BudgetsProps) {
    const page = usePage<{ userPreferenceCurrency?: string }>();
    const displayCurrency = resolveCurrencyCode(page.props.userPreferenceCurrency);
    const [isModalOpen, setIsModalOpen] = useState(false);
    const categories =
        data?.categories && data.categories.length > 0
            ? data.categories
            : [];
    const budgets = data?.budgets ?? [];

    const {
        data: formData,
        setData,
        post,
        processing,
        errors,
        reset,
    } = useForm({
        category_id: categories[0]?.id ?? '',
        amount_limit: '',
        period: 'monthly',
        note: '',
    });

    const closeModal = (): void => {
        setIsModalOpen(false);
        reset();
        setData('category_id', categories[0]?.id ?? '');
        setData('period', 'monthly');
    };

    const submitBudget = (event: FormEvent): void => {
        event.preventDefault();

        post(expense.budgets().url, {
            preserveScroll: true,
            onSuccess: () => {
                closeModal();
            },
        });
    };

    const totalBudget = budgets.reduce((acc, budget) => acc + budget.limit, 0);
    const totalSpent = budgets.reduce((acc, budget) => acc + budget.spent, 0);
    const overallPercentage =
        totalBudget > 0 ? Math.min((totalSpent / totalBudget) * 100, 100) : 0;

    return (
        <TrackerLayout
            title="Ngân sách"
            heading="Kế hoạch ngân sách"
            description="Đặt giới hạn theo tháng và bám sát mục tiêu."
            activePath="/user/budgets"
            navigation={navigation}
            profile={profile}
            action={
                <button
                    type="button"
                    onClick={() => setIsModalOpen(true)}
                    className="hover:bg-primary-700 flex items-center gap-2 rounded-xl bg-primary-600 px-4 py-2 font-medium text-white shadow-sm shadow-primary-500/30 transition-colors"
                >
                    <Plus className="h-4 w-4" />
                    Tạo ngân sách
                </button>
            }
        >
            <div className="space-y-6">
                <div className="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm transition-shadow hover:shadow-md md:p-8">
                    <h2 className="mb-6 text-lg font-bold text-slate-900">
                        Tổng quan tháng
                    </h2>

                    <div className="flex flex-col items-center gap-8 md:flex-row">
                        <div className="relative h-48 w-48 shrink-0">
                            <svg
                                className="h-full w-full -rotate-90 transform"
                                viewBox="0 0 100 100"
                            >
                                <circle
                                    className="stroke-current text-slate-100"
                                    strokeWidth="10"
                                    cx="50"
                                    cy="50"
                                    r="40"
                                    fill="transparent"
                                ></circle>
                                <circle
                                    className={`${overallPercentage > 100 ? 'text-danger-500' : overallPercentage > 80 ? 'text-amber-500' : 'text-primary-500'} stroke-current transition-all duration-1000 ease-out`}
                                    strokeWidth="10"
                                    strokeLinecap="round"
                                    cx="50"
                                    cy="50"
                                    r="40"
                                    fill="transparent"
                                    strokeDasharray={`${overallPercentage * 2.51327} 251.327`}
                                ></circle>
                            </svg>
                            <div className="absolute inset-0 flex flex-col items-center justify-center">
                                <span className="text-3xl font-bold text-slate-900">
                                    {Math.round(overallPercentage)}%
                                </span>
                                <span className="text-sm font-medium text-slate-500">
                                    Đã dùng
                                </span>
                            </div>
                        </div>

                        <div className="w-full grow space-y-6">
                            <div className="grid grid-cols-2 gap-4">
                                <div className="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                    <p className="mb-1 text-sm font-medium text-slate-500">
                                        Tổng hạn mức
                                    </p>
                                    <p className="text-2xl font-bold text-slate-900">
                                        {formatCurrencyAmount(
                                            totalBudget,
                                            displayCurrency,
                                        )}
                                    </p>
                                </div>
                                <div className="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                    <p className="mb-1 text-sm font-medium text-slate-500">
                                        Tổng đã chi
                                    </p>
                                    <p className="text-2xl font-bold text-slate-900">
                                        {formatCurrencyAmount(
                                            totalSpent,
                                            displayCurrency,
                                        )}
                                    </p>
                                </div>
                            </div>

                            <div className="text-primary-900 flex items-start gap-3 rounded-xl border border-primary-100 bg-primary-50 p-4">
                                <Target className="mt-0.5 h-5 w-5 text-primary-500" />
                                <div>
                                    <p className="text-sm font-semibold">
                                        Bạn đang làm rất tốt!
                                    </p>
                                    <p className="text-primary-700/80 mt-1 text-sm">
                                        Bạn vẫn còn{' '}
                                        <strong>
                                            {formatCurrencyAmount(
                                                totalBudget - totalSpent,
                                                displayCurrency,
                                            )}
                                        </strong>{' '}
                                        cho phần còn lại của tháng.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                    {budgets.map((budget) => {
                        const category = categories.find(
                            (c) => c.id === budget.categoryId,
                        );
                        const percentage = Math.min(
                            (budget.spent / budget.limit) * 100,
                            100,
                        );
                        const isWarning = percentage >= 80 && percentage < 100;
                        const isDanger = percentage >= 100;

                        return (
                            <div
                                key={budget.id}
                                className="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm transition-shadow hover:shadow-md"
                            >
                                <div className="mb-4 flex items-start justify-between">
                                    <div className="flex items-center gap-3">
                                        <div
                                            className="flex h-10 w-10 items-center justify-center rounded-xl"
                                            style={{
                                                backgroundColor: `${category?.color}15`,
                                                color: category?.color,
                                            }}
                                        >
                                            <div
                                                className="h-3 w-3 rounded-full"
                                                style={{
                                                    backgroundColor:
                                                        category?.color,
                                                }}
                                            ></div>
                                        </div>
                                        <div>
                                            <h3 className="font-bold text-slate-900">
                                                {category?.name}
                                            </h3>
                                            <p className="text-xs font-medium tracking-wider text-slate-500 uppercase">
                                                {budget.period === 'monthly'
                                                    ? 'HÀNG THÁNG'
                                                    : 'HÀNG NĂM'}
                                            </p>
                                        </div>
                                    </div>
                                    <div className="text-right">
                                        <p className="font-bold text-slate-900">
                                            {formatCurrencyAmount(
                                                budget.spent,
                                                displayCurrency,
                                                0,
                                            )}
                                        </p>
                                        <p className="text-xs font-medium text-slate-500">
                                            trên{' '}
                                            {formatCurrencyAmount(
                                                budget.limit,
                                                displayCurrency,
                                                0,
                                            )}
                                        </p>
                                    </div>
                                </div>

                                <div className="space-y-2">
                                    <div className="flex justify-between text-xs font-medium">
                                        <span
                                            className={
                                                isDanger
                                                    ? 'text-danger-600'
                                                    : isWarning
                                                      ? 'text-amber-600'
                                                      : 'text-slate-500'
                                            }
                                        >
                                            {Math.round(percentage)}% đã dùng
                                        </span>
                                        <span className="text-slate-500">
                                            {formatCurrencyAmount(
                                                budget.limit - budget.spent,
                                                displayCurrency,
                                                0,
                                            )}{' '}
                                            còn lại
                                        </span>
                                    </div>
                                    <div className="h-2.5 w-full overflow-hidden rounded-full bg-slate-100">
                                        <div
                                            className={`h-full rounded-full transition-all duration-500 ease-out ${
                                                isDanger
                                                    ? 'bg-danger-500'
                                                    : isWarning
                                                      ? 'bg-amber-500'
                                                      : 'bg-primary-500'
                                            }`}
                                            style={{ width: `${percentage}%` }}
                                        ></div>
                                    </div>
                                </div>

                                {isDanger && (
                                    <div className="mt-4 flex items-center gap-2 rounded-lg bg-danger-50 px-3 py-2 text-xs font-medium text-danger-600">
                                        <AlertCircle className="h-4 w-4" />
                                        Bạn đã vượt quá hạn mức ngân sách này!
                                    </div>
                                )}
                            </div>
                        );
                    })}
                    {budgets.length === 0 ? (
                        <div className="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center text-sm text-slate-500 md:col-span-2">
                            Chưa có ngân sách nào. Hãy tạo ngân sách đầu tiên của bạn.
                        </div>
                    ) : null}
                </div>

                {isModalOpen ? (
                    <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4 backdrop-blur-sm">
                        <div className="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-xl">
                            <div className="flex items-center justify-between border-b border-slate-100 p-5">
                                <h2 className="text-xl font-bold text-slate-900">
                                    Tạo ngân sách
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
                                onSubmit={submitBudget}
                                className="space-y-4 p-5"
                            >
                                <div>
                                    <label className="mb-2 block text-sm font-medium text-slate-700">
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
                                        className="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"
                                    >
                                        {categories.map((category) => (
                                            <option
                                                key={category.id}
                                                value={category.id}
                                            >
                                                {category.name}
                                            </option>
                                        ))}
                                    </select>
                                    {errors.category_id ? (
                                        <p className="mt-1 text-sm text-red-600">
                                            {errors.category_id}
                                        </p>
                                    ) : null}
                                </div>

                                <div>
                                    <label className="mb-2 block text-sm font-medium text-slate-700">
                                        Hạn mức
                                    </label>
                                    <input
                                        type="number"
                                        min="0.01"
                                        step="0.01"
                                        value={formData.amount_limit}
                                        onChange={(event) =>
                                            setData(
                                                'amount_limit',
                                                event.target.value,
                                            )
                                        }
                                        placeholder="0.00"
                                        className="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"
                                    />
                                    {errors.amount_limit ? (
                                        <p className="mt-1 text-sm text-red-600">
                                            {errors.amount_limit}
                                        </p>
                                    ) : null}
                                </div>

                                <div>
                                    <label className="mb-2 block text-sm font-medium text-slate-700">
                                        Chu kỳ
                                    </label>
                                    <select
                                        value={formData.period}
                                        onChange={(event) =>
                                            setData(
                                                'period',
                                                event.target.value as
                                                    | 'monthly'
                                                    | 'yearly',
                                            )
                                        }
                                        className="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"
                                    >
                                        <option value="monthly">Hàng tháng</option>
                                        <option value="yearly">Hàng năm</option>
                                    </select>
                                    {errors.period ? (
                                        <p className="mt-1 text-sm text-red-600">
                                            {errors.period}
                                        </p>
                                    ) : null}
                                </div>

                                <div>
                                    <label className="mb-2 block text-sm font-medium text-slate-700">
                                        Ghi chú
                                    </label>
                                    <textarea
                                        value={formData.note}
                                        onChange={(event) =>
                                            setData('note', event.target.value)
                                        }
                                        rows={3}
                                        placeholder="Ghi chú ngân sách (tùy chọn)"
                                        className="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"
                                    />
                                    {errors.note ? (
                                        <p className="mt-1 text-sm text-red-600">
                                            {errors.note}
                                        </p>
                                    ) : null}
                                </div>

                                <div className="flex gap-3 pt-2">
                                    <button
                                        type="submit"
                                        disabled={processing}
                                        className="bg-primary-600 hover:bg-primary-700 rounded-xl px-4 py-2 text-sm font-medium text-white disabled:opacity-60"
                                    >
                                        Lưu ngân sách
                                    </button>
                                    <button
                                        type="button"
                                        onClick={closeModal}
                                        className="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                                    >
                                        Hủy
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
