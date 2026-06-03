import api from "@/lib/api";
import type { App, Category, User, DashboardStats, PaginatedResponse, ApiResponse } from "@/types";

export const adminService = {
  // Dashboard
  async stats(): Promise<DashboardStats> {
    const { data } = await api.get<{ success: boolean; data: DashboardStats }>("/v1/admin/dashboard");
    return data.data;
  },

  // Apps
  async listApps(params: Record<string, any> = {}): Promise<PaginatedResponse<App>> {
    const { data } = await api.get<PaginatedResponse<App>>("/v1/admin/apps", { params });
    return data;
  },

  async getApp(id: string | number): Promise<App> {
    const { data } = await api.get<ApiResponse<App>>(`/v1/admin/apps/${id}`);
    return data.data;
  },

  async createApp(form: FormData): Promise<App> {
    const { data } = await api.post<ApiResponse<App>>("/v1/admin/apps", form, {
      headers: { "Content-Type": "multipart/form-data" },
    });
    return data.data;
  },

  async updateApp(id: string | number, form: FormData): Promise<App> {
    const { data } = await api.post<ApiResponse<App>>(`/v1/admin/apps/${id}`, form, {
      headers: { "Content-Type": "multipart/form-data" },
    });
    return data.data;
  },

  async deleteApp(id: string | number): Promise<void> {
    await api.delete(`/v1/admin/apps/${id}`);
  },

  async archiveApp(id: string | number): Promise<App> {
    const { data } = await api.post<ApiResponse<App>>(`/v1/admin/apps/${id}/archive`);
    return data.data;
  },

  async toggleActiveApp(id: string | number): Promise<App> {
    const { data } = await api.post<ApiResponse<App>>(`/v1/admin/apps/${id}/toggle-active`);
    return data.data;
  },

  // Uploads
  async uploadIpa(appId: number, file: File): Promise<any> {
    const form = new FormData();
    form.append("app_id", String(appId));
    form.append("ipa", file);
    const { data } = await api.post("/v1/admin/upload/ipa", form, {
      headers: { "Content-Type": "multipart/form-data" },
    });
    return data.data;
  },

  async uploadIcon(appId: number, file: File): Promise<any> {
    const form = new FormData();
    form.append("app_id", String(appId));
    form.append("icon", file);
    const { data } = await api.post("/v1/admin/upload/icon", form, {
      headers: { "Content-Type": "multipart/form-data" },
    });
    return data.data;
  },

  async uploadScreenshots(appId: number, files: File[]): Promise<any> {
    const form = new FormData();
    form.append("app_id", String(appId));
    files.forEach((f) => form.append("screenshots[]", f));
    const { data } = await api.post("/v1/admin/upload/screenshots", form, {
      headers: { "Content-Type": "multipart/form-data" },
    });
    return data.data;
  },

  // Categories
  async listCategories(): Promise<PaginatedResponse<Category>> {
    const { data } = await api.get<PaginatedResponse<Category>>("/v1/admin/categories");
    return data;
  },

  async createCategory(payload: Partial<Category>): Promise<Category> {
    const { data } = await api.post<ApiResponse<Category>>("/v1/admin/categories", payload);
    return data.data;
  },

  async updateCategory(id: number, payload: Partial<Category>): Promise<Category> {
    const { data } = await api.put<ApiResponse<Category>>(`/v1/admin/categories/${id}`, payload);
    return data.data;
  },

  async deleteCategory(id: number): Promise<void> {
    await api.delete(`/v1/admin/categories/${id}`);
  },

  // Users
  async listUsers(params: Record<string, any> = {}): Promise<PaginatedResponse<User>> {
    const { data } = await api.get<PaginatedResponse<User>>("/v1/admin/users", { params });
    return data;
  },

  async createUser(payload: any): Promise<User> {
    const { data } = await api.post<ApiResponse<User>>("/v1/admin/users", payload);
    return data.data;
  },

  async updateUser(id: number, payload: any): Promise<User> {
    const { data } = await api.put<ApiResponse<User>>(`/v1/admin/users/${id}`, payload);
    return data.data;
  },

  async deleteUser(id: number): Promise<void> {
    await api.delete(`/v1/admin/users/${id}`);
  },

  // Activity logs
  async getActivityLogs(params: Record<string, any> = {}): Promise<PaginatedResponse<any>> {
    const { data } = await api.get<PaginatedResponse<any>>("/v1/admin/activity-logs", { params });
    return data;
  },
};
