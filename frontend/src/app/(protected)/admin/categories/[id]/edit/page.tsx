import { CategoryFormView } from "@/features/admin/finance/category-form-view";
import type { Metadata } from "next";
import { notFound } from "next/navigation";

export const metadata: Metadata = { title: "Edit Category" };

export default async function EditAdminCategoryPage({
    params,
}: {
    params: Promise<{ id: string }>;
}) {
    const { id } = await params;
    const categoryId = Number(id);

    if (!Number.isInteger(categoryId) || categoryId < 1) {
        notFound();
    }

    return <CategoryFormView categoryId={categoryId} />;
}
