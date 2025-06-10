import { useEffect, useState, useCallback } from 'react';
import { usePage } from '@inertiajs/react';
import { MenuItem } from '@/types';

// Cache simples em memória para evitar flash no reload
const menuCache = new Map<string, { menu: MenuItem[]; modules: string[]; timestamp: number }>();
const CACHE_DURATION = 5 * 60 * 1000; // 5 minutos

interface UseMenuReturn {
  menu: MenuItem[];
  modules: string[];
  loading: boolean;
  error: string | null;
  refetch: () => void;
}

interface UseMenuOptions {
  module?: string;
  tenant_id?: number;
  auto_fetch?: boolean;
  use_shared_data?: boolean;
}

export function useMenu(options: UseMenuOptions = {}): UseMenuReturn {
  const { menu: sharedMenu } = usePage<{ menu: MenuItem[] }>().props;
  const [menu, setMenu] = useState<MenuItem[]>([]);
  const [modules, setModules] = useState<string[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const { module, tenant_id, auto_fetch = true, use_shared_data = true } = options;

  // Gera chave de cache baseada nos parâmetros
  const cacheKey = `menu_${module || 'default'}_${tenant_id || 'default'}`;

  // Verifica se deve usar dados compartilhados (sem filtros específicos)
  const canUseSharedData = use_shared_data && !module && !tenant_id;

  // Inicialização com dados compartilhados ou cache
  useEffect(() => {
    if (canUseSharedData && sharedMenu && Array.isArray(sharedMenu) && sharedMenu.length > 0) {
      console.debug('Menu carregado dos dados compartilhados do Inertia');
      setMenu(sharedMenu);
      setModules([]); // Módulos podem ser derivados do menu se necessário
      setLoading(false);
      
      // Salva no cache também
      menuCache.set(cacheKey, {
        menu: sharedMenu,
        modules: [],
        timestamp: Date.now(),
      });
      return;
    }

    // Fallback para cache
    const cached = menuCache.get(cacheKey);
    if (cached && Date.now() - cached.timestamp < CACHE_DURATION) {
      setMenu(cached.menu);
      setModules(cached.modules);
      setLoading(false);
    }
  }, [cacheKey, canUseSharedData, sharedMenu]);

  const fetchMenu = useCallback(async () => {
    const startTime = performance.now();
    
    try {
      // Verifica cache primeiro
      const cached = menuCache.get(cacheKey);
      if (cached && Date.now() - cached.timestamp < CACHE_DURATION) {
        console.debug(`Menu carregado do cache em ${(performance.now() - startTime).toFixed(2)}ms`);
        setMenu(cached.menu);
        setModules(cached.modules);
        setLoading(false);
        return;
      }

      // Não mostra loading se já temos dados (para evitar flash)
      if (menu.length === 0) {
        setLoading(true);
      }
      setError(null);

      const params = new URLSearchParams();
      if (module) params.append('module', module);
      if (tenant_id) params.append('tenant_id', tenant_id.toString());

      const response = await fetch(`/api/menu?${params.toString()}`, {
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data = await response.json();
      
      // Garante que menu e modules são sempre arrays
      const menuData = Array.isArray(data.menu) ? data.menu : [];
      const modulesData = Array.isArray(data.modules) ? data.modules : [];
      
      // Salva no cache
      menuCache.set(cacheKey, {
        menu: menuData,
        modules: modulesData,
        timestamp: Date.now(),
      });
      
      const loadTime = performance.now() - startTime;
      console.debug(`Menu carregado da API em ${loadTime.toFixed(2)}ms`);
      
      setMenu(menuData);
      setModules(modulesData);
    } catch (err) {
      console.error('Erro ao buscar menu:', err);
      setError(err instanceof Error ? err.message : 'Erro desconhecido');
      setMenu([]);
      setModules([]);
    } finally {
      setLoading(false);
    }
  }, [module, tenant_id, menu.length, cacheKey]);

  useEffect(() => {
    if (auto_fetch && (!canUseSharedData || !sharedMenu || sharedMenu.length === 0)) {
      fetchMenu();
    }
  }, [auto_fetch, fetchMenu, canUseSharedData, sharedMenu]);

  return {
    menu,
    modules,
    loading,
    error,
    refetch: fetchMenu,
  };
}

interface UseBreadcrumbReturn {
  breadcrumb: Array<{
    title: string;
    href: string | null;
    is_current: boolean;
  }>;
  loading: boolean;
  error: string | null;
}

export function useBreadcrumb(routeName?: string): UseBreadcrumbReturn {
  const [breadcrumb, setBreadcrumb] = useState<UseBreadcrumbReturn['breadcrumb']>([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (!routeName) {
      setBreadcrumb([]);
      return;
    }

    const fetchBreadcrumb = async () => {
      try {
        setLoading(true);
        setError(null);

        const response = await fetch(`/api/menu/breadcrumb?route=${encodeURIComponent(routeName)}`, {
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
          },
        });

        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        setBreadcrumb(data.breadcrumb || []);
      } catch (err) {
        console.error('Erro ao buscar breadcrumb:', err);
        setError(err instanceof Error ? err.message : 'Erro desconhecido');
        setBreadcrumb([]);
      } finally {
        setLoading(false);
      }
    };

    fetchBreadcrumb();
  }, [routeName]);

  return {
    breadcrumb,
    loading,
    error,
  };
}
