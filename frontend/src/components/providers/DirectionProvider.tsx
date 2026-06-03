"use client";

import { createContext, useContext, useEffect } from "react";
import { useSettingsStore } from "@/stores/settingsStore";
import { isRTL } from "@/lib/utils";

interface DirectionContextValue {
  dir: "ltr" | "rtl";
  locale: string;
}

const DirectionContext = createContext<DirectionContextValue | undefined>(undefined);

export function DirectionProvider({ children }: { children: React.ReactNode }) {
  const locale = useSettingsStore((s) => s.locale);
  const dir = isRTL(locale) ? "rtl" : "ltr";

  useEffect(() => {
    document.documentElement.lang = locale;
    document.documentElement.dir = dir;
  }, [locale, dir]);

  return <DirectionContext.Provider value={{ dir, locale }}>{children}</DirectionContext.Provider>;
}

export const useDirection = () => {
  const ctx = useContext(DirectionContext);
  if (!ctx) throw new Error("useDirection must be used within DirectionProvider");
  return ctx;
};
