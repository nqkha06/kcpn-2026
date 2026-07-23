"use client";

import { UNAUTHENTICATED_EVENT } from "@/lib/api/client";
import { isProtectedPath } from "@/lib/auth/routes";
import { authService } from "@/services/auth.service";
import type { AuthUser, LoginCredentials, LoginPayload, RegisterPayload } from "@/types/auth";
import { useQuery, useQueryClient } from "@tanstack/react-query";
import { usePathname, useRouter } from "next/navigation";
import {
  createContext,
  useCallback,
  useContext,
  useEffect,
  type ReactNode,
} from "react";

const authQueryKey = ["auth", "me"] as const;

interface AuthContextValue {
  user: AuthUser | null;
  isLoading: boolean;
  isAuthenticated: boolean;
  login: (credentials: LoginCredentials) => Promise<LoginPayload>;
  register: (payload: RegisterPayload) => Promise<AuthUser>;
  logout: () => Promise<void>;
  refresh: () => Promise<AuthUser | null>;
  hasRole: (role: string) => boolean;
  can: (permission: string) => boolean;
}

const AuthContext = createContext<AuthContextValue | null>(null);

export function AuthProvider({ children }: { children: ReactNode }) {
  const router = useRouter();
  const pathname = usePathname();
  const queryClient = useQueryClient();
  const authQuery = useQuery({
    queryKey: authQueryKey,
    queryFn: authService.me,
    retry: false,
    staleTime: 5 * 60 * 1000,
  });
  const user = authQuery.data?.user ?? null;

  const clearAuthentication = useCallback(() => {
    queryClient.setQueryData(authQueryKey, null);

    if (isProtectedPath(pathname)) {
      const next = encodeURIComponent(pathname);
      router.replace(`/login?next=${next}`);
    }
  }, [pathname, queryClient, router]);

  useEffect(() => {
    window.addEventListener(UNAUTHENTICATED_EVENT, clearAuthentication);

    return () => window.removeEventListener(UNAUTHENTICATED_EVENT, clearAuthentication);
  }, [clearAuthentication]);

  const login = useCallback(
    async (credentials: LoginCredentials): Promise<LoginPayload> => {
      const result = await authService.login(credentials);

      if (result.user) {
        queryClient.setQueryData(authQueryKey, { user: result.user });
      }

      return result;
    },
    [queryClient],
  );

  const register = useCallback(
    async (payload: RegisterPayload): Promise<AuthUser> => {
      const result = await authService.register(payload);
      queryClient.setQueryData(authQueryKey, result);

      return result.user;
    },
    [queryClient],
  );

  const logout = useCallback(async (): Promise<void> => {
    try {
      await authService.logout();
    } finally {
      queryClient.setQueryData(authQueryKey, null);
      queryClient.removeQueries({ predicate: (query) => query.queryKey[0] !== "auth" });
      router.replace("/login");
    }
  }, [queryClient, router]);

  const refresh = useCallback(async (): Promise<AuthUser | null> => {
    const result = await authQuery.refetch();

    return result.data?.user ?? null;
  }, [authQuery]);

  const value: AuthContextValue = {
    user,
    isLoading: authQuery.isLoading,
    isAuthenticated: user !== null,
    login,
    register,
    logout,
    refresh,
    hasRole: (role) => user?.roles.includes(role) ?? false,
    can: (permission) => user?.permissions.includes(permission) ?? false,
  };

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth(): AuthContextValue {
  const context = useContext(AuthContext);

  if (!context) {
    throw new Error("useAuth must be used inside AuthProvider");
  }

  return context;
}
