"use client";

import { usePathname } from "next/navigation";
import { useLocale, useTranslations } from "next-intl";
import { Link } from "@/i18n/routing";
import {
  LayoutDashboard,
  Smartphone,
  FolderTree,
  Users,
  Activity,
  Settings,
  LogOut,
} from "lucide-react";
import { cn } from "@/lib/utils";
import { useAuthStore } from "@/stores/authStore";

const items = [
  { href: "/admin/dashboard", icon: LayoutDashboard, key: "dashboard" },
  { href: "/admin/apps", icon: Smartphone, key: "apps" },
  { href: "/admin/categories", icon: FolderTree, key: "categories" },
  { href: "/admin/users", icon: Users, key: "users" },
  { href: "/admin/activity", icon: Activity, key: "activity" },
  { href: "/admin/settings", icon: Settings, key: "settings" },
];

export function AdminSidebar() {
  const t = useTranslations("admin");
  const locale = useLocale();
  const pathname = usePathname();
  const clearAuth = useAuthStore((s) => s.clear);
  const user = useAuthStore((s) => s.user);

  return (
    <nav className="rounded-xl border bg-card p-2 space-y-1">
      {items.map((item) => {
        const Icon = item.icon;
        const href = `/${locale}${item.href}`;
        const isActive = pathname === href || pathname.startsWith(href + "/");
        return (
          <Link
            key={item.href}
            href={item.href}
            className={cn(
              "flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium transition-colors",
              isActive
                ? "bg-primary text-primary-foreground"
                : "text-muted-foreground hover:bg-accent hover:text-foreground"
            )}
          >
            <Icon className="h-4 w-4" />
            <span>{t(item.key)}</span>
          </Link>
        );
      })}

      <div className="pt-2 mt-2 border-t">
        <div className="px-3 py-2 text-xs text-muted-foreground">
          {user?.email}
        </div>
        <button
          onClick={clearAuth}
          className="w-full flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium text-destructive hover:bg-destructive/10"
        >
          <LogOut className="h-4 w-4" />
          Sign out
        </button>
      </div>
    </nav>
  );
}
