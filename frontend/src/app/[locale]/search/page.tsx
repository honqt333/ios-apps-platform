import { getTranslations } from "next-intl/server";
import { Card, CardContent } from "@/components/ui/card";
import { AppGrid } from "@/components/apps/AppGrid";
import { SearchFilters } from "@/components/apps/SearchFilters";
import { appsService } from "@/services/appsService";
import type { App, SortOption } from "@/types";

interface PageProps {
  searchParams: Promise<{
    q?: string;
    category?: string;
    developer?: string;
    sort?: string;
    page?: string;
  }>;
}

export default async function SearchPage({ searchParams }: PageProps) {
  const sp = await searchParams;
  const t = await getTranslations("search");

  let apps: App[] = [];
  let total = 0;

  try {
    const res = await appsService.search({
      q: sp.q || "",
      category: sp.category,
      developer: sp.developer,
      sort: (sp.sort as SortOption) || "newest",
      per_page: 24,
      page: sp.page ? Number(sp.page) : 1,
    });
    apps = res.data;
    total = res.meta.total;
  } catch (e) {
    apps = [];
  }

  return (
    <div className="container mx-auto py-8 space-y-6">
      <h1 className="text-3xl font-bold">{t("title")}</h1>

      <SearchFilters
        initialQ={sp.q}
        initialCategory={sp.category}
        initialSort={(sp.sort as SortOption) || "newest"}
      />

      {sp.q && (
        <p className="text-muted-foreground">
          {total > 0 ? t("results_for", { query: sp.q }) : t("no_results", { query: sp.q })}
        </p>
      )}

      {apps.length === 0 ? (
        <Card>
          <CardContent className="p-12 text-center text-muted-foreground">—</CardContent>
        </Card>
      ) : (
        <AppGrid apps={apps} columns={4} />
      )}
    </div>
  );
}
