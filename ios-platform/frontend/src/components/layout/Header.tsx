"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { useLocale, useTranslations } from "next-intl";
import { Search, Menu, X, Sun, Moon, Languages, LogOut, LayoutDashboard, User as UserIcon } from "lucide-react";
import { useState } from "react";
import { Button } from "@/components/ui/button";
import { useTheme } from "@/components/providers/ThemeProvider";
import { useDirection } from "@/components/providers/DirectionProvider";
import { useAuthStore } from "@/stores/authStore";
import { useSettingsStore } from "@/stores/settingsStore";
import { locales } from "@/i18n/request";
import { cn } from "@/lib/utils";
import { isRTL } from "@/lib/utils";

export function Header() {
  const t = useTranslations("nav");
  const locale = useLocale();
  const { toggle } = useTheme();
  const { dir } = useDirection();
  const pathname = usePathname();
  const [open, setOpen] = useState(false);

  const user = useAuthStore((s) => s.user);
  const isAuthenticated = useAuthStore((s) => s.isAuthenticated());
  const clearAuth = useAuthStore((s) => s.clear);

  const cycleLocale = useSettingsStore((s) => s.cycleLocale);

  const stripLocale = (path: string) => {
    return path.replace(/^\/(en|ar)/, "") || "/";
  };

  const localePath = (path: string) => {
    const stripped = stripLocale(path);
    return `/${locale}${stripped === "/" ? "" : stripped}`;
  };

  const navLinks = [
    { href: "/", label: t("home") },
    { href: "/apps", label: t("apps") },
    { href: "/categories", label: t("categories") },
  ];

  return (
    <header className="sticky top-0 z-40 w-full border-b bg-background/80 backdrop-blur-xl supports-[backdrop-filter]:bg-background/60">
      <div className="container mx-auto flex h-16 items-center justify-between gap-4">
        {/* Logo */}
        <Link href={localePath("/")} className="flex items-center gap-2 font-bold text-lg">
          <div className="h-8 w-8 rounded-lg app-card-gradient flex items-center justify-center text-white text-sm font-extrabold">
            iP
          </div>
          <span className="hidden sm:inline">iOS Apps</span>
        </Link>

        {/* Desktop nav */}
        <nav className="hidden md:flex items-center gap-6">
          {navLinks.map((link) => {
            const isActive = stripLocale(pathname) === link.href;
            return (
              <Link
                key={link.href}
                href={localePath(link.href)}
                className={cn(
                  "text-sm font-medium transition-colors hover:text-primary",
                  isActive ? "text-primary" : "text-muted-foreground"
                )}
              >
                {link.label}
              </Link>
            );
          })}
        </nav>

        {/* Right side */}
        <div className="flex items-center gap-1">
          <Link href={localePath("/search")} aria-label="Search">
            <Button variant="ghost" size="icon">
              <Search className="h-4 w-4" />
            </Button>
          </Link>

          <Button variant="ghost" size="icon" onClick={toggle} aria-label="Toggle theme">
            <Sun className="h-4 w-4 dark:hidden" />
            <Moon className="h-4 w-4 hidden dark:block" />
          </Button>

          <Button variant="ghost" size="icon" onClick={cycleLocale} aria-label="Toggle language">
            <Languages className="h-4 w-4" />
          </Button>

          {isAuthenticated && user ? (
            <div className="hidden md:flex items-center gap-2 ms-2">
              <Link href={localePath("/admin/dashboard")}>
                <Button variant="ghost" size="sm">
                  <LayoutDashboard className="h-4 w-4 me-2" />
                  {t("admin")}
                </Button>
              </Link>
              <Button variant="ghost" size="icon" onClick={clearAuth} aria-label="Logout">
                <LogOut className="h-4 w-4" />
              </Button>
            </div>
          ) : (
            <div className="hidden md:flex items-center gap-2 ms-2">
              <Link href={localePath("/auth/login")}>
                <Button variant="ghost" size="sm">{t("login")}</Button>
              </Link>
              <Link href={localePath("/auth/register")}>
                <Button size="sm">{t("register")}</Button>
              </Link>
            </div>
          )}

          {/* Mobile menu */}
          <Button
            variant="ghost"
            size="icon"
            className="md:hidden"
            onClick={() => setOpen(!open)}
            aria-label="Menu"
          >
            {open ? <X className="h-5 w-5" /> : <Menu className="h-5 w-5" />}
          </Button>
        </div>
      </div>

      {/* Mobile nav */}
      {open && (
        <div className="md:hidden border-t">
          <div className="container mx-auto py-3 space-y-1">
            {navLinks.map((link) => (
              <Link
                key={link.href}
                href={localePath(link.href)}
                onClick={() => setOpen(false)}
                className="block px-3 py-2 rounded-md text-sm font-medium hover:bg-accent"
              >
                {link.label}
              </Link>
            ))}
            <div className="pt-2 border-t mt-2 space-y-1">
              {isAuthenticated ? (
                <>
                  <Link
                    href={localePath("/admin/dashboard")}
                    onClick={() => setOpen(false)}
                    className="block px-3 py-2 rounded-md text-sm font-medium hover:bg-accent"
                  >
                    {t("admin")}
                  </Link>
                  <button
                    onClick={() => {
                      clearAuth();
                      setOpen(false);
                    }}
                    className="block w-full text-start px-3 py-2 rounded-md text-sm font-medium hover:bg-accent"
                  >
                    {t("logout")}
                  </button>
                </>
              ) : (
                <>
                  <Link
                    href={localePath("/auth/login")}
                    onClick={() => setOpen(false)}
                    className="block px-3 py-2 rounded-md text-sm font-medium hover:bg-accent"
                  >
                    {t("login")}
                  </Link>
                  <Link
                    href={localePath("/auth/register")}
                    onClick={() => setOpen(false)}
                    className="block px-3 py-2 rounded-md text-sm font-medium hover:bg-accent"
                  >
                    {t("register")}
                  </Link>
                </>
              )}
            </div>
          </div>
        </div>
      )}
    </header>
  );
}
