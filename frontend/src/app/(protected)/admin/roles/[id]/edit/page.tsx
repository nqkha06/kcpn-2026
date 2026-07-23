import { RoleFormView } from "@/features/admin/access-control/role-form-view";
import type { Metadata } from "next";
import { notFound } from "next/navigation";

export const metadata: Metadata = { title: "Edit Role" };

export default async function EditAdminRolePage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;
  const roleId = Number(id);
  if (!Number.isInteger(roleId) || roleId < 1) notFound();

  return <RoleFormView roleId={roleId} />;
}
