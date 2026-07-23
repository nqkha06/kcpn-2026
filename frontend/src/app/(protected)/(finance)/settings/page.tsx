import { SettingsView } from "@/features/settings/settings-view";
import type { Metadata } from "next";

export const metadata: Metadata = { title: "Cài đặt" };

export default function SettingsPage() {
  return <SettingsView />;
}
