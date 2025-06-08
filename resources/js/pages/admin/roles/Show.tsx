import { Head, Link, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { toast } from 'sonner';
import AppLayout from '@/layouts/app-layout';
import { Role, Permission, User } from '@/types';
import { ArrowLeft, Edit, Trash2, Crown, Shield, Users, UserCheck } from 'lucide-react';

interface RoleShowProps {
  role: Role;
  permissions: Permission[];
  users: User[];
  statistics: {
    permissions_count: number;
    users_count: number;
  };
}

export default function RoleShow({ role, permissions, users, statistics }: RoleShowProps) {
  const handleDelete = () => {
    if (confirm('Tem certeza que deseja deletar este role?')) {
      router.delete(route('admin.roles.destroy', role.id), {
        onSuccess: () => {
          toast.success('Role deletado com sucesso!');
        },
        onError: (errors: Record<string, string>) => {
          toast.error(errors.message || 'Erro ao deletar role.');
        },
      });
    }
  };

  const getInitials = (name: string) => {
    return name
      .split(' ')
      .map(word => word.charAt(0))
      .join('')
      .toUpperCase()
      .slice(0, 2);
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
      <Head title={`Role: ${role.name}`} />

      <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-4">
            <Button variant="outline" size="sm" asChild>
              <Link href={route('admin.roles.index')}>
                <ArrowLeft className="w-4 h-4 mr-2" />
                Voltar
              </Link>
            </Button>
            <div>
              <h1 className="text-3xl font-bold tracking-tight">Role: {role.name}</h1>
              <p className="text-muted-foreground">
                Detalhes e relacionamentos do role
              </p>
            </div>
          </div>
          <div className="flex gap-2">
            <Button variant="outline" asChild>
              <Link href={route('admin.roles.edit', role.id)}>
                <Edit className="w-4 h-4 mr-2" />
                Editar
              </Link>
            </Button>
            {!['Super Admin', 'Admin', 'Manager', 'User'].includes(role.name) && (
              <Button variant="destructive" onClick={handleDelete}>
                <Trash2 className="w-4 h-4 mr-2" />
                Deletar
              </Button>
            )}
          </div>
        </div>

        <div className="grid gap-6 lg:grid-cols-3">
          {/* Role Details */}
          <div className="lg:col-span-1">
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Crown className="w-5 h-5" />
                  Detalhes do Role
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div>
                  <h4 className="font-medium text-sm mb-2">Nome</h4>
                  <div className="flex items-center gap-2">
                    <Badge variant={getRoleVariant(role.name)}>
                      {role.name}
                    </Badge>
                  </div>
                </div>

                {role.description && (
                  <div>
                    <h4 className="font-medium text-sm mb-2">Descrição</h4>
                    <p className="text-sm text-muted-foreground">
                      {role.description}
                    </p>
                  </div>
                )}

                <div>
                  <h4 className="font-medium text-sm mb-2">Guard</h4>
                  <Badge variant="outline">{role.guard_name}</Badge>
                </div>

                <div>
                  <h4 className="font-medium text-sm mb-2">Criado em</h4>
                  <p className="text-sm text-muted-foreground">
                    {new Date(role.created_at).toLocaleString('pt-BR')}
                  </p>
                </div>

                <div>
                  <h4 className="font-medium text-sm mb-2">Atualizado em</h4>
                  <p className="text-sm text-muted-foreground">
                    {new Date(role.updated_at).toLocaleString('pt-BR')}
                  </p>
                </div>
              </CardContent>
            </Card>

            {/* Statistics */}
            <Card>
              <CardHeader>
                <CardTitle className="text-lg">Estatísticas</CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="flex items-center justify-between">
                  <div className="flex items-center gap-2">
                    <Shield className="w-4 h-4 text-muted-foreground" />
                    <span className="text-sm">Permissões</span>
                  </div>
                  <Badge variant="secondary">{statistics.permissions_count}</Badge>
                </div>

                <div className="flex items-center justify-between">
                  <div className="flex items-center gap-2">
                    <Users className="w-4 h-4 text-muted-foreground" />
                    <span className="text-sm">Usuários</span>
                  </div>
                  <Badge variant="secondary">{statistics.users_count}</Badge>
                </div>
              </CardContent>
            </Card>
          </div>

          {/* Permissions and Users */}
          <div className="lg:col-span-2 space-y-6">
            {/* Permissions */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Shield className="w-5 h-5" />
                  Permissões deste Role ({permissions.length})
                </CardTitle>
                <CardDescription>
                  Permissões atribuídas a este role
                </CardDescription>
              </CardHeader>
              <CardContent>
                {permissions.length === 0 ? (
                  <p className="text-center text-muted-foreground py-8">
                    Nenhuma permissão atribuída a este role
                  </p>
                ) : (
                  <div className="space-y-4">
                    {/* Group permissions by module */}
                    {Object.entries(
                      permissions.reduce((acc, permission) => {
                        const module = permission.module || 'Outros';
                        if (!acc[module]) acc[module] = [];
                        acc[module].push(permission);
                        return acc;
                      }, {} as Record<string, Permission[]>)
                    ).map(([module, modulePermissions]) => (
                      <div key={module} className="border rounded-lg p-4">
                        <div className="flex items-center justify-between mb-3">
                          <h4 className="font-medium">{module}</h4>
                          <Badge variant="outline">
                            {modulePermissions.length} permissão(ões)
                          </Badge>
                        </div>
                        <div className="grid gap-2 sm:grid-cols-2">
                          {modulePermissions.map((permission) => (
                            <div
                              key={permission.id}
                              className="flex items-center gap-2 p-2 bg-muted/50 rounded"
                            >
                              <Shield className="w-3 h-3 text-muted-foreground" />
                              <span className="font-mono text-xs">
                                {permission.name}
                              </span>
                              {permission.description && (
                                <span className="text-xs text-muted-foreground truncate">
                                  - {permission.description}
                                </span>
                              )}
                            </div>
                          ))}
                        </div>
                      </div>
                    ))}
                  </div>
                )}
              </CardContent>
            </Card>

            {/* Users */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <UserCheck className="w-5 h-5" />
                  Usuários com este Role ({users.length})
                </CardTitle>
                <CardDescription>
                  Usuários que possuem este role atribuído
                </CardDescription>
              </CardHeader>
              <CardContent>
                {users.length === 0 ? (
                  <p className="text-center text-muted-foreground py-8">
                    Nenhum usuário possui este role
                  </p>
                ) : (
                  <Table>
                    <TableHeader>
                      <TableRow>
                        <TableHead>Usuário</TableHead>
                        <TableHead>Email</TableHead>
                        <TableHead>Ações</TableHead>
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      {users.map((user) => (
                        <TableRow key={user.id}>
                          <TableCell>
                            <div className="flex items-center gap-3">
                              <Avatar className="w-8 h-8">
                                <AvatarImage src={user.avatar} />
                                <AvatarFallback className="text-xs">
                                  {getInitials(user.name)}
                                </AvatarFallback>
                              </Avatar>
                              <div>
                                <p className="font-medium">{user.name}</p>
                              </div>
                            </div>
                          </TableCell>
                          <TableCell>{user.email}</TableCell>
                          <TableCell>
                            <Button variant="outline" size="sm" asChild>
                              <Link href={route('admin.users.show', user.id)}>
                                Ver Usuário
                              </Link>
                            </Button>
                          </TableCell>
                        </TableRow>
                      ))}
                    </TableBody>
                  </Table>
                )}
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </AppLayout>
  );
}