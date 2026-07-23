import { RolesListView } from "@/features/admin/access-control/roles-list-view";
import type { Metadata } from "next";

export const metadata: Metadata = { title: "Roles" };

export default function AdminRolesPage() {
  return <RolesListView />;
}
