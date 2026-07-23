const protectedPrefixes = [
  "/dashboard",
  "/wallets",
  "/transactions",
  "/budgets",
  "/settings",
  "/admin",
];

const guestPrefixes = [
  "/login",
  "/register",
  "/forgot-password",
  "/reset-password",
  "/two-factor-challenge",
];

export function isProtectedPath(pathname: string): boolean {
  return protectedPrefixes.some(
    (prefix) => pathname === prefix || pathname.startsWith(`${prefix}/`),
  );
}

export function isGuestPath(pathname: string): boolean {
  return guestPrefixes.some(
    (prefix) => pathname === prefix || pathname.startsWith(`${prefix}/`),
  );
}

export function isAdminPath(pathname: string): boolean {
  return pathname === "/admin" || pathname.startsWith("/admin/");
}
