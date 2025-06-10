import { LucideIcon } from 'lucide-react';
import type { Config } from 'ziggy-js';

export interface Auth {
  user: User;
}

export interface BreadcrumbItem {
  title: string;
  href: string;
}

export interface NavGroup {
  title: string;
  items: NavItem[];
}

export interface NavItem {
  title: string;
  href: string;
  icon?: LucideIcon | null;
  isActive?: boolean;
}

export interface SharedData {
  name: string;
  quote: { message: string; author: string };
  auth: Auth;
  ziggy: Config & { location: string };
  sidebarOpen: boolean;
  flash?: FlashMessages;
  [key: string]: unknown;
}

export interface FlashMessages {
  success?: string;
  error?: string;
  warning?: string;
  info?: string;
}

export interface User {
  id: number;
  name: string;
  email: string;
  avatar?: string;
  email_verified_at: string | null;
  created_at: string;
  updated_at: string;
  roles?: Role[];
  permissions?: Permission[];
  [key: string]: unknown; // This allows for additional properties...
}

export interface Role {
  id: number;
  name: string;
  description?: string;
  guard_name: string;
  created_at: string;
  updated_at: string;
  permissions?: Permission[];
  users_count?: number;
  permissions_count?: number;
}

export interface Permission {
  id: number;
  name: string;
  module: string;
  description?: string;
  guard_name: string;
  created_at: string;
  updated_at: string;
  roles?: Role[];
  users?: User[];
}

export interface PermissionsByModule {
  [module: string]: Permission[];
}

export interface PaginatedData<T> {
  data: T[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number;
  to: number;
  links: PaginationLink[];
}

export interface PaginationLink {
  url?: string;
  label: string;
  active: boolean;
}

export interface FilterState {
  search?: string;
  module?: string;
  per_page?: number;
  [key: string]: unknown;
}

export interface PermissionStatistics {
  total_permissions: number;
  total_modules: number;
  permissions_by_module: Record<string, number>;
  most_used_permissions: Array<{
    name: string;
    usage_count: number;
  }>;
}

export interface RoleStatistics {
  total_roles: number;
  total_permissions: number;
  roles_breakdown: Array<{
    name: string;
    description?: string;
    users_count: number;
    permissions_count: number;
  }>;
}

// Menu System Interfaces
export interface MenuItem {
  id: number;
  tenant_id?: number | null;
  parent_id?: number | null;
  title: string;
  route_name?: string | null;
  icon?: string | null;
  permission_required?: string | null;
  order_index: number;
  is_active: boolean;
  module: string;
  created_at: string;
  updated_at: string;
  children?: MenuItem[];
  parent?: MenuItem;
}

export interface MenuConfig {
  module: string;
  tenant_id?: number | null;
  show_inactive?: boolean;
  max_depth?: number;
  filter_permissions?: boolean;
}

export interface MenuTreeNode extends MenuItem {
  children: MenuTreeNode[];
  level: number;
  has_permission: boolean;
  is_current_route: boolean;
}

export interface DynamicMenuProps {
  items: MenuItem[];
  config?: Partial<MenuConfig>;
  className?: string;
  onItemClick?: (item: MenuItem) => void;
  currentRoute?: string;
  collapsible?: boolean;
  showIcons?: boolean;
}

export interface MenuItemComponentProps {
  item: MenuTreeNode;
  level?: number;
  isCollapsed?: boolean;
  showIcon?: boolean;
  onItemClick?: (item: MenuItem) => void;
  currentRoute?: string;
}
