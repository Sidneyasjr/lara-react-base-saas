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
import { showToast } from '@/hooks/use-toast';
import AppLayout from '@/layouts/app-layout';
import { Permission, Role, User } from '@/types';
import { ArrowLeft, Edit, Trash2, Shield, Users, UserCheck, Crown } from 'lucide-react';

interface PermissionShowProps {
  permission: Permission;
  roles: Role[];
  users: User[];
  statistics: {
    roles_count: number;
    users_count: number;
  };
}

export default function PermissionShow({ permission, roles, users, statistics }: PermissionShowProps) {

  const handleDelete = () => {
    if (confirm('Tem certeza que deseja deletar esta permissão?')) {
      router.delete(route('admin.permissions.destroy', permission.id), {
        onSuccess: () => {
          showToast.success('Permissão deletada com sucesso!');
        },
        onError: (errors: Record<string, string>) => {
          showToast.error(errors.message || 'Erro ao deletar permissão.');
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

  return (
    <AppLayout>
      <Head title={`Permissão: ${permission.name}`} />

      <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-4">
            <Button variant="outline" size="sm" asChild>
              <Link href={route('admin.permissions.index')}>
                <ArrowLeft className="w-4 h-4 mr-2" />
                Voltar
              </Link>
            </Button>
            <div>
              <h1 className="text-3xl font-bold tracking-tight">Permissão: {permission.name}</h1>
              <p className="text-muted-foreground">
                Detalhes e relacionamentos da permissão
              </p>
            </div>
          </div>
          <div className="flex gap-2">
            <Button variant="outline" asChild>
              <Link href={route('admin.permissions.edit', permission.id)}>
                <Edit className="w-4 h-4 mr-2" />
                Editar
              </Link>
            </Button>
            <Button variant="destructive" onClick={handleDelete}>
              <Trash2 className="w-4 h-4 mr-2" />
              Deletar
            </Button>
          </div>
        </div>

        <div className="grid gap-6 lg:grid-cols-3">
          {/* Permission Details */}
          <div className="lg:col-span-1">
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Shield className="w-5 h-5" />
                  Detalhes da Permissão
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div>
                  <h4 className="font-medium text-sm mb-2">Nome</h4>
                  <p className="text-sm bg-muted p-2 rounded font-mono">
                    {permission.name}
                  </p>
                </div>

                <div>
                  <h4 className="font-medium text-sm mb-2">Módulo</h4>
                  <Badge variant="secondary">{permission.module}</Badge>
                </div>

                {permission.description && (
                  <div>
                    <h4 className="font-medium text-sm mb-2">Descrição</h4>
                    <p className="text-sm text-muted-foreground">
                      {permission.description}
                    </p>
                  </div>
                )}

                <div>
                  <h4 className="font-medium text-sm mb-2">Guard</h4>
                  <Badge variant="outline">{permission.guard_name}</Badge>
                </div>

                <div>
                  <h4 className="font-medium text-sm mb-2">Criado em</h4>
                  <p className="text-sm text-muted-foreground">
                    {new Date(permission.created_at).toLocaleString('pt-BR')}
                  </p>
                </div>

                <div>
                  <h4 className="font-medium text-sm mb-2">Atualizado em</h4>
                  <p className="text-sm text-muted-foreground">
                    {new Date(permission.updated_at).toLocaleString('pt-BR')}
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
                    <Crown className="w-4 h-4 text-muted-foreground" />
                    <span className="text-sm">Roles</span>
                  </div>
                  <Badge variant="secondary">{statistics.roles_count}</Badge>
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

          {/* Roles and Users */}
          <div className="lg:col-span-2 space-y-6">
            {/* Roles */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Crown className="w-5 h-5" />
                  Roles com esta Permissão ({roles.length})
                </CardTitle>
                <CardDescription>
                  Roles que possuem esta permissão atribuída
                </CardDescription>
              </CardHeader>
              <CardContent>
                {roles.length === 0 ? (
                  <p className="text-center text-muted-foreground py-8">
                    Nenhum role possui esta permissão
                  </p>
                ) : (
                  <Table>
                    <TableHeader>
                      <TableRow>
                        <TableHead>Nome</TableHead>
                        <TableHead>Descrição</TableHead>
                        <TableHead>Usuários</TableHead>
                        <TableHead>Ações</TableHead>
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      {roles.map((role) => (
                        <TableRow key={role.id}>
                          <TableCell className="font-medium">
                            {role.name}
                          </TableCell>
                          <TableCell className="max-w-xs truncate">
                            {role.description || '-'}
                          </TableCell>
                          <TableCell>
                            <Badge variant="outline">
                              {role.users_count || 0} usuário(s)
                            </Badge>
                          </TableCell>
                          <TableCell>
                            <Button variant="outline" size="sm" asChild>
                              <Link href={route('admin.roles.show', role.id)}>
                                Ver Role
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

            {/* Users */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <UserCheck className="w-5 h-5" />
                  Usuários com esta Permissão ({users.length})
                </CardTitle>
                <CardDescription>
                  Usuários que possuem esta permissão (via roles ou atribuição direta)
                </CardDescription>
              </CardHeader>
              <CardContent>
                {users.length === 0 ? (
                  <p className="text-center text-muted-foreground py-8">
                    Nenhum usuário possui esta permissão
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