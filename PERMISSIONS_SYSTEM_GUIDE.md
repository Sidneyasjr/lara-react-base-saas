# Sistema de Permissões e Menus - Laravel + Inertia + React

## 📋 Visão Geral

Este documento descreve o sistema completo de permissões e menus implementado para aplicações Laravel com Inertia.js e React. O sistema fornece uma solução robusta, escalável e fácil de manter para controle de acesso.

## 🏗️ Arquitetura

### Componentes Principais

1. **Models**
   - `Permission` - Gerencia permissões individuais
   - `Role` - Gerencia grupos de permissões
   - `User` - Usuários com roles e permissões
   - `MenuItem` - Estrutura hierárquica de menus

2. **Seeders**
   - `PublicPermissionsSeeder` - Permissões públicas básicas
   - `RolesAndPermissionsSeeder` - Roles e permissões administrativas
   - `MenuItemsSeeder` - Estrutura de menus
   - `ProductionPermissionsSeeder` - Setup otimizado para produção
   - `AdvancedPermissionsSeeder` - Permissões avançadas (opcional)

3. **Comandos Console**
   - `permissions:generate-from-routes` - Gera permissões automaticamente das rotas
   - `permissions:validate` - Valida integridade do sistema
   - `permissions:report` - Relatórios detalhados
   - `permissions:backup` - Backup e restore

## 🚀 Instalação e Setup

### 1. Migrações
```bash
php artisan migrate
```

### 2. Seeders Básicos
```bash
# Setup completo
php artisan db:seed

# Ou individual
php artisan db:seed --class=PublicPermissionsSeeder
php artisan db:seed --class=RolesAndPermissionsSeeder
php artisan db:seed --class=MenuItemsSeeder
```

### 3. Setup para Produção
```bash
php artisan db:seed --class=ProductionPermissionsSeeder
```

## 🔧 Comandos Disponíveis

### Geração Automática de Permissões
```bash
# Visualizar o que seria gerado
php artisan permissions:generate-from-routes --dry-run

# Gerar todas as permissões
php artisan permissions:generate-from-routes

# Filtrar por prefix
php artisan permissions:generate-from-routes --filter=admin

# Executar o seeder gerado
php artisan db:seed --class=GeneratedPermissionsSeeder
```

### Validação do Sistema
```bash
# Verificar integridade
php artisan permissions:validate

# Corrigir problemas automaticamente
php artisan permissions:validate --fix

# Remover permissões órfãs
php artisan permissions:validate --fix --remove-orphaned
```

### Relatórios
```bash
# Relatório em tabela
php artisan permissions:report

# Exportar como JSON
php artisan permissions:report --format=json --export=report.json

# Relatório de role específico
php artisan permissions:report --role="Admin"

# Exportar como CSV
php artisan permissions:report --format=csv --export=report.csv
```

### Backup e Restore
```bash
# Criar backup
php artisan permissions:backup

# Backup com nome customizado
php artisan permissions:backup --filename=my_backup.json

# Restaurar backup
php artisan permissions:backup --restore=permissions_backup_2025-06-10_03-22-45.json
```

## 🎯 Uso em Controllers

### Verificação Básica de Permissões
```php
class UserController extends Controller
{
    public function index(Request $request): Response
    {
        // Verificação automática via middleware
        $this->authorize('users.index');
        
        $users = User::paginate(15);
        
        return Inertia::render('Users/Index', [
            'users' => UserResource::collection($users)
        ]);
    }
    
    public function create(): Response
    {
        $this->authorize('users.create');
        
        return Inertia::render('Users/Create');
    }
}
```

### Middleware de Proteção
```php
// Em routes/web.php
Route::middleware(['auth', 'permission:admin.access'])->prefix('admin')->group(function () {
    Route::resource('users', Admin\UserController::class);
    Route::resource('roles', Admin\RoleController::class);
});
```

## 🎨 Uso no Frontend (React/TypeScript)

### Hook de Permissões
```typescript
// resources/js/Hooks/usePermissions.ts
import { usePage } from '@inertiajs/react';

interface User {
    id: number;
    name: string;
    permissions: string[];
    roles: string[];
}

export function usePermissions() {
    const { auth } = usePage<{ auth: { user: User } }>().props;
    
    const hasPermission = (permission: string): boolean => {
        return auth.user?.permissions?.includes(permission) ?? false;
    };
    
    const hasRole = (role: string): boolean => {
        return auth.user?.roles?.includes(role) ?? false;
    };
    
    const hasAnyPermission = (permissions: string[]): boolean => {
        return permissions.some(permission => hasPermission(permission));
    };
    
    return { hasPermission, hasRole, hasAnyPermission };
}
```

### Componente de Proteção
```typescript
// resources/js/Components/ProtectedContent.tsx
import { ReactNode } from 'react';
import { usePermissions } from '@/Hooks/usePermissions';

interface ProtectedContentProps {
    permission?: string;
    role?: string;
    permissions?: string[];
    fallback?: ReactNode;
    children: ReactNode;
}

export default function ProtectedContent({
    permission,
    role,
    permissions,
    fallback = null,
    children
}: ProtectedContentProps) {
    const { hasPermission, hasRole, hasAnyPermission } = usePermissions();
    
    let hasAccess = true;
    
    if (permission && !hasPermission(permission)) {
        hasAccess = false;
    }
    
    if (role && !hasRole(role)) {
        hasAccess = false;
    }
    
    if (permissions && !hasAnyPermission(permissions)) {
        hasAccess = false;
    }
    
    return hasAccess ? <>{children}</> : <>{fallback}</>;
}
```

### Uso em Componentes
```typescript
// resources/js/Pages/Users/Index.tsx
import ProtectedContent from '@/Components/ProtectedContent';
import { Button } from '@/Components/ui/button';

export default function UsersIndex() {
    return (
        <div>
            <h1>Usuários</h1>
            
            <ProtectedContent permission="users.create">
                <Button onClick={() => Inertia.visit('/admin/users/create')}>
                    Criar Usuário
                </Button>
            </ProtectedContent>
            
            <ProtectedContent 
                permissions={["users.edit", "users.delete"]}
                fallback={<p>Você não tem permissão para gerenciar usuários</p>}
            >
                <UserManagementPanel />
            </ProtectedContent>
        </div>
    );
}
```

## 📱 Sistema de Menus Dinâmicos

### API de Menus
```php
// app/Http/Controllers/MenuController.php
class MenuController extends Controller
{
    public function getMenu(Request $request)
    {
        $user = $request->user();
        $menu = MenuItem::getMenuTree($user);
        
        return response()->json($menu);
    }
    
    public function breadcrumb(Request $request)
    {
        $routeName = $request->get('route');
        $menuItem = MenuItem::findByRoute($routeName);
        
        $breadcrumb = [];
        if ($menuItem) {
            $breadcrumb = $this->buildBreadcrumb($menuItem);
        }
        
        return response()->json($breadcrumb);
    }
}
```

### Componente de Menu React
```typescript
// resources/js/Components/Navigation/DynamicMenu.tsx
import { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';

interface MenuItem {
    id: number;
    title: string;
    route_name: string;
    icon: string;
    children?: MenuItem[];
}

export default function DynamicMenu() {
    const [menuItems, setMenuItems] = useState<MenuItem[]>([]);
    
    useEffect(() => {
        fetch('/api/menu')
            .then(res => res.json())
            .then(setMenuItems)
            .catch(console.error);
    }, []);
    
    const handleMenuClick = (routeName: string) => {
        router.visit(route(routeName));
    };
    
    return (
        <nav className="space-y-2">
            {menuItems.map(item => (
                <MenuItemComponent 
                    key={item.id}
                    item={item}
                    onClick={handleMenuClick}
                />
            ))}
        </nav>
    );
}
```

## 🔒 Boas Práticas de Segurança

### 1. Validação Server-Side
```php
// app/Http/Requests/UserStoreRequest.php
class UserStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('users.create');
    }
    
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'roles' => 'array|exists:roles,id',
        ];
    }
}
```

### 2. Middleware Personalizado
```php
// app/Http/Middleware/CheckModuleAccess.php
class CheckModuleAccess
{
    public function handle(Request $request, Closure $next, string $module): Response
    {
        if (!$request->user()?->can("admin.access") && 
            !$request->user()?->can("{$module}.index")) {
            abort(403, 'Acesso negado ao módulo.');
        }
        
        return $next($request);
    }
}
```

### 3. Policies para Recursos
```php
// app/Policies/UserPolicy.php
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('users.index');
    }
    
    public function create(User $user): bool
    {
        return $user->can('users.create');
    }
    
    public function update(User $authUser, User $user): bool
    {
        // Super admin pode editar qualquer um
        if ($authUser->hasRole('Super Administrador')) {
            return true;
        }
        
        // Usuário pode editar apenas a si mesmo
        if ($authUser->id === $user->id) {
            return $authUser->can('profile.edit');
        }
        
        // Outros precisam da permissão específica
        return $authUser->can('users.edit');
    }
}
```

## 📊 Estrutura de Dados

### Permissões por Módulo
```
admin/
├── admin.access          # Acesso ao painel
├── admin.dashboard       # Dashboard administrativo

users/
├── users.index          # Listar usuários
├── users.show           # Visualizar usuário
├── users.create         # Criar usuários
├── users.edit           # Editar usuários
├── users.delete         # Deletar usuários
└── users.roles          # Gerenciar roles

roles/
├── roles.index          # Listar roles
├── roles.show           # Visualizar role
├── roles.create         # Criar roles
├── roles.edit           # Editar roles
└── roles.delete         # Deletar roles

permissions/
├── permissions.index    # Listar permissões
├── permissions.show     # Visualizar permissão
├── permissions.create   # Criar permissões
├── permissions.edit     # Editar permissões
└── permissions.delete   # Deletar permissões
```

### Hierarquia de Roles
```
Super Administrador (62 permissões)
├── Todas as permissões do sistema
└── Acesso irrestrito

Administrador (48 permissões)
├── Gestão completa de usuários
├── Gestão completa de roles/permissões
├── Configurações gerais
└── Relatórios completos

Gerente (17 permissões)
├── Visualização de usuários
├── Visualização de roles/permissões
├── Relatórios básicos
└── Configurações pessoais

Usuário (18 permissões)
├── Perfil pessoal
├── Configurações básicas
└── Acesso limitado

Visualizador (10 permissões)
├── Apenas visualização
└── Sem edições
```

## 🚨 Troubleshooting

### Problemas Comuns

1. **Permissões não atualizando**
   ```bash
   php artisan permission:cache-reset
   php artisan config:clear
   ```

2. **Usuário sem acesso após atribuir role**
   ```bash
   # Verificar se o usuário tem a role
   php artisan tinker
   >>> User::find(1)->roles
   
   # Verificar permissões da role
   >>> Role::where('name', 'Admin')->first()->permissions
   ```

3. **Menus não aparecendo**
   ```bash
   # Verificar permissões do menu
   php artisan permissions:validate
   
   # Verificar API de menu
   curl http://localhost:8000/api/menu
   ```

### Logs de Debug
```php
// Em controllers, adicione logs para debug
Log::info('User permissions check', [
    'user_id' => auth()->id(),
    'permission' => 'users.create',
    'has_permission' => auth()->user()->can('users.create'),
    'roles' => auth()->user()->roles->pluck('name')
]);
```

## 📈 Performance

### Cache de Permissões
O sistema usa cache automático do Spatie Laravel Permission. Para otimizar:

```php
// config/permission.php
'cache' => [
    'expiration_time' => \DateInterval::createFromDateString('24 hours'),
    'key' => 'spatie.permission.cache',
    'store' => 'default',
],
```

### Eager Loading
```php
// Sempre carregar relacionamentos necessários
$users = User::with(['roles', 'permissions'])->get();
$menus = MenuItem::with('children')->active()->get();
```

## 🔄 Manutenção

### Backup Regular
```bash
# Script de backup diário
#!/bin/bash
php artisan permissions:backup --filename="backup_$(date +%Y%m%d).json"
```

### Auditoria de Permissões
```bash
# Executar semanalmente
php artisan permissions:validate
php artisan permissions:report --export="audit_$(date +%Y%m%d).json"
```

## 📝 Changelog

### v1.0.0 - Setup Inicial
- Estrutura básica de permissões e roles
- Sistema de menus dinâmicos
- Comandos de geração automática
- Seeders organizados por ambiente

### Próximas Versões
- [ ] Interface web para gestão de permissões
- [ ] Logs de auditoria avançados
- [ ] API REST completa
- [ ] Testes automatizados
- [ ] Documentação interativa

---

**Desenvolvido para Laravel + Inertia + React + TypeScript + shadcn/ui**
