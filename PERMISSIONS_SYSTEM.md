# Sistema de Roles e Permissions - Laravel + Spatie Permission

## 📋 Resumo da Implementação

### ✅ Estrutura Implementada

#### 1. **Tabelas do Banco de Dados**
- `roles` - Roles do sistema (com description)
- `permissions` - Permissions do sistema (com module e description)
- `model_has_roles` - Relação usuários ↔ roles
- `model_has_permissions` - Relação usuários ↔ permissions diretas
- `role_has_permissions` - Relação roles ↔ permissions

#### 2. **Models Personalizados**
- **`App\Models\Role`** - Extends Spatie Role com campo description
- **`App\Models\Permission`** - Extends Spatie Permission com campos module e description
- **`App\Models\User`** - Já configurado com trait `HasRoles`

#### 3. **Permissions Criadas por Módulo**

| Módulo | Permissions |
|--------|-------------|
| **users** | view, create, edit, delete, invite |
| **roles** | view, create, edit, delete, assign |
| **settings** | view, edit |
| **analytics** | view |
| **audit** | view |
| **menu** | view, edit |

#### 4. **Roles Base Criados**

| Role | Descrição | Permissions |
|------|-----------|-------------|
| **Super Admin** | Acesso total ao sistema | Todas (16) |
| **Admin** | Administrador com gestão de usuários | 15 permissions |
| **Manager** | Gerente com acesso limitado | 8 permissions |
| **User** | Usuário básico | 1 permission (menu.view) |

### 🛠️ Ferramentas e Helpers

#### 1. **Service Class**
```php
App\Services\PermissionService
```
- Métodos para gerenciar roles e permissions
- Estatísticas do sistema
- Operações CRUD em roles/permissions

#### 2. **Helpers Globais**
```php
can_user('permission')           // Verificar permission
has_role('role')                // Verificar role
has_any_role(['roles'])         // Verificar qualquer role
user_permissions()              // Listar permissions do usuário
user_roles()                    // Listar roles do usuário
is_super_admin()               // Verificar se é super admin
can_manage_users()             // Verificar se pode gerenciar usuários
can_manage_roles()             // Verificar se pode gerenciar roles
```

#### 3. **Trait para Controllers**
```php
App\Traits\HasPermissionChecks
```
- Métodos para verificar permissions em controllers
- Respostas padronizadas para APIs
- Verificações de módulos completos

#### 4. **Middleware Personalizado**
```php
App\Http\Middleware\CheckPermission
```
- Verificação de permissions com mensagens personalizadas
- Suporte a JSON e redirecionamentos

#### 5. **Comando Artisan**
```bash
php artisan permission:manage list-roles
php artisan permission:manage list-permissions
php artisan permission:manage assign-role --user=email --role=nome
php artisan permission:manage remove-role --user=email --role=nome
php artisan permission:manage create-role --name=nome --description=desc
php artisan permission:manage user-permissions --user=email
```

### 🎯 Controllers Administrativos

#### 1. **Admin\RoleController**
- CRUD completo de roles
- Gestão de permissions por role
- Atribuir/remover roles de usuários
- API endpoints para integração

#### 2. **Admin\UserController**
- CRUD completo de usuários
- Gestão de roles por usuário
- Filtros e busca
- Visualização de permissions

### 🔗 Rotas Configuradas

#### Administração
```php
Route::prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', Admin\UserController::class);
    Route::resource('roles', Admin\RoleController::class);
    // + rotas específicas para gestão
});
```

#### Testes do Sistema
```php
Route::prefix('permission-test')->group(function () {
    // Rotas para testar o sistema
});
```

### 🔧 Configurações

#### Frontend (Inertia)
- Permissions e roles disponíveis globalmente via `HandleInertiaRequests`
- Dados do usuário com permissions e roles

#### Middleware Registrados
```php
'permission' => CheckPermission::class,
'role' => RoleMiddleware::class,
'role_or_permission' => RoleOrPermissionMiddleware::class,
```

### 📊 Dados de Exemplo

#### Usuários Criados
- **admin@example.com** - Super Admin (password: password)
- **test@example.com** - User (password: password)
- **15 usuários aleatórios** com roles distribuídos

#### Estatísticas Atuais
- **4 roles** cadastrados
- **16 permissions** em 6 módulos
- **Usuários distribuídos** entre os roles

### 🚀 Como Usar

#### 1. **Em Controllers**
```php
use App\Traits\HasPermissionChecks;

class MeuController extends Controller
{
    use HasPermissionChecks;
    
    public function metodo()
    {
        if ($error = $this->checkPermission('users.view')) {
            return $error;
        }
        // ... lógica
    }
}
```

#### 2. **Em Middleware**
```php
Route::get('/rota', 'Controller@method')
    ->middleware('permission:users.view');
```

#### 3. **Em Views/Frontend**
```javascript
// Dados disponíveis globalmente
$page.props.auth.permissions
$page.props.auth.roles
```

#### 4. **Helpers PHP**
```php
@if(can_user('users.create'))
    <button>Criar Usuário</button>
@endif

@if(has_role('Admin'))
    <div>Painel Admin</div>
@endif
```

### 🔍 Endpoints de Teste

- `GET /permission-test` - Dashboard do sistema
- `GET /permission-test/users-view` - Testar users.view
- `GET /permission-test/users-create` - Testar users.create
- `GET /permission-test/roles-edit` - Testar roles.edit
- `GET /permission-test/super-admin` - Testar role Super Admin
- `GET /permission-test/helpers` - Testar helpers globais

### 📝 Próximos Passos Sugeridos

1. **Frontend React/Inertia**
   - Criar páginas para Admin\UserController
   - Criar páginas para Admin\RoleController
   - Dashboard de permissions

2. **Auditoria**
   - Log de ações com permissions
   - Histórico de mudanças de roles

3. **APIs**
   - Endpoints REST para mobile
   - Autenticação via Sanctum

4. **Cache**
   - Cache de permissions por usuário
   - Invalidação automática

5. **Testes**
   - Unit tests para permissions
   - Feature tests para controllers

O sistema está **completamente funcional** e pronto para uso! 🎉
