import { getTranslations } from "next-intl/server";
import { Card, CardContent } from "@/components/ui/card";
import { Smartphone, Film, Music, Book, Code } from "lucide-react";
import { Link } from "@/i18n/routing";
import { categoriesService } from "@/services/appsService";
import type { Category } from "@/types";

const iconMap: Record<string, any> = {
  briefcase: Smartphone,
  gamepad: Film,
  wrench: Smartphone,
  users: Smartphone,
  book: Book,
  film: Film,
  heart: Smartphone,
  camera: Smartphone,
  music: Music,
  code: Code,
};

export default async function CategoriesPage() {
  const t = await getTranslations("nav");

  let categories: Category[] = [];
  try {
    categories = await categoriesService.list();
  } catch (e) {
    categories = [];
  }

  return (
    <div className="container mx-auto py-8 space-y-6">
      <h1 className="text-3xl font-bold">{t("categories")}</h1>

      {categories.length === 0 ? (
        <Card>
          <CardContent className="p-12 text-center text-muted-foreground">No categories</CardContent>
        </Card>
      ) : (
        <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
          {categories.map((c) => {
            const Icon = iconMap[c.icon || ""] || Smartphone;
            return (
              <Link key={c.id} href={`/apps?category=${c.id}`}>
                <Card className="hover:border-primary hover:shadow-lg transition-all cursor-pointer h-full">
                  <CardContent className="p-5 text-center space-y-2">
                    <div
                      className="h-14 w-14 rounded-2xl mx-auto flex items-center justify-center"
                      style={{
                        backgroundColor: (c.color || "#3B82F6") + "20",
                        color: c.color || "#3B82F6",
                      }}
                    >
                      <Icon className="h-7 w-7" />
                    </div>
                    <h3 className="font-medium text-sm">{c.name}</h3>
                    {c.description && (
                      <p className="text-xs text-muted-foreground line-clamp-2">{c.description}</p>
                    )}
                  </CardContent>
                </Card>
              </Link>
            );
          })}
        </div>
      )}
    </div>
  );
}
