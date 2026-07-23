import { AuthGuard } from "@/components/common/auth-guard";
import { Suspense, type ReactNode } from "react";

export default function ProtectedLayout({ children }: { children: ReactNode }) {
  return (
    <Suspense>
      <AuthGuard>{children}</AuthGuard>
    </Suspense>
  );
}
