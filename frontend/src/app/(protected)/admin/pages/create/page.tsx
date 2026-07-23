import { PageFormView } from "@/features/admin/content/page-form-view";
import type { Metadata } from "next";

export const metadata: Metadata = { title: "Create Page" };
export default function CreateAdminPagePage() {
    return <PageFormView />;
}
