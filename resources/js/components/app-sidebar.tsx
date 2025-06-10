import React from 'react';
import { NavFooter } from '@/components/nav-footer';
import { NavUser } from '@/components/nav-user';
import { DynamicMenu } from '@/components/DynamicMenu';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { MenuSkeleton } from '@/components/ui/skeleton';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { useMenu } from '@/hooks/useMenu';
import AppLogo from './app-logo';

const footerNavItems: NavItem[] = [];

export function AppSidebar() {
  const { url } = usePage();
  const { menu, loading, error } = useMenu({
    use_shared_data: true, // Usa dados compartilhados quando possível
  });

  // Extrai o nome da rota atual da URL
  const getCurrentRoute = () => {
    // Esta é uma implementação simples - você pode melhorar isso
    // baseado na estrutura de rotas da sua aplicação
    if (url === '/dashboard') return 'dashboard';
    if (url.startsWith('/admin/users')) return 'admin.users.index';
    if (url.startsWith('/admin/roles')) return 'admin.roles.index';
    if (url.startsWith('/admin/permissions')) return 'admin.permissions.index';
    if (url.startsWith('/admin/menus')) return 'admin.menus.index';
    if (url === '/profile') return 'profile.edit';
    return undefined;
  };

  return (
    <Sidebar collapsible="icon" variant="inset">
      <SidebarHeader>
        <SidebarMenu>
          <SidebarMenuItem>
            <SidebarMenuButton size="lg" asChild>
              <Link href="/dashboard" prefetch>
                <AppLogo />
              </Link>
            </SidebarMenuButton>
          </SidebarMenuItem>
        </SidebarMenu>
      </SidebarHeader>

      <SidebarContent>
        {loading ? (
          <MenuSkeleton className="px-2" itemCount={4} />
        ) : error ? (
          <div className="p-4 text-sm text-red-500">
            Erro ao carregar menu: {error}
          </div>
        ) : (
          <DynamicMenu
            items={menu}
            currentRoute={getCurrentRoute()}
            className="px-2"
            showIcons={true}
            collapsible={true}
            config={{ filter_permissions: true }}
          />
        )}
      </SidebarContent>

      <SidebarFooter>
        <NavFooter items={footerNavItems} className="mt-auto" />
        <NavUser />
      </SidebarFooter>
    </Sidebar>
  );
}
