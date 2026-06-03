"use client";

import Link from "next/link";
import { useLocale, useTranslations } from "next-intl";
import { Github, Twitter, Mail } from "lucide-react";

export function Footer() {
  const t = useTranslations("common");
  const locale = useLocale();

  const localePath = (path: string) => `/${locale}${path === "/" ? "" : path}`;

  return (
    <footer className="border-t bg-card/30 mt-auto">
      <div className="container mx-auto py-10">
        <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-8">
          <div>
            <div className="flex items-center gap-2 font-bold text-lg mb-3">
              <div className="h-8 w-8 rounded-lg app-card-gradient flex items-center justify-center text-white text-sm font-extrabold">
                iP
              </div>
              <span>iOS Apps</span>
            </div>
            <p className="text-sm text-muted-foreground">
              {t("app_name")} — discover, install, manage.
            </p>
          </div>

          <div>
            <h4 className="font-semibold mb-3">Platform</h4>
            <ul className="space-y-2 text-sm text-muted-foreground">
              <li><Link href={localePath("/apps")} className="hover:text-primary">Apps</Link></li>
              <li><Link href={localePath("/categories")} className="hover:text-primary">Categories</Link></li>
              <li><Link href={localePath("/search")} className="hover:text-primary">Search</Link></li>
            </ul>
          </div>

          <div>
            <h4 className="font-semibold mb-3">Account</h4>
            <ul className="space-y-2 text-sm text-muted-foreground">
              <li><Link href={localePath("/auth/login")} className="hover:text-primary">Sign in</Link></li>
              <li><Link href={localePath("/auth/register")} className="hover:text-primary">Sign up</Link></li>
              <li><Link href={localePath("/admin/dashboard")} className="hover:text-primary">Admin</Link></li>
            </ul>
          </div>

          <div>
            <h4 className="font-semibold mb-3">Connect</h4>
            <div className="flex items-center gap-2">
              <a href="#" className="h-9 w-9 rounded-md hover:bg-accent flex items-center justify-center">
                <Github className="h-4 w-4" />
              </a>
              <a href="#" className="h-9 w-9 rounded-md hover:bg-accent flex items-center justify-center">
                <Twitter className="h-4 w-4" />
              </a>
              <a href="mailto:hello@platform.local" className="h-9 w-9 rounded-md hover:bg-accent flex items-center justify-center">
                <Mail className="h-4 w-4" />
              </a>
            </div>
          </div>
        </div>

        <div className="mt-8 pt-6 border-t text-center text-xs text-muted-foreground">
          © {new Date().getFullYear()} iOS Apps Platform. All rights reserved.
        </div>
      </div>
    </footer>
  );
}
