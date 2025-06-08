import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { ArrowLeft, User, Mail, Lock, Shield } from 'lucide-react';
import { showToast } from '@/hooks/use-toast';
import { type BreadcrumbItem, type Role } from '@/types';

interface User {
  id: number;
  name: string;
  email: string;
  email_verified_at: string | null;
  created_at: string;
  updated_at: string;
}

interface Props {
  user: User;
  roles: Role[];
  userRoles: string[];
}

export default function Edit({ user, roles, userRoles }: Props) {
  const breadcrumbs: BreadcrumbItem[] = [
    {
      title: 'Usuários',
      href: '/users',
    },
    {
      title: user.name,
      href: `/users/${user.id}`,
    },
    {
      title: 'Editar',
      href: `/users/${user.id}/edit`,
    },
  ];

  const { data, setData, put, processing, errors } = useForm({
    name: user.name,
    email: user.email,
    password: '',
    password_confirmation: '',
    roles: userRoles || [],
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    
    const toastId = showToast.loading('Salvando alterações...');
    
    put(`/users/${user.id}`, {
      onSuccess: () => {
        showToast.dismiss(toastId);
        showToast.success('Usuário atualizado com sucesso!');
      },
      onError: () => {
        showToast.dismiss(toastId);
        showToast.error('Erro ao atualizar usuário. Verifique os dados informados.');
      },
    });
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={`Editar ${user.name}`} />

      <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold tracking-tight">Editar Usuário</h1>
            <p className="text-muted-foreground">
              Atualize as informações do usuário {user.name}.
            </p>
          </div>
          <div className="flex items-center gap-2">
            <Link href={`/users/${user.id}`}>
              <Button variant="outline">
                Visualizar
              </Button>
            </Link>
            <Link href="/users">
              <Button variant="outline">
                <ArrowLeft className="mr-2 h-4 w-4" />
                Voltar
              </Button>
            </Link>
          </div>
        </div>

        <Card>
          <CardHeader>
            <CardTitle>Informações do Usuário</CardTitle>
          </CardHeader>
          <CardContent>
            <form onSubmit={handleSubmit} className="space-y-6">
              <div className="grid gap-6 md:grid-cols-2">
                <div className="space-y-2">
                  <Label htmlFor="name">Nome *</Label>
                  <div className="relative">
                    <User className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                      id="name"
                      type="text"
                      placeholder="Digite o nome completo"
                      value={data.name}
                      onChange={(e) => setData('name', e.target.value)}
                      className="pl-10"
                      required
                    />
                  </div>
                  {errors.name && (
                    <p className="text-sm text-destructive">{errors.name}</p>
                  )}
                </div>

                <div className="space-y-2">
                  <Label htmlFor="email">E-mail *</Label>
                  <div className="relative">
                    <Mail className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                      id="email"
                      type="email"
                      placeholder="Digite o e-mail"
                      value={data.email}
                      onChange={(e) => setData('email', e.target.value)}
                      className="pl-10"
                      required
                    />
                  </div>
                  {errors.email && (
                    <p className="text-sm text-destructive">{errors.email}</p>
                  )}
                </div>

                <div className="space-y-2">
                  <Label htmlFor="password">Nova Senha</Label>
                  <div className="relative">
                    <Lock className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                      id="password"
                      type="password"
                      placeholder="Digite a nova senha (deixe em branco para manter)"
                      value={data.password}
                      onChange={(e) => setData('password', e.target.value)}
                      className="pl-10"
                      minLength={8}
                    />
                  </div>
                  {errors.password && (
                    <p className="text-sm text-destructive">{errors.password}</p>
                  )}
                  <p className="text-xs text-muted-foreground">
                    Deixe em branco para manter a senha atual
                  </p>
                </div>

                <div className="space-y-2">
                  <Label htmlFor="password_confirmation">Confirmar Nova Senha</Label>
                  <div className="relative">
                    <Lock className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                      id="password_confirmation"
                      type="password"
                      placeholder="Confirme a nova senha"
                      value={data.password_confirmation}
                      onChange={(e) => setData('password_confirmation', e.target.value)}
                      className="pl-10"
                      minLength={8}
                    />
                  </div>
                  {errors.password_confirmation && (
                    <p className="text-sm text-destructive">{errors.password_confirmation}</p>
                  )}
                </div>
              </div>

              {/* Roles Section */}
              <div className="space-y-4">
                <div className="space-y-2">
                  <Label className="text-base font-medium flex items-center gap-2">
                    <Shield className="h-4 w-4" />
                    Papéis (Roles)
                  </Label>
                  <p className="text-sm text-muted-foreground">
                    Selecione os papéis que serão atribuídos ao usuário.
                  </p>
                </div>
                
                {roles.length > 0 ? (
                  <div className="grid gap-3 md:grid-cols-2 lg:grid-cols-3">
                    {roles.map((role) => (
                      <div key={role.id} className="flex items-center space-x-2">
                        <Checkbox
                          id={`role-${role.id}`}
                          checked={data.roles.includes(role.name)}
                          onCheckedChange={(checked) => {
                            if (checked) {
                              setData('roles', [...data.roles, role.name]);
                            } else {
                              setData('roles', data.roles.filter((r) => r !== role.name));
                            }
                          }}
                        />
                        <Label 
                          htmlFor={`role-${role.id}`}
                          className="text-sm font-normal cursor-pointer"
                        >
                          {role.name}
                          {role.description && (
                            <span className="block text-xs text-muted-foreground">
                              {role.description}
                            </span>
                          )}
                        </Label>
                      </div>
                    ))}
                  </div>
                ) : (
                  <p className="text-sm text-muted-foreground">
                    Nenhum papel disponível.
                  </p>
                )}
                
                {errors.roles && (
                  <p className="text-sm text-destructive">{errors.roles}</p>
                )}
              </div>

              <div className="flex items-center gap-4 pt-4">
                <Button type="submit" disabled={processing}>
                  {processing ? 'Salvando...' : 'Salvar Alterações'}
                </Button>
                <Link href={`/users/${user.id}`}>
                  <Button type="button" variant="outline">
                    Cancelar
                  </Button>
                </Link>
              </div>
            </form>
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  );
}
