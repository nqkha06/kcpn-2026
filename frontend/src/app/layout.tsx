import { AppProviders } from "@/components/common/app-providers";
import type { Metadata } from "next";
import localFont from "next/font/local";
import "./globals.css";

const instrumentSans = localFont({
  src: [
    { path: "./fonts/instrument-sans-latin.woff2", weight: "400 700", style: "normal" },
    { path: "./fonts/instrument-sans-latin-ext.woff2", weight: "400 700", style: "normal" },
  ],
  variable: "--font-instrument-sans",
  display: "swap",
  fallback: ["Arial", "sans-serif"],
});

export const metadata: Metadata = {
  title: {
    default: "Cashback",
    template: "%s | Cashback",
  },
  description: "Quản lý ví, giao dịch và ngân sách cá nhân.",
};

export default function RootLayout({ children }: Readonly<{ children: React.ReactNode }>) {
  return (
    <html lang="vi" suppressHydrationWarning>
      <body className={`${instrumentSans.variable} min-h-screen bg-background font-sans antialiased`}>
        <AppProviders>{children}</AppProviders>
      </body>
    </html>
  );
}
