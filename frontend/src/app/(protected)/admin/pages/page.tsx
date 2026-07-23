import { PagesListView } from "@/features/admin/content/pages-list-view";
import type { Metadata } from "next";

export const metadata: Metadata = { title: "Pages" };
export default function AdminPagesPage() {
    return <PagesListView />;
}
