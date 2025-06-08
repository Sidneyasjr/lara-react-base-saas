<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Services\PermissionService;

class ManagePermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permission:manage 
                            {action : The action to perform (list-roles, list-permissions, assign-role, remove-role, create-role, user-permissions)}
                            {--user= : User email for user-specific actions}
                            {--role= : Role name}
                            {--name= : Name for new role}
                            {--description= : Description for new role}
                            {--permissions=* : Permissions for new role}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage roles and permissions';

    protected PermissionService $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        parent::__construct();
        $this->permissionService = $permissionService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'list-roles':
                $this->listRoles();
                break;
            case 'list-permissions':
                $this->listPermissions();
                break;
            case 'assign-role':
                $this->assignRole();
                break;
            case 'remove-role':
                $this->removeRole();
                break;
            case 'create-role':
                $this->createRole();
                break;
            case 'user-permissions':
                $this->showUserPermissions();
                break;
            default:
                $this->error("Ação desconhecida: {$action}");
                $this->showHelp();
        }
    }

    protected function listRoles()
    {
        $roles = $this->permissionService->getRolesWithPermissionCount();
        
        $this->info('Roles disponíveis:');
        $this->table(
            ['ID', 'Nome', 'Descrição', 'Permissions', 'Usuários'],
            $roles->map(function ($role) {
                return [
                    $role->id,
                    $role->name,
                    $role->description ?? 'N/A',
                    $role->permissions_count,
                    $role->users()->count(),
                ];
            })
        );
    }

    protected function listPermissions()
    {
        $permissions = $this->permissionService->getPermissionsByModule();
        
        foreach ($permissions as $module => $modulePermissions) {
            $this->info("\nMódulo: {$module}");
            $this->table(
                ['Permission', 'Descrição'],
                $modulePermissions->map(function ($permission) {
                    return [
                        $permission->name,
                        $permission->description ?? 'N/A',
                    ];
                })
            );
        }
    }

    protected function assignRole()
    {
        $userEmail = $this->option('user');
        $roleName = $this->option('role');

        if (!$userEmail || !$roleName) {
            $this->error('É necessário fornecer --user e --role');
            return;
        }

        $user = User::where('email', $userEmail)->first();
        if (!$user) {
            $this->error("Usuário com email {$userEmail} não encontrado");
            return;
        }

        $success = $this->permissionService->assignRole($user, $roleName);
        
        if ($success) {
            $this->info("Role '{$roleName}' atribuído com sucesso ao usuário {$userEmail}");
        } else {
            $this->error("Erro ao atribuir role. Verifique se o role '{$roleName}' existe.");
        }
    }

    protected function removeRole()
    {
        $userEmail = $this->option('user');
        $roleName = $this->option('role');

        if (!$userEmail || !$roleName) {
            $this->error('É necessário fornecer --user e --role');
            return;
        }

        $user = User::where('email', $userEmail)->first();
        if (!$user) {
            $this->error("Usuário com email {$userEmail} não encontrado");
            return;
        }

        $success = $this->permissionService->removeRole($user, $roleName);
        
        if ($success) {
            $this->info("Role '{$roleName}' removido com sucesso do usuário {$userEmail}");
        } else {
            $this->error("Erro ao remover role. Verifique se o role '{$roleName}' existe.");
        }
    }

    protected function createRole()
    {
        $name = $this->option('name');
        $description = $this->option('description') ?? '';
        $permissions = $this->option('permissions') ?? [];

        if (!$name) {
            $this->error('É necessário fornecer --name para o novo role');
            return;
        }

        try {
            $role = $this->permissionService->createRole($name, $description, $permissions);
            $this->info("Role '{$name}' criado com sucesso!");
            
            if (!empty($permissions)) {
                $this->info("Permissions atribuídas: " . implode(', ', $permissions));
            }
        } catch (\Exception $e) {
            $this->error("Erro ao criar role: " . $e->getMessage());
        }
    }

    protected function showUserPermissions()
    {
        $userEmail = $this->option('user');

        if (!$userEmail) {
            $this->error('É necessário fornecer --user');
            return;
        }

        $user = User::where('email', $userEmail)->first();
        if (!$user) {
            $this->error("Usuário com email {$userEmail} não encontrado");
            return;
        }

        $permissions = $this->permissionService->getUserPermissions($user);
        
        $this->info("Permissions do usuário {$userEmail}:");
        
        $this->info("\nRoles:");
        foreach ($user->roles as $role) {
            $this->line("- {$role->name}");
        }

        $this->info("\nPermissions via roles:");
        foreach ($permissions['via_roles'] as $permission) {
            $this->line("- {$permission->name} ({$permission->description})");
        }

        if ($permissions['direct']->count() > 0) {
            $this->info("\nPermissions diretas:");
            foreach ($permissions['direct'] as $permission) {
                $this->line("- {$permission->name} ({$permission->description})");
            }
        }
    }

    protected function showHelp()
    {
        $this->info('Ações disponíveis:');
        $this->line('list-roles                              - Listar todos os roles');
        $this->line('list-permissions                        - Listar todas as permissions');
        $this->line('assign-role --user=email --role=nome    - Atribuir role a usuário');
        $this->line('remove-role --user=email --role=nome    - Remover role de usuário');
        $this->line('create-role --name=nome [--description=desc] [--permissions=perm1,perm2] - Criar novo role');
        $this->line('user-permissions --user=email           - Mostrar permissions de usuário');
    }
}
