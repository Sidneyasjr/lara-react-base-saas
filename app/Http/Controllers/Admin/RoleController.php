<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Http\JsonResponse;

class RoleController extends Controller
{
    protected PermissionService $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
        $this->middleware('permission:roles.view')->only(['index', 'show']);
        $this->middleware('permission:roles.create')->only(['create', 'store']);
        $this->middleware('permission:roles.edit')->only(['edit', 'update']);
        $this->middleware('permission:roles.delete')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::withCount(['users', 'permissions'])->get();
        $statistics = $this->permissionService->getRoleStatistics();

        return Inertia::render('Admin/Roles/Index', [
            'roles' => $roles,
            'statistics' => $statistics,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $permissions = $this->permissionService->getPermissionsByModule();

        return Inertia::render('Admin/Roles/Create', [
            'permissions' => $permissions,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'description' => 'nullable|string|max:500',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role = $this->permissionService->createRole(
            $request->name,
            $request->description ?? '',
            $request->permissions ?? []
        );

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        $role->load(['permissions', 'users']);
        
        $statistics = [
            'permissions_count' => $role->permissions->count(),
            'users_count' => $role->users->count(),
        ];

        return Inertia::render('Admin/Roles/Show', [
            'role' => $role,
            'permissions' => $role->permissions,
            'users' => $role->users,
            'statistics' => $statistics,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        $role->load('permissions');
        $permissions = $this->permissionService->getPermissionsByModule();

        return Inertia::render('Admin/Roles/Edit', [
            'role' => $role,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'description' => 'nullable|string|max:500',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role->update([
            'name' => $request->name,
            'description' => $request->description ?? '',
        ]);

        $this->permissionService->updateRolePermissions($role, $request->permissions ?? []);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        // Verificar se o role não é um role do sistema
        if (in_array($role->name, ['Super Admin', 'Admin', 'Manager', 'User'])) {
            return back()->with('error', 'Não é possível deletar roles do sistema.');
        }

        // Verificar se o role tem usuários associados
        if ($role->users()->count() > 0) {
            return back()->with('error', 'Não é possível deletar um role que possui usuários associados.');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role deletado com sucesso!');
    }

    /**
     * Get permissions for API.
     */
    public function permissions(): JsonResponse
    {
        $permissions = $this->permissionService->getPermissionsByModule();

        return response()->json($permissions);
    }

    /**
     * Assign role to user.
     */
    public function assignToUser(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_name' => 'required|exists:roles,name',
        ]);

        $user = \App\Models\User::find($request->user_id);
        $success = $this->permissionService->assignRole($user, $request->role_name);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Role atribuído com sucesso!' : 'Erro ao atribuir role.',
        ]);
    }

    /**
     * Remove role from user.
     */
    public function removeFromUser(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_name' => 'required|exists:roles,name',
        ]);

        $user = \App\Models\User::find($request->user_id);
        $success = $this->permissionService->removeRole($user, $request->role_name);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Role removido com sucesso!' : 'Erro ao remover role.',
        ]);
    }
}
