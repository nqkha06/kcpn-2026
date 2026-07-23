import { RoleFormView } from "@/features/admin/access-control/role-form-view";
import type { Metadata } from "next";

export const metadata: Metadata = { title: "Create Role" };

export default function CreateAdminRolePage() {
  return <RoleFormView />;
}
