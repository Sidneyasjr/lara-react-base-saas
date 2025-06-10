<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class MenuItem extends Model
{
    protected $fillable = [
        'tenant_id',
        'parent_id',
        'title',
        'route_name',
        'icon',
        'permission_required',
        'order_index',
        'is_active',
        'module',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order_index' => 'integer',
    ];

    /**
     * Relacionamento com o item pai
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    /**
     * Relacionamento com os itens filhos
     */
    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')
            ->where('is_active', true)
            ->orderBy('order_index');
    }

    /**
     * Relacionamento com todos os descendentes (recursivo)
     */
    public function descendants(): HasMany
    {
        return $this->children()->with('descendants');
    }

    /**
     * Scope para itens ativos
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para itens de nível raiz (sem pai)
     */
    public function scopeRootLevel(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope para ordenar por índice
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order_index');
    }

    /**
     * Scope para filtrar por módulo
     */
    public function scopeForModule(Builder $query, string $module): Builder
    {
        return $query->where('module', $module);
    }

    /**
     * Scope para filtrar por tenant
     */
    public function scopeForTenant(Builder $query, ?int $tenantId = null): Builder
    {
        if ($tenantId) {
            return $query->where(function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId)
                  ->orWhereNull('tenant_id'); // Itens globais
            });
        }
        
        return $query->whereNull('tenant_id');
    }

    /**
     * Verifica se o usuário tem permissão para acessar este item
     */
    public function hasPermission(?User $user = null): bool
    {
        if (!$this->permission_required) {
            return true;
        }

        if (!$user) {
            $user = auth()->user();
        }

        if (!$user) {
            return false;
        }

        return $user->can($this->permission_required);
    }

    /**
     * Verifica se é um item de nível raiz
     */
    public function isRootLevel(): bool
    {
        return is_null($this->parent_id);
    }

    /**
     * Verifica se tem filhos
     */
    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    /**
     * Retorna o menu hierárquico com permissões filtradas
     */
    public static function getMenuTree(?User $user = null, ?string $module = null, ?int $tenantId = null): Collection
    {
        $query = static::active()
            ->rootLevel()
            ->ordered()
            ->with(['descendants' => function ($query) {
                $query->active()->ordered();
            }]);

        if ($module) {
            $query->forModule($module);
        }

        if ($tenantId !== null) {
            $query->forTenant($tenantId);
        }

        $menuItems = $query->get();

        return $menuItems->filter(function ($item) use ($user) {
            return static::filterItemWithChildren($item, $user);
        });
    }

    /**
     * Filtra um item e seus filhos baseado nas permissões
     */
    protected static function filterItemWithChildren(MenuItem $item, ?User $user = null): bool
    {
        // Se o item não tem permissão, verifica se algum filho tem
        if (!$item->hasPermission($user)) {
            // Se tem filhos, verifica se algum deles tem permissão
            if ($item->children->isNotEmpty()) {
                $hasValidChildren = $item->children->some(function ($child) use ($user) {
                    return static::filterItemWithChildren($child, $user);
                });
                
                if (!$hasValidChildren) {
                    return false;
                }
            } else {
                return false;
            }
        }

        // Filtra os filhos recursivamente
        $item->setRelation('children', $item->children->filter(function ($child) use ($user) {
            return static::filterItemWithChildren($child, $user);
        }));

        return true;
    }

    /**
     * Encontra um item por rota
     */
    public static function findByRoute(string $routeName): ?MenuItem
    {
        return static::where('route_name', $routeName)->first();
    }

    /**
     * Retorna todos os módulos disponíveis
     */
    public static function getAvailableModules(): Collection
    {
        return static::select('module')
            ->distinct()
            ->whereNotNull('module')
            ->orderBy('module')
            ->pluck('module');
    }
}
