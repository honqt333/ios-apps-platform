"use client";

import { useEffect, useState } from "react";
import { useLocale, useTranslations } from "next-intl";
import { Loader2 } from "lucide-react";
import { Card, CardContent } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { adminService } from "@/services/adminService";
import { formatRelativeTime } from "@/lib/utils";

export default function ActivityPage() {
  const t = useTranslations("admin");
  const locale = useLocale();
  const [logs, setLogs] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    adminService
      .getActivityLogs({ per_page: 50 })
      .then((res) => setLogs(res.data))
      .finally(() => setLoading(false));
  }, []);

  return (
    <div className="space-y-4">
      <h1 className="text-3xl font-bold">{t("activity")}</h1>

      <Card>
        <CardContent className="p-0">
          {loading ? (
            <div className="p-12 text-center"><Loader2 className="h-5 w-5 animate-spin mx-auto" /></div>
          ) : logs.length === 0 ? (
            <p className="p-12 text-center text-muted-foreground">—</p>
          ) : (
            <ul className="divide-y">
              {logs.map((log) => (
                <li key={log.id} className="p-4 flex items-start gap-3">
                  <div className="h-2 w-2 rounded-full bg-primary mt-2" />
                  <div className="flex-1 min-w-0">
                    <p className="text-sm font-medium">{log.description}</p>
                    <div className="flex flex-wrap items-center gap-2 mt-1 text-xs text-muted-foreground">
                      {log.causer && <span>by {log.causer.name}</span>}
                      {log.subject_type && (
                        <Badge variant="outline" className="text-[10px]">
                          {log.subject_type.split("\\").pop()}
                        </Badge>
                      )}
                      <span>{formatRelativeTime(log.created_at, locale)}</span>
                    </div>
                  </div>
                </li>
              ))}
            </ul>
          )}
        </CardContent>
      </Card>
    </div>
  );
}
