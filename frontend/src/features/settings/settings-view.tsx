"use client";

import { ErrorState } from "@/components/common/error-state";
import { LoadingState } from "@/components/common/loading-state";
import { PageHeader } from "@/components/layouts/page-header";
import { ApiError } from "@/lib/api/errors";
import { useAuth } from "@/lib/auth/auth-context";
import { settingsQueryKey, settingsService } from "@/services/settings.service";
import type { UserSettingsData } from "@/types/finance";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { Bell, ChevronRight, Globe, Palette, Shield, User } from "lucide-react";
import { useForm } from "react-hook-form";
import { toast } from "sonner";

interface ProfileForm {
  name: string;
  email: string;
}

interface PreferenceForm {
  currency: string;
}

export function SettingsView() {
  const settingsQuery = useQuery({ queryKey: settingsQueryKey, queryFn: settingsService.show });

  if (settingsQuery.isLoading) {
    return <LoadingState label="Đang tải cài đặt..." />;
  }

  if (settingsQuery.isError || !settingsQuery.data) {
    return <ErrorState onRetry={() => void settingsQuery.refetch()} />;
  }

  return <SettingsContent settings={settingsQuery.data} />;
}

function SettingsContent({ settings }: { settings: UserSettingsData }) {
  const queryClient = useQueryClient();
  const { refresh } = useAuth();
  const profileForm = useForm<ProfileForm>({ defaultValues: settings.profile });
  const preferenceForm = useForm<PreferenceForm>({
    defaultValues: { currency: settings.preferences.currency },
  });
  const profileMutation = useMutation({
    mutationFn: settingsService.updateProfile,
    onSuccess: async () => {
      await Promise.all([
        queryClient.invalidateQueries({ queryKey: settingsQueryKey }),
        refresh(),
      ]);
      toast.success("Đã cập nhật thông tin hồ sơ.");
    },
    onError: (error) => applyApiErrors(error, profileForm.setError),
  });
  const preferenceMutation = useMutation({
    mutationFn: settingsService.updatePreferences,
    onSuccess: async () => {
      await queryClient.invalidateQueries({ queryKey: settingsQueryKey });
      toast.success("Đã cập nhật đơn vị tiền tệ.");
    },
    onError: (error) => {
      if (error instanceof ApiError) {
        preferenceForm.setError("currency", {
          message: error.firstError("currency") ?? error.message,
        });
        return;
      }

      preferenceForm.setError("currency", { message: "Không thể lưu đơn vị tiền tệ." });
    },
  });

  return (
    <>
      <PageHeader title="Cài đặt" description="Quản lý hồ sơ và tùy chọn ứng dụng của bạn." />

      <div className="mx-auto w-full max-w-4xl space-y-6">
        <div className="grid grid-cols-1 gap-6 md:grid-cols-4">
          <div className="space-y-1 md:col-span-1">
            <button className="flex w-full items-center justify-between rounded-xl bg-primary-50 px-4 py-2.5 text-sm font-medium text-primary-700 transition-colors">
              <div className="flex items-center gap-3"><User className="size-4" />Hồ sơ</div>
              <ChevronRight className="size-4 opacity-50" />
            </button>
            <SettingsNavigation icon={<Bell className="size-4" />} label="Thông báo" />
            <SettingsNavigation icon={<Shield className="size-4" />} label="Bảo mật" />
            <SettingsNavigation icon={<Palette className="size-4" />} label="Giao diện" />
            <SettingsNavigation icon={<Globe className="size-4" />} label="Ngôn ngữ" />
          </div>

          <div className="space-y-6 md:col-span-3">
            <div className="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
              <h2 className="mb-6 text-lg font-bold text-slate-900">Thông tin hồ sơ</h2>
              <div className="mb-8 flex items-center gap-6">
                <div className="flex h-20 w-20 items-center justify-center overflow-hidden rounded-full border-4 border-white bg-linear-to-tr from-primary-500 to-indigo-500 text-2xl font-bold text-white shadow-md">
                  JD
                </div>
                <div>
                  <button className="mb-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50">
                    Đổi ảnh đại diện
                  </button>
                  <p className="text-xs text-slate-500">JPG, GIF hoặc PNG. Dung lượng tối đa 800K</p>
                </div>
              </div>

              <form
                className="grid grid-cols-1 gap-6 sm:grid-cols-2"
                onSubmit={profileForm.handleSubmit((values) => profileMutation.mutate(values))}
              >
                <SettingsField label="Họ và tên" error={profileForm.formState.errors.name?.message}>
                  <input
                    type="text"
                    {...profileForm.register("name", { required: "Vui lòng nhập họ và tên." })}
                    className="block w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-slate-900 shadow-sm transition-colors focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                  />
                </SettingsField>
                <SettingsField label="Địa chỉ email" error={profileForm.formState.errors.email?.message}>
                  <input
                    type="email"
                    {...profileForm.register("email", { required: "Vui lòng nhập email." })}
                    className="block w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-slate-900 shadow-sm transition-colors focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                  />
                </SettingsField>
                <div className="flex justify-end gap-3 border-t border-slate-100 pt-6 sm:col-span-2">
                  <button
                    type="button"
                    onClick={() => profileForm.reset()}
                    className="rounded-xl border border-slate-200 bg-white px-4 py-2 font-medium text-slate-700 transition-colors hover:bg-slate-50"
                  >
                    Hủy
                  </button>
                  <button
                    type="submit"
                    disabled={profileMutation.isPending}
                    className="rounded-xl bg-primary-600 px-6 py-2 font-medium text-white shadow-sm shadow-primary-500/30 transition-colors hover:bg-primary-700 disabled:cursor-not-allowed disabled:opacity-70"
                  >
                    Cập nhật hồ sơ
                  </button>
                </div>
              </form>
            </div>

            <form
              className="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm"
              onSubmit={preferenceForm.handleSubmit((values) => preferenceMutation.mutate(values))}
            >
              <h2 className="mb-6 text-lg font-bold text-slate-900">Tùy chọn tài chính</h2>
              <div className="grid grid-cols-1 gap-6">
                <SettingsField label="Tiền tệ" error={preferenceForm.formState.errors.currency?.message}>
                  <select
                    {...preferenceForm.register("currency", { required: "Vui lòng chọn đơn vị tiền tệ." })}
                    className="block w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-slate-900 shadow-sm transition-colors focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                  >
                    {settings.currency_options.length === 0 ? (
                      <option value="" disabled>Chưa có lựa chọn tiền tệ</option>
                    ) : null}
                    {settings.currency_options.map((option) => (
                      <option key={option.code} value={option.code}>{option.label}</option>
                    ))}
                  </select>
                </SettingsField>
              </div>
              <div className="mt-8 flex justify-end gap-3 border-t border-slate-100 pt-6">
                <button
                  type="button"
                  onClick={() => preferenceForm.reset()}
                  className="rounded-xl border border-slate-200 bg-white px-4 py-2 font-medium text-slate-700 transition-colors hover:bg-slate-50"
                >
                  Hủy
                </button>
                <button
                  type="submit"
                  disabled={preferenceMutation.isPending}
                  className="rounded-xl bg-primary-600 px-6 py-2 font-medium text-white shadow-sm shadow-primary-500/30 transition-colors hover:bg-primary-700 disabled:cursor-not-allowed disabled:opacity-70"
                >
                  Lưu tiền tệ
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </>
  );
}

function SettingsNavigation({ icon, label }: { icon: React.ReactNode; label: string }) {
  return (
    <button className="flex w-full items-center justify-between rounded-xl px-4 py-2.5 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-50 hover:text-slate-900">
      <div className="flex items-center gap-3">{icon}{label}</div>
    </button>
  );
}

function SettingsField({
  label,
  error,
  children,
}: {
  label: string;
  error?: string;
  children: React.ReactNode;
}) {
  return (
    <div className="sm:col-span-1">
      <label className="mb-1 block text-sm font-medium text-slate-700">{label}</label>
      {children}
      {error ? <p className="mt-1 text-sm text-rose-600">{error}</p> : null}
    </div>
  );
}

function applyApiErrors(
  error: Error,
  setError: ReturnType<typeof useForm<ProfileForm>>["setError"],
): void {
  if (!(error instanceof ApiError)) {
    setError("name", { message: "Không thể cập nhật hồ sơ." });
    return;
  }

  const nameError = error.firstError("name");
  const emailError = error.firstError("email");
  if (nameError) setError("name", { message: nameError });
  if (emailError) setError("email", { message: emailError });
  if (!nameError && !emailError) setError("name", { message: error.message });
}
