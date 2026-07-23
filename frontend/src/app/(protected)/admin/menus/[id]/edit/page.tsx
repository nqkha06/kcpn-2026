import { MenuFormView } from "@/features/admin/content/menu-form-view";
import type { Metadata } from "next";
import { notFound } from "next/navigation";

export const metadata: Metadata = { title: "Edit Menu" };
export default async function EditAdminMenuPage({
    params,
}: {
    params: Promise<{ id: string }>;
}) {
    const { id } = await params;
    const menuId = Number(id);
    if (!Number.isInteger(menuId) || menuId < 1) notFound();
    return <MenuFormView menuId={menuId} />;
}
