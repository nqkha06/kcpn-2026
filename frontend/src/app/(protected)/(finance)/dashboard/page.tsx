import { DashboardView } from "@/features/dashboard/dashboard-view";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Tổng quan",
};

export default function DashboardPage() {
  return <DashboardView />;
}
