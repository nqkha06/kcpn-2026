import { CategoriesListView } from "@/features/admin/finance/categories-list-view";
import type { Metadata } from "next";

export const metadata: Metadata = { title: "Categories" };

export default function AdminCategoriesPage() {
    return <CategoriesListView />;
}
