"use client";

import { create } from "zustand";
import { persist } from "zustand/middleware";
import { type Locale, defaultLocale, locales } from "../request";

type Theme = "light" | "dark" | "system";

interface SettingsState {
  locale: Locale;
  theme: Theme;
  setLocale: (locale: Locale) => void;
  setTheme: (theme: Theme) => void;
  toggleTheme: () => void;
  cycleLocale: () => void;
}

export const useSettingsStore = create<SettingsState>()(
  persist(
    (set, get) => ({
      locale: defaultLocale,
      theme: "system",
      setLocale: (locale) => {
        if (locales.includes(locale)) set({ locale });
      },
      setTheme: (theme) => set({ theme }),
      toggleTheme: () => {
        const current = get().theme;
        const resolved = current === "system"
          ? (typeof window !== "undefined" && window.matchMedia("(prefers-color-scheme: dark)").matches ? "light" : "dark")
          : current;
        set({ theme: resolved === "dark" ? "light" : "dark" });
      },
      cycleLocale: () => {
        const current = get().locale;
        const idx = locales.indexOf(current);
        const next = locales[(idx + 1) % locales.length];
        set({ locale: next });
      },
    }),
    { name: "ios-platform-settings" }
  )
);
