"use client";

import { AuthLayout } from "@/components/layouts/auth-layout";
import { FormError } from "@/components/common/form-error";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { ApiError } from "@/lib/api/errors";
import { useAuth } from "@/lib/auth/auth-context";
import { authService } from "@/services/auth.service";
import { zodResolver } from "@hookform/resolvers/zod";
import { LoaderCircle } from "lucide-react";
import { useRouter } from "next/navigation";
import { useState } from "react";
import { useForm, useWatch } from "react-hook-form";
import { z } from "zod";

const schema = z.object({ value: z.string().trim().min(1, "Vui lòng nhập mã xác thực") });
type ChallengeForm = z.infer<typeof schema>;

export default function TwoFactorChallengePage() {
  const [usesRecoveryCode, setUsesRecoveryCode] = useState(false);
  const { refresh } = useAuth();
  const router = useRouter();
  const {
    register,
    handleSubmit,
    reset,
    setError,
    setValue,
    control,
    formState: { errors, isSubmitting },
  } = useForm<ChallengeForm>({ resolver: zodResolver(schema) });
  const code = useWatch({ control, name: "value" }) ?? "";
  const title = usesRecoveryCode ? "Recovery code" : "Authentication code";
  const description = usesRecoveryCode
    ? "Please confirm access to your account by entering one of your emergency recovery codes."
    : "Enter the authentication code provided by your authenticator application.";

  const submit = handleSubmit(async ({ value }) => {
    try {
      await authService.twoFactorChallenge(
        usesRecoveryCode ? { recovery_code: value } : { code: value },
      );
      await refresh();
      router.replace("/dashboard");
    } catch (error) {
      if (error instanceof ApiError) {
        const field = usesRecoveryCode ? "recovery_code" : "code";
        setError("value", { message: error.firstError(field) ?? error.message });
        return;
      }
      setError("root", { message: "Không thể kết nối máy chủ. Vui lòng thử lại." });
    }
  });

  return (
    <AuthLayout title={title} description={description}>
      <div className="space-y-6">
        <form className="space-y-4" onSubmit={submit} noValidate>
          {usesRecoveryCode ? (
            <>
              <Input
                type="text"
                placeholder="Enter recovery code"
                autoFocus
                required
                {...register("value")}
              />
              <FormError message={errors.value?.message ?? errors.root?.message} />
            </>
          ) : (
            <div className="flex flex-col items-center justify-center space-y-3 text-center">
              <div className="flex w-full items-center justify-center">
                <label className="relative flex items-center">
                  <input
                    type="text"
                    inputMode="numeric"
                    autoComplete="one-time-code"
                    autoFocus
                    value={code}
                    onChange={(event) => {
                      setValue("value", event.target.value.replace(/\D/g, "").slice(0, 6), {
                        shouldValidate: true,
                      });
                    }}
                    className="absolute inset-0 z-20 cursor-text opacity-0"
                    aria-label="Authentication code"
                  />
                  {Array.from({ length: 6 }, (_, index) => (
                    <span
                      key={index}
                      className="relative flex h-9 w-9 items-center justify-center border-y border-r border-input text-sm shadow-sm transition-all first:rounded-l-md first:border-l last:rounded-r-md"
                    >
                      {code[index] ?? ""}
                    </span>
                  ))}
                </label>
              </div>
              <FormError message={errors.value?.message ?? errors.root?.message} />
            </div>
          )}

          <Button type="submit" className="w-full" disabled={isSubmitting}>
            {isSubmitting && <LoaderCircle className="size-4 animate-spin" />}
            Continue
          </Button>

          <div className="text-center text-sm text-muted-foreground">
            <span>or you can </span>
            <button
              type="button"
              className="cursor-pointer text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current dark:decoration-neutral-500"
              onClick={() => {
                setUsesRecoveryCode((value) => !value);
                reset();
              }}
            >
              {usesRecoveryCode
                ? "login using an authentication code"
                : "login using a recovery code"}
            </button>
          </div>
        </form>
      </div>
    </AuthLayout>
  );
}
