<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    protected PermissionService $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
        $this->middleware('permission:view users', ['only' => ['index', 'show']]);
        $this->middleware('permission:create users', ['only' => ['create', 'store']]);
        $this->middleware('permission:edit users', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete users', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::with(['roles']);

        // Filtros
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('role')) {
            $query->role($request->role);
        }

        $perPage = $request->input('per_page', 15);
        $users = $query->paginate($perPage)->appends($request->query());
        $roles = Role::all();

        return Inertia::render('admin/users/index', [
            'users' => $users,
            'roles' => $roles,
            'filters' => $request->only(['search', 'role', 'per_page']) + ['per_page' => $perPage],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::all();

        return Inertia::render('admin/users/create', [
            'roles' => $roles,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'roles' => 'array',
            'roles.*' => 'exists:roles,name',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        if ($request->filled('roles')) {
            $user->assignRole($request->roles);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuário criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $user->load(['roles', 'permissions']);
        $userPermissions = $this->permissionService->getUserPermissions($user);
        $permissionsByModule = collect($userPermissions['all'])->groupBy('module');

        return Inertia::render('admin/users/show', [
            'user' => $user,
            'userPermissions' => $userPermissions,
            'permissionsByModule' => $permissionsByModule,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $user->load('roles');
        $roles = Role::all();
        $userRoles = $user->roles->pluck('name')->toArray();

        return Inertia::render('admin/users/edit', [
            'user' => $user,
            'roles' => $roles,
            'userRoles' => $userRoles,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:users,email,' . $user->id,
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'roles' => 'array',
            'roles.*' => 'exists:roles,name',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        if ($request->filled('password')) {
            $user->update([
                'password' => Hash::make($request->password),
            ]);
        }

        // Sincronizar roles
        $user->syncRoles($request->roles ?? []);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuário atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // Verificar se não é o próprio usuário
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Você não pode deletar sua própria conta.');
        }

        // Verificar se não é um super admin (proteção extra)
        if ($user->hasRole('Super Admin')) {
            return back()->with('error', 'Não é possível deletar um Super Admin.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuário deletado com sucesso!');
    }

    /**
     * Update user roles.
     */
    public function updateRoles(Request $request, User $user)
    {
        $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name',
        ]);

        $user->syncRoles($request->roles);

        return response()->json([
            'success' => true,
            'message' => 'Roles atualizados com sucesso!',
        ]);
    }

    /**
     * Get user permissions summary.
     */
    public function permissions(User $user)
    {
        $permissions = $this->permissionService->getUserPermissions($user);

        return response()->json($permissions);
    }
}
