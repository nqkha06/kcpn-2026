"use client";

import { AuthLayout } from "@/components/layouts/auth-layout";
import { FormError } from "@/components/common/form-error";
import { TextLink } from "@/components/common/text-link";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { ApiError } from "@/lib/api/errors";
import { authService } from "@/services/auth.service";
import { zodResolver } from "@hookform/resolvers/zod";
import { LoaderCircle } from "lucide-react";
import { useState } from "react";
import { useForm } from "react-hook-form";
import { z } from "zod";

const schema = z.object({ email: z.email("Email không hợp lệ") });
type ForgotPasswordForm = z.infer<typeof schema>;

export default function ForgotPasswordPage() {
  const [message, setMessage] = useState<string | null>(null);
  const {
    register,
    handleSubmit,
    setError,
    formState: { errors, isSubmitting },
  } = useForm<ForgotPasswordForm>({ resolver: zodResolver(schema) });

  const submit = handleSubmit(async ({ email }) => {
    setMessage(null);
    try {
      setMessage(await authService.forgotPassword(email));
    } catch (error) {
      if (error instanceof ApiError) {
        setError("email", { message: error.firstError("email") ?? error.message });
        return;
      }
      setError("root", { message: "Không thể kết nối máy chủ. Vui lòng thử lại." });
    }
  });

  return (
    <AuthLayout title="Forgot password" description="Enter your email to receive a password reset link">
      {message ? (
        <div className="mb-4 text-center text-sm font-medium text-green-600">{message}</div>
      ) : null}

      <div className="space-y-6">
        <form onSubmit={submit} noValidate>
          <div className="grid gap-2">
            <Label htmlFor="email">Email address</Label>
            <Input
              id="email"
              type="email"
              autoComplete="off"
              autoFocus
              placeholder="email@example.com"
              {...register("email")}
            />
            <FormError message={errors.email?.message ?? errors.root?.message} />
          </div>

          <div className="my-6 flex items-center justify-start">
            <Button className="w-full" type="submit" disabled={isSubmitting}>
              {isSubmitting && <LoaderCircle className="size-4 animate-spin" />}
              Email password reset link
            </Button>
          </div>
        </form>

        <div className="space-x-1 text-center text-sm text-muted-foreground">
          <span>Or, return to</span>
          <TextLink href="/login">log in</TextLink>
        </div>
      </div>
    </AuthLayout>
  );
}
