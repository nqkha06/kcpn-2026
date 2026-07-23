import { UserFormView } from "@/features/admin/access-control/user-form-view";
import type { Metadata } from "next";

export const metadata: Metadata = { title: "Create User" };

export default function CreateAdminUserPage() {
  return <UserFormView />;
}
