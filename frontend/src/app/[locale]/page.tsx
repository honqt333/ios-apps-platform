import { getTranslations } from "next-intl/server";
import { Link } from "@/i18n/routing";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { AppGrid } from "@/components/apps/AppGrid";
import { Download, Search, Shield, Zap, Globe, Sparkles, ArrowRight } from "lucide-react";
import { appsService, categoriesService } from "@/services/appsService";
import { Smartphone, Camera, Music, Code } from "lucide-react";

const iconMap: Record<string, any> = {
  briefcase: Smartphone,
  gamepad: Zap,
  wrench: Shield,
  users: Globe,
  book: Search,
  film: Sparkles,
  heart: Shield,
  camera: Camera,
  music: Music,
  code: Code,
};

export default async function HomePage({ params }: { params: Promise<{ locale: string }> }) {
  const { locale } = await params;
  const t = await getTranslations("home");
  const tCommon = await getTranslations("common");
  const tApps = await getTranslations("apps");
  const tNav = await getTranslations("nav");

  // Fetch data in parallel
  const [featured, recent, categories] = await Promise.all([
    appsService.featured(8).catch(() => []),
    appsService.recent(8).catch(() => []),
    categoriesService.list().catch(() => []),
  ]);

  return (
    <div>
      {/* Hero */}
      <section className="relative overflow-hidden">
        <div className="absolute inset-0 -z-10 bg-gradient-to-b from-primary/10 via-background to-background" />
        <div className="absolute inset-0 -z-10 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-primary/20 via-transparent to-transparent" />
        <div className="container mx-auto pt-16 pb-20 text-center">
          <Badge className="mb-4" variant="outline">
            <Sparkles className="h-3 w-3 me-1" />
            {locale === "ar" ? "منصة حديثة" : "Modern platform"}
          </Badge>
          <h1 className="text-4xl sm:text-5xl md:text-7xl font-extrabold tracking-tight mb-6">
            <span className="block">{t("hero_title")}</span>
            <span className="block bg-gradient-to-r from-primary via-blue-500 to-purple-600 bg-clip-text text-transparent">
              {t("hero_subtitle")}
            </span>
          </h1>
          <p className="text-lg sm:text-xl text-muted-foreground max-w-2xl mx-auto mb-8">
            {t("hero_description")}
          </p>
          <div className="flex flex-wrap items-center justify-center gap-3">
            <Link href="/apps">
              <Button size="xl" className="rounded-full">
                {tNav("apps")}
                <ArrowRight className="h-4 w-4 ms-2 rtl:rotate-180" />
              </Button>
            </Link>
            <Link href="/search">
              <Button size="xl" variant="outline" className="rounded-full">
                <Search className="h-4 w-4 me-2" />
                {tCommon("search")}
              </Button>
            </Link>
          </div>
        </div>
      </section>

      {/* Featured */}
      {featured.length > 0 && (
        <section className="container mx-auto py-12">
          <div className="flex items-center justify-between mb-6">
            <h2 className="text-2xl sm:text-3xl font-bold">{t("featured_apps")}</h2>
            <Link href="/apps?sort=newest">
              <Button variant="ghost" size="sm">
                {tCommon("view_all")}
                <ArrowRight className="h-4 w-4 ms-2 rtl:rotate-180" />
              </Button>
            </Link>
          </div>
          <AppGrid apps={featured} columns={4} />
        </section>
      )}

      {/* Categories */}
      {categories.length > 0 && (
        <section className="container mx-auto py-12">
          <h2 className="text-2xl sm:text-3xl font-bold mb-6">{t("explore_categories")}</h2>
          <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
            {categories.slice(0, 10).map((c) => {
              const Icon = iconMap[c.icon || ""] || Smartphone;
              return (
                <Link key={c.id} href={`/apps?category=${c.id}`}>
                  <Card className="hover:border-primary hover:shadow-md transition-all cursor-pointer h-full">
                    <CardContent className="p-4 flex items-center gap-3">
                      <div
                        className="h-10 w-10 rounded-lg flex items-center justify-center"
                        style={{
                          backgroundColor: (c.color || "#3B82F6") + "20",
                          color: c.color || "#3B82F6",
                        }}
                      >
                        <Icon className="h-5 w-5" />
                      </div>
                      <div className="flex-1 min-w-0">
                        <p className="font-medium truncate text-sm">{c.name}</p>
                        <p className="text-xs text-muted-foreground">{c.apps_count ?? 0} apps</p>
                      </div>
                    </CardContent>
                  </Card>
                </Link>
              );
            })}
          </div>
        </section>
      )}

      {/* Recent */}
      {recent.length > 0 && (
        <section className="container mx-auto py-12">
          <div className="flex items-center justify-between mb-6">
            <h2 className="text-2xl sm:text-3xl font-bold">{t("recently_added")}</h2>
            <Link href="/apps">
              <Button variant="ghost" size="sm">
                {tCommon("view_all")}
                <ArrowRight className="h-4 w-4 ms-2 rtl:rotate-180" />
              </Button>
            </Link>
          </div>
          <AppGrid apps={recent} columns={4} />
        </section>
      )}

      {/* Features */}
      <section className="container mx-auto py-16">
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          {[
            {
              icon: Zap,
              title: locale === "ar" ? "تثبيت بنقرة واحدة" : "One-tap install",
              desc: locale === "ar" ? "تثبيت فوري عبر itms-services" : "Instant install via itms-services",
            },
            {
              icon: Shield,
              title: locale === "ar" ? "آمن وموثوق" : "Secure & trusted",
              desc: locale === "ar" ? "تنزيلات آمنة مع روابط موقّعة" : "Secure downloads with signed links",
            },
            {
              icon: Download,
              title: locale === "ar" ? "محدّث دائماً" : "Always updated",
              desc: locale === "ar" ? "تحديثات وسجل تغييرات لكل إصدار" : "Changelogs and updates per version",
            },
          ].map((f) => (
            <Card key={f.title} className="border-dashed">
              <CardContent className="p-6 text-center">
                <f.icon className="h-10 w-10 mx-auto mb-3 text-primary" />
                <h3 className="font-semibold mb-1">{f.title}</h3>
                <p className="text-sm text-muted-foreground">{f.desc}</p>
              </CardContent>
            </Card>
          ))}
        </div>
      </section>
    </div>
  );
}
