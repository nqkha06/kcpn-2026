import { WalletsView } from "@/features/wallets/wallets-view";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Ví tiền",
};

export default function WalletsPage() {
  return <WalletsView />;
}
