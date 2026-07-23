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
  AlertTriangle,
  ChevronRight,
  Folder,
  LayoutDashboard,
  ListTree,
  LogOut,
  Menu,
  Moon,
  PanelLeftClose,
  PanelLeftOpen,
  PiggyBank,
  ReceiptText,
  Settings,
  Sun,
  Tags,
  Users,
  X,
  type LucideIcon,
} from "lucide-react";
import Link from "next/link";
import { usePathname } from "next/navigation";
import { useTheme } from "next-themes";
import {
  useEffect,
  useMemo,
  useRef,
  useState,
  type ReactNode,
} from "react";

interface AdminNavigationItem {
  label: string;
  href?: string;
  icon: LucideIcon;
  children?: Array<{ label: string; href: string }>;
}

const navigation: AdminNavigationItem[] = [
  { label: "Dashboard", href: "/admin/dashboard", icon: LayoutDashboard },
  {
    label: "Users",
    icon: Users,
    children: [
      { label: "Users", href: "/admin/users" },
      { label: "Roles", href: "/admin/roles" },
      { label: "Permissions", href: "/admin/permissions" },
    ],
  },
  { label: "Categories", href: "/admin/categories", icon: Tags },
  { label: "Budgets", href: "/admin/budgets", icon: PiggyBank },
  { label: "Transactions", href: "/admin/transactions", icon: ReceiptText },
  {
    label: "Appearance",
    icon: Settings,
    children: [{ label: "Theme Options", href: "/admin/settings/appearance" }],
  },
  { label: "Pages", href: "/admin/pages", icon: Folder },
  { label: "Menus", href: "/admin/menus", icon: ListTree },
];

function isActivePath(pathname: string, href: string): boolean {
  return pathname === href || pathname.startsWith(`${href}/`);
}

function activeLabel(pathname: string): string {
  for (const item of navigation) {
    if (item.href && isActivePath(pathname, item.href)) {
      return item.label;
    }

    const child = item.children?.find((entry) => isActivePath(pathname, entry.href));
    if (child) {
      return child.label;
    }
  }

  return "Admin";
}

function AdminNavigation({
  collapsed,
  pathname,
  onNavigate,
}: {
  collapsed: boolean;
  pathname: string;
  onNavigate?: () => void;
}) {
  const [openGroups, setOpenGroups] = useState<string[]>(() =>
    navigation
      .filter((item) =>
        item.children?.some((child) => isActivePath(pathname, child.href)),
      )
      .map((item) => item.label),
  );

  function toggleGroup(label: string): void {
    setOpenGroups((current) =>
      current.includes(label)
        ? current.filter((item) => item !== label)
        : [...current, label],
    );
  }

  return (
    <nav className="flex-1 overflow-y-auto px-2 py-2" aria-label="Admin navigation">
      {!collapsed ? (
        <p className="px-2 pb-2 text-xs font-medium text-sidebar-foreground/60">Platform</p>
      ) : null}
      <ul className="space-y-1">
        {navigation.map((item) => {
          const Icon = item.icon;
          const childActive = item.children?.some((child) =>
            isActivePath(pathname, child.href),
          );

          if (item.children) {
            const isOpen = openGroups.includes(item.label) || Boolean(childActive);

            return (
              <li key={item.label}>
                <button
                  type="button"
                  title={collapsed ? item.label : undefined}
                  onClick={() => toggleGroup(item.label)}
                  className={cn(
                    "flex h-8 w-full items-center gap-2 rounded-md px-2 text-left text-sm transition-colors hover:bg-sidebar-accent hover:text-sidebar-accent-foreground",
                    childActive && "bg-sidebar-accent text-sidebar-accent-foreground",
                    collapsed && "justify-center",
                  )}
                >
                  <Icon className="size-4 shrink-0" />
                  {!collapsed ? (
                    <>
                      <span className="flex-1 truncate">{item.label}</span>
                      <ChevronRight
                        className={cn(
                          "size-4 transition-transform duration-200 motion-reduce:duration-0",
                          isOpen && "rotate-90",
                        )}
                      />
                    </>
                  ) : null}
                </button>
                {!collapsed && isOpen ? (
                  <ul className="mx-3.5 mt-1 space-y-1 border-l border-sidebar-border px-2.5">
                    {item.children.map((child) => {
                      const active = isActivePath(pathname, child.href);

                      return (
                        <li key={child.href}>
                          <Link
                            href={child.href}
                            onClick={onNavigate}
                            className={cn(
                              "flex h-7 items-center rounded-md px-2 text-sm transition-colors hover:bg-sidebar-accent hover:text-sidebar-accent-foreground",
                              active && "bg-sidebar-accent font-medium text-sidebar-accent-foreground",
                            )}
                          >
                            {child.label}
                          </Link>
                        </li>
                      );
                    })}
                  </ul>
                ) : null}
              </li>
            );
          }

          const active = item.href ? isActivePath(pathname, item.href) : false;

          return (
            <li key={item.label}>
              <Link
                href={item.href ?? "#"}
                title={collapsed ? item.label : undefined}
                onClick={onNavigate}
                className={cn(
                  "flex h-8 items-center gap-2 rounded-md px-2 text-sm transition-colors hover:bg-sidebar-accent hover:text-sidebar-accent-foreground",
                  active && "bg-sidebar-accent font-medium text-sidebar-accent-foreground",
                  collapsed && "justify-center",
                )}
              >
                <Icon className="size-4 shrink-0" />
                {!collapsed ? <span className="truncate">{item.label}</span> : null}
              </Link>
            </li>
          );
        })}
      </ul>
    </nav>
  );
}

function SidebarContent({
  collapsed,
  pathname,
  brandName,
  logo,
  onNavigate,
}: {
  collapsed: boolean;
  pathname: string;
  brandName: string;
  logo: string;
  onNavigate?: () => void;
}) {
  return (
    <div className="flex h-full w-full flex-col overflow-hidden rounded-lg bg-sidebar text-sidebar-foreground">
      <div className="p-2">
        <Link
          href="/admin/dashboard"
          onClick={onNavigate}
          className={cn(
            "flex h-12 items-center gap-2 rounded-md px-2 hover:bg-sidebar-accent",
            collapsed && "justify-center px-0",
          )}
        >
          <span className="flex size-8 shrink-0 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground">
            {/* eslint-disable-next-line @next/next/no-img-element */}
            <img src={logo} alt={brandName} className="size-6 rounded-sm object-contain" />
          </span>
          {!collapsed ? (
            <span className="ml-1 truncate text-sm font-semibold">{brandName}</span>
          ) : null}
        </Link>
      </div>

      <AdminNavigation
        collapsed={collapsed}
        pathname={pathname}
        onNavigate={onNavigate}
      />

      <div className="p-2">
        <Link
          href="/dashboard"
          target="_blank"
          title={collapsed ? "Go to User" : undefined}
          className={cn(
            "flex h-9 items-center justify-center gap-2 rounded-md border border-sidebar-border bg-background px-3 text-sm font-medium text-foreground shadow-xs transition-colors hover:bg-accent",
            collapsed && "px-0",
          )}
        >
          <AlertTriangle className="size-4" />
          {!collapsed ? "Go to User" : null}
        </Link>
      </div>
    </div>
  );
}

export function AdminLayout({ children }: { children: ReactNode }) {
  const pathname = usePathname();
  const { user, logout } = useAuth();
  const { resolvedTheme, setTheme } = useTheme();
  const [collapsed, setCollapsed] = useState(false);
  const [mobileOpen, setMobileOpen] = useState(false);
  const [profileOpen, setProfileOpen] = useState(false);
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
  const breadcrumb = useMemo(() => activeLabel(pathname), [pathname]);

  useEffect(() => {
    function closeProfile(event: MouseEvent): void {
      if (!profileRef.current?.contains(event.target as Node)) {
        setProfileOpen(false);
      }
    }

    document.addEventListener("mousedown", closeProfile);
    return () => document.removeEventListener("mousedown", closeProfile);
  }, []);

  return (
    <div className="flex min-h-svh w-full bg-sidebar">
      <aside
        className={cn(
          "fixed inset-y-0 left-0 z-30 hidden p-2 transition-[width] duration-200 ease-linear motion-reduce:duration-0 md:flex",
          collapsed ? "w-16" : "w-64",
        )}
      >
        <SidebarContent
          collapsed={collapsed}
          pathname={pathname}
          brandName={brandName}
          logo={logo}
        />
      </aside>

      {mobileOpen ? (
        <div className="fixed inset-0 z-50 md:hidden">
          <button
            type="button"
            aria-label="Close admin navigation"
            className="absolute inset-0 bg-black/50"
            onClick={() => setMobileOpen(false)}
          />
          <aside className="relative h-full w-72 p-2">
            <button
              type="button"
              aria-label="Close admin navigation"
              onClick={() => setMobileOpen(false)}
              className="absolute top-5 right-5 z-10 flex size-8 items-center justify-center rounded-md hover:bg-sidebar-accent"
            >
              <X className="size-4" />
            </button>
            <SidebarContent
              collapsed={false}
              pathname={pathname}
              brandName={brandName}
              logo={logo}
              onNavigate={() => setMobileOpen(false)}
            />
          </aside>
        </div>
      ) : null}

      <div
        className={cn(
          "min-w-0 flex-1 p-2 transition-[margin] duration-200 ease-linear motion-reduce:duration-0",
          collapsed ? "md:ml-16" : "md:ml-64",
        )}
      >
        <main className="min-h-[calc(100svh-1rem)] overflow-hidden rounded-xl bg-background shadow-sm">
          <header className="flex h-16 items-center justify-between gap-2 border-b border-sidebar-border/50 px-4 md:px-4">
            <div className="flex min-w-0 items-center gap-2">
              <button
                type="button"
                aria-label="Open admin navigation"
                onClick={() => setMobileOpen(true)}
                className="flex size-8 items-center justify-center rounded-md hover:bg-accent md:hidden"
              >
                <Menu className="size-4" />
              </button>
              <button
                type="button"
                aria-label={collapsed ? "Expand admin navigation" : "Collapse admin navigation"}
                onClick={() => setCollapsed((current) => !current)}
                className="hidden size-8 items-center justify-center rounded-md hover:bg-accent md:flex"
              >
                {collapsed ? (
                  <PanelLeftOpen className="size-4" />
                ) : (
                  <PanelLeftClose className="size-4" />
                )}
              </button>
              <div className="h-4 w-px bg-border" />
              <span className="truncate text-sm font-medium">{breadcrumb}</span>
            </div>

            <div className="flex items-center gap-2">
              <button
                type="button"
                aria-label="Toggle color theme"
                title={resolvedTheme === "dark" ? "Dark" : "Light"}
                onClick={() => setTheme(resolvedTheme === "dark" ? "light" : "dark")}
                className="flex size-9 items-center justify-center rounded-md hover:bg-accent"
              >
                {resolvedTheme === "dark" ? (
                  <Moon className="size-5 opacity-80" />
                ) : (
                  <Sun className="size-5 opacity-80" />
                )}
              </button>

              <div ref={profileRef} className="relative">
                <button
                  type="button"
                  aria-haspopup="menu"
                  aria-expanded={profileOpen}
                  onClick={() => setProfileOpen((current) => !current)}
                  className="flex size-10 items-center justify-center rounded-full p-1 hover:bg-accent"
                >
                  <span className="flex size-8 items-center justify-center rounded-full bg-neutral-200 text-sm font-semibold text-black dark:bg-neutral-700 dark:text-white">
                    {userInitials(user?.name)}
                  </span>
                </button>

                {profileOpen ? (
                  <div
                    role="menu"
                    className="absolute top-[calc(100%+0.25rem)] right-0 z-50 w-56 rounded-md border bg-popover p-1 text-popover-foreground shadow-md"
                  >
                    <div className="px-2 py-1.5">
                      <p className="truncate text-sm font-semibold">{user?.name}</p>
                      <p className="truncate text-xs text-muted-foreground">{user?.email}</p>
                    </div>
                    <div className="-mx-1 my-1 h-px bg-border" />
                    <Link
                      href="/settings"
                      role="menuitem"
                      onClick={() => setProfileOpen(false)}
                      className="flex items-center gap-2 rounded-sm px-2 py-1.5 text-sm hover:bg-accent"
                    >
                      <Settings className="size-4" />
                      Settings
                    </Link>
                    <div className="-mx-1 my-1 h-px bg-border" />
                    <button
                      type="button"
                      role="menuitem"
                      onClick={() => void logout()}
                      className="flex w-full items-center gap-2 rounded-sm px-2 py-1.5 text-sm hover:bg-accent"
                    >
                      <LogOut className="size-4" />
                      Log out
                    </button>
                  </div>
                ) : null}
              </div>
            </div>
          </header>

          {children}
        </main>
      </div>
    </div>
  );
}
