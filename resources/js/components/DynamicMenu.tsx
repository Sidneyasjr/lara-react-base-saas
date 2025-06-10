import React, { useMemo } from 'react';
import { router } from '@inertiajs/react';
import { 
  Home,
  Users,
  Settings,
  LayoutDashboard,
  User,
  Lock,
  Key,
  Shield,
  Menu,
  File,
  Folder,
  Cog,
  Bell,
  Mail,
  Calendar,
  BarChart3,
  Database,
  Globe,
  type LucideIcon
} from 'lucide-react';
import { cn } from '@/lib/utils';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { 
  DynamicMenuProps, 
  MenuItem,
  User as UserType
} from '@/types';
import { 
  filterMenuByPermissions
} from '@/utils/permissions';
import { usePage } from '@inertiajs/react';

export function DynamicMenu({
  items,
  config = {},
  className,
  onItemClick,
  currentRoute,
  showIcons = true,
}: DynamicMenuProps) {
  const { auth } = usePage<{ auth: { user: UserType } }>().props;
  const user = auth?.user || null;

  // Organiza itens por módulo/categoria para criar separadores
  const organizedMenu = useMemo(() => {
    // Garante que items é sempre um array
    const validItems = Array.isArray(items) ? items : [];
    
    const filteredItems = config.filter_permissions !== false 
      ? filterMenuByPermissions(validItems, user)
      : validItems;

    // Agrupa itens por módulo
    const groupedItems = filteredItems.reduce((groups, item) => {
      const module = item.module || 'Geral';
      if (!groups[module]) {
        groups[module] = [];
      }
      groups[module].push(item);
      return groups;
    }, {} as Record<string, typeof filteredItems>);

    // Ordena itens dentro de cada grupo por order_index
    Object.keys(groupedItems).forEach(module => {
      groupedItems[module].sort((a, b) => a.order_index - b.order_index);
    });

    return groupedItems;
  }, [items, user, config]);

  // Se não há itens de menu, mostra um skeleton ou placeholder
  if (Object.keys(organizedMenu).length === 0) {
    return (
      <nav className={cn('space-y-1', className)}>
        <div className="px-3 py-2 text-sm text-muted-foreground">
          {Array.isArray(items) && items.length === 0 ? 'Nenhum item de menu disponível' : 'Carregando...'}
        </div>
      </nav>
    );
  }

  // Mapeamento de ícones disponíveis
  const iconMap: Record<string, LucideIcon> = {
    'home': Home,
    'dashboard': LayoutDashboard,
    'users': Users,
    'user': User,
    'settings': Settings,
    'lock': Lock,
    'key': Key,
    'shield': Shield,
    'menu': Menu,
    'file': File,
    'folder': Folder,
    'cog': Cog,
    'bell': Bell,
    'mail': Mail,
    'calendar': Calendar,
    'chart': BarChart3,
    'database': Database,
    'globe': Globe,
  };

  const getIcon = (iconName?: string | null) => {
    if (!showIcons || !iconName) return null;
    const IconComponent = iconMap[iconName.toLowerCase()];
    return IconComponent ? <IconComponent className="h-4 w-4" /> : null;
  };

  const handleItemClick = (item: MenuItem) => {
    // Chama callback personalizado se fornecido
    if (onItemClick) {
      onItemClick(item);
      return;
    }

    // Navegação padrão usando route_name
    if (item.route_name) {
      router.visit(route(item.route_name));
    }
  };

  const renderMenuItem = (item: MenuItem) => {
    const isActive = currentRoute === item.route_name;

    return (
      <Button
        key={item.id}
        variant="ghost"
        onClick={() => handleItemClick(item)}
        className={cn(
          'w-full justify-start px-3 py-2 h-auto text-sm',
          'hover:bg-accent hover:text-accent-foreground',
          isActive && 'bg-accent text-accent-foreground'
        )}
      >
        {getIcon(item.icon)}
        <span className="ml-2">{item.title}</span>
      </Button>
    );
  };

  const renderMenuSection = (moduleName: string, items: MenuItem[]) => {
    return (
      <div key={moduleName} className="space-y-1">
        {/* Separador com nome do módulo */}
        <div className="px-3 py-2">
          <h4 className="text-xs font-semibold text-muted-foreground uppercase tracking-wider">
            {moduleName}
          </h4>
          <Separator className="mt-2" />
        </div>
        {/* Itens do módulo */}
        <div className="space-y-1">
          {items.map(item => renderMenuItem(item))}
        </div>
      </div>
    );
  };

  return (
    <nav className={cn('space-y-4', className)}>
      {Object.entries(organizedMenu)
        .sort(([a], [b]) => a.localeCompare(b)) // Ordena módulos alfabeticamente
        .map(([moduleName, items]) => renderMenuSection(moduleName, items))
      }
    </nav>
  );
}

export default DynamicMenu;
