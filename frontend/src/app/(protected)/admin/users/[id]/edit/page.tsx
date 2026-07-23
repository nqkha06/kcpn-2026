import { UserFormView } from "@/features/admin/access-control/user-form-view";
import type { Metadata } from "next";
import { notFound } from "next/navigation";

export const metadata: Metadata = { title: "Edit User" };

export default async function EditAdminUserPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;
  const userId = Number(id);
  if (!Number.isInteger(userId) || userId < 1) notFound();

  return <UserFormView userId={userId} />;
}
