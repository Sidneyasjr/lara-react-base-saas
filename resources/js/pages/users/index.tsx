import React, { useState } from 'react';
import { Head, Link, router, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Search, Plus, Edit, Trash2, Eye, MoreHorizontal } from 'lucide-react';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle } from '@/components/ui/alert-dialog';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { showToast } from '@/hooks/use-toast';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Usuários',
    href: '/users',
  },
];

interface User {
  id: number;
  name: string;
  email: string;
  email_verified_at: string | null;
  created_at: string;
  updated_at: string;
}

interface PaginatedUsers {
  data: User[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  links: {
    url: string | null;
    label: string;
    active: boolean;
  }[];
}

interface Props {
  users: PaginatedUsers;
  filters: {
    search?: string;
    per_page: number;
  };
}

export default function UsersIndex({ users, filters }: Props) {
  const [deleteUserId, setDeleteUserId] = useState<number | null>(null);
  
  const { data, setData, get, processing } = useForm({
    search: filters.search || '',
    per_page: filters.per_page.toString(),
  });

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    get(route('users.index'), {
      preserveState: true,
      replace: true,
    });
  };

  const handleDelete = (userId: number) => {
    const deletePromise = new Promise((resolve, reject) => {
      router.delete(route('users.destroy', userId), {
        onSuccess: () => {
          setDeleteUserId(null);
          resolve(userId);
        },
        onError: () => {
          reject(new Error('Erro ao excluir usuário'));
        },
      });
    });

    showToast.promise(deletePromise, {
      loading: 'Excluindo usuário...',
      success: 'Usuário excluído com sucesso!',
      error: 'Erro ao excluir usuário',
    });
  };

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('pt-BR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    });
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Usuários" />

      <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-2xl font-bold tracking-tight">Usuários</h1>
            <p className="text-muted-foreground">
              Gerencie os usuários do sistema
            </p>
          </div>
          <Button asChild>
            <Link href={route('users.create')}>
              <Plus className="mr-2 h-4 w-4" />
              Novo Usuário
            </Link>
          </Button>
        </div>

        <Card>
          <CardHeader>
            <CardTitle>Lista de Usuários</CardTitle>
            <div className="flex items-center space-x-4">
              <form onSubmit={handleSearch} className="flex items-center space-x-2 flex-1">
                <div className="relative flex-1 max-w-sm">
                  <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                  <Input
                    placeholder="Buscar usuários..."
                    value={data.search}
                    onChange={(e) => setData('search', e.target.value)}
                    className="pl-9"
                  />
                </div>
                <Button type="submit" disabled={processing}>
                  Buscar
                </Button>
              </form>
              
              <Select
                value={data.per_page}
                onValueChange={(value) => {
                  setData('per_page', value);
                  router.get(route('users.index'), { ...filters, per_page: value }, {
                    preserveState: true,
                    replace: true,
                  });
                }}
              >
                <SelectTrigger className="w-24">
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="10">10</SelectItem>
                  <SelectItem value="25">25</SelectItem>
                  <SelectItem value="50">50</SelectItem>
                  <SelectItem value="100">100</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </CardHeader>
          
          <CardContent>
            <div className="rounded-md border">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Nome</TableHead>
                    <TableHead>E-mail</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead>Criado em</TableHead>
                    <TableHead className="text-right">Ações</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {users.data.length === 0 ? (
                    <TableRow>
                      <TableCell colSpan={5} className="text-center py-8">
                        <div className="text-muted-foreground">
                          Nenhum usuário encontrado
                        </div>
                      </TableCell>
                    </TableRow>
                  ) : (
                    users.data.map((user) => (
                      <TableRow key={user.id}>
                        <TableCell className="font-medium">{user.name}</TableCell>
                        <TableCell>{user.email}</TableCell>
                        <TableCell>
                          <Badge variant={user.email_verified_at ? 'default' : 'secondary'}>
                            {user.email_verified_at ? 'Verificado' : 'Não verificado'}
                          </Badge>
                        </TableCell>
                        <TableCell>{formatDate(user.created_at)}</TableCell>
                        <TableCell className="text-right">
                          <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                              <Button variant="ghost" size="sm">
                                <MoreHorizontal className="h-4 w-4" />
                              </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                              <DropdownMenuItem asChild>
                                <Link href={route('users.show', user.id)}>
                                  <Eye className="mr-2 h-4 w-4" />
                                  Visualizar
                                </Link>
                              </DropdownMenuItem>
                              <DropdownMenuItem asChild>
                                <Link href={route('users.edit', user.id)}>
                                  <Edit className="mr-2 h-4 w-4" />
                                  Editar
                                </Link>
                              </DropdownMenuItem>
                              <DropdownMenuItem
                                className="text-destructive"
                                onClick={() => setDeleteUserId(user.id)}
                              >
                                <Trash2 className="mr-2 h-4 w-4" />
                                Excluir
                              </DropdownMenuItem>
                            </DropdownMenuContent>
                          </DropdownMenu>
                        </TableCell>
                      </TableRow>
                    ))
                  )}
                </TableBody>
              </Table>
            </div>

            {/* Paginação */}
            {users.last_page > 1 && (
              <div className="flex items-center justify-between space-x-2 py-4">
                <div className="text-sm text-muted-foreground">
                  Mostrando {users.data.length} de {users.total} usuários
                </div>
                <div className="flex items-center space-x-2">
                  {users.links.map((link, index) => {
                    if (link.label === 'Previous' || link.label === 'Next') {
                      return (
                        <Button
                          key={index}
                          variant="outline"
                          size="sm"
                          disabled={!link.url}
                          onClick={() => link.url && router.get(link.url)}
                        >
                          {link.label === 'Previous' ? 'Anterior' : 'Próximo'}
                        </Button>
                      );
                    }
                    
                    if (link.label === '...') {
                      return <span key={index} className="px-3 py-1">...</span>;
                    }
                    
                    return (
                      <Button
                        key={index}
                        variant={link.active ? 'default' : 'outline'}
                        size="sm"
                        onClick={() => link.url && router.get(link.url)}
                      >
                        {link.label}
                      </Button>
                    );
                  })}
                </div>
              </div>
            )}
          </CardContent>
        </Card>
      </div>

      {/* Dialog de confirmação de exclusão */}
      <AlertDialog open={deleteUserId !== null} onOpenChange={() => setDeleteUserId(null)}>
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>Confirmar exclusão</AlertDialogTitle>
            <AlertDialogDescription>
              Tem certeza que deseja excluir este usuário? Esta ação não pode ser desfeita.
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <AlertDialogCancel>Cancelar</AlertDialogCancel>
            <AlertDialogAction
              className="bg-destructive text-destructive-foreground hover:bg-destructive/90"
              onClick={() => deleteUserId && handleDelete(deleteUserId)}
            >
              Excluir
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
    </AppLayout>
  );
}
