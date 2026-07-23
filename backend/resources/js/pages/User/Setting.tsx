import { useForm } from '@inertiajs/react';
import { User, Bell, Shield, Palette, Globe, ChevronRight } from 'lucide-react';
import type { FormEventHandler } from 'react';
import TrackerLayout from '@/components/expense-tracker/layout';
import type {
    TrackerNavigationItem,
    TrackerProfile,
} from '@/types/expense-tracker';

interface CurrencyOption {
    code: string;
    label: string;
}

interface SettingProps {
    navigation: TrackerNavigationItem[];
    profile?: TrackerProfile;
    userProfile?: {
        name?: string;
        email?: string;
    };
    preferences?: {
        currency?: string;
    };
    currencyOptions?: CurrencyOption[];
}

export default function Setting({
    navigation,
    profile,
    userProfile,
    preferences,
    currencyOptions,
}: SettingProps) {
    const options = currencyOptions ?? [];

    const initialCurrency =
        preferences?.currency ?? options[0]?.code ?? '';

    const profileForm = useForm({
        name: userProfile?.name ?? '',
        email: userProfile?.email ?? '',
    });

    const preferenceForm = useForm({
        currency: initialCurrency,
    });

    const submitProfile: FormEventHandler<HTMLFormElement> = (event) => {
        event.preventDefault();

        profileForm.patch('/user/settings/profile', {
            preserveScroll: true,
        });
    };

    const submitPreference: FormEventHandler<HTMLFormElement> = (event) => {
        event.preventDefault();

        preferenceForm.patch('/user/settings/preferences', {
            preserveScroll: true,
        });
    };

    return (
        <TrackerLayout
            title="Cài đặt"
            heading="Cài đặt"
            description="Quản lý hồ sơ và tùy chọn ứng dụng của bạn."
            activePath="/user/settings"
            navigation={navigation}
            profile={profile}
        >
            <div className="mx-auto w-full max-w-4xl space-y-6">
                <div className="grid grid-cols-1 gap-6 md:grid-cols-4">
                    {/* Navigation Sidebar */}
                    <div className="space-y-1 md:col-span-1">
                        <button className="text-primary-700 flex w-full items-center justify-between rounded-xl bg-primary-50 px-4 py-2.5 text-sm font-medium transition-colors">
                            <div className="flex items-center gap-3">
                                <User className="h-4 w-4" />
                                Hồ sơ
                            </div>
                            <ChevronRight className="h-4 w-4 opacity-50" />
                        </button>
                        <button className="flex w-full items-center justify-between rounded-xl px-4 py-2.5 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-50 hover:text-slate-900">
                            <div className="flex items-center gap-3">
                                <Bell className="h-4 w-4" />
                                Thông báo
                            </div>
                        </button>
                        <button className="flex w-full items-center justify-between rounded-xl px-4 py-2.5 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-50 hover:text-slate-900">
                            <div className="flex items-center gap-3">
                                <Shield className="h-4 w-4" />
                                Bảo mật
                            </div>
                        </button>
                        <button className="flex w-full items-center justify-between rounded-xl px-4 py-2.5 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-50 hover:text-slate-900">
                            <div className="flex items-center gap-3">
                                <Palette className="h-4 w-4" />
                                Giao diện
                            </div>
                        </button>
                        <button className="flex w-full items-center justify-between rounded-xl px-4 py-2.5 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-50 hover:text-slate-900">
                            <div className="flex items-center gap-3">
                                <Globe className="h-4 w-4" />
                                Ngôn ngữ
                            </div>
                        </button>
                    </div>

                    {/* Content Area */}
                    <div className="space-y-6 md:col-span-3">
                        <div className="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                            <h2 className="mb-6 text-lg font-bold text-slate-900">
                                Thông tin hồ sơ
                            </h2>

                            <div className="mb-8 flex items-center gap-6">
                                <div className="flex h-20 w-20 items-center justify-center overflow-hidden rounded-full border-4 border-white bg-linear-to-tr from-primary-500 to-indigo-500 text-2xl font-bold text-white shadow-md">
                                    JD
                                </div>
                                <div>
                                    <button className="mb-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50">
                                        Đổi ảnh đại diện
                                    </button>
                                    <p className="text-xs text-slate-500">
                                        JPG, GIF hoặc PNG. Dung lượng tối đa
                                        800K
                                    </p>
                                </div>
                            </div>

                            <form
                                className="grid grid-cols-1 gap-6 sm:grid-cols-2"
                                onSubmit={submitProfile}
                            >
                                <div>
                                    <label className="mb-1 block text-sm font-medium text-slate-700">
                                        Họ và tên
                                    </label>
                                    <input
                                        type="text"
                                        value={profileForm.data.name}
                                        onChange={(event) => {
                                            profileForm.setData(
                                                'name',
                                                event.target.value,
                                            );
                                        }}
                                        className="block w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-slate-900 shadow-sm transition-colors focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                                    />
                                    {profileForm.errors.name ? (
                                        <p className="mt-1 text-sm text-rose-600">
                                            {profileForm.errors.name}
                                        </p>
                                    ) : null}
                                </div>
                                <div className="sm:col-span-1">
                                    <label className="mb-1 block text-sm font-medium text-slate-700">
                                        Địa chỉ email
                                    </label>
                                    <input
                                        type="email"
                                        value={profileForm.data.email}
                                        onChange={(event) => {
                                            profileForm.setData(
                                                'email',
                                                event.target.value,
                                            );
                                        }}
                                        className="block w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-slate-900 shadow-sm transition-colors focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                                    />
                                    {profileForm.errors.email ? (
                                        <p className="mt-1 text-sm text-rose-600">
                                            {profileForm.errors.email}
                                        </p>
                                    ) : null}
                                </div>
                                <div className="sm:col-span-2 flex justify-end gap-3 border-t border-slate-100 pt-6">
                                    <button
                                        type="button"
                                        onClick={() => {
                                            profileForm.reset();
                                        }}
                                        className="rounded-xl border border-slate-200 bg-white px-4 py-2 font-medium text-slate-700 transition-colors hover:bg-slate-50"
                                    >
                                        Hủy
                                    </button>
                                    <button
                                        type="submit"
                                        disabled={profileForm.processing}
                                        className="hover:bg-primary-700 rounded-xl bg-primary-600 px-6 py-2 font-medium text-white shadow-sm shadow-primary-500/30 transition-colors disabled:cursor-not-allowed disabled:opacity-70"
                                    >
                                        Cập nhật hồ sơ
                                    </button>
                                </div>
                            </form>
                        </div>

                        <form
                            className="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm"
                            onSubmit={submitPreference}
                        >
                            <h2 className="mb-6 text-lg font-bold text-slate-900">
                                Tùy chọn tài chính
                            </h2>

                            <div className="grid grid-cols-1 gap-6">
                                <div className="sm:col-span-2">
                                    <label className="mb-1 block text-sm font-medium text-slate-700">
                                        Tiền tệ
                                    </label>
                                    <select
                                        value={preferenceForm.data.currency}
                                        onChange={(event) => {
                                            preferenceForm.setData(
                                                'currency',
                                                event.target.value,
                                            );
                                        }}
                                        className="block w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-slate-900 shadow-sm transition-colors focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                                    >
                                        {options.length === 0 ? (
                                            <option value="" disabled>
                                                Chưa có lựa chọn tiền tệ
                                            </option>
                                        ) : null}
                                        {options.map((option) => (
                                            <option
                                                key={option.code}
                                                value={option.code}
                                            >
                                                {option.label}
                                            </option>
                                        ))}
                                    </select>
                                    {preferenceForm.errors.currency ? (
                                        <p className="mt-1 text-sm text-rose-600">
                                            {preferenceForm.errors.currency}
                                        </p>
                                    ) : null}
                                </div>
                            </div>

                            <div className="mt-8 flex justify-end gap-3 border-t border-slate-100 pt-6">
                                <button
                                    type="button"
                                    onClick={() => {
                                        preferenceForm.reset();
                                    }}
                                    className="rounded-xl border border-slate-200 bg-white px-4 py-2 font-medium text-slate-700 transition-colors hover:bg-slate-50"
                                >
                                    Hủy
                                </button>
                                <button
                                    type="submit"
                                    disabled={preferenceForm.processing}
                                    className="hover:bg-primary-700 rounded-xl bg-primary-600 px-6 py-2 font-medium text-white shadow-sm shadow-primary-500/30 transition-colors disabled:cursor-not-allowed disabled:opacity-70"
                                >
                                    Lưu tiền tệ
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </TrackerLayout>
    );
}
