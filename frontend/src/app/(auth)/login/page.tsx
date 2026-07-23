"use client";

import { FormError } from "@/components/common/form-error";
import { TextLink } from "@/components/common/text-link";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { AuthLayout } from "@/components/layouts/auth-layout";
import { ApiError } from "@/lib/api/errors";
import { useAuth } from "@/lib/auth/auth-context";
import { zodResolver } from "@hookform/resolvers/zod";
import { LoaderCircle } from "lucide-react";
import { useRouter, useSearchParams } from "next/navigation";
import { useForm } from "react-hook-form";
import { z } from "zod";

const loginSchema = z.object({
  email: z.email("Email không hợp lệ"),
  password: z.string().min(1, "Vui lòng nhập mật khẩu"),
  remember: z.boolean(),
});

type LoginForm = z.infer<typeof loginSchema>;

export default function LoginPage() {
  const { login } = useAuth();
  const router = useRouter();
  const searchParams = useSearchParams();
  const {
    register,
    handleSubmit,
    setError,
    formState: { errors, isSubmitting },
  } = useForm<LoginForm>({
    resolver: zodResolver(loginSchema),
    defaultValues: { email: "", password: "", remember: false },
  });

  const submit = handleSubmit(async (values) => {
    try {
      const result = await login(values);

      if (result.requires_two_factor) {
        router.push("/two-factor-challenge");
        return;
      }

      const requestedPath = searchParams.get("next");
      const destination = requestedPath?.startsWith("/") && !requestedPath.startsWith("//")
        ? requestedPath
        : "/dashboard";
      router.replace(destination);
    } catch (error) {
      if (error instanceof ApiError) {
        setError("root", { message: error.message });
        const emailError = error.firstError("email");
        if (emailError) setError("email", { message: emailError });
        return;
      }

      setError("root", { message: "Không thể kết nối máy chủ. Vui lòng thử lại." });
    }
  });

  return (
    <AuthLayout title="Log in to your account" description="Enter your email and password below to log in">
      <form className="flex flex-col gap-6" onSubmit={submit} noValidate>
        <div className="grid gap-6">
          <div className="grid gap-2">
            <Label htmlFor="email">Email address</Label>
            <Input
              id="email"
              type="email"
              required
              autoFocus
              tabIndex={1}
              autoComplete="email"
              placeholder="email@example.com"
              {...register("email")}
            />
            <FormError message={errors.email?.message ?? errors.root?.message} />
          </div>

          <div className="grid gap-2">
            <div className="flex items-center">
              <Label htmlFor="password">Password</Label>
              <TextLink href="/forgot-password" className="ml-auto text-sm" tabIndex={5}>
                Forgot password?
              </TextLink>
            </div>
            <Input
              id="password"
              type="password"
              required
              tabIndex={2}
              autoComplete="current-password"
              placeholder="Password"
              {...register("password")}
            />
            <FormError message={errors.password?.message} />
          </div>

          <div className="flex items-center space-x-3">
            <input
              id="remember"
              type="checkbox"
              tabIndex={3}
              className="size-4 rounded border-input"
              {...register("remember")}
            />
            <Label htmlFor="remember">Remember me</Label>
          </div>

          <Button type="submit" className="mt-4 w-full" tabIndex={4} disabled={isSubmitting}>
            {isSubmitting && <LoaderCircle className="size-4 animate-spin" />}
            Log in
          </Button>
        </div>

        <div className="text-center text-sm text-muted-foreground">
          Don&apos;t have an account?{" "}
          <TextLink href="/register" tabIndex={5}>Sign up</TextLink>
        </div>
      </form>
    </AuthLayout>
  );
}
