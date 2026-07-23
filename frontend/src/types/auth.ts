export interface AuthUser {
  id: number;
  name: string;
  first_name: string | null;
  last_name: string | null;
  phone_number: string | null;
  email: string;
  email_verified_at: string | null;
  roles: string[];
  permissions: string[];
  two_factor_enabled: boolean;
  created_at: string | null;
  updated_at: string | null;
}

export interface LoginCredentials {
  email: string;
  password: string;
  remember?: boolean;
}

export interface RegisterPayload {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
}

export interface ResetPasswordPayload {
  email: string;
  token: string;
  password: string;
  password_confirmation: string;
}

export interface AuthPayload {
  user: AuthUser;
}

export interface LoginPayload {
  requires_two_factor: boolean;
  user: AuthUser | null;
}

export interface TwoFactorPayload {
  code?: string;
  recovery_code?: string;
}
