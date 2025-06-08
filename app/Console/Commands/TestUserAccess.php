<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Http\Controllers\Admin\UserController;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TestUserAccess extends Command
{
    protected $signature = 'test:user-access {email}';
    protected $description = 'Test if a user can access the user management system';

    public function handle()
    {
        $email = $this->argument('email');
        
        $user = User::where('email', $email)->first();
        if (!$user) {
            $this->error("User with email {$email} not found");
            return;
        }

        // Login the user
        Auth::login($user);
        
        $this->info("Testing access for user: {$user->name}");
        $this->info("User roles: " . $user->roles->pluck('name')->implode(', '));
        
        // Test each permission
        $permissions = ['view users', 'create users', 'edit users', 'delete users', 'manage users'];
        
        $this->info("\n=== Permission Check ===");
        foreach ($permissions as $permission) {
            $hasPermission = $user->can($permission);
            $status = $hasPermission ? '✅ YES' : '❌ NO';
            $this->line("{$permission}: {$status}");
        }
        
        // Test middleware simulation
        $this->info("\n=== Middleware Simulation ===");
        
        // Simulate the middleware checks that happen in UserController
        $middlewareTests = [
            'index/show (view users)' => $user->can('view users'),
            'create/store (create users)' => $user->can('create users'),
            'edit/update (edit users)' => $user->can('edit users'),
            'destroy (delete users)' => $user->can('delete users'),
        ];
        
        foreach ($middlewareTests as $action => $result) {
            $status = $result ? '✅ PASS' : '❌ FAIL';
            $this->line("{$action}: {$status}");
        }
        
        // Try to instantiate the controller
        $this->info("\n=== Controller Test ===");
        try {
            $controller = new UserController(new PermissionService());
            $this->info('✅ UserController can be instantiated');
            
            // Check if user can access admin area
            $canAccessAdmin = $user->can('access admin');
            $adminStatus = $canAccessAdmin ? '✅ YES' : '❌ NO';
            $this->line("Can access admin area: {$adminStatus}");
            
        } catch (\Exception $e) {
            $this->error("❌ Error instantiating UserController: " . $e->getMessage());
        }
        
        $this->info("\n=== Summary ===");
        if ($user->can('view users') && $user->can('access admin')) {
            $this->info('✅ User CAN access user management system');
        } else {
            $this->error('❌ User CANNOT access user management system');
            
            if (!$user->can('access admin')) {
                $this->error('   - Missing "access admin" permission');
            }
            if (!$user->can('view users')) {
                $this->error('   - Missing "view users" permission');
            }
        }
    }
}
