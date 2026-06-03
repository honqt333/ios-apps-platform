import { getTranslations } from "next-intl/server";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Smartphone, Download, Users, Archive, FolderTree, TrendingUp } from "lucide-react";
import { adminService } from "@/services/adminService";
import { AppCard } from "@/components/apps/AppCard";
import { formatNumber } from "@/lib/utils";

export default async function AdminDashboardPage() {
  const t = await getTranslations("admin");

  let stats: any = null;
  try {
    stats = await adminService.stats();
  } catch (e) {
    stats = null;
  }

  return (
    <div className="space-y-6">
      <h1 className="text-3xl font-bold">{t("dashboard")}</h1>

      <div className="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3">
        <StatCard icon={Smartphone} label={t("total_apps")} value={stats?.totals?.apps ?? 0} color="blue" />
        <StatCard icon={TrendingUp} label={t("active_apps")} value={stats?.totals?.active_apps ?? 0} color="green" />
        <StatCard icon={Archive} label={t("archived_apps")} value={stats?.totals?.archived_apps ?? 0} color="amber" />
        <StatCard icon={FolderTree} label={t("total_users") === t("total_users") ? "Categories" : "Categories"} value={stats?.totals?.categories ?? 0} color="purple" />
        <StatCard icon={Users} label={t("total_users")} value={stats?.totals?.users ?? 0} color="indigo" />
        <StatCard icon={Download} label={t("total_downloads")} value={formatNumber(stats?.totals?.downloads ?? 0)} color="rose" />
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <Card>
          <CardHeader>
            <CardTitle>{t("recent_apps")}</CardTitle>
          </CardHeader>
          <CardContent className="space-y-2">
            {stats?.recent_apps?.length > 0 ? (
              stats.recent_apps.map((app: any) => <AppCard key={app.id} app={app} variant="compact" />)
            ) : (
              <p className="text-muted-foreground text-sm py-4 text-center">—</p>
            )}
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>{t("top_apps")}</CardTitle>
          </CardHeader>
          <CardContent className="space-y-2">
            {stats?.top_apps?.length > 0 ? (
              stats.top_apps.map((app: any) => <AppCard key={app.id} app={app} variant="compact" />)
            ) : (
              <p className="text-muted-foreground text-sm py-4 text-center">—</p>
            )}
          </CardContent>
        </Card>
      </div>
    </div>
  );
}

function StatCard({ icon: Icon, label, value, color }: any) {
  const colors: Record<string, string> = {
    blue: "from-blue-500/20 to-blue-500/5 text-blue-600 dark:text-blue-400",
    green: "from-emerald-500/20 to-emerald-500/5 text-emerald-600 dark:text-emerald-400",
    amber: "from-amber-500/20 to-amber-500/5 text-amber-600 dark:text-amber-400",
    purple: "from-purple-500/20 to-purple-500/5 text-purple-600 dark:text-purple-400",
    indigo: "from-indigo-500/20 to-indigo-500/5 text-indigo-600 dark:text-indigo-400",
    rose: "from-rose-500/20 to-rose-500/5 text-rose-600 dark:text-rose-400",
  };

  return (
    <Card>
      <CardContent className={`p-4 bg-gradient-to-br ${colors[color] || ""}`}>
        <div className="flex items-center justify-between mb-2">
          <Icon className="h-5 w-5" />
        </div>
        <p className="text-2xl font-bold">{value}</p>
        <p className="text-xs opacity-80 mt-1">{label}</p>
      </CardContent>
    </Card>
  );
}
