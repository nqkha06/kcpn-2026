import { PermissionFormView } from "@/features/admin/access-control/permission-form-view";
import type { Metadata } from "next";
import { notFound } from "next/navigation";

export const metadata: Metadata = { title: "Edit Permission" };

export default async function EditAdminPermissionPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;
  const permissionId = Number(id);
  if (!Number.isInteger(permissionId) || permissionId < 1) notFound();

  return <PermissionFormView permissionId={permissionId} />;
}
