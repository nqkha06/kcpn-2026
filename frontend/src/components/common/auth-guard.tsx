"use client";

import { LoadingState } from "@/components/common/loading-state";
import { useAuth } from "@/lib/auth/auth-context";
import { usePathname, useRouter, useSearchParams } from "next/navigation";
import { useEffect, type ReactNode } from "react";

interface AuthGuardProps {
  children: ReactNode;
  guestOnly?: boolean;
  role?: string;
  permission?: string;
}

export function AuthGuard({ children, guestOnly = false, role, permission }: AuthGuardProps) {
  const { user, isLoading, hasRole, can } = useAuth();
  const pathname = usePathname();
  const searchParams = useSearchParams();
  const router = useRouter();

  useEffect(() => {
    if (isLoading) return;

    if (guestOnly && user) {
      router.replace(searchParams.get("next") || "/dashboard");
      return;
    }

    if (!guestOnly && !user) {
      router.replace(`/login?next=${encodeURIComponent(pathname)}`);
      return;
    }

    if (user && role && !hasRole(role)) {
      router.replace("/403");
      return;
    }

    if (user && permission && !can(permission)) {
      router.replace("/403");
    }
  }, [can, guestOnly, hasRole, isLoading, pathname, permission, role, router, searchParams, user]);

  const denied = guestOnly
    ? user !== null
    : user === null || (role !== undefined && !hasRole(role)) || (permission !== undefined && !can(permission));

  if (isLoading || denied) {
    return <LoadingState label="Checking your session..." />;
  }

  return children;
}
