import { isProtectedPath } from "@/lib/auth/routes";
import type { NextRequest } from "next/server";
import { NextResponse } from "next/server";

export function proxy(request: NextRequest) {
  const { pathname, search } = request.nextUrl;
  const configuredCookie = process.env.LARAVEL_SESSION_COOKIE;
  const hasSession = request.cookies.getAll().some(({ name, value }) => {
    const isSessionCookie =
      name === configuredCookie ||
      name === "laravel_session" ||
      name.endsWith("_session") ||
      name.endsWith("-session");

    return isSessionCookie && value !== "";
  });

  if (isProtectedPath(pathname) && !hasSession) {
    const loginUrl = new URL("/login", request.url);
    loginUrl.searchParams.set("next", `${pathname}${search}`);

    return NextResponse.redirect(loginUrl);
  }

  return NextResponse.next();
}

export const config = {
  matcher: ["/((?!_next/static|_next/image|favicon.ico|.*\\..*).*)"],
};
