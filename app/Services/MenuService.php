<?php

namespace App\Services;

use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MenuService
{
    /**
     * Cache key prefix para menus
     */
    const CACHE_PREFIX = 'menu_';
    
    /**
     * Tempo de cache em minutos
     */
    const CACHE_TTL = 60;

    /**
     * Obtém o menu hierárquico para um usuário e módulo específico
     */
    public function getMenuForUser(
        ?User $user = null, 
        ?string $module = null, 
        ?int $tenantId = null,
        bool $useCache = true
    ): Collection {
        $user = $user ?? auth()->user();
        
        if ($useCache) {
            $cacheKey = $this->getCacheKey($user?->id, $module, $tenantId);
            
            return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user, $module, $tenantId) {
                return $this->buildMenuTree($user, $module, $tenantId);
            });
        }

        return $this->buildMenuTree($user, $module, $tenantId);
    }

    /**
     * Constrói a árvore de menu
     */
    protected function buildMenuTree(?User $user, ?string $module, ?int $tenantId): Collection
    {
        try {
            return MenuItem::getMenuTree($user, $module, $tenantId);
        } catch (\Exception $e) {
            Log::error('Erro ao construir árvore de menu', [
                'user_id' => $user?->id,
                'module' => $module,
                'tenant_id' => $tenantId,
                'error' => $e->getMessage()
            ]);
            
            return collect();
        }
    }

    /**
     * Obtém todos os módulos disponíveis para um usuário
     */
    public function getAvailableModulesForUser(?User $user = null): Collection
    {
        $user = $user ?? auth()->user();
        
        $allModules = MenuItem::getAvailableModules();
        
        if (!$user) {
            return collect();
        }

        // Filtra módulos baseado nas permissões do usuário
        return $allModules->filter(function ($module) use ($user) {
            return $this->userCanAccessModule($user, $module);
        });
    }

    /**
     * Verifica se o usuário pode acessar um módulo
     */
    public function userCanAccessModule(User $user, string $module): bool
    {
        // Verifica se existe pelo menos um item do módulo que o usuário pode acessar
        $menuItems = MenuItem::active()
            ->forModule($module)
            ->get();

        return $menuItems->some(function ($item) use ($user) {
            return $item->hasPermission($user);
        });
    }

    /**
     * Cria um novo item de menu
     */
    public function createMenuItem(array $data): MenuItem
    {
        $menuItem = MenuItem::create($data);
        
        $this->clearMenuCache();
        
        Log::info('Item de menu criado', [
            'item_id' => $menuItem->id,
            'title' => $menuItem->title,
            'module' => $menuItem->module
        ]);
        
        return $menuItem;
    }

    /**
     * Atualiza um item de menu
     */
    public function updateMenuItem(MenuItem $menuItem, array $data): MenuItem
    {
        $menuItem->update($data);
        
        $this->clearMenuCache();
        
        Log::info('Item de menu atualizado', [
            'item_id' => $menuItem->id,
            'title' => $menuItem->title
        ]);
        
        return $menuItem;
    }

    /**
     * Remove um item de menu
     */
    public function deleteMenuItem(MenuItem $menuItem): bool
    {
        // Verifica se tem filhos
        if ($menuItem->hasChildren()) {
            throw new \Exception('Não é possível excluir um item que possui sub-itens.');
        }

        $deleted = $menuItem->delete();
        
        if ($deleted) {
            $this->clearMenuCache();
            
            Log::info('Item de menu removido', [
                'item_id' => $menuItem->id,
                'title' => $menuItem->title
            ]);
        }
        
        return $deleted;
    }

    /**
     * Reordena itens de menu
     */
    public function reorderMenuItems(array $itemOrders): void
    {
        foreach ($itemOrders as $order) {
            MenuItem::where('id', $order['id'])
                ->update(['order_index' => $order['order_index']]);
        }
        
        $this->clearMenuCache();
        
        Log::info('Itens de menu reordenados', [
            'items_count' => count($itemOrders)
        ]);
    }

    /**
     * Ativa/desativa um item de menu
     */
    public function toggleMenuItem(MenuItem $menuItem): MenuItem
    {
        $menuItem->update(['is_active' => !$menuItem->is_active]);
        
        $this->clearMenuCache();
        
        Log::info('Status do item de menu alterado', [
            'item_id' => $menuItem->id,
            'is_active' => $menuItem->is_active
        ]);
        
        return $menuItem;
    }

    /**
     * Obtém o breadcrumb para uma rota específica
     */
    public function getBreadcrumbForRoute(string $routeName): Collection
    {
        $menuItem = MenuItem::findByRoute($routeName);
        
        if (!$menuItem) {
            return collect();
        }

        $breadcrumb = collect();
        $current = $menuItem;

        // Constrói o breadcrumb subindo na hierarquia
        while ($current) {
            $breadcrumb->prepend([
                'title' => $current->title,
                'href' => $current->route_name ? route($current->route_name) : null,
                'is_current' => $current->id === $menuItem->id
            ]);
            
            $current = $current->parent;
        }

        return $breadcrumb;
    }

    /**
     * Encontra item de menu pelo título
     */
    public function findMenuItemByTitle(string $title, ?string $module = null): ?MenuItem
    {
        $query = MenuItem::where('title', 'like', "%{$title}%");
        
        if ($module) {
            $query->forModule($module);
        }
        
        return $query->first();
    }

    /**
     * Obtém estatísticas do menu
     */
    public function getMenuStatistics(): array
    {
        return [
            'total_items' => MenuItem::count(),
            'active_items' => MenuItem::active()->count(),
            'inactive_items' => MenuItem::where('is_active', false)->count(),
            'root_items' => MenuItem::rootLevel()->count(),
            'items_by_module' => MenuItem::selectRaw('module, COUNT(*) as count')
                ->groupBy('module')
                ->pluck('count', 'module')
                ->toArray(),
            'items_with_permissions' => MenuItem::whereNotNull('permission_required')->count(),
        ];
    }

    /**
     * Limpa todo o cache de menus
     */
    public function clearMenuCache(): void
    {
        $pattern = self::CACHE_PREFIX . '*';
        
        // Remove todas as chaves de cache que começam com o prefixo
        $keys = Cache::getRedis()->keys($pattern);
        
        if (!empty($keys)) {
            Cache::getRedis()->del($keys);
        }
        
        Log::info('Cache de menus limpo');
    }

    /**
     * Gera a chave de cache para o menu
     */
    protected function getCacheKey(?int $userId, ?string $module, ?int $tenantId): string
    {
        return self::CACHE_PREFIX . implode('_', [
            $userId ?? 'guest',
            $module ?? 'all',
            $tenantId ?? 'global'
        ]);
    }

    /**
     * Valida a estrutura de dados do menu
     */
    public function validateMenuData(array $data): array
    {
        $rules = [
            'title' => 'required|string|max:255',
            'route_name' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:100',
            'permission_required' => 'nullable|string|max:255',
            'order_index' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'module' => 'required|string|max:100',
            'parent_id' => 'nullable|exists:menu_items,id',
            'tenant_id' => 'nullable|integer'
        ];

        return validator($data, $rules)->validate();
    }
}
