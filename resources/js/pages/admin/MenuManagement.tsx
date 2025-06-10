import React, { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { 
  Table, 
  TableBody, 
  TableCell, 
  TableHead, 
  TableHeader, 
  TableRow 
} from '@/components/ui/table';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuTrigger,
  DropdownMenuItem,
  DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import { 
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { toast } from 'sonner';
import { 
  Plus, 
  Search, 
  MoreHorizontal, 
  Edit, 
  Trash2, 
  Eye, 
  EyeOff,
  RefreshCw,
  FileText,
  BarChart3
} from 'lucide-react';
import { MenuItem } from '@/types';
import AppShell from '@/components/app-shell';

interface MenuManagementProps {
  menuItems: {
    data: MenuItem[];
    current_page: number;
    last_page: number;
    total: number;
  };
  statistics: {
    total_items: number;
    active_items: number;
    inactive_items: number;
    modules: string[];
    max_depth: number;
  };
  filters?: {
    search?: string;
    module?: string;
    is_active?: boolean;
  };
}

export default function MenuManagement({ menuItems, statistics, filters = {} }: MenuManagementProps) {
  const [searchTerm, setSearchTerm] = useState(filters.search || '');
  const [deleteDialog, setDeleteDialog] = useState<{ open: boolean; item: MenuItem | null }>({
    open: false,
    item: null,
  });
  const [isLoading, setIsLoading] = useState(false);

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    router.get(route('admin.menus.index'), { 
      search: searchTerm,
      module: filters.module,
      is_active: filters.is_active,
    }, { 
      preserveState: true,
      replace: true 
    });
  };

  const handleDelete = async (item: MenuItem) => {
    try {
      setIsLoading(true);
      await router.delete(route('admin.menus.destroy', item.id), {
        onSuccess: () => {
          toast.success('Item do menu excluído com sucesso!');
          setDeleteDialog({ open: false, item: null });
        },
        onError: (errors) => {
          toast.error('Erro ao excluir item do menu.');
          console.error(errors);
        },
      });
    } catch (error) {
      toast.error('Erro inesperado ao excluir item.');
      console.error(error);
    } finally {
      setIsLoading(false);
    }
  };

  const handleToggleActive = async (item: MenuItem) => {
    try {
      await router.patch(route('admin.menus.toggle', item.id), {}, {
        onSuccess: () => {
          toast.success(`Item ${item.is_active ? 'desativado' : 'ativado'} com sucesso!`);
        },
        onError: (errors) => {
          toast.error('Erro ao alterar status do item.');
          console.error(errors);
        },
      });
    } catch (error) {
      toast.error('Erro inesperado ao alterar status.');
      console.error(error);
    }
  };

  const handleClearCache = async () => {
    try {
      setIsLoading(true);
      await router.post(route('admin.menus.clear-cache'), {}, {
        onSuccess: () => {
          toast.success('Cache do menu limpo com sucesso!');
        },
        onError: (errors) => {
          toast.error('Erro ao limpar cache do menu.');
          console.error(errors);
        },
      });
    } catch (error) {
      toast.error('Erro inesperado ao limpar cache.');
      console.error(error);
    } finally {
      setIsLoading(false);
    }
  };

  const getIndentationLevel = (item: MenuItem): number => {
    let level = 0;
    let current = item;
    while (current.parent_id) {
      level++;
      current = menuItems.data.find(i => i.id === current.parent_id) || current;
      if (level > 10) break; // Prevenção de loop infinito
    }
    return level;
  };

  return (
    <AppShell>
      <Head title="Gerenciamento de Menus" />
      
      <div className="container mx-auto py-6 space-y-6">
        {/* Header */}
        <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
          <div>
            <h1 className="text-3xl font-bold">Gerenciamento de Menus</h1>
            <p className="text-muted-foreground">
              Configure e organize os itens do menu do sistema
            </p>
          </div>
          <div className="flex gap-2">
            <Button
              variant="outline"
              onClick={handleClearCache}
              disabled={isLoading}
            >
              <RefreshCw className="h-4 w-4 mr-2" />
              Limpar Cache
            </Button>
            <Button onClick={() => router.get(route('admin.menus.create'))}>
              <Plus className="h-4 w-4 mr-2" />
              Novo Item
            </Button>
          </div>
        </div>

        {/* Statistics Cards */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total de Itens</CardTitle>
              <FileText className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{statistics.total_items}</div>
            </CardContent>
          </Card>
          
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Itens Ativos</CardTitle>
              <Eye className="h-4 w-4 text-green-600" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-green-600">{statistics.active_items}</div>
            </CardContent>
          </Card>
          
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Itens Inativos</CardTitle>
              <EyeOff className="h-4 w-4 text-red-600" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-red-600">{statistics.inactive_items}</div>
            </CardContent>
          </Card>
          
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Módulos</CardTitle>
              <BarChart3 className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{statistics.modules.length}</div>
            </CardContent>
          </Card>
        </div>

        {/* Search and Filters */}
        <Card>
          <CardHeader>
            <CardTitle>Filtros</CardTitle>
          </CardHeader>
          <CardContent>
            <form onSubmit={handleSearch} className="flex gap-2">
              <div className="flex-1">
                <Input
                  placeholder="Buscar por título ou rota..."
                  value={searchTerm}
                  onChange={(e) => setSearchTerm(e.target.value)}
                  className="w-full"
                />
              </div>
              <Button type="submit" variant="outline">
                <Search className="h-4 w-4 mr-2" />
                Buscar
              </Button>
            </form>
          </CardContent>
        </Card>

        {/* Menu Items Table */}
        <Card>
          <CardHeader>
            <CardTitle>Itens do Menu</CardTitle>
            <CardDescription>
              {menuItems.total} itens encontrados
            </CardDescription>
          </CardHeader>
          <CardContent>
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Título</TableHead>
                  <TableHead>Rota</TableHead>
                  <TableHead>Módulo</TableHead>
                  <TableHead>Permissão</TableHead>
                  <TableHead>Status</TableHead>
                  <TableHead>Ordem</TableHead>
                  <TableHead className="text-right">Ações</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {menuItems.data.map((item) => (
                  <TableRow key={item.id}>
                    <TableCell>
                      <div 
                        className="flex items-center"
                        style={{ paddingLeft: `${getIndentationLevel(item) * 20}px` }}
                      >
                        {item.icon && (
                          <span className="mr-2 text-muted-foreground">
                            {item.icon}
                          </span>
                        )}
                        <span className="font-medium">{item.title}</span>
                      </div>
                    </TableCell>
                    <TableCell>
                      <code className="text-xs bg-muted px-1 py-0.5 rounded">
                        {item.route_name || '-'}
                      </code>
                    </TableCell>
                    <TableCell>
                      {item.module && (
                        <Badge variant="outline">{item.module}</Badge>
                      )}
                    </TableCell>
                    <TableCell>
                      {item.permission_required && (
                        <Badge variant="secondary" className="text-xs">
                          {item.permission_required}
                        </Badge>
                      )}
                    </TableCell>
                    <TableCell>
                      <Badge 
                        variant={item.is_active ? "default" : "destructive"}
                        className="text-xs"
                      >
                        {item.is_active ? 'Ativo' : 'Inativo'}
                      </Badge>
                    </TableCell>
                    <TableCell>
                      <span className="text-sm text-muted-foreground">
                        {item.order_index}
                      </span>
                    </TableCell>
                    <TableCell className="text-right">
                      <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                          <Button variant="ghost" className="h-8 w-8 p-0">
                            <MoreHorizontal className="h-4 w-4" />
                          </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                          <DropdownMenuItem
                            onClick={() => router.get(route('admin.menus.edit', item.id))}
                          >
                            <Edit className="h-4 w-4 mr-2" />
                            Editar
                          </DropdownMenuItem>
                          <DropdownMenuItem
                            onClick={() => handleToggleActive(item)}
                          >
                            {item.is_active ? (
                              <EyeOff className="h-4 w-4 mr-2" />
                            ) : (
                              <Eye className="h-4 w-4 mr-2" />
                            )}
                            {item.is_active ? 'Desativar' : 'Ativar'}
                          </DropdownMenuItem>
                          <DropdownMenuSeparator />
                          <DropdownMenuItem
                            onClick={() => setDeleteDialog({ open: true, item })}
                            className="text-red-600 focus:text-red-600"
                          >
                            <Trash2 className="h-4 w-4 mr-2" />
                            Excluir
                          </DropdownMenuItem>
                        </DropdownMenuContent>
                      </DropdownMenu>
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>

            {menuItems.data.length === 0 && (
              <div className="text-center py-8 text-muted-foreground">
                Nenhum item de menu encontrado.
              </div>
            )}
          </CardContent>
        </Card>

        {/* Pagination */}
        {menuItems.last_page > 1 && (
          <div className="flex justify-center gap-2">
            {Array.from({ length: menuItems.last_page }, (_, i) => i + 1).map((page) => (
              <Button
                key={page}
                variant={page === menuItems.current_page ? "default" : "outline"}
                size="sm"
                onClick={() => router.get(route('admin.menus.index'), { 
                  page,
                  search: filters.search,
                  module: filters.module,
                  is_active: filters.is_active,
                })}
              >
                {page}
              </Button>
            ))}
          </div>
        )}
      </div>

      {/* Delete Confirmation Dialog */}
      <AlertDialog 
        open={deleteDialog.open} 
        onOpenChange={(open) => setDeleteDialog({ open, item: null })}
      >
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>Confirmar Exclusão</AlertDialogTitle>
            <AlertDialogDescription>
              Tem certeza que deseja excluir o item "{deleteDialog.item?.title}"?
              Esta ação não pode ser desfeita.
              {deleteDialog.item?.children && deleteDialog.item.children.length > 0 && (
                <div className="mt-2 p-2 bg-yellow-50 border border-yellow-200 rounded text-yellow-800">
                  ⚠️ Este item possui {deleteDialog.item.children.length} subitem(s) que também serão excluídos.
                </div>
              )}
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <AlertDialogCancel>Cancelar</AlertDialogCancel>
            <AlertDialogAction
              onClick={() => deleteDialog.item && handleDelete(deleteDialog.item)}
              disabled={isLoading}
              className="bg-red-600 hover:bg-red-700"
            >
              {isLoading ? 'Excluindo...' : 'Excluir'}
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
    </AppShell>
  );
}
