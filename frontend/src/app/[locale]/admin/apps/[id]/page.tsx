"use client";

import { useEffect, useState, use } from "react";
import { useRouter } from "next/navigation";
import { useLocale, useTranslations } from "next-intl";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { ArrowLeft, Loader2, Save, Trash2, Upload, X, Package } from "lucide-react";
import Image from "next/image";
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
import { formatBytes, formatDate } from "@/lib/utils";

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

export default function EditAppPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = use(params);
  const t = useTranslations("admin");
  const router = useRouter();
  const [loading, setLoading] = useState(false);
  const [app, setApp] = useState<App | null>(null);
  const [categories, setCategories] = useState<Category[]>([]);
  const [icon, setIcon] = useState<File | null>(null);
  const [screenshots, setScreenshots] = useState<File[]>([]);
  const [ipa, setIpa] = useState<File | null>(null);
  const [uploadingType, setUploadingType] = useState<"ipa" | "icon" | "screenshots" | null>(null);

  const {
    register,
    handleSubmit,
    formState: { errors },
    setValue,
    reset,
  } = useForm<FormData>({ resolver: zodResolver(schema) });

  useEffect(() => {
    Promise.all([
      adminService.getApp(id),
      categoriesService.list(),
    ])
      .then(([a, c]) => {
        setApp(a);
        setCategories(c);
        reset({
          name: a.name,
          developer: a.developer,
          description: a.description || "",
          long_description: a.long_description || "",
          bundle_id: a.bundle_id,
          version: a.version,
          build_number: a.build_number || "",
          minimum_ios_version: a.minimum_ios_version,
          category_id: a.category ? String(a.category.id) : undefined,
          changelog: a.changelog || "",
          is_active: a.is_active,
          is_featured: a.is_featured,
        });
      })
      .catch(() => toast.error("Failed to load app"));
  }, [id, reset]);

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

      // Use POST with _method=PUT for multipart (Laravel-style)
      form.append("_method", "PUT");
      const updated = await adminService.updateApp(id, form);
      setApp(updated);
      setIcon(null);
      setScreenshots([]);
      setIpa(null);
      toast.success("App updated");
    } catch (e: any) {
      toast.error(e?.response?.data?.message || "Update failed");
    } finally {
      setLoading(false);
    }
  };

  const handleQuickUpload = async (type: "ipa" | "icon" | "screenshots") => {
    if (!app) return;
    setUploadingType(type);
    try {
      if (type === "ipa" && ipa) {
        await adminService.uploadIpa(app.id, ipa);
        toast.success("IPA uploaded");
        setIpa(null);
      } else if (type === "icon" && icon) {
        await adminService.uploadIcon(app.id, icon);
        toast.success("Icon uploaded");
        setIcon(null);
      } else if (type === "screenshots" && screenshots.length > 0) {
        await adminService.uploadScreenshots(app.id, screenshots);
        toast.success("Screenshots uploaded");
        setScreenshots([]);
      }
      const a = await adminService.getApp(id);
      setApp(a);
    } catch (e: any) {
      toast.error(e?.response?.data?.message || "Upload failed");
    } finally {
      setUploadingType(null);
    }
  };

  if (!app) {
    return <div className="text-center py-12"><Loader2 className="h-8 w-8 animate-spin mx-auto" /></div>;
  }

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <Link href="/admin/apps">
          <Button variant="ghost" size="icon">
            <ArrowLeft className="h-4 w-4 rtl:rotate-180" />
          </Button>
        </Link>
        <div className="flex-1">
          <h1 className="text-2xl font-bold">{t("edit_app")}</h1>
          <p className="text-sm text-muted-foreground">{app.bundle_id}</p>
        </div>
        <Button
          variant="destructive"
          onClick={async () => {
            if (!confirm("Delete this app permanently?")) return;
            try {
              await adminService.deleteApp(id);
              toast.success("App deleted");
              router.push("/admin/apps");
            } catch (e: any) {
              toast.error("Delete failed");
            }
          }}
        >
          <Trash2 className="h-4 w-4 me-2" />
          Delete
        </Button>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div className="lg:col-span-2">
          <form onSubmit={handleSubmit(onSubmit)} className="space-y-6">
            <Card>
              <CardHeader><CardTitle>Basic Information</CardTitle></CardHeader>
              <CardContent className="space-y-4">
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div className="space-y-1.5">
                    <Label htmlFor="name">{t("app_name")}</Label>
                    <Input id="name" {...register("name")} />
                  </div>
                  <div className="space-y-1.5">
                    <Label htmlFor="developer">Developer</Label>
                    <Input id="developer" {...register("developer")} />
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
                  <Label>Category</Label>
                  <Select
                    defaultValue={app.category ? String(app.category.id) : undefined}
                    onValueChange={(v) => setValue("category_id", v)}
                  >
                    <SelectTrigger><SelectValue placeholder="Select category" /></SelectTrigger>
                    <SelectContent>
                      {categories.map((c) => (
                        <SelectItem key={c.id} value={String(c.id)}>{c.name}</SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardHeader><CardTitle>iOS Metadata</CardTitle></CardHeader>
              <CardContent className="space-y-4">
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div className="space-y-1.5">
                    <Label htmlFor="bundle_id">{t("app_bundle_id")}</Label>
                    <Input id="bundle_id" {...register("bundle_id")} />
                  </div>
                  <div className="space-y-1.5">
                    <Label htmlFor="version">{t("app_version")}</Label>
                    <Input id="version" {...register("version")} />
                  </div>
                  <div className="space-y-1.5">
                    <Label htmlFor="build_number">Build</Label>
                    <Input id="build_number" {...register("build_number")} />
                  </div>
                  <div className="space-y-1.5">
                    <Label htmlFor="minimum_ios_version">{t("app_minimum_ios")}</Label>
                    <Input id="minimum_ios_version" {...register("minimum_ios_version")} />
                  </div>
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardHeader><CardTitle>{t("app_changelog")}</CardTitle></CardHeader>
              <CardContent>
                <Textarea rows={4} {...register("changelog")} />
              </CardContent>
            </Card>

            <div className="flex items-center gap-4">
              <label className="flex items-center gap-2 text-sm">
                <input type="checkbox" {...register("is_active")} />
                {t("app_active")}
              </label>
              <label className="flex items-center gap-2 text-sm">
                <input type="checkbox" {...register("is_featured")} />
                {t("app_featured")}
              </label>
            </div>

            <Button type="submit" size="lg" disabled={loading}>
              {loading ? <Loader2 className="h-4 w-4 me-2 animate-spin" /> : <Save className="h-4 w-4 me-2" />}
              Save changes
            </Button>
          </form>
        </div>

        {/* Sidebar: Uploads */}
        <aside className="space-y-4">
          <Card>
            <CardHeader>
              <CardTitle>Icon</CardTitle>
            </CardHeader>
            <CardContent className="space-y-3">
              {app.icon_url && (
                <div className="flex justify-center">
                  <Image src={app.icon_url} alt={app.name} width={128} height={128} className="rounded-2xl" />
                </div>
              )}
              <Input type="file" accept="image/*" onChange={(e) => setIcon(e.target.files?.[0] || null)} />
              {icon && (
                <Button size="sm" onClick={() => handleQuickUpload("icon")} disabled={uploadingType === "icon"}>
                  <Upload className="h-3 w-3 me-1" />
                  Upload icon
                </Button>
              )}
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>IPA</CardTitle>
            </CardHeader>
            <CardContent className="space-y-3">
              {app.files && app.files[0] && (
                <div className="text-sm space-y-1 p-3 rounded-lg bg-muted/30">
                  <p className="flex items-center gap-2 font-medium">
                    <Package className="h-4 w-4" />
                    v{app.files[0].version}
                  </p>
                  <p className="text-xs text-muted-foreground">{app.files[0].size_human}</p>
                  {app.files[0].checksum_sha256 && (
                    <p className="text-xs text-muted-foreground font-mono truncate">
                      SHA: {app.files[0].checksum_sha256.slice(0, 16)}…
                    </p>
                  )}
                </div>
              )}
              <Input type="file" accept=".ipa" onChange={(e) => setIpa(e.target.files?.[0] || null)} />
              {ipa && (
                <Button size="sm" onClick={() => handleQuickUpload("ipa")} disabled={uploadingType === "ipa"}>
                  <Upload className="h-3 w-3 me-1" />
                  Upload IPA
                </Button>
              )}
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Screenshots</CardTitle>
            </CardHeader>
            <CardContent className="space-y-3">
              {app.screenshots && app.screenshots.length > 0 && (
                <div className="grid grid-cols-3 gap-2">
                  {app.screenshots.map((s) => (
                    <div key={s.id} className="aspect-[9/16] rounded-md overflow-hidden bg-muted">
                      <Image src={s.url} alt="" width={100} height={180} className="object-cover w-full h-full" />
                    </div>
                  ))}
                </div>
              )}
              <Input
                type="file"
                accept="image/*"
                multiple
                onChange={(e) => setScreenshots(Array.from(e.target.files || []))}
              />
              {screenshots.length > 0 && (
                <Button size="sm" onClick={() => handleQuickUpload("screenshots")} disabled={uploadingType === "screenshots"}>
                  <Upload className="h-3 w-3 me-1" />
                  Upload {screenshots.length} screenshot(s)
                </Button>
              )}
            </CardContent>
          </Card>
        </aside>
      </div>
    </div>
  );
}
