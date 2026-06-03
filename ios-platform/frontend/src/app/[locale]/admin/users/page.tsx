"use client";

import { useEffect, useState } from "react";
import { useTranslations } from "next-intl";
import { Plus, Edit, Trash2, Loader2, MoreVertical, Search } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Card, CardContent } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogFooter,
} from "@/components/ui/dropdown-menu-shim";
import {
  DropdownMenu,
  DropdownMenuTrigger,
  DropdownMenuContent,
  DropdownMenuItem,
} from "@/components/ui/dropdown-menu";
import { adminService } from "@/services/adminService";
import { toast } from "sonner";
import type { User } from "@/types";

const ROLES = [
  { value: "super-admin", label: "Super Admin" },
  { value: "admin", label: "Admin" },
  { value: "moderator", label: "Moderator" },
  { value: "editor", label: "Editor" },
];

export default function AdminUsersPage() {
  const t = useTranslations("admin");
  const [items, setItems] = useState<User[]>([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState("");
  const [editing, setEditing] = useState<User | null>(null);
  const [open, setOpen] = useState(false);
  const [form, setForm] = useState({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
    role: "editor",
    is_active: true,
  });

  const fetch_ = async () => {
    setLoading(true);
    try {
      const res = await adminService.listUsers({ search, per_page: 30 });
      setItems(res.data);
    } catch {
      setItems([]);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    const t = setTimeout(fetch_, 300);
    return () => clearTimeout(t);
  }, [search]);

  const openNew = () => {
    setEditing(null);
    setForm({ name: "", email: "", password: "", password_confirmation: "", role: "editor", is_active: true });
    setOpen(true);
  };

  const openEdit = (u: User) => {
    setEditing(u);
    setForm({
      name: u.name,
      email: u.email,
      password: "",
      password_confirmation: "",
      role: u.roles?.[0] || "editor",
      is_active: u.is_active,
    });
    setOpen(true);
  };

  const save = async () => {
    try {
      if (editing) {
        const payload: any = { ...form };
        if (!payload.password) delete payload.password;
        await adminService.updateUser(editing.id, payload);
        toast.success("User updated");
      } else {
        await adminService.createUser(form);
        toast.success("User created");
      }
      setOpen(false);
      fetch_();
    } catch (e: any) {
      toast.error(e?.response?.data?.message || "Failed");
    }
  };

  const remove = async (id: number) => {
    if (!confirm("Delete this user?")) return;
    try {
      await adminService.deleteUser(id);
      toast.success("Deleted");
      fetch_();
    } catch (e: any) {
      toast.error("Delete failed");
    }
  };

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <h1 className="text-3xl font-bold">{t("users")}</h1>
        <Button onClick={openNew}>
          <Plus className="h-4 w-4 me-2" />
          {t("add_user")}
        </Button>
      </div>

      <div className="relative max-w-sm">
        <Search className="absolute start-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
        <Input
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          placeholder="Search users..."
          className="ps-10"
        />
      </div>

      <Card>
        <CardContent className="p-0">
          <div className="overflow-x-auto">
            <table className="w-full text-sm">
              <thead className="bg-muted/50 text-xs uppercase">
                <tr>
                  <th className="text-start p-3">User</th>
                  <th className="text-start p-3">Email</th>
                  <th className="text-start p-3">Role</th>
                  <th className="text-start p-3">Status</th>
                  <th className="text-end p-3">Actions</th>
                </tr>
              </thead>
              <tbody>
                {loading ? (
                  <tr><td colSpan={5} className="p-12 text-center"><Loader2 className="h-5 w-5 animate-spin mx-auto" /></td></tr>
                ) : items.length === 0 ? (
                  <tr><td colSpan={5} className="p-12 text-center text-muted-foreground">—</td></tr>
                ) : items.map((u) => (
                  <tr key={u.id} className="border-t hover:bg-muted/30">
                    <td className="p-3">
                      <div className="flex items-center gap-2">
                        <div className="h-9 w-9 rounded-full bg-gradient-to-br from-primary/30 to-primary/10 flex items-center justify-center text-sm font-semibold">
                          {u.name?.[0]?.toUpperCase()}
                        </div>
                        <div>
                          <p className="font-medium">{u.name}</p>
                          <p className="text-xs text-muted-foreground">@{u.username || "—"}</p>
                        </div>
                      </div>
                    </td>
                    <td className="p-3 text-muted-foreground">{u.email}</td>
                    <td className="p-3">
                      {u.roles?.map((r) => (
                        <Badge key={r} variant="secondary" className="me-1">{r}</Badge>
                      ))}
                    </td>
                    <td className="p-3">
                      {u.is_active ? (
                        <Badge variant="success">Active</Badge>
                      ) : (
                        <Badge variant="warning">Inactive</Badge>
                      )}
                    </td>
                    <td className="p-3 text-end">
                      <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                          <Button variant="ghost" size="icon">
                            <MoreVertical className="h-4 w-4" />
                          </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                          <DropdownMenuItem onClick={() => openEdit(u)}>
                            <Edit className="h-4 w-4 me-2" />
                            Edit
                          </DropdownMenuItem>
                          <DropdownMenuItem onClick={() => remove(u.id)} className="text-destructive">
                            <Trash2 className="h-4 w-4 me-2" />
                            Delete
                          </DropdownMenuItem>
                        </DropdownMenuContent>
                      </DropdownMenu>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </CardContent>
      </Card>

      <Dialog open={open} onOpenChange={setOpen}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>{editing ? "Edit user" : "New user"}</DialogTitle>
          </DialogHeader>
          <div className="space-y-3">
            <div>
              <Label>Name</Label>
              <Input value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} />
            </div>
            <div>
              <Label>Email</Label>
              <Input type="email" value={form.email} onChange={(e) => setForm({ ...form, email: e.target.value })} />
            </div>
            <div>
              <Label>Password {editing && "(leave blank to keep current)"}</Label>
              <Input
                type="password"
                value={form.password}
                onChange={(e) => setForm({ ...form, password: e.target.value })}
              />
            </div>
            <div>
              <Label>Confirm password</Label>
              <Input
                type="password"
                value={form.password_confirmation}
                onChange={(e) => setForm({ ...form, password_confirmation: e.target.value })}
              />
            </div>
            <div>
              <Label>Role</Label>
              <select
                className="w-full h-10 px-3 rounded-lg border bg-background"
                value={form.role}
                onChange={(e) => setForm({ ...form, role: e.target.value })}
              >
                {ROLES.map((r) => (
                  <option key={r.value} value={r.value}>{r.label}</option>
                ))}
              </select>
            </div>
            <label className="flex items-center gap-2 text-sm">
              <input
                type="checkbox"
                checked={form.is_active}
                onChange={(e) => setForm({ ...form, is_active: e.target.checked })}
              />
              Active
            </label>
          </div>
          <DialogFooter>
            <Button variant="outline" onClick={() => setOpen(false)}>Cancel</Button>
            <Button onClick={save}>Save</Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  );
}
