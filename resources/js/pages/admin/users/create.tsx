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

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Usuários',
    href: '/admin/users',
  },
  {
    title: 'Criar Usuário',
    href: '/users/create',
  },
];

interface Props {
  roles: Role[];
}

export default function Create({ roles }: Props) {
  const { data, setData, post, processing, errors, reset } = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    roles: [] as string[],
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    
    const toastId = showToast.loading('Criando usuário...');
    
    post('/users', {
      onSuccess: () => {
        reset();
        showToast.dismiss(toastId);
        showToast.success('Usuário criado com sucesso!');
      },
      onError: () => {
        showToast.dismiss(toastId);
        showToast.error('Erro ao criar usuário. Verifique os dados informados.');
      },
    });
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Criar Usuário" />

      <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold tracking-tight">Criar Usuário</h1>
            <p className="text-muted-foreground">
              Adicione um novo usuário ao sistema.
            </p>
          </div>
          <Link href="/users">
            <Button variant="outline">
              <ArrowLeft className="mr-2 h-4 w-4" />
              Voltar
            </Button>
          </Link>
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
                  <Label htmlFor="password">Senha *</Label>
                  <div className="relative">
                    <Lock className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                      id="password"
                      type="password"
                      placeholder="Digite a senha"
                      value={data.password}
                      onChange={(e) => setData('password', e.target.value)}
                      className="pl-10"
                      required
                      minLength={8}
                    />
                  </div>
                  {errors.password && (
                    <p className="text-sm text-destructive">{errors.password}</p>
                  )}
                </div>

                <div className="space-y-2">
                  <Label htmlFor="password_confirmation">Confirmar Senha *</Label>
                  <div className="relative">
                    <Lock className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                      id="password_confirmation"
                      type="password"
                      placeholder="Confirme a senha"
                      value={data.password_confirmation}
                      onChange={(e) => setData('password_confirmation', e.target.value)}
                      className="pl-10"
                      required
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
                  {processing ? 'Criando...' : 'Criar Usuário'}
                </Button>
                <Link href={route('admin.users.index')}>
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
