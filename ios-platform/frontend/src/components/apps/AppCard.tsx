"use client";

import Link from "next/link";
import Image from "next/image";
import { useLocale, useTranslations } from "next-intl";
import { Download, Star, Smartphone } from "lucide-react";
import { Card, CardContent } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { formatNumber, formatRelativeTime, cn } from "@/lib/utils";
import type { App as AppType } from "@/types";

interface AppCardProps {
  app: AppType;
  variant?: "default" | "compact";
  className?: string;
}

export function AppCard({ app, variant = "default", className }: AppCardProps) {
  const t = useTranslations("apps");
  const locale = useLocale();

  const localePath = (path: string) => `/${locale}${path}`;

  return (
    <Link href={localePath(`/apps/${app.slug}`)} className={cn("group", className)}>
      <Card className="overflow-hidden h-full hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
        <CardContent className={cn("p-0", variant === "compact" ? "p-3" : "p-4")}>
          <div className="flex items-start gap-3">
            {/* Icon */}
            <div
              className={cn(
                "relative shrink-0 overflow-hidden rounded-2xl bg-gradient-to-br from-primary/30 to-primary/10 flex items-center justify-center",
                variant === "compact" ? "h-14 w-14" : "h-20 w-20"
              )}
            >
              {app.icon_url ? (
                <Image
                  src={app.icon_url}
                  alt={app.name}
                  width={80}
                  height={80}
                  className="object-cover w-full h-full"
                />
              ) : (
                <Smartphone className="h-8 w-8 text-primary" />
              )}
              {app.is_featured && (
                <Badge className="absolute top-1 right-1 px-1.5 py-0 text-[10px] bg-amber-500 text-white">
                  <Star className="h-2.5 w-2.5 fill-current" />
                </Badge>
              )}
            </div>

            {/* Info */}
            <div className="flex-1 min-w-0">
              <h3 className={cn("font-semibold leading-tight line-clamp-1 group-hover:text-primary transition-colors",
                variant === "compact" ? "text-sm" : "text-base")}>
                {app.name}
              </h3>
              <p className="text-xs text-muted-foreground line-clamp-1 mt-0.5">
                {app.developer}
              </p>
              {!variant.includes("compact") && app.description && (
                <p className="text-xs text-muted-foreground line-clamp-2 mt-1.5">
                  {app.description}
                </p>
              )}

              <div className="flex items-center gap-2 mt-2 text-xs">
                <Badge variant="secondary" className="text-[10px] px-1.5 py-0">
                  v{app.version}
                </Badge>
                <span className="flex items-center gap-1 text-muted-foreground">
                  <Download className="h-3 w-3" />
                  {formatNumber(app.downloads_count)}
                </span>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </Link>
  );
}
