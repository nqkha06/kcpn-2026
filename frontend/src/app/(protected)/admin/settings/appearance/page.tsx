import { AppearanceView } from "@/features/admin/content/appearance-view";
import type { Metadata } from "next";

export const metadata: Metadata = { title: "Appearance Options" };
export default function AdminAppearancePage() {
    return <AppearanceView />;
}
