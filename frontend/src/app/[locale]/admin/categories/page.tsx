"use client";

import { useEffect, useState } from "react";
import { useTranslations } from "next-intl";
import { Plus, Edit, Trash2, Loader2 } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogFooter,
} from "@/components/ui/dropdown-menu-shim";
import { adminService } from "@/services/adminService";
import { toast } from "sonner";
import type { Category } from "@/types";
import { useForm } from "react-hook-form";

// Reuse dialog primitives via a simple inline re-export
import * as DialogPrimitive from "@radix-ui/react-dialog";

const Dialog = DialogPrimitive.Root;
const DialogTrigger = DialogPrimitive.Trigger;
const DialogContent = DialogPrimitive.Content;

export default function AdminCategoriesPage() {
  const t = useTranslations("admin");
  const [items, setItems] = useState<Category[]>([]);
  const [loading, setLoading] = useState(true);
  const [editing, setEditing] = useState<Category | null>(null);
  const [open, setOpen] = useState(false);
  const [name, setName] = useState("");
  const [description, setDescription] = useState("");
  const [color, setColor] = useState("#3B82F6");
  const [icon, setIcon] = useState("");

  const fetch_ = async () => {
    setLoading(true);
    try {
      const res = await adminService.listCategories();
      setItems(res.data);
    } catch {
      setItems([]);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetch_();
  }, []);

  const openNew = () => {
    setEditing(null);
    setName("");
    setDescription("");
    setColor("#3B82F6");
    setIcon("");
    setOpen(true);
  };

  const openEdit = (c: Category) => {
    setEditing(c);
    setName(c.name);
    setDescription(c.description || "");
    setColor(c.color || "#3B82F6");
    setIcon(c.icon || "");
    setOpen(true);
  };

  const save = async () => {
    try {
      if (editing) {
        await adminService.updateCategory(editing.id, { name, description, color, icon });
        toast.success("Category updated");
      } else {
        await adminService.createCategory({ name, description, color, icon });
        toast.success("Category created");
      }
      setOpen(false);
      fetch_();
    } catch (e: any) {
      toast.error(e?.response?.data?.message || "Failed");
    }
  };

  const remove = async (id: number) => {
    if (!confirm("Delete this category?")) return;
    try {
      await adminService.deleteCategory(id);
      toast.success("Deleted");
      fetch_();
    } catch (e: any) {
      toast.error("Delete failed");
    }
  };

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <h1 className="text-3xl font-bold">{t("categories")}</h1>
        <Button onClick={openNew}>
          <Plus className="h-4 w-4 me-2" />
          {t("add_category")}
        </Button>
      </div>

      <Card>
        <CardContent className="p-0">
          <div className="overflow-x-auto">
            <table className="w-full text-sm">
              <thead className="bg-muted/50 text-xs uppercase">
                <tr>
                  <th className="text-start p-3">Name</th>
                  <th className="text-start p-3">Slug</th>
                  <th className="text-start p-3">Color</th>
                  <th className="text-start p-3">Apps</th>
                  <th className="text-end p-3">Actions</th>
                </tr>
              </thead>
              <tbody>
                {loading ? (
                  <tr><td colSpan={5} className="p-12 text-center"><Loader2 className="h-5 w-5 animate-spin mx-auto" /></td></tr>
                ) : items.length === 0 ? (
                  <tr><td colSpan={5} className="p-12 text-center text-muted-foreground">—</td></tr>
                ) : items.map((c) => (
                  <tr key={c.id} className="border-t hover:bg-muted/30">
                    <td className="p-3 font-medium">{c.name}</td>
                    <td className="p-3 text-muted-foreground font-mono text-xs">{c.slug}</td>
                    <td className="p-3">
                      <div className="flex items-center gap-2">
                        <div className="h-5 w-5 rounded" style={{ backgroundColor: c.color || "#3B82F6" }} />
                        <span className="text-xs text-muted-foreground">{c.color}</span>
                      </div>
                    </td>
                    <td className="p-3">
                      <Badge variant="secondary">{c.apps_count ?? 0}</Badge>
                    </td>
                    <td className="p-3 text-end space-x-1">
                      <Button variant="ghost" size="icon" onClick={() => openEdit(c)}>
                        <Edit className="h-4 w-4" />
                      </Button>
                      <Button variant="ghost" size="icon" onClick={() => remove(c.id)} className="text-destructive">
                        <Trash2 className="h-4 w-4" />
                      </Button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </CardContent>
      </Card>

      <Dialog open={open} onOpenChange={setOpen}>
        <DialogContent className="max-w-md">
          <DialogPrimitive.Header>
            <DialogPrimitive.Title>
              {editing ? "Edit category" : "New category"}
            </DialogPrimitive.Title>
          </DialogPrimitive.Header>
          <div className="space-y-3">
            <div>
              <label className="text-sm font-medium">Name</label>
              <Input value={name} onChange={(e) => setName(e.target.value)} />
            </div>
            <div>
              <label className="text-sm font-medium">Description</label>
              <Input value={description} onChange={(e) => setDescription(e.target.value)} />
            </div>
            <div className="grid grid-cols-2 gap-2">
              <div>
                <label className="text-sm font-medium">Color</label>
                <Input type="color" value={color} onChange={(e) => setColor(e.target.value)} />
              </div>
              <div>
                <label className="text-sm font-medium">Icon name</label>
                <Input value={icon} onChange={(e) => setIcon(e.target.value)} placeholder="briefcase" />
              </div>
            </div>
          </div>
          <DialogPrimitive.Footer className="gap-2">
            <Button variant="outline" onClick={() => setOpen(false)}>Cancel</Button>
            <Button onClick={save}>Save</Button>
          </DialogPrimitive.Footer>
        </DialogContent>
      </Dialog>
    </div>
  );
}
