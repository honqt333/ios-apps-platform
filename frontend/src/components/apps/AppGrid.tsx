"use client";

import { useTranslations } from "next-intl";
import { AppCard } from "./AppCard";
import type { App as AppType } from "@/types";

interface AppGridProps {
  apps: AppType[];
  emptyMessage?: string;
  columns?: 2 | 3 | 4 | 5 | 6;
}

export function AppGrid({ apps, emptyMessage, columns = 4 }: AppGridProps) {
  const t = useTranslations("apps");

  if (!apps || apps.length === 0) {
    return (
      <div className="text-center py-12 text-muted-foreground">
        {emptyMessage || t("no_apps")}
      </div>
    );
  }

  const colsClass = {
    2: "grid-cols-1 sm:grid-cols-2",
    3: "grid-cols-1 sm:grid-cols-2 lg:grid-cols-3",
    4: "grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4",
    5: "grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5",
    6: "grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6",
  }[columns];

  return (
    <div className={`grid gap-4 ${colsClass}`}>
      {apps.map((app) => (
        <AppCard key={app.id} app={app} />
      ))}
    </div>
  );
}
