import { PermissionFormView } from "@/features/admin/access-control/permission-form-view";
import type { Metadata } from "next";

export const metadata: Metadata = { title: "Create Permission" };

export default function CreateAdminPermissionPage() {
  return <PermissionFormView />;
}
