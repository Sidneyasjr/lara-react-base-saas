import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/react';
import { Bell, LayoutGrid, Shield, UserCog, KeyRound } from 'lucide-react';
import AppLogo from './app-logo';

const mainNavItems: NavItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
    icon: LayoutGrid,
  },
  {
    title: 'Toast Demo',
    href: '/toast-demo',
    icon: Bell,
  },
];

const adminNavItems: NavItem[] = [
  {
    title: 'Gestão de Usuários',
    href: '/admin/users',
    icon: UserCog,
  },
  {
    title: 'Gestão de Roles',
    href: '/admin/roles',
    icon: Shield,
  },
  {
    title: 'Gestão de Permissões',
    href: '/admin/permissions',
    icon: KeyRound,
  },
];


const footerNavItems: NavItem[] = [
];

export function AppSidebar() {
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
        <NavMain items={mainNavItems} title="Principal" />
        <NavMain items={adminNavItems} title="Administração" />
      </SidebarContent>

      <SidebarFooter>
        <NavFooter items={footerNavItems} className="mt-auto" />
        <NavUser />
      </SidebarFooter>
    </Sidebar>
  );
}
