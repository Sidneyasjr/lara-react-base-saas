<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class PermissionController extends Controller
{
    protected PermissionService $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
        $this->middleware('permission:view permissions')->only(['index', 'show']);
        $this->middleware('permission:create permissions')->only(['create', 'store']);
        $this->middleware('permission:edit permissions')->only(['edit', 'update']);
        $this->middleware('permission:delete permissions')->only(['destroy']);
    }

    /**
     * Display a listing of permissions.
     */
    public function index(Request $request): Response
    {
        $search = $request->get('search');
        $module = $request->get('module');
        $perPage = $request->get('per_page', 15);

        $query = Permission::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($module) {
            $query->where('module', $module);
        }

        $permissions = $query->orderBy('module')
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        $modules = Permission::getModules();
        $statistics = $this->getPermissionStatistics();

        return Inertia::render('admin/permissions/Index', [
            'permissions' => $permissions,
            'modules' => $modules,
            'statistics' => $statistics,
            'filters' => [
                'search' => $search,
                'module' => $module,
                'per_page' => $perPage,
            ],
        ]);
    }

    /**
     * Show the form for creating a new permission.
     */
    public function create(): Response
    {
        $modules = Permission::getModules();
        $existingModules = collect($modules)->map(fn($module) => ['value' => $module, 'label' => $module]);

        return Inertia::render('admin/permissions/Create', [
            'existingModules' => $existingModules,
        ]);
    }

    /**
     * Store a newly created permission.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
            'module' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        Permission::create([
            'name' => $request->name,
            'module' => $request->module,
            'description' => $request->description,
            'guard_name' => 'web',
        ]);

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permissão criada com sucesso!');
    }

    /**
     * Display the specified permission.
     */
    public function show(Permission $permission): Response
    {
        $permission->load(['roles' => function ($query) {
            $query->withCount('users');
        }]);

        $rolesWithPermission = $permission->roles;
        $usersWithPermission = collect();

        // Get users who have this permission through roles or direct assignment
        foreach ($rolesWithPermission as $role) {
            $roleUsers = $role->users()->get();
            $usersWithPermission = $usersWithPermission->merge($roleUsers);
        }

        // Add users with direct permission
        $directUsers = $permission->users()->get();
        $usersWithPermission = $usersWithPermission->merge($directUsers)->unique('id');

        return Inertia::render('admin/permissions/Show', [
            'permission' => $permission,
            'roles' => $rolesWithPermission,
            'users' => $usersWithPermission->values(),
            'statistics' => [
                'roles_count' => $rolesWithPermission->count(),
                'users_count' => $usersWithPermission->count(),
            ],
        ]);
    }

    /**
     * Show the form for editing the specified permission.
     */
    public function edit(Permission $permission): Response
    {
        $modules = Permission::getModules();
        $existingModules = collect($modules)->map(fn($module) => ['value' => $module, 'label' => $module]);

        return Inertia::render('admin/permissions/Edit', [
            'permission' => $permission,
            'existingModules' => $existingModules,
        ]);
    }

    /**
     * Update the specified permission.
     */
    public function update(Request $request, Permission $permission): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $permission->id,
            'module' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $permission->update([
            'name' => $request->name,
            'module' => $request->module,
            'description' => $request->description,
        ]);

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permissão atualizada com sucesso!');
    }

    /**
     * Remove the specified permission.
     */
    public function destroy(Permission $permission): RedirectResponse
    {
        // Check if permission is used by any roles
        if ($permission->roles()->count() > 0) {
            return back()->with('error', 'Não é possível deletar uma permissão que está sendo usada por roles.');
        }

        // Check if permission is directly assigned to users
        if ($permission->users()->count() > 0) {
            return back()->with('error', 'Não é possível deletar uma permissão que está atribuída diretamente a usuários.');
        }

        $permission->delete();

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permissão deletada com sucesso!');
    }

    /**
     * Get permissions for API.
     */
    public function api(): JsonResponse
    {
        $permissions = $this->permissionService->getPermissionsByModule();

        return response()->json($permissions);
    }

    /**
     * Bulk assign permissions to role.
     */
    public function bulkAssignToRole(Request $request): JsonResponse
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permission_ids' => 'required|array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);

        $role = Role::find($request->role_id);
        $permissions = Permission::whereIn('id', $request->permission_ids)->get();

        $role->givePermissionTo($permissions);

        return response()->json([
            'success' => true,
            'message' => 'Permissões atribuídas ao role com sucesso!',
        ]);
    }

    /**
     * Bulk remove permissions from role.
     */
    public function bulkRemoveFromRole(Request $request): JsonResponse
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permission_ids' => 'required|array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);

        $role = Role::find($request->role_id);
        $permissions = Permission::whereIn('id', $request->permission_ids)->get();

        $role->revokePermissionTo($permissions);

        return response()->json([
            'success' => true,
            'message' => 'Permissões removidas do role com sucesso!',
        ]);
    }

    /**
     * Get permission statistics.
     */
    private function getPermissionStatistics(): array
    {
        $permissions = Permission::with(['roles', 'users'])->get();
        $modules = Permission::getModules();

        return [
            'total_permissions' => $permissions->count(),
            'total_modules' => count($modules),
            'permissions_by_module' => collect($modules)->mapWithKeys(function ($module) use ($permissions) {
                return [$module => $permissions->where('module', $module)->count()];
            }),
            'most_used_permissions' => $permissions->sortByDesc(function ($permission) {
                return $permission->roles->count() + $permission->users->count();
            })->take(5)->map(function ($permission) {
                return [
                    'name' => $permission->name,
                    'usage_count' => $permission->roles->count() + $permission->users->count(),
                ];
            })->values(),
        ];
    }
}