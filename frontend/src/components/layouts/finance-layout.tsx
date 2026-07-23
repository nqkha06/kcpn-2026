"use client";

import { useAuth } from "@/lib/auth/auth-context";
import { userInitials } from "@/lib/finance/format";
import { cn } from "@/lib/utils";
import {
  publicConfigurationQueryKey,
  publicSiteService,
} from "@/services/public-site.service";
import { useQuery } from "@tanstack/react-query";
import {
  ArrowLeftRight,
  ChevronUp,
  Gauge,
  PiggyBank,
  Settings2,
  Tags,
  Wallet,
} from "lucide-react";
import Link from "next/link";
import { usePathname } from "next/navigation";
import { useEffect, useRef, useState, type ComponentType, type ReactNode } from "react";

interface NavigationItem {
  label: string;
  href: string;
  icon: ComponentType<{ className?: string }>;
}

const navigation: NavigationItem[] = [
  { label: "Tổng quan", href: "/dashboard", icon: Gauge },
  { label: "Giao dịch", href: "/transactions", icon: ArrowLeftRight },
  { label: "Ngân sách", href: "/budgets", icon: PiggyBank },
  { label: "Ví tiền", href: "/wallets", icon: Wallet },
  { label: "Danh mục", href: "/categories", icon: Tags },
  { label: "Cài đặt", href: "/settings", icon: Settings2 },
];

function isPathActive(pathname: string, href: string): boolean {
  return pathname === href || pathname.startsWith(`${href}/`);
}

function MobileNavigation({ pathname }: { pathname: string }) {
  const [isOpen, setIsOpen] = useState(false);
  const quickNavigation = navigation.slice(0, 3);
  const overflowNavigation = navigation.slice(3);
  const isOverflowActive = overflowNavigation.some((item) => isPathActive(pathname, item.href));

  return (
    <div className="fixed inset-x-0 bottom-0 z-50 md:hidden">
      <div className="mx-auto w-full max-w-7xl px-4 pb-[calc(0.75rem+env(safe-area-inset-bottom))]">
        <div className="relative">
          <div
            className={cn(
              "absolute inset-x-0 bottom-[calc(100%+0.75rem)] origin-bottom rounded-3xl border border-slate-200/80 bg-white/95 p-2 shadow-xl shadow-slate-900/10 backdrop-blur transition-all duration-300",
              isOpen
                ? "translate-y-0 opacity-100"
                : "pointer-events-none translate-y-4 opacity-0",
            )}
          >
            <p className="px-2 pb-2 text-[11px] font-semibold tracking-[0.14em] text-slate-400 uppercase">
              Điều hướng
            </p>
            <ul className="space-y-1">
              {navigation.map((item) => {
                const active = isPathActive(pathname, item.href);
                const Icon = item.icon;

                return (
                  <li key={item.href}>
                    <Link
                      href={item.href}
                      aria-current={active ? "page" : undefined}
                      onClick={() => setIsOpen(false)}
                      className={cn(
                        "flex items-center justify-between rounded-2xl px-3 py-3 text-sm font-medium transition-colors",
                        active
                          ? "bg-primary-50 text-primary-700"
                          : "text-slate-700 hover:bg-slate-100 hover:text-slate-900",
                      )}
                    >
                      <span className="flex items-center gap-3">
                        <span
                          className={cn(
                            "inline-flex size-9 items-center justify-center rounded-xl",
                            active
                              ? "bg-primary-100 text-primary-700"
                              : "bg-slate-100 text-slate-500",
                          )}
                        >
                          <Icon className="size-4" />
                        </span>
                        {item.label}
                      </span>
                      {active ? (
                        <span className="rounded-full bg-primary-100 px-2 py-1 text-[11px] font-semibold text-primary-700">
                          Hiện tại
                        </span>
                      ) : null}
                    </Link>
                  </li>
                );
              })}
            </ul>
          </div>

          <nav
            aria-label="Điều hướng di động"
            className="rounded-3xl border border-slate-200/80 bg-white/95 p-2 shadow-lg shadow-slate-900/10 backdrop-blur"
          >
            <ul className="grid grid-cols-4 gap-1">
              {quickNavigation.map((item) => {
                const active = isPathActive(pathname, item.href);
                const Icon = item.icon;

                return (
                  <li key={item.href}>
                    <Link
                      href={item.href}
                      aria-current={active ? "page" : undefined}
                      onClick={() => setIsOpen(false)}
                      className={cn(
                        "relative flex min-h-14 flex-col items-center justify-center rounded-2xl px-2 py-2 text-center text-[11px] font-semibold transition-all",
                        active
                          ? "bg-primary-50 text-primary-700 ring-1 ring-primary-200"
                          : "text-slate-500 hover:bg-slate-100 hover:text-slate-900 active:scale-[0.98] active:bg-slate-200 active:text-slate-900",
                      )}
                    >
                      <Icon className={cn("size-5 transition-transform duration-300", active && "scale-105")} />
                      <span className="mt-1 truncate">{item.label}</span>
                      {active ? <span className="mt-1 h-1 w-6 rounded-full bg-primary-600/80" /> : null}
                    </Link>
                  </li>
                );
              })}
              <li>
                <button
                  type="button"
                  aria-expanded={isOpen}
                  aria-label={isOpen ? "Đóng menu điều hướng" : "Mở menu điều hướng"}
                  onClick={() => setIsOpen((current) => !current)}
                  className={cn(
                    "flex min-h-14 w-full flex-col items-center justify-center rounded-2xl px-2 py-2 text-center text-[11px] font-semibold transition-all",
                    isOpen || isOverflowActive
                      ? "bg-primary-50 text-primary-700"
                      : "text-slate-500 hover:bg-slate-100 hover:text-slate-900 active:scale-[0.98] active:bg-slate-200 active:text-slate-900",
                  )}
                >
                  <span
                    className={cn(
                      "inline-flex size-8 items-center justify-center rounded-full bg-slate-100 transition-transform duration-300",
                      isOpen && "rotate-180 bg-primary-100 text-primary-700",
                    )}
                  >
                    <ChevronUp className="size-4" />
                  </span>
                  <span className="mt-1">{isOpen ? "Đóng" : "Menu"}</span>
                </button>
              </li>
            </ul>
          </nav>
        </div>
      </div>
    </div>
  );
}

export function FinanceLayout({ children }: { children: ReactNode }) {
  const pathname = usePathname();
  const { user, logout } = useAuth();
  const [isProfileOpen, setIsProfileOpen] = useState(false);
  const profileRef = useRef<HTMLDivElement>(null);
  const configurationQuery = useQuery({
    queryKey: publicConfigurationQueryKey,
    queryFn: publicSiteService.configuration,
    staleTime: 30 * 60 * 1000,
  });
  const appearance = configurationQuery.data?.appearance;
  const brandName = appearance?.site_name || "Spendify";
  const backendUrl = process.env.NEXT_PUBLIC_BACKEND_URL?.replace(/\/$/, "") ?? "";
  const logo = appearance?.logo_light || appearance?.logo_dark || `${backendUrl}/logo.png`;

  useEffect(() => {
    function closeProfile(event: MouseEvent): void {
      if (!profileRef.current?.contains(event.target as Node)) {
        setIsProfileOpen(false);
      }
    }

    document.addEventListener("mousedown", closeProfile);
    return () => document.removeEventListener("mousedown", closeProfile);
  }, []);

  return (
    <div className="min-h-screen bg-slate-50 text-slate-900">
      <header className="sticky top-0 z-40 border-b border-slate-200 bg-white/90 backdrop-blur">
        <div className="mx-auto flex h-18 w-full max-w-7xl items-center justify-between px-4 sm:px-6">
          <Link href="/dashboard" className="flex items-center gap-2">
            <div className="h-9 w-9">
              {/* The legacy interface uses a regular image because this URL is configured by Laravel. */}
              {/* eslint-disable-next-line @next/next/no-img-element */}
              <img
                className="h-full w-full rounded-xl object-cover shadow-sm"
                src={logo}
                alt={`${brandName} Logo`}
              />
            </div>
            <span className="text-lg font-semibold tracking-tight text-slate-900">{brandName}</span>
          </Link>

          <nav className="hidden self-stretch items-stretch gap-1 md:flex" aria-label="Điều hướng chính">
            {navigation.map((item) => {
              const active = isPathActive(pathname, item.href);

              return (
                <Link
                  key={item.href}
                  href={item.href}
                  aria-current={active ? "page" : undefined}
                  className={cn(
                    "relative inline-flex h-full items-center px-3 text-sm font-medium transition-colors after:absolute after:right-3 after:bottom-0 after:left-3 after:h-[2px] after:rounded-full after:transition-all",
                    active
                      ? "text-primary-700 after:bg-primary-600"
                      : "text-slate-600 after:bg-transparent hover:text-slate-900 hover:after:bg-slate-300",
                  )}
                >
                  {item.label}
                </Link>
              );
            })}
          </nav>

          <div ref={profileRef} className="relative">
            <button
              type="button"
              aria-expanded={isProfileOpen}
              aria-haspopup="menu"
              onClick={() => setIsProfileOpen((current) => !current)}
              className="flex items-center gap-3 rounded-xl px-2 py-1.5 transition-colors hover:bg-slate-100"
            >
              <div className="hidden text-right sm:block">
                <p className="text-sm font-semibold text-slate-900">{user?.name}</p>
                <p className="text-xs text-slate-500">{user?.email}</p>
              </div>
              <span className="flex size-9 items-center justify-center rounded-full bg-primary-100 font-semibold text-primary-700">
                {userInitials(user?.name)}
              </span>
            </button>

            {isProfileOpen ? (
              <div
                role="menu"
                className="absolute top-[calc(100%+0.25rem)] right-0 z-50 w-56 min-w-32 overflow-hidden rounded-md border bg-white p-1 text-slate-900 shadow-md"
              >
                <div className="px-2 py-1.5">
                  <p className="text-sm font-semibold text-slate-900">{user?.name}</p>
                  <p className="text-xs text-slate-500">{user?.email}</p>
                </div>
                <div className="-mx-1 my-1 h-px bg-slate-200" />
                <Link
                  href="/settings"
                  role="menuitem"
                  onClick={() => setIsProfileOpen(false)}
                  className="flex cursor-pointer items-center rounded-sm px-2 py-1.5 text-sm outline-none hover:bg-slate-100"
                >
                  Cài đặt
                </Link>
                <div className="-mx-1 my-1 h-px bg-slate-200" />
                <button
                  type="button"
                  role="menuitem"
                  onClick={() => void logout()}
                  className="flex w-full cursor-pointer items-center rounded-sm px-2 py-1.5 text-sm outline-none hover:bg-slate-100"
                >
                  Đăng xuất
                </button>
              </div>
            ) : null}
          </div>
        </div>
      </header>

      <main className="mx-auto w-full max-w-7xl px-4 py-8 pb-28 sm:px-6 md:pb-8">
        <div className="flex items-start gap-6">
          <section className="w-full">{children}</section>
        </div>
      </main>

      <footer className="border-t border-slate-200 bg-white">
        <div className="mx-auto flex w-full max-w-7xl flex-col items-center justify-between gap-4 px-4 py-6 text-sm text-slate-500 sm:flex-row sm:px-6">
          <p>{brandName} © {new Date().getFullYear()}</p>
          <div className="flex items-center gap-4">
            <a href="#" className="hover:text-primary-700">Quyền riêng tư</a>
            <a href="#" className="hover:text-primary-700">Điều khoản</a>
            <a href="#" className="hover:text-primary-700">Hỗ trợ</a>
          </div>
        </div>
      </footer>

      <MobileNavigation pathname={pathname} />
    </div>
  );
}
