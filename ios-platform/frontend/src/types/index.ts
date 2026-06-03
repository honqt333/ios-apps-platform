// Shared TypeScript types matching backend resources

export interface Category {
  id: number;
  name: string;
  slug: string;
  description?: string;
  icon?: string;
  color?: string;
  sort_order: number;
  is_active: boolean;
  parent_id?: number | null;
  apps_count?: number;
  children?: Category[];
}

export interface Screenshot {
  id: number;
  url: string;
  device_type: "iphone" | "ipad";
  width?: number;
  height?: number;
  sort_order: number;
}

export interface AppFile {
  id: number;
  version: string;
  build_number?: string;
  disk: string;
  size_bytes: number;
  size_human: string;
  checksum_sha256?: string;
  is_current: boolean;
  url?: string;
  manifest_url?: string;
  metadata?: Record<string, any>;
  created_at: string;
}

export interface App {
  id: number;
  name: string;
  slug: string;
  developer: string;
  description?: string;
  long_description?: string;
  bundle_id: string;
  version: string;
  build_number?: string;
  minimum_ios_version: string;
  file_size_bytes: number;
  file_size_human?: string;
  icon_url?: string;
  icon_path?: string;
  ipa_size_bytes: number;
  downloads_count: number;
  is_active: boolean;
  is_archived: boolean;
  is_featured: boolean;
  is_installable: boolean;
  install_url?: string;
  changelog?: string;
  changelog_history?: Array<{ version: string; date: string; notes: string }>;
  localized?: Record<string, any>;
  category?: Category;
  screenshots?: Screenshot[];
  files?: AppFile[];
  created_at: string;
  updated_at: string;
}

export interface User {
  id: number;
  name: string;
  email: string;
  username?: string;
  phone?: string;
  avatar?: string;
  locale: string;
  is_active: boolean;
  roles: string[];
  permissions?: string[];
  last_login_at?: string;
  last_login_ip?: string;
  created_at: string;
}

export interface PaginatedResponse<T> {
  success: boolean;
  data: T[];
  meta: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}

export interface ApiResponse<T> {
  success: boolean;
  message?: string;
  data: T;
  errors?: Record<string, string[]>;
}

export interface DashboardStats {
  totals: {
    apps: number;
    categories: number;
    users: number;
    downloads: number;
    active_apps: number;
    archived_apps: number;
  };
  recent_apps: App[];
  top_apps: App[];
  recent_downloads: any[];
}

export type SortOption = "newest" | "oldest" | "name" | "downloads";
