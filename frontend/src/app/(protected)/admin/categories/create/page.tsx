import { CategoryFormView } from "@/features/admin/finance/category-form-view";
import type { Metadata } from "next";

export const metadata: Metadata = { title: "Create Category" };

export default function CreateAdminCategoryPage() {
    return <CategoryFormView />;
}
