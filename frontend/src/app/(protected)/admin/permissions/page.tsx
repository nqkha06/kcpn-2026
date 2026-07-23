import { PermissionsListView } from "@/features/admin/access-control/permissions-list-view";
import type { Metadata } from "next";

export const metadata: Metadata = { title: "Permissions" };

export default function AdminPermissionsPage() {
  return <PermissionsListView />;
}
