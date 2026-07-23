import { PageFormView } from "@/features/admin/content/page-form-view";
import type { Metadata } from "next";
import { notFound } from "next/navigation";

export const metadata: Metadata = { title: "Edit Page" };
export default async function EditAdminPagePage({
    params,
}: {
    params: Promise<{ id: string }>;
}) {
    const { id } = await params;
    const pageId = Number(id);
    if (!Number.isInteger(pageId) || pageId < 1) notFound();
    return <PageFormView pageId={pageId} />;
}
