<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\MenuItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class PermissionsSystemTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed basic permissions and roles
        $this->seed([
            \Database\Seeders\PublicPermissionsSeeder::class,
            \Database\Seeders\RolesAndPermissionsSeeder::class,
        ]);
    }

    /** @test */
    public function user_can_access_route_with_correct_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('admin.users.index');

        $response = $this->actingAs($user)
            ->get(route('admin.users.index'));

        $response->assertStatus(200);
    }

    /** @test */
    public function user_cannot_access_route_without_permission()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('admin.users.index'));

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_role_has_expected_permissions()
    {
        $adminRole = Role::where('name', 'Admin')->first();
        
        $this->assertTrue($adminRole->hasPermissionTo('admin.users.index'));
        $this->assertTrue($adminRole->hasPermissionTo('admin.users.create'));
        $this->assertTrue($adminRole->hasPermissionTo('admin.users.edit'));
    }

    /** @test */
    public function user_role_has_limited_permissions()
    {
        $userRole = Role::where('name', 'User')->first();
        
        $this->assertFalse($userRole->hasPermissionTo('admin.users.index'));
        $this->assertTrue($userRole->hasPermissionTo('settings.profile.edit'));
    }

    /** @test */
    public function menu_items_filter_by_user_permissions()
    {
        $user = User::factory()->create();
        $user->givePermissionTo(['admin.users.index', 'dashboard']);

        $menuTree = MenuItem::getMenuTree($user);
        
        $visibleMenus = $menuTree->pluck('route_name')->toArray();
        
        $this->assertContains('admin.users.index', $visibleMenus);
        $this->assertContains('dashboard', $visibleMenus);
    }

    /** @test */
    public function menu_items_hide_when_user_lacks_permission()
    {
        $user = User::factory()->create();
        // User has no permissions

        $menuTree = MenuItem::getMenuTree($user);
        
        $this->assertEmpty($menuTree);
    }

    /** @test */
    public function permission_validation_command_detects_orphaned_permissions()
    {
        // Create a permission that doesn't match any route
        Permission::create([
            'name' => 'fake.permission',
            'guard_name' => 'web',
            'module' => 'test',
            'description' => 'Fake permission for testing'
        ]);

        $this->artisan('permissions:validate')
            ->expectsOutput('⚠️ Found 1 permissions without matching routes:')
            ->assertExitCode(0);
    }

    /** @test */
    public function backup_command_creates_valid_backup()
    {
        $filename = 'test_backup.json';
        
        $this->artisan('permissions:backup', ['--filename' => $filename])
            ->assertExitCode(0);

        $backupPath = storage_path('app/backups/' . $filename);
        $this->assertFileExists($backupPath);

        $backup = json_decode(file_get_contents($backupPath), true);
        $this->assertArrayHasKey('permissions', $backup);
        $this->assertArrayHasKey('roles', $backup);
        $this->assertArrayHasKey('menu_items', $backup);
    }

    /** @test */
    public function generate_permissions_from_routes_creates_valid_seeder()
    {
        $this->artisan('permissions:generate-from-routes')
            ->assertExitCode(0);

        $seederPath = database_path('seeders/GeneratedPermissionsSeeder.php');
        $this->assertFileExists($seederPath);

        // Test that the generated seeder is valid PHP
        $content = file_get_contents($seederPath);
        $this->assertStringContainsString('class GeneratedPermissionsSeeder', $content);
        $this->assertStringContainsString('public function run()', $content);
    }

    /** @test */
    public function user_with_super_admin_role_can_access_everything()
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        // Test a few different admin routes
        $routes = [
            'admin.users.index',
            'admin.roles.index', 
            'admin.permissions.index',
            'admin.menus.index'
        ];

        foreach ($routes as $routeName) {
            $response = $this->actingAs($user)->get(route($routeName));
            $response->assertStatus(200);
        }
    }

    /** @test */
    public function permissions_report_command_generates_accurate_data()
    {
        $this->artisan('permissions:report', ['--format' => 'json'])
            ->assertExitCode(0);

        // Capture the output
        $output = $this->artisan('permissions:report', ['--format' => 'json'])
            ->run();

        // The JSON output should be valid
        $this->assertIsString($output);
    }

    /** @test */
    public function middleware_protects_admin_routes()
    {
        $user = User::factory()->create();
        // User has no admin permissions

        $response = $this->actingAs($user)
            ->get('/admin/users');

        $response->assertStatus(403);
    }

    /** @test */
    public function api_menu_endpoint_returns_filtered_menu()
    {
        $user = User::factory()->create();
        $user->givePermissionTo(['dashboard', 'admin.users.index']);

        $response = $this->actingAs($user)
            ->getJson('/api/menu');

        $response->assertStatus(200)
            ->assertJsonCount(2); // Should return 2 menu items
    }

    /** @test */
    public function breadcrumb_api_returns_correct_path()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('admin.users.index');

        $response = $this->actingAs($user)
            ->getJson('/api/menu/breadcrumb?route=admin.users.index');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => ['title', 'route_name']
            ]);
    }

    /** @test */
    public function role_hierarchy_is_maintained()
    {
        $superAdmin = Role::where('name', 'Super Admin')->first();
        $admin = Role::where('name', 'Admin')->first();
        $user = Role::where('name', 'User')->first();

        // Super Admin should have more permissions than Admin
        $this->assertGreaterThan(
            $admin->permissions->count(),
            $superAdmin->permissions->count()
        );

        // Admin should have more permissions than User
        $this->assertGreaterThan(
            $user->permissions->count(),
            $admin->permissions->count()
        );
    }

    /** @test */
    public function permission_caching_works_correctly()
    {
        $user = User::factory()->create();
        $permission = Permission::first();
        
        // Give permission
        $user->givePermissionTo($permission);
        $this->assertTrue($user->can($permission->name));

        // Remove permission
        $user->revokePermissionTo($permission);
        
        // Clear cache to ensure fresh check
        app()['cache']->forget('spatie.permission.cache');
        
        $this->assertFalse($user->can($permission->name));
    }
}
