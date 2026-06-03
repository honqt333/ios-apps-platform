"use client";

import { create } from "zustand";
import { persist } from "zustand/middleware";

export interface AuthUser {
  id: number;
  name: string;
  email: string;
  username?: string;
  avatar?: string;
  locale: string;
  roles: string[];
  permissions?: string[];
  is_active: boolean;
}

interface AuthState {
  user: AuthUser | null;
  token: string | null;
  expiresAt: number | null;
  setAuth: (user: AuthUser, token: string, expiresIn: number) => void;
  setUser: (user: AuthUser) => void;
  setToken: (token: string, expiresIn: number) => void;
  clear: () => void;
  isAuthenticated: () => boolean;
  hasRole: (role: string | string[]) => boolean;
  hasPermission: (permission: string) => boolean;
}

export const useAuthStore = create<AuthState>()(
  persist(
    (set, get) => ({
      user: null,
      token: null,
      expiresAt: null,
      setAuth: (user, token, expiresIn) =>
        set({
          user,
          token,
          expiresAt: Date.now() + expiresIn * 1000,
        }),
      setUser: (user) => set({ user }),
      setToken: (token, expiresIn) =>
        set({ token, expiresAt: Date.now() + expiresIn * 1000 }),
      clear: () => set({ user: null, token: null, expiresAt: null }),
      isAuthenticated: () => {
        const s = get();
        if (!s.token || !s.expiresAt) return false;
        return s.expiresAt > Date.now();
      },
      hasRole: (role) => {
        const u = get().user;
        if (!u) return false;
        const roles = Array.isArray(role) ? role : [role];
        return roles.some((r) => u.roles.includes(r));
      },
      hasPermission: (permission) => {
        const u = get().user;
        return u?.permissions?.includes(permission) ?? false;
      },
    }),
    {
      name: "ios-platform-auth",
      partialize: (s) => ({ user: s.user, token: s.token, expiresAt: s.expiresAt }),
    }
  )
);
