"use client";

import { useEffect, useState } from "react";
import { useLocale, useTranslations } from "next-intl";
import { Plus, Search, MoreVertical, Archive, Trash2, Edit, Power, Eye, Download } from "lucide-react";
import Image from "next/image";
import { Link } from "@/i18n/routing";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent } from "@/components/ui/card";
import {
  DropdownMenu,
  DropdownMenuTrigger,
  DropdownMenuContent,
  DropdownMenuItem,
} from "@/components/ui/dropdown-menu";
import { adminService } from "@/services/adminService";
import { useAuthStore } from "@/stores/authStore";
import type { App } from "@/types";
import { formatNumber, formatRelativeTime } from "@/lib/utils";
import { toast } from "sonner";

export default function AdminAppsPage() {
  const t = useTranslations("admin");
  const locale = useLocale();
  const token = useAuthStore((s) => s.token);

  const [apps, setApps] = useState<App[]>([]);
  const [meta, setMeta] = useState({ total: 0, last_page: 1, current_page: 1 });
  const [search, setSearch] = useState("");
  const [page, setPage] = useState(1);
  const [loading, setLoading] = useState(true);

  const fetch_ = async () => {
    setLoading(true);
    try {
      const res = await adminService.listApps({
        q: search || undefined,
        page,
        per_page: 20,
      });
      setApps(res.data);
      setMeta({ total: res.meta.total, last_page: res.meta.last_page, current_page: res.meta.current_page });
    } catch (e) {
      setApps([]);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    if (token) fetch_();
  }, [token, page, search]);

  const onAction = async (id: number, action: "delete" | "archive" | "toggle") => {
    try {
      if (action === "delete") {
        await adminService.deleteApp(id);
        toast.success("App deleted");
      } else if (action === "archive") {
        await adminService.archiveApp(id);
        toast.success("App updated");
      } else if (action === "toggle") {
        await adminService.toggleActiveApp(id);
        toast.success("App updated");
      }
      fetch_();
    } catch (e: any) {
      toast.error(e?.response?.data?.message || "Action failed");
    }
  };

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <h1 className="text-3xl font-bold">{t("apps")}</h1>
        <Link href="/admin/apps/new">
          <Button>
            <Plus className="h-4 w-4 me-2" />
            {t("add_app")}
          </Button>
        </Link>
      </div>

      <div className="flex items-center gap-2">
        <div className="relative flex-1 max-w-sm">
          <Search className="absolute start-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
          <Input
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            placeholder="Search..."
            className="ps-10"
          />
        </div>
      </div>

      <Card>
        <CardContent className="p-0">
          <div className="overflow-x-auto">
            <table className="w-full text-sm">
              <thead className="bg-muted/50 text-xs uppercase">
                <tr>
                  <th className="text-start p-3">App</th>
                  <th className="text-start p-3">Category</th>
                  <th className="text-start p-3">Version</th>
                  <th className="text-start p-3">Downloads</th>
                  <th className="text-start p-3">Status</th>
                  <th className="text-start p-3">Updated</th>
                  <th className="text-end p-3">Actions</th>
                </tr>
              </thead>
              <tbody>
                {loading ? (
                  <tr>
                    <td colSpan={7} className="text-center py-12 text-muted-foreground">Loading...</td>
                  </tr>
                ) : apps.length === 0 ? (
                  <tr>
                    <td colSpan={7} className="text-center py-12 text-muted-foreground">No apps</td>
                  </tr>
                ) : (
                  apps.map((app) => (
                    <tr key={app.id} className="border-t hover:bg-muted/30">
                      <td className="p-3">
                        <div className="flex items-center gap-2">
                          <div className="h-10 w-10 rounded-lg overflow-hidden bg-muted shrink-0">
                            {app.icon_url ? (
                              <Image src={app.icon_url} alt={app.name} width={40} height={40} className="object-cover" />
                            ) : (
                              <div className="h-full w-full bg-gradient-to-br from-primary/30 to-primary/10" />
                            )}
                          </div>
                          <div className="min-w-0">
                            <p className="font-medium truncate">{app.name}</p>
                            <p className="text-xs text-muted-foreground truncate">{app.bundle_id}</p>
                          </div>
                        </div>
                      </td>
                      <td className="p-3 text-muted-foreground">{app.category?.name || "—"}</td>
                      <td className="p-3">v{app.version}</td>
                      <td className="p-3">{formatNumber(app.downloads_count)}</td>
                      <td className="p-3">
                        <div className="flex flex-wrap gap-1">
                          {app.is_archived ? (
                            <Badge variant="warning">Archived</Badge>
                          ) : app.is_active ? (
                            <Badge variant="success">Active</Badge>
                          ) : (
                            <Badge variant="secondary">Inactive</Badge>
                          )}
                          {app.is_featured && <Badge className="bg-amber-500 text-white">Featured</Badge>}
                        </div>
                      </td>
                      <td className="p-3 text-muted-foreground text-xs">
                        {formatRelativeTime(app.updated_at, locale)}
                      </td>
                      <td className="p-3 text-end">
                        <DropdownMenu>
                          <DropdownMenuTrigger asChild>
                            <Button variant="ghost" size="icon">
                              <MoreVertical className="h-4 w-4" />
                            </Button>
                          </DropdownMenuTrigger>
                          <DropdownMenuContent align="end">
                            <DropdownMenuItem asChild>
                              <Link href={`/admin/apps/${app.id}`}>
                                <Edit className="h-4 w-4 me-2" />
                                Edit
                              </Link>
                            </DropdownMenuItem>
                            <DropdownMenuItem asChild>
                              <Link href={`/apps/${app.slug}`}>
                                <Eye className="h-4 w-4 me-2" />
                                View
                              </Link>
                            </DropdownMenuItem>
                            <DropdownMenuItem onClick={() => onAction(app.id, "toggle")}>
                              <Power className="h-4 w-4 me-2" />
                              Toggle active
                            </DropdownMenuItem>
                            <DropdownMenuItem onClick={() => onAction(app.id, "archive")}>
                              <Archive className="h-4 w-4 me-2" />
                              {app.is_archived ? "Unarchive" : "Archive"}
                            </DropdownMenuItem>
                            <DropdownMenuItem
                              onClick={() => {
                                if (confirm("Delete this app?")) onAction(app.id, "delete");
                              }}
                              className="text-destructive"
                            >
                              <Trash2 className="h-4 w-4 me-2" />
                              Delete
                            </DropdownMenuItem>
                          </DropdownMenuContent>
                        </DropdownMenu>
                      </td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>
        </CardContent>
      </Card>

      {meta.last_page > 1 && (
        <div className="flex items-center justify-center gap-1">
          {Array.from({ length: meta.last_page }, (_, i) => i + 1).map((p) => (
            <Button
              key={p}
              variant={p === meta.current_page ? "default" : "outline"}
              size="sm"
              onClick={() => setPage(p)}
            >
              {p}
            </Button>
          ))}
        </div>
      )}
    </div>
  );
}
