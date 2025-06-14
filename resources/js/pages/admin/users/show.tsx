import React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { ArrowLeft, Edit, Trash2, User, Mail, Calendar, Shield } from 'lucide-react';
import ConfirmDeleteDialog from '@/components/confirm-delete-dialog';
import { useConfirmDelete } from '@/hooks/use-confirm-delete';
import { showToast } from '@/hooks/use-toast';
import { type BreadcrumbItem } from '@/types';

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
}

export default function Show({ user }: Props) {
  const { dialogProps, openDialog } = useConfirmDelete({
    onConfirm: () => {
      const deletePromise = new Promise((resolve, reject) => {
        router.delete(`/admin/users/${user.id}`, {
          onSuccess: () => resolve(user.id),
          onError: () => reject(new Error('Erro ao excluir usuário')),
        });
      });

      showToast.promise(deletePromise, {
        loading: 'Excluindo usuário...',
        success: 'Usuário excluído com sucesso!',
        error: 'Erro ao excluir usuário',
      });
    },
    title: 'Excluir usuário',
    description: `Tem certeza que deseja excluir o usuário "${user.name}"? Esta ação não pode ser desfeita.`,
  });
  
  const breadcrumbs: BreadcrumbItem[] = [
    {
      title: 'Usuários',
      href: '/admin/users',
    },
    {
      title: user.name,
      href: `/admin/users/${user.id}`,
    },
  ];

  const formatDate = (dateString: string) => {
    return new Intl.DateTimeFormat('pt-BR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    }).format(new Date(dateString));
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={user.name} />

      <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold tracking-tight">{user.name}</h1>
            <p className="text-muted-foreground">
              Detalhes do usuário
            </p>
          </div>
          <div className="flex items-center gap-2">
            <Link href={`/admin/users/${user.id}/edit`}>
              <Button>
                <Edit className="mr-2 h-4 w-4" />
                Editar
              </Button>
            </Link>
            <Button variant="destructive" onClick={openDialog}>
              <Trash2 className="mr-2 h-4 w-4" />
              Excluir
            </Button>
            <Link href="/admin/users">
              <Button variant="outline">
                <ArrowLeft className="mr-2 h-4 w-4" />
                Voltar
              </Button>
            </Link>
          </div>
        </div>

        <div className="grid gap-6 md:grid-cols-2">
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <User className="h-5 w-5" />
                Informações Pessoais
              </CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div>
                <label className="text-sm font-medium text-muted-foreground">Nome</label>
                <p className="text-sm">{user.name}</p>
              </div>
              <div>
                <label className="text-sm font-medium text-muted-foreground">E-mail</label>
                <div className="flex items-center gap-2">
                  <p className="text-sm">{user.email}</p>
                  {user.email_verified_at ? (
                    <Badge variant="default" className="bg-green-100 text-green-800 hover:bg-green-100">
                      <Shield className="mr-1 h-3 w-3" />
                      Verificado
                    </Badge>
                  ) : (
                    <Badge variant="secondary">
                      Não verificado
                    </Badge>
                  )}
                </div>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Calendar className="h-5 w-5" />
                Informações do Sistema
              </CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div>
                <label className="text-sm font-medium text-muted-foreground">Data de Criação</label>
                <p className="text-sm">{formatDate(user.created_at)}</p>
              </div>
              <div>
                <label className="text-sm font-medium text-muted-foreground">Última Atualização</label>
                <p className="text-sm">{formatDate(user.updated_at)}</p>
              </div>
              {user.email_verified_at && (
                <div>
                  <label className="text-sm font-medium text-muted-foreground">E-mail Verificado em</label>
                  <p className="text-sm">{formatDate(user.email_verified_at)}</p>
                </div>
              )}
            </CardContent>
          </Card>
        </div>

        <Card>
          <CardHeader>
            <CardTitle>Ações Rápidas</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="flex flex-wrap gap-2">
              <Link href={`/admin/users/${user.id}/edit`}>
                <Button variant="outline" size="sm">
                  <Edit className="mr-2 h-4 w-4" />
                  Editar Usuário
                </Button>
              </Link>
              <Button variant="outline" size="sm" disabled>
                <Mail className="mr-2 h-4 w-4" />
                Enviar E-mail
              </Button>
              <Button variant="outline" size="sm" disabled>
                <Shield className="mr-2 h-4 w-4" />
                Redefinir Senha
              </Button>
            </div>
            <p className="text-xs text-muted-foreground mt-2">
              Algumas ações podem não estar disponíveis dependendo das suas permissões.
            </p>
          </CardContent>
        </Card>
      </div>

      <ConfirmDeleteDialog {...dialogProps} />
    </AppLayout>
  );
}
