import { FinanceLayout } from "@/components/layouts/finance-layout";
import type { ReactNode } from "react";

export default function UserFinanceLayout({ children }: { children: ReactNode }) {
  return <FinanceLayout>{children}</FinanceLayout>;
}
