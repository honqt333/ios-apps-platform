import api from "@/lib/api";
import type { App, Category, PaginatedResponse, ApiResponse, SortOption } from "@/types";

export interface AppsQuery {
  q?: string;
  category?: string | number;
  developer?: string;
  sort?: SortOption;
  per_page?: number;
  page?: number;
}

export const appsService = {
  async list(params: AppsQuery = {}): Promise<PaginatedResponse<App>> {
    const { data } = await api.get<PaginatedResponse<App>>("/v1/apps", { params });
    return data;
  },

  async featured(limit = 8): Promise<App[]> {
    const { data } = await api.get<ApiResponse<App[]>>("/v1/apps/featured", { params: { limit } });
    return data.data;
  },

  async mostDownloaded(limit = 10): Promise<App[]> {
    const { data } = await api.get<ApiResponse<App[]>>("/v1/apps/most-downloaded", { params: { limit } });
    return data.data;
  },

  async recent(limit = 10): Promise<App[]> {
    const { data } = await api.get<ApiResponse<App[]>>("/v1/apps/recent", { params: { limit } });
    return data.data;
  },

  async get(slugOrId: string | number): Promise<App> {
    const { data } = await api.get<ApiResponse<App>>(`/v1/apps/${slugOrId}`);
    return data.data;
  },

  async trackInstall(slugOrId: string | number): Promise<{ install_url: string; manifest_url: string }> {
    const { data } = await api.post<ApiResponse<{ install_url: string; manifest_url: string }>>(
      `/v1/apps/${slugOrId}/track`
    );
    return data.data;
  },

  async search(params: AppsQuery): Promise<PaginatedResponse<App>> {
    const { data } = await api.get<PaginatedResponse<App>>("/v1/search", { params });
    return data;
  },
};

export const categoriesService = {
  async list(): Promise<Category[]> {
    const { data } = await api.get<ApiResponse<Category[]>>("/v1/categories");
    return data.data;
  },

  async tree(): Promise<Category[]> {
    const { data } = await api.get<ApiResponse<Category[]>>("/v1/categories/tree");
    return data.data;
  },

  async get(slug: string): Promise<Category> {
    const { data } = await api.get<ApiResponse<Category>>(`/v1/categories/${slug}`);
    return data.data;
  },
};
