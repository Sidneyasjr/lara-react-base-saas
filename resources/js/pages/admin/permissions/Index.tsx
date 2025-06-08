import { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
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
import { PaginatedData, Permission, PermissionStatistics, FilterState } from '@/types';
import { Plus, Search, Filter, MoreVertical, Eye, Edit, Trash2, Shield } from 'lucide-react';

interface PermissionsIndexProps {
  permissions: PaginatedData<Permission>;
  modules: string[];
  statistics: PermissionStatistics;
  filters: FilterState;
}

export default function PermissionsIndex({ permissions, modules, statistics, filters }: PermissionsIndexProps) {
  const [searchTerm, setSearchTerm] = useState(filters.search || '');
  const [selectedModule, setSelectedModule] = useState(filters.module || 'all');
  const [perPage, setPerPage] = useState(filters.per_page?.toString() || '15');

  const handleSearch = () => {
    router.get(route('admin.permissions.index'), {
      search: searchTerm,
      module: selectedModule === 'all' ? '' : selectedModule,
      per_page: perPage,
    }, {
      preserveState: true,
      replace: true,
    });
  };

  const handleReset = () => {
    setSearchTerm('');
    setSelectedModule('all');
    setPerPage('15');
    router.get(route('admin.permissions.index'));
  };

  const handleDelete = (permission: Permission) => {
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

  return (
    <AppLayout>
      <Head title="Gerenciar Permissões" />

      <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold tracking-tight">Permissões</h1>
            <p className="text-muted-foreground">
              Gerencie as permissões do sistema
            </p>
          </div>
          <Button asChild>
            <Link href={route('admin.permissions.create')}>
              <Plus className="w-4 h-4 mr-2" />
              Nova Permissão
            </Link>
          </Button>
        </div>

        {/* Statistics Cards */}
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total de Permissões</CardTitle>
              <Shield className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{statistics.total_permissions}</div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Módulos</CardTitle>
              <Filter className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{statistics.total_modules}</div>
            </CardContent>
          </Card>

          <Card className="lg:col-span-2">
            <CardHeader>
              <CardTitle className="text-sm font-medium">Permissões por Módulo</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="flex flex-wrap gap-2">
                {Object.entries(statistics.permissions_by_module).map(([module, count]) => (
                  <Badge key={module} variant="secondary">
                    {module}: {count}
                  </Badge>
                ))}
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Filters */}
        <Card>
          <CardHeader>
            <CardTitle>Filtros</CardTitle>
            <CardDescription>
              Use os filtros abaixo para encontrar permissões específicas
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="flex flex-col gap-4 md:flex-row md:items-end">
              <div className="flex-1">
                <label htmlFor="search" className="block text-sm font-medium mb-2">
                  Buscar
                </label>
                <div className="relative">
                  <Search className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                  <Input
                    id="search"
                    placeholder="Nome ou descrição da permissão..."
                    value={searchTerm}
                    onChange={(e) => setSearchTerm(e.target.value)}
                    className="pl-10"
                    onKeyDown={(e) => e.key === 'Enter' && handleSearch()}
                  />
                </div>
              </div>

              <div className="w-full md:w-48">
                <label className="block text-sm font-medium mb-2">
                  Módulo
                </label>
                <Select value={selectedModule} onValueChange={setSelectedModule}>
                  <SelectTrigger>
                    <SelectValue placeholder="Todos os módulos" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="all">Todos os módulos</SelectItem>
                    {modules.map((module) => (
                      <SelectItem key={module} value={module}>
                        {module}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>

              <div className="w-full md:w-32">
                <label className="block text-sm font-medium mb-2">
                  Por página
                </label>
                <Select value={perPage} onValueChange={setPerPage}>
                  <SelectTrigger>
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="10">10</SelectItem>
                    <SelectItem value="15">15</SelectItem>
                    <SelectItem value="25">25</SelectItem>
                    <SelectItem value="50">50</SelectItem>
                  </SelectContent>
                </Select>
              </div>

              <div className="flex gap-2">
                <Button onClick={handleSearch}>
                  <Search className="w-4 h-4 mr-2" />
                  Buscar
                </Button>
                <Button variant="outline" onClick={handleReset}>
                  Limpar
                </Button>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Permissions Table */}
        <Card>
          <CardHeader>
            <CardTitle>Lista de Permissões</CardTitle>
            <CardDescription>
              {permissions.total} permissão(ões) encontrada(s)
            </CardDescription>
          </CardHeader>
          <CardContent>
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Nome</TableHead>
                  <TableHead>Módulo</TableHead>
                  <TableHead>Descrição</TableHead>
                  <TableHead>Criado em</TableHead>
                  <TableHead className="w-[70px]">Ações</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {permissions.data.length === 0 ? (
                  <TableRow>
                    <TableCell colSpan={5} className="text-center text-muted-foreground">
                      Nenhuma permissão encontrada
                    </TableCell>
                  </TableRow>
                ) : (
                  permissions.data.map((permission) => (
                    <TableRow key={permission.id}>
                      <TableCell className="font-medium">
                        {permission.name}
                      </TableCell>
                      <TableCell>
                        <Badge variant="outline">{permission.module}</Badge>
                      </TableCell>
                      <TableCell className="max-w-xs truncate">
                        {permission.description || '-'}
                      </TableCell>
                      <TableCell>
                        {new Date(permission.created_at).toLocaleDateString('pt-BR')}
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
                              <Link href={route('admin.permissions.show', permission.id)}>
                                <Eye className="w-4 h-4 mr-2" />
                                Visualizar
                              </Link>
                            </DropdownMenuItem>
                            <DropdownMenuItem asChild>
                              <Link href={route('admin.permissions.edit', permission.id)}>
                                <Edit className="w-4 h-4 mr-2" />
                                Editar
                              </Link>
                            </DropdownMenuItem>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem
                              className="text-destructive"
                              onClick={() => handleDelete(permission)}
                            >
                              <Trash2 className="w-4 h-4 mr-2" />
                              Deletar
                            </DropdownMenuItem>
                          </DropdownMenuContent>
                        </DropdownMenu>
                      </TableCell>
                    </TableRow>
                  ))
                )}
              </TableBody>
            </Table>

            {/* Pagination */}
            {permissions.last_page > 1 && (
              <div className="flex items-center justify-between mt-4">
                <p className="text-sm text-muted-foreground">
                  Mostrando {permissions.from} a {permissions.to} de {permissions.total} resultados
                </p>
                <div className="flex items-center space-x-2">
                  {permissions.links.map((link, index) => (
                    <Button
                      key={index}
                      variant={link.active ? "default" : "outline"}
                      size="sm"
                      disabled={!link.url}
                      onClick={() => link.url && router.get(link.url)}
                      dangerouslySetInnerHTML={{ __html: link.label }}
                    />
                  ))}
                </div>
              </div>
            )}
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  );
}