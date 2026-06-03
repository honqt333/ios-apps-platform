import { getTranslations } from "next-intl/server";
import { Link } from "@/i18n/routing";
import { AppGrid } from "@/components/apps/AppGrid";
import { SearchFilters } from "@/components/apps/SearchFilters";
import { Card, CardContent } from "@/components/ui/card";
import { appsService } from "@/services/appsService";
import type { App, SortOption } from "@/types";

interface PageProps {
  searchParams: Promise<{
    q?: string;
    category?: string;
    developer?: string;
    sort?: string;
    page?: string;
    per_page?: string;
  }>;
}

export default async function AppsPage({ searchParams }: PageProps) {
  const sp = await searchParams;
  const t = await getTranslations("apps");

  const filters = {
    q: sp.q,
    category: sp.category,
    developer: sp.developer,
    sort: (sp.sort as SortOption) || "newest",
  };

  let apps: App[] = [];
  let total = 0;
  let lastPage = 1;

  try {
    const res = await appsService.list({
      ...filters,
      per_page: 24,
      page: sp.page ? Number(sp.page) : 1,
    });
    apps = res.data;
    total = res.meta.total;
    lastPage = res.meta.last_page;
  } catch (e) {
    apps = [];
  }

  return (
    <div className="container mx-auto py-8 space-y-6">
      <div>
        <h1 className="text-3xl font-bold mb-2">{t("title")}</h1>
        <p className="text-muted-foreground">{total} apps</p>
      </div>

      <SearchFilters
        initialQ={sp.q}
        initialCategory={sp.category}
        initialSort={(sp.sort as SortOption) || "newest"}
        showFilters
      />

      {apps.length === 0 ? (
        <Card>
          <CardContent className="p-12 text-center text-muted-foreground">{t("no_apps")}</CardContent>
        </Card>
      ) : (
        <>
          <AppGrid apps={apps} columns={4} />

          {lastPage > 1 && (
            <div className="flex justify-center gap-2 pt-6">
              {Array.from({ length: lastPage }, (_, i) => i + 1).map((p) => {
                const params = new URLSearchParams();
                Object.entries(sp).forEach(([k, v]) => v && params.set(k, String(v)));
                params.set("page", String(p));
                return (
                  <Link
                    key={p}
                    href={`/apps?${params.toString()}`}
                    className={`h-9 px-3 inline-flex items-center justify-center rounded-md text-sm ${
                      p === Number(sp.page || 1) ? "bg-primary text-primary-foreground" : "hover:bg-accent"
                    }`}
                  >
                    {p}
                  </Link>
                );
              })}
            </div>
          )}
        </>
      )}
    </div>
  );
}
