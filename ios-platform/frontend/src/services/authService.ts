import api from "@/lib/api";
import type { User } from "@/types";

export interface LoginPayload {
  email: string;
  password: string;
  remember?: boolean;
}

export interface RegisterPayload {
  name: string;
  email: string;
  username?: string;
  password: string;
  password_confirmation: string;
  phone?: string;
  locale?: "en" | "ar";
}

export interface AuthResponse {
  user: User;
  access_token: string;
  token_type: string;
  expires_in: number;
}

export const authService = {
  async login(payload: LoginPayload): Promise<AuthResponse> {
    const { data } = await api.post<{ success: boolean; data: AuthResponse }>("/v1/auth/login", payload);
    return data.data;
  },

  async register(payload: RegisterPayload): Promise<AuthResponse> {
    const { data } = await api.post<{ success: boolean; data: AuthResponse }>("/v1/auth/register", payload);
    return data.data;
  },

  async me(): Promise<User> {
    const { data } = await api.get<{ success: boolean; data: User }>("/v1/auth/me");
    return data.data;
  },

  async logout(): Promise<void> {
    await api.post("/v1/auth/logout");
  },

  async refresh(): Promise<{ access_token: string; expires_in: number }> {
    const { data } = await api.post<{ success: boolean; data: { access_token: string; expires_in: number } }>(
      "/v1/auth/refresh"
    );
    return data.data;
  },

  async updateProfile(payload: Partial<User>): Promise<User> {
    const { data } = await api.patch<{ success: boolean; data: User }>("/v1/auth/me", payload);
    return data.data;
  },

  async changePassword(payload: { current_password: string; password: string; password_confirmation: string }) {
    await api.post("/v1/auth/change-password", payload);
  },
};
