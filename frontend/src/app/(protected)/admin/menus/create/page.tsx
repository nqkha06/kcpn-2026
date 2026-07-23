import { MenuFormView } from "@/features/admin/content/menu-form-view";
import type { Metadata } from "next";

export const metadata: Metadata = { title: "Create Menu" };
export default function CreateAdminMenuPage() {
    return <MenuFormView />;
}
