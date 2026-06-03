"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import { useLocale, useTranslations } from "next-intl";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { ArrowLeft, Loader2, Save, Plus, X, Upload as UploadIcon } from "lucide-react";
import { Link } from "@/i18n/routing";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Badge } from "@/components/ui/badge";
import { adminService } from "@/services/adminService";
import { categoriesService } from "@/services/appsService";
import { toast } from "sonner";
import type { Category, App } from "@/types";

const schema = z.object({
  name: z.string().min(2),
  developer: z.string().min(2),
  description: z.string().optional(),
  long_description: z.string().optional(),
  bundle_id: z.string().min(3),
  version: z.string().min(1),
  build_number: z.string().optional(),
  minimum_ios_version: z.string().min(1),
  category_id: z.string().optional(),
  changelog: z.string().optional(),
  is_active: z.boolean().default(true),
  is_featured: z.boolean().default(false),
});

type FormData = z.infer<typeof schema>;

export default function NewAppPage() {
  const t = useTranslations("admin");
  const router = useRouter();
  const [loading, setLoading] = useState(false);
  const [categories, setCategories] = useState<Category[]>([]);
  const [icon, setIcon] = useState<File | null>(null);
  const [screenshots, setScreenshots] = useState<File[]>([]);
  const [ipa, setIpa] = useState<File | null>(null);

  const {
    register,
    handleSubmit,
    formState: { errors },
    setValue,
    watch,
  } = useForm<FormData>({ resolver: zodResolver(schema) });

  useEffect(() => {
    categoriesService.list().then(setCategories).catch(() => setCategories([]));
  }, []);

  const onSubmit = async (data: FormData) => {
    setLoading(true);
    try {
      const form = new FormData();
      Object.entries(data).forEach(([k, v]) => {
        if (v !== undefined && v !== null) form.append(k, String(v));
      });
      if (icon) form.append("icon", icon);
      screenshots.forEach((f) => form.append("screenshots[]", f));
      if (ipa) form.append("ipa", ipa);

      const app: App = await adminService.createApp(form);
      toast.success("App created");
      router.push(`/admin/apps/${app.id}`);
    } catch (e: any) {
      toast.error(e?.response?.data?.message || "Failed to create app");
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <Link href="/admin/apps">
          <Button variant="ghost" size="icon">
            <ArrowLeft className="h-4 w-4 rtl:rotate-180" />
          </Button>
        </Link>
        <h1 className="text-3xl font-bold">{t("add_app")}</h1>
      </div>

      <form onSubmit={handleSubmit(onSubmit)} className="space-y-6">
        <Card>
          <CardHeader>
            <CardTitle>Basic Information</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div className="space-y-1.5">
                <Label htmlFor="name">{t("app_name")}</Label>
                <Input id="name" {...register("name")} />
                {errors.name && <p className="text-xs text-destructive">{errors.name.message}</p>}
              </div>
              <div className="space-y-1.5">
                <Label htmlFor="developer">Developer</Label>
                <Input id="developer" {...register("developer")} />
                {errors.developer && <p className="text-xs text-destructive">{errors.developer.message}</p>}
              </div>
            </div>

            <div className="space-y-1.5">
              <Label htmlFor="description">Description</Label>
              <Textarea id="description" rows={3} {...register("description")} />
            </div>

            <div className="space-y-1.5">
              <Label htmlFor="long_description">Full Description</Label>
              <Textarea id="long_description" rows={6} {...register("long_description")} />
            </div>

            <div className="space-y-1.5">
              <Label htmlFor="category_id">Category</Label>
              <Select onValueChange={(v) => setValue("category_id", v)}>
                <SelectTrigger>
                  <SelectValue placeholder="Select category" />
                </SelectTrigger>
                <SelectContent>
                  {categories.map((c) => (
                    <SelectItem key={c.id} value={String(c.id)}>
                      {c.name}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>iOS Metadata</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div className="space-y-1.5">
                <Label htmlFor="bundle_id">{t("app_bundle_id")}</Label>
                <Input id="bundle_id" placeholder="com.example.app" {...register("bundle_id")} />
                {errors.bundle_id && <p className="text-xs text-destructive">{errors.bundle_id.message}</p>}
              </div>
              <div className="space-y-1.5">
                <Label htmlFor="version">{t("app_version")}</Label>
                <Input id="version" placeholder="1.0.0" {...register("version")} />
                {errors.version && <p className="text-xs text-destructive">{errors.version.message}</p>}
              </div>
              <div className="space-y-1.5">
                <Label htmlFor="build_number">Build Number</Label>
                <Input id="build_number" placeholder="1" {...register("build_number")} />
              </div>
              <div className="space-y-1.5">
                <Label htmlFor="minimum_ios_version">{t("app_minimum_ios")}</Label>
                <Input id="minimum_ios_version" placeholder="15.0" {...register("minimum_ios_version")} />
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Files</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="space-y-1.5">
              <Label>{t("app_icon")}</Label>
              <Input
                type="file"
                accept="image/*"
                onChange={(e) => setIcon(e.target.files?.[0] || null)}
              />
              {icon && <Badge variant="secondary">{icon.name}</Badge>}
            </div>

            <div className="space-y-1.5">
              <Label>{t("app_screenshots")}</Label>
              <Input
                type="file"
                accept="image/*"
                multiple
                onChange={(e) => setScreenshots(Array.from(e.target.files || []))}
              />
              {screenshots.length > 0 && (
                <div className="flex flex-wrap gap-2 mt-2">
                  {screenshots.map((f, i) => (
                    <Badge key={i} variant="secondary">
                      {f.name}
                      <button
                        type="button"
                        onClick={() => setScreenshots((prev) => prev.filter((_, j) => j !== i))}
                        className="ms-2"
                      >
                        <X className="h-3 w-3" />
                      </button>
                    </Badge>
                  ))}
                </div>
              )}
            </div>

            <div className="space-y-1.5">
              <Label>{t("app_ipa")}</Label>
              <Input
                type="file"
                accept=".ipa"
                onChange={(e) => setIpa(e.target.files?.[0] || null)}
              />
              {ipa && <Badge variant="secondary">{ipa.name}</Badge>}
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>{t("app_changelog")}</CardTitle>
          </CardHeader>
          <CardContent>
            <Textarea rows={4} {...register("changelog")} placeholder="What's new in this version?" />
          </CardContent>
        </Card>

        <div className="flex items-center gap-3">
          <label className="flex items-center gap-2 text-sm">
            <input type="checkbox" {...register("is_active")} />
            {t("app_active")}
          </label>
          <label className="flex items-center gap-2 text-sm">
            <input type="checkbox" {...register("is_featured")} />
            {t("app_featured")}
          </label>
        </div>

        <div className="flex gap-2">
          <Button type="submit" size="lg" disabled={loading}>
            {loading ? <Loader2 className="h-4 w-4 me-2 animate-spin" /> : <Save className="h-4 w-4 me-2" />}
            Create
          </Button>
          <Link href="/admin/apps">
            <Button type="button" variant="outline" size="lg">
              Cancel
            </Button>
          </Link>
        </div>
      </form>
    </div>
  );
}
