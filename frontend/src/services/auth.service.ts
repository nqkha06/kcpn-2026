import { apiClient } from "@/lib/api/client";
import type { ApiSuccess } from "@/types/api";
import type {
  AuthPayload,
  LoginCredentials,
  LoginPayload,
  RegisterPayload,
  ResetPasswordPayload,
  TwoFactorPayload,
} from "@/types/auth";

export const authService = {
  csrf: () => apiClient.get<void>("/sanctum/csrf-cookie"),

  async login(credentials: LoginCredentials): Promise<LoginPayload> {
    await authService.csrf();
    const response = await apiClient.post<ApiSuccess<LoginPayload>>("/auth/login", credentials);

    return response.data;
  },

  async register(payload: RegisterPayload): Promise<AuthPayload> {
    await authService.csrf();
    const response = await apiClient.post<ApiSuccess<AuthPayload>>("/auth/register", payload);

    return response.data;
  },

  async twoFactorChallenge(payload: TwoFactorPayload): Promise<LoginPayload> {
    const response = await apiClient.post<ApiSuccess<LoginPayload>>(
      "/auth/two-factor-challenge",
      payload,
    );

    return response.data;
  },

  async logout(): Promise<void> {
    await apiClient.post<ApiSuccess<Record<string, never>>>("/auth/logout");
  },

  async me(): Promise<AuthPayload> {
    const response = await apiClient.get<ApiSuccess<AuthPayload>>("/auth/me");

    return response.data;
  },

  async forgotPassword(email: string): Promise<string> {
    await authService.csrf();
    const response = await apiClient.post<ApiSuccess<Record<string, never>>>(
      "/auth/forgot-password",
      { email },
    );

    return response.message;
  },

  async resetPassword(payload: ResetPasswordPayload): Promise<string> {
    await authService.csrf();
    const response = await apiClient.post<ApiSuccess<Record<string, never>>>(
      "/auth/reset-password",
      payload,
    );

    return response.message;
  },
};
