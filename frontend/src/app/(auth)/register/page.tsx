"use client";

import { AuthLayout } from "@/components/layouts/auth-layout";
import { FormError } from "@/components/common/form-error";
import { TextLink } from "@/components/common/text-link";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { ApiError } from "@/lib/api/errors";
import { useAuth } from "@/lib/auth/auth-context";
import { zodResolver } from "@hookform/resolvers/zod";
import { LoaderCircle } from "lucide-react";
import { useRouter } from "next/navigation";
import { useForm } from "react-hook-form";
import { z } from "zod";

const registerSchema = z
  .object({
    name: z.string().trim().min(1, "Vui lòng nhập họ tên").max(255),
    email: z.email("Email không hợp lệ"),
    password: z.string().min(8, "Mật khẩu phải có ít nhất 8 ký tự"),
    password_confirmation: z.string(),
  })
  .refine((values) => values.password === values.password_confirmation, {
    message: "Mật khẩu xác nhận không khớp",
    path: ["password_confirmation"],
  });

type RegisterForm = z.infer<typeof registerSchema>;

export default function RegisterPage() {
  const { register: registerAccount } = useAuth();
  const router = useRouter();
  const {
    register,
    handleSubmit,
    setError,
    formState: { errors, isSubmitting },
  } = useForm<RegisterForm>({ resolver: zodResolver(registerSchema) });

  const submit = handleSubmit(async (values) => {
    try {
      await registerAccount(values);
      router.replace("/dashboard");
    } catch (error) {
      if (error instanceof ApiError) {
        setError("root", { message: error.message });
        for (const field of ["name", "email", "password"] as const) {
          const message = error.firstError(field);
          if (message) setError(field, { message });
        }
        return;
      }

      setError("root", { message: "Không thể kết nối máy chủ. Vui lòng thử lại." });
    }
  });

  return (
    <AuthLayout title="Create an account" description="Enter your details below to create your account">
      <form className="flex flex-col gap-6" onSubmit={submit} noValidate>
        <div className="grid gap-6">
          <div className="grid gap-2">
            <Label htmlFor="name">Name</Label>
            <Input
              id="name"
              type="text"
              required
              autoFocus
              tabIndex={1}
              autoComplete="name"
              placeholder="Full name"
              {...register("name")}
            />
            <FormError message={errors.name?.message ?? errors.root?.message} className="mt-2" />
          </div>

          <div className="grid gap-2">
            <Label htmlFor="email">Email address</Label>
            <Input
              id="email"
              type="email"
              required
              tabIndex={2}
              autoComplete="email"
              placeholder="email@example.com"
              {...register("email")}
            />
            <FormError message={errors.email?.message} />
          </div>

          <div className="grid gap-2">
            <Label htmlFor="password">Password</Label>
            <Input
              id="password"
              type="password"
              required
              tabIndex={3}
              autoComplete="new-password"
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
              required
              tabIndex={4}
              autoComplete="new-password"
              placeholder="Confirm password"
              {...register("password_confirmation")}
            />
            <FormError message={errors.password_confirmation?.message} />
          </div>

          <Button type="submit" className="mt-2 w-full" tabIndex={5} disabled={isSubmitting}>
            {isSubmitting && <LoaderCircle className="size-4 animate-spin" />}
            Create account
          </Button>
        </div>

        <div className="text-center text-sm text-muted-foreground">
          Already have an account?{" "}
          <TextLink href="/login" tabIndex={6}>Log in</TextLink>
        </div>
      </form>
    </AuthLayout>
  );
}
