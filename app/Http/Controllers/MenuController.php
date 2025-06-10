<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Services\MenuService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class MenuController extends Controller
{
    protected MenuService $menuService;

    public function __construct(MenuService $menuService)
    {
        $this->menuService = $menuService;
    }

    /**
     * Retorna o menu para o usuário atual
     */
    public function getMenu(Request $request)
    {
        $module = $request->get('module');
        $tenantId = $request->get('tenant_id');
        
        $menu = $this->menuService->getMenuForUser(
            Auth::user(),
            $module,
            $tenantId
        );

        return response()->json([
            'menu' => $menu->values()->toArray(), // Força conversão para array indexado
            'modules' => $this->menuService->getAvailableModulesForUser(Auth::user())->values()->toArray()
        ]);
    }

    /**
     * Página de administração dos menus
     */
    public function index(): Response
    {
        $this->authorize('settings.menus');

        $menuItems = MenuItem::with(['parent', 'children'])
            ->orderBy('order_index')
            ->paginate(20);

        $statistics = $this->menuService->getMenuStatistics();
        $modules = MenuItem::getAvailableModules();

        return Inertia::render('Settings/MenuManagement', [
            'menuItems' => $menuItems,
            'statistics' => $statistics,
            'modules' => $modules,
        ]);
    }

    /**
     * Formulário para criar um novo item de menu
     */
    public function create(): Response
    {
        $this->authorize('settings.menus');

        $parentItems = MenuItem::rootLevel()
            ->active()
            ->orderBy('order_index')
            ->get(['id', 'title', 'module']);

        $modules = MenuItem::getAvailableModules();

        return Inertia::render('Settings/MenuItemForm', [
            'parentItems' => $parentItems,
            'modules' => $modules,
            'item' => null,
        ]);
    }

    /**
     * Armazena um novo item de menu
     */
    public function store(Request $request)
    {
        $this->authorize('settings.menus');

        $validatedData = $this->menuService->validateMenuData($request->all());

        $menuItem = $this->menuService->createMenuItem($validatedData);

        return redirect()
            ->route('settings.menus.index')
            ->with('success', 'Item de menu criado com sucesso!');
    }

    /**
     * Formulário para editar um item de menu
     */
    public function edit(MenuItem $menuItem): Response
    {
        $this->authorize('settings.menus');

        $parentItems = MenuItem::rootLevel()
            ->where('id', '!=', $menuItem->id)
            ->active()
            ->orderBy('order_index')
            ->get(['id', 'title', 'module']);

        $modules = MenuItem::getAvailableModules();

        return Inertia::render('Settings/MenuItemForm', [
            'parentItems' => $parentItems,
            'modules' => $modules,
            'item' => $menuItem->load('parent'),
        ]);
    }

    /**
     * Atualiza um item de menu
     */
    public function update(Request $request, MenuItem $menuItem)
    {
        $this->authorize('settings.menus');

        $validatedData = $this->menuService->validateMenuData($request->all());

        $this->menuService->updateMenuItem($menuItem, $validatedData);

        return redirect()
            ->route('settings.menus.index')
            ->with('success', 'Item de menu atualizado com sucesso!');
    }

    /**
     * Remove um item de menu
     */
    public function destroy(MenuItem $menuItem)
    {
        $this->authorize('settings.menus');

        try {
            $this->menuService->deleteMenuItem($menuItem);
            
            return redirect()
                ->route('settings.menus.index')
                ->with('success', 'Item de menu removido com sucesso!');
        } catch (\Exception $e) {
            return redirect()
                ->route('settings.menus.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Alterna o status ativo/inativo de um item
     */
    public function toggle(MenuItem $menuItem)
    {
        $this->authorize('settings.menus');

        $this->menuService->toggleMenuItem($menuItem);

        return response()->json([
            'message' => 'Status do item alterado com sucesso!',
            'is_active' => $menuItem->fresh()->is_active
        ]);
    }

    /**
     * Reordena os itens de menu
     */
    public function reorder(Request $request)
    {
        $this->authorize('settings.menus');

        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:menu_items,id',
            'items.*.order_index' => 'required|integer|min:0',
        ]);

        $this->menuService->reorderMenuItems($request->items);

        return response()->json([
            'message' => 'Ordem dos itens atualizada com sucesso!'
        ]);
    }

    /**
     * Retorna o breadcrumb para uma rota
     */
    public function breadcrumb(Request $request)
    {
        $routeName = $request->get('route');
        
        if (!$routeName) {
            return response()->json(['breadcrumb' => []]);
        }

        $breadcrumb = $this->menuService->getBreadcrumbForRoute($routeName);

        return response()->json(['breadcrumb' => $breadcrumb]);
    }

    /**
     * Limpa o cache dos menus
     */
    public function clearCache()
    {
        $this->authorize('settings.menus');

        $this->menuService->clearMenuCache();

        return response()->json([
            'message' => 'Cache dos menus limpo com sucesso!'
        ]);
    }

    /**
     * Busca itens de menu
     */
    public function search(Request $request)
    {
        $this->authorize('settings.menus');

        $query = $request->get('q');
        $module = $request->get('module');

        $menuItems = MenuItem::query()
            ->when($query, function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('route_name', 'like', "%{$query}%");
            })
            ->when($module, function ($q) use ($module) {
                $q->forModule($module);
            })
            ->with(['parent:id,title'])
            ->orderBy('order_index')
            ->limit(10)
            ->get();

        return response()->json(['items' => $menuItems]);
    }
}
