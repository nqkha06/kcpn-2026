import { AuthGuard } from "@/components/common/auth-guard";
import { AdminLayout } from "@/components/layouts/admin-layout";
import type { ReactNode } from "react";

export default function AdminRouteLayout({ children }: { children: ReactNode }) {
  return (
    <AuthGuard role="admin">
      <AdminLayout>{children}</AdminLayout>
    </AuthGuard>
  );
}
