import { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { showToast } from '@/hooks/use-toast';
import AppLayout from '@/layouts/app-layout';
import { Role, RoleStatistics } from '@/types';
import { Plus, Search, MoreVertical, Eye, Edit, Trash2, Crown, Users, Shield } from 'lucide-react';

interface RolesIndexProps {
  roles: Role[];
  statistics: RoleStatistics;
}

export default function RolesIndex({ roles, statistics }: RolesIndexProps) {
  const [searchTerm, setSearchTerm] = useState('');

  const filteredRoles = roles.filter(role => 
    role.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
    (role.description && role.description.toLowerCase().includes(searchTerm.toLowerCase()))
  );

  const handleDelete = (role: Role) => {
    if (confirm(`Tem certeza que deseja deletar o role "${role.name}"?`)) {
      router.delete(route('admin.roles.destroy', role.id), {
        onSuccess: () => {
          showToast.success('Role deletado com sucesso!');
        },
        onError: (errors: Record<string, string>) => {
          showToast.error(errors.message || 'Erro ao deletar role.');
        },
      });
    }
  };

  const getRoleVariant = (roleName: string) => {
    switch (roleName.toLowerCase()) {
      case 'super admin':
        return 'destructive';
      case 'admin':
        return 'default';
      case 'manager':
        return 'secondary';
      default:
        return 'outline';
    }
  };

  return (
    <AppLayout>
      <Head title="Gerenciar Roles" />

      <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold tracking-tight">Roles</h1>
            <p className="text-muted-foreground">
              Gerencie os roles e suas permissões
            </p>
          </div>
          <Button asChild>
            <Link href={route('admin.roles.create')}>
              <Plus className="w-4 h-4 mr-2" />
              Novo Role
            </Link>
          </Button>
        </div>

        {/* Statistics Cards */}
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total de Roles</CardTitle>
              <Crown className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{statistics.total_roles}</div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total de Permissões</CardTitle>
              <Shield className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{statistics.total_permissions}</div>
            </CardContent>
          </Card>

          <Card className="md:col-span-2">
            <CardHeader>
              <CardTitle className="text-sm font-medium">Distribuição de Roles</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-2">
                {statistics.roles_breakdown.slice(0, 3).map((role) => (
                  <div key={role.name} className="flex items-center justify-between">
                    <div className="flex items-center gap-2">
                      <Badge variant={getRoleVariant(role.name)}>
                        {role.name}
                      </Badge>
                    </div>
                    <div className="flex items-center gap-4 text-sm text-muted-foreground">
                      <span>{role.users_count} usuários</span>
                      <span>{role.permissions_count} permissões</span>
                    </div>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Search */}
        <Card>
          <CardHeader>
            <CardTitle>Buscar Roles</CardTitle>
            <CardDescription>
              Encontre roles por nome ou descrição
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="flex gap-4">
              <div className="flex-1">
                <div className="relative">
                  <Search className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                  <Input
                    placeholder="Nome ou descrição do role..."
                    value={searchTerm}
                    onChange={(e) => setSearchTerm(e.target.value)}
                    className="pl-10"
                  />
                </div>
              </div>
              <Button
                variant="outline"
                onClick={() => setSearchTerm('')}
                disabled={!searchTerm}
              >
                Limpar
              </Button>
            </div>
          </CardContent>
        </Card>

        {/* Roles Table */}
        <Card>
          <CardHeader>
            <CardTitle>Lista de Roles</CardTitle>
            <CardDescription>
              {filteredRoles.length} role(s) encontrado(s)
            </CardDescription>
          </CardHeader>
          <CardContent>
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Nome</TableHead>
                  <TableHead>Descrição</TableHead>
                  <TableHead>Usuários</TableHead>
                  <TableHead>Permissões</TableHead>
                  <TableHead>Criado em</TableHead>
                  <TableHead className="w-[70px]">Ações</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {filteredRoles.length === 0 ? (
                  <TableRow>
                    <TableCell colSpan={6} className="text-center text-muted-foreground">
                      {searchTerm ? 'Nenhum role encontrado com os filtros aplicados' : 'Nenhum role encontrado'}
                    </TableCell>
                  </TableRow>
                ) : (
                  filteredRoles.map((role) => (
                    <TableRow key={role.id}>
                      <TableCell>
                        <div className="flex items-center gap-2">
                          <Badge variant={getRoleVariant(role.name)}>
                            {role.name}
                          </Badge>
                        </div>
                      </TableCell>
                      <TableCell className="max-w-xs truncate">
                        {role.description || '-'}
                      </TableCell>
                      <TableCell>
                        <div className="flex items-center gap-2">
                          <Users className="w-4 h-4 text-muted-foreground" />
                          <span>{role.users_count || 0}</span>
                        </div>
                      </TableCell>
                      <TableCell>
                        <div className="flex items-center gap-2">
                          <Shield className="w-4 h-4 text-muted-foreground" />
                          <span>{role.permissions_count || 0}</span>
                        </div>
                      </TableCell>
                      <TableCell>
                        {new Date(role.created_at).toLocaleDateString('pt-BR')}
                      </TableCell>
                      <TableCell>
                        <DropdownMenu>
                          <DropdownMenuTrigger asChild>
                            <Button variant="ghost" size="sm">
                              <MoreVertical className="w-4 h-4" />
                            </Button>
                          </DropdownMenuTrigger>
                          <DropdownMenuContent align="end">
                            <DropdownMenuLabel>Ações</DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem asChild>
                              <Link href={route('admin.roles.show', role.id)}>
                                <Eye className="w-4 h-4 mr-2" />
                                Visualizar
                              </Link>
                            </DropdownMenuItem>
                            <DropdownMenuItem asChild>
                              <Link href={route('admin.roles.edit', role.id)}>
                                <Edit className="w-4 h-4 mr-2" />
                                Editar
                              </Link>
                            </DropdownMenuItem>
                            {!['Super Admin', 'Admin', 'Manager', 'User'].includes(role.name) && (
                              <>
                                <DropdownMenuSeparator />
                                <DropdownMenuItem
                                  className="text-destructive"
                                  onClick={() => handleDelete(role)}
                                >
                                  <Trash2 className="w-4 h-4 mr-2" />
                                  Deletar
                                </DropdownMenuItem>
                              </>
                            )}
                          </DropdownMenuContent>
                        </DropdownMenu>
                      </TableCell>
                    </TableRow>
                  ))
                )}
              </TableBody>
            </Table>
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  );
}