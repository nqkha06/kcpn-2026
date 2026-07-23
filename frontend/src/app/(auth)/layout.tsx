import { AuthGuard } from "@/components/common/auth-guard";
import { Suspense, type ReactNode } from "react";

export default function GuestLayout({ children }: { children: ReactNode }) {
  return (
    <Suspense>
      <AuthGuard guestOnly>{children}</AuthGuard>
    </Suspense>
  );
}
