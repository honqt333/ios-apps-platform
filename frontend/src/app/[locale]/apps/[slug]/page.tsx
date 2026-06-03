import { notFound } from "next/navigation";
import { getTranslations } from "next-intl/server";
import Image from "next/image";
import { Link } from "@/i18n/routing";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent } from "@/components/ui/card";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { ScreenshotsCarousel } from "@/components/apps/ScreenshotsCarousel";
import { InstallButton } from "@/components/apps/InstallButton";
import {
  Download,
  Calendar,
  HardDrive,
  Code2,
  Smartphone,
  ArrowLeft,
  Building2,
  Tag,
} from "lucide-react";
import { appsService } from "@/services/appsService";
import { formatBytes, formatNumber, formatDate, formatRelativeTime } from "@/lib/utils";
import { AppGrid } from "@/components/apps/AppGrid";

interface PageProps {
  params: Promise<{ locale: string; slug: string }>;
}

export default async function AppDetailPage({ params }: PageProps) {
  const { locale, slug } = await params;
  const t = await getTranslations("apps");

  let app: Awaited<ReturnType<typeof appsService.get>> | null = null;
  try {
    app = await appsService.get(slug);
  } catch {
    notFound();
  }

  if (!app) notFound();

  const manifestUrl = app.manifest_path
    ? `${process.env.NEXT_PUBLIC_API_URL?.replace("/api", "")}/${app.manifest_path}`
    : "";

  return (
    <div className="container mx-auto py-6">
      <Link
        href="/apps"
        className="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground mb-4"
      >
        <ArrowLeft className="h-4 w-4 rtl:rotate-180" />
        {t("title")}
      </Link>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {/* Main content */}
        <div className="lg:col-span-2 space-y-6">
          <div className="flex items-start gap-4">
            <div className="relative h-24 w-24 sm:h-32 sm:w-32 rounded-2xl overflow-hidden bg-gradient-to-br from-primary/30 to-primary/10 flex items-center justify-center shrink-0">
              {app.icon_url ? (
                <Image src={app.icon_url} alt={app.name} fill className="object-cover" />
              ) : (
                <Smartphone className="h-12 w-12 text-primary" />
              )}
            </div>
            <div className="flex-1 min-w-0">
              <h1 className="text-2xl sm:text-3xl font-bold leading-tight">{app.name}</h1>
              <p className="text-muted-foreground mt-1">{app.developer}</p>
              <div className="flex flex-wrap items-center gap-2 mt-3">
                <Badge variant="secondary">v{app.version}</Badge>
                {app.is_featured && <Badge className="bg-amber-500 text-white">Featured</Badge>}
                {!app.is_installable && <Badge variant="destructive">{t("not_installable")}</Badge>}
                <span className="flex items-center gap-1 text-xs text-muted-foreground">
                  <Download className="h-3 w-3" />
                  {formatNumber(app.downloads_count)}
                </span>
              </div>
            </div>
          </div>

          {/* Install */}
          {app.is_installable && (
            <Card>
              <CardContent className="p-4 flex flex-col sm:flex-row items-center gap-3">
                <div className="flex-1 text-sm">
                  <p className="font-medium">{t("install")}</p>
                  <p className="text-muted-foreground text-xs mt-1">{t("install_instructions")}</p>
                </div>
                <InstallButton appSlug={app.slug} installUrl={app.install_url} />
              </CardContent>
            </Card>
          )}

          <Tabs defaultValue="about" className="w-full">
            <TabsList>
              <TabsTrigger value="about">About</TabsTrigger>
              <TabsTrigger value="screenshots">{t("screenshots")}</TabsTrigger>
              <TabsTrigger value="changelog">{t("changelog")}</TabsTrigger>
              <TabsTrigger value="versions">{t("changelog_history")}</TabsTrigger>
            </TabsList>

            <TabsContent value="about" className="space-y-4">
              {app.long_description ? (
                <p className="whitespace-pre-wrap leading-relaxed">{app.long_description}</p>
              ) : (
                <p className="text-muted-foreground">{app.description}</p>
              )}

              <div className="grid grid-cols-2 sm:grid-cols-3 gap-3 pt-4">
                <InfoItem icon={Building2} label={t("developer")} value={app.developer} />
                <InfoItem icon={Tag} label={t("category")} value={app.category?.name} />
                <InfoItem icon={Code2} label={t("bundle_id")} value={app.bundle_id} />
                <InfoItem icon={Smartphone} label={t("minimum_ios")} value={app.minimum_ios_version} />
                <InfoItem
                  icon={HardDrive}
                  label={t("size")}
                  value={
                    app.file_size_human ||
                    (app.ipa_size_bytes ? formatBytes(app.ipa_size_bytes) : "—")
                  }
                />
                <InfoItem
                  icon={Calendar}
                  label={t("last_updated")}
                  value={formatRelativeTime(app.updated_at, locale)}
                />
              </div>
            </TabsContent>

            <TabsContent value="screenshots">
              <ScreenshotsCarousel screenshots={app.screenshots || []} />
            </TabsContent>

            <TabsContent value="changelog">
              {app.changelog ? (
                <pre className="whitespace-pre-wrap font-sans text-sm leading-relaxed bg-muted p-4 rounded-lg">
                  {app.changelog}
                </pre>
              ) : (
                <p className="text-muted-foreground">—</p>
              )}
            </TabsContent>

            <TabsContent value="versions" className="space-y-3">
              {app.changelog_history && app.changelog_history.length > 0 ? (
                app.changelog_history.map((v, i) => (
                  <Card key={i}>
                    <CardContent className="p-4">
                      <div className="flex items-center justify-between mb-2">
                        <Badge>v{v.version}</Badge>
                        <span className="text-xs text-muted-foreground">{v.date}</span>
                      </div>
                      <p className="text-sm whitespace-pre-wrap">{v.notes}</p>
                    </CardContent>
                  </Card>
                ))
              ) : (
                <p className="text-muted-foreground">—</p>
              )}
            </TabsContent>
          </Tabs>
        </div>

        {/* Sidebar */}
        <aside className="space-y-4">
          <Card>
            <CardContent className="p-5 space-y-3">
              <h3 className="font-semibold">Information</h3>
              <SidebarItem label={t("version")} value={app.version} />
              {app.build_number && <SidebarItem label={t("build")} value={app.build_number} />}
              <SidebarItem
                label={t("size")}
                value={
                  app.file_size_human ||
                  (app.ipa_size_bytes ? formatBytes(app.ipa_size_bytes) : "—")
                }
              />
              <SidebarItem label={t("minimum_ios")} value={app.minimum_ios_version} />
              <SidebarItem label={t("bundle_id")} value={app.bundle_id} />
              <SidebarItem label={t("developer")} value={app.developer} />
              <SidebarItem
                label={t("last_updated")}
                value={formatDate(app.updated_at, locale)}
              />
            </CardContent>
          </Card>
        </aside>
      </div>
    </div>
  );
}

function InfoItem({ icon: Icon, label, value }: { icon: any; label: string; value?: string | null }) {
  return (
    <div className="flex items-start gap-2 p-3 rounded-lg bg-muted/30">
      <Icon className="h-4 w-4 mt-0.5 text-muted-foreground shrink-0" />
      <div className="min-w-0">
        <p className="text-xs text-muted-foreground">{label}</p>
        <p className="text-sm font-medium truncate">{value || "—"}</p>
      </div>
    </div>
  );
}

function SidebarItem({ label, value }: { label: string; value?: string | null }) {
  return (
    <div className="flex items-center justify-between text-sm">
      <span className="text-muted-foreground">{label}</span>
      <span className="font-medium text-end">{value || "—"}</span>
    </div>
  );
}
