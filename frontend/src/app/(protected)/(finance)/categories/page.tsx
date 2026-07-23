import { CategoriesView } from "@/features/categories/categories-view";
import type { Metadata } from "next";

export const metadata: Metadata = { title: "Danh mục riêng" };

export default function CategoriesPage() {
  return <CategoriesView />;
}
