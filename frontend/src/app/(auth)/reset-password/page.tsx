"use client";

import { AuthLayout } from "@/components/layouts/auth-layout";
import { FormError } from "@/components/common/form-error";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { ApiError } from "@/lib/api/errors";
import { authService } from "@/services/auth.service";
import { zodResolver } from "@hookform/resolvers/zod";
import { LoaderCircle } from "lucide-react";
import { useRouter, useSearchParams } from "next/navigation";
import { useForm } from "react-hook-form";
import { z } from "zod";

const schema = z
  .object({
    email: z.email("Email không hợp lệ"),
    password: z.string().min(8, "Mật khẩu phải có ít nhất 8 ký tự"),
    password_confirmation: z.string(),
  })
  .refine((values) => values.password === values.password_confirmation, {
    message: "Mật khẩu xác nhận không khớp",
    path: ["password_confirmation"],
  });
type ResetPasswordForm = z.infer<typeof schema>;

export default function ResetPasswordPage() {
  const searchParams = useSearchParams();
  const router = useRouter();
  const token = searchParams.get("token") ?? "";
  const {
    register,
    handleSubmit,
    setError,
    formState: { errors, isSubmitting },
  } = useForm<ResetPasswordForm>({
    resolver: zodResolver(schema),
    defaultValues: { email: searchParams.get("email") ?? "" },
  });

  const submit = handleSubmit(async (values) => {
    if (!token) {
      setError("root", { message: "Liên kết đặt lại mật khẩu không hợp lệ." });
      return;
    }

    try {
      await authService.resetPassword({ ...values, token });
      router.replace("/login");
    } catch (error) {
      if (error instanceof ApiError) {
        setError("root", { message: error.message });
        for (const field of ["email", "password"] as const) {
          const message = error.firstError(field);
          if (message) setError(field, { message });
        }
        return;
      }
      setError("root", { message: "Không thể kết nối máy chủ. Vui lòng thử lại." });
    }
  });

  return (
    <AuthLayout title="Reset password" description="Please enter your new password below">
      <form onSubmit={submit} noValidate>
        <div className="grid gap-6">
        <div className="grid gap-2">
          <Label htmlFor="email">Email</Label>
          <Input
            id="email"
            type="email"
            autoComplete="email"
            className="mt-1 block w-full"
            readOnly
            {...register("email")}
          />
          <FormError message={errors.email?.message ?? errors.root?.message} className="mt-2" />
        </div>
        <div className="grid gap-2">
          <Label htmlFor="password">Password</Label>
          <Input
            id="password"
            type="password"
            autoComplete="new-password"
            className="mt-1 block w-full"
            autoFocus
            placeholder="Password"
            {...register("password")}
          />
          <FormError message={errors.password?.message} />
        </div>
        <div className="grid gap-2">
          <Label htmlFor="password_confirmation">Confirm password</Label>
          <Input
            id="password_confirmation"
            type="password"
            autoComplete="new-password"
            className="mt-1 block w-full"
            placeholder="Confirm password"
            {...register("password_confirmation")}
          />
          <FormError message={errors.password_confirmation?.message} className="mt-2" />
        </div>
        <Button type="submit" className="mt-4 w-full" disabled={isSubmitting || !token}>
          {isSubmitting && <LoaderCircle className="size-4 animate-spin" />}
          Reset password
        </Button>
        </div>
      </form>
    </AuthLayout>
  );
}
