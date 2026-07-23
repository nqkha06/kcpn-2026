import { UsersListView } from "@/features/admin/access-control/users-list-view";
import type { Metadata } from "next";

export const metadata: Metadata = { title: "Users" };

export default function AdminUsersPage() {
  return <UsersListView />;
}
