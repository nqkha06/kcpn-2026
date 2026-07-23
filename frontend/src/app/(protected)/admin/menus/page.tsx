import { MenusListView } from "@/features/admin/content/menus-list-view";
import type { Metadata } from "next";

export const metadata: Metadata = { title: "Menus" };
export default function AdminMenusPage() {
    return <MenusListView />;
}
