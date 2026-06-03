"use client";

import { useState, useEffect } from "react";
import { useRouter, useSearchParams } from "next/navigation";
import { useLocale, useTranslations } from "next-intl";
import { Search, X } from "lucide-react";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Badge } from "@/components/ui/badge";
import { categoriesService } from "@/services/appsService";
import type { Category, SortOption } from "@/types";

interface SearchFiltersProps {
  initialQ?: string;
  initialCategory?: string;
  initialSort?: SortOption;
  showFilters?: boolean;
}

export function SearchFilters({ initialQ, initialCategory, initialSort, showFilters = true }: SearchFiltersProps) {
  const t = useTranslations("search");
  const locale = useLocale();
  const router = useRouter();
  const searchParams = useSearchParams();

  const [q, setQ] = useState(initialQ || "");
  const [category, setCategory] = useState(initialCategory || "all");
  const [sort, setSort] = useState<SortOption>(initialSort || "newest");
  const [categories, setCategories] = useState<Category[]>([]);

  useEffect(() => {
    categoriesService.list().then(setCategories).catch(() => setCategories([]));
  }, []);

  const apply = () => {
    const params = new URLSearchParams();
    if (q) params.set("q", q);
    if (category && category !== "all") params.set("category", category);
    if (sort) params.set("sort", sort);
    router.push(`/${locale}/search?${params.toString()}`);
  };

  const onKey = (e: React.KeyboardEvent) => {
    if (e.key === "Enter") apply();
  };

  const clear = () => {
    setQ("");
    setCategory("all");
    setSort("newest");
    router.push(`/${locale}/search`);
  };

  return (
    <div className="space-y-3">
      <div className="flex gap-2">
        <div className="relative flex-1">
          <Search className="absolute start-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
          <Input
            value={q}
            onChange={(e) => setQ(e.target.value)}
            onKeyDown={onKey}
            placeholder={t("placeholder")}
            className="ps-10 h-12 text-base"
          />
          {q && (
            <button
              onClick={() => setQ("")}
              className="absolute end-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
            >
              <X className="h-4 w-4" />
            </button>
          )}
        </div>
        <Button onClick={apply} size="lg">{t("title")}</Button>
      </div>

      {showFilters && (
        <div className="flex flex-wrap items-center gap-2">
          <Select value={category} onValueChange={setCategory}>
            <SelectTrigger className="w-[180px]">
              <SelectValue placeholder={t("category")} />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">{t("category")}</SelectItem>
              {categories.map((c) => (
                <SelectItem key={c.id} value={String(c.id)}>
                  {c.name}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>

          <Select value={sort} onValueChange={(v) => setSort(v as SortOption)}>
            <SelectTrigger className="w-[180px]">
              <SelectValue placeholder={t("sort_by")} />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="newest">{t("sort_newest")}</SelectItem>
              <SelectItem value="downloads">{t("sort_downloads")}</SelectItem>
              <SelectItem value="name">{t("sort_name")}</SelectItem>
            </SelectContent>
          </Select>

          {(q || category !== "all" || sort !== "newest") && (
            <Button variant="ghost" size="sm" onClick={clear}>
              <X className="h-3 w-3 me-1" />
              {t("title")}
            </Button>
          )}
        </div>
      )}
    </div>
  );
}
