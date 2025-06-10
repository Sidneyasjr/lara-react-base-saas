import { User, Permission, MenuItem, MenuTreeNode } from '@/types';

/**
 * Verifica se o usuário tem uma permissão específica
 */
export function hasPermission(user: User | null, permission: string): boolean {
  if (!user || !permission) {
    return false;
  }

  // Verifica se o usuário tem a permissão diretamente
  if (user.permissions?.some(p => p.name === permission)) {
    return true;
  }

  // Verifica se o usuário tem a permissão através de roles
  if (user.roles?.some(role => 
    role.permissions?.some(p => p.name === permission)
  )) {
    return true;
  }

  return false;
}

/**
 * Verifica se o usuário pode acessar um item de menu
 */
export function canAccessMenuItem(user: User | null, menuItem: MenuItem): boolean {
  if (!menuItem.permission_required) {
    return true;
  }

  return hasPermission(user, menuItem.permission_required);
}

/**
 * Filtra uma lista de itens de menu baseado nas permissões do usuário
 */
export function filterMenuByPermissions(
  menuItems: MenuItem[], 
  user: User | null
): MenuItem[] {
  // Garante que menuItems é um array
  if (!Array.isArray(menuItems)) {
    console.warn('filterMenuByPermissions: menuItems não é um array', menuItems);
    return [];
  }

  return menuItems.filter(item => {
    // Se o item não tem permissão, verifica se algum filho tem
    if (!canAccessMenuItem(user, item)) {
      // Se tem filhos, verifica se algum deles tem permissão
      if (item.children && Array.isArray(item.children) && item.children.length > 0) {
        const filteredChildren = filterMenuByPermissions(item.children, user);
        if (filteredChildren.length === 0) {
          return false;
        }
        // Atualiza os filhos filtrados
        item.children = filteredChildren;
      } else {
        return false;
      }
    } else {
      // Se o item tem permissão, filtra os filhos recursivamente
      if (item.children && Array.isArray(item.children) && item.children.length > 0) {
        item.children = filterMenuByPermissions(item.children, user);
      }
    }

    return true;
  });
}

/**
 * Converte MenuItem para MenuTreeNode com informações adicionais
 */
export function buildMenuTree(
  menuItems: MenuItem[],
  user: User | null,
  currentRoute?: string,
  level: number = 0
): MenuTreeNode[] {
  // Garante que menuItems é um array
  if (!Array.isArray(menuItems)) {
    console.warn('buildMenuTree: menuItems não é um array', menuItems);
    return [];
  }

  return menuItems.map(item => {
    const hasPermission = canAccessMenuItem(user, item);
    const isCurrentRoute = currentRoute === item.route_name;
    
    // Verifica se children existe e é um array antes de processar
    const children = (item.children && Array.isArray(item.children))
      ? buildMenuTree(item.children, user, currentRoute, level + 1)
      : [];

    return {
      ...item,
      children,
      level,
      has_permission: hasPermission,
      is_current_route: isCurrentRoute,
    };
  });
}

/**
 * Verifica se o usuário tem pelo menos uma das permissões
 */
export function hasAnyPermission(user: User | null, permissions: string[]): boolean {
  if (!user || !permissions.length) {
    return false;
  }

  return permissions.some(permission => hasPermission(user, permission));
}

/**
 * Verifica se o usuário tem todas as permissões
 */
export function hasAllPermissions(user: User | null, permissions: string[]): boolean {
  if (!user || !permissions.length) {
    return false;
  }

  return permissions.every(permission => hasPermission(user, permission));
}

/**
 * Retorna todas as permissões do usuário
 */
export function getUserPermissions(user: User | null): string[] {
  if (!user) {
    return [];
  }

  const directPermissions = user.permissions?.map(p => p.name) || [];
  const rolePermissions = user.roles?.flatMap(role => 
    role.permissions?.map(p => p.name) || []
  ) || [];

  // Remove duplicatas
  return [...new Set([...directPermissions, ...rolePermissions])];
}

/**
 * Agrupa permissões por módulo
 */
export function groupPermissionsByModule(permissions: Permission[]): Record<string, Permission[]> {
  return permissions.reduce((acc, permission) => {
    const module = permission.module || 'general';
    if (!acc[module]) {
      acc[module] = [];
    }
    acc[module].push(permission);
    return acc;
  }, {} as Record<string, Permission[]>);
}

/**
 * Verifica se o usuário pode acessar um módulo específico
 */
export function canAccessModule(user: User | null, module: string): boolean {
  if (!user) {
    return false;
  }

  const userPermissions = getUserPermissions(user);
  
  // Verifica se o usuário tem pelo menos uma permissão do módulo
  return userPermissions.some(permission => 
    permission.startsWith(`${module}.`) || permission === module
  );
}
