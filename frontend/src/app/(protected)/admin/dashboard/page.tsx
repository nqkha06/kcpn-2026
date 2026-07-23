import { AdminDashboardView } from "@/features/admin/dashboard/admin-dashboard-view";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Admin Dashboard",
};

export default function AdminDashboardPage() {
  return <AdminDashboardView />;
}
