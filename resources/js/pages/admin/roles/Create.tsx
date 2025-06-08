import { FormEvent, useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import { Badge } from '@/components/ui/badge';
import { toast } from 'sonner';
import AppLayout from '@/layouts/app-layout';
import { PermissionsByModule } from '@/types';
import { ArrowLeft, Save, Crown, Shield } from 'lucide-react';

interface RoleCreateProps {
  permissions: PermissionsByModule;
}

export default function RoleCreate({ permissions }: RoleCreateProps) {
  const [expandedModules, setExpandedModules] = useState<Record<string, boolean>>({});

  const { data, setData, post, processing, errors, reset } = useForm({
    name: '',
    description: '',
    permissions: [] as string[],
  });

  const handleSubmit = (e: FormEvent) => {
    e.preventDefault();
    
    post(route('admin.roles.store'), {
      onSuccess: () => {
        toast.success('Role criado com sucesso!');
        reset();
      },
      onError: () => {
        toast.error('Erro ao criar role. Verifique os dados e tente novamente.');
      },
    });
  };

  const toggleModule = (module: string) => {
    setExpandedModules(prev => ({
      ...prev,
      [module]: !prev[module]
    }));
  };

  const toggleModulePermissions = (module: string, checked: boolean) => {
    const modulePermissions = permissions[module]?.map(p => p.name) || [];
    
    if (checked) {
      // Add all permissions from this module
      setData('permissions', [...new Set([...data.permissions, ...modulePermissions])]);
    } else {
      // Remove all permissions from this module
      setData('permissions', data.permissions.filter((p: string) => !modulePermissions.includes(p)));
    }
  };

  const togglePermission = (permissionName: string, checked: boolean) => {
    if (checked) {
      setData('permissions', [...data.permissions, permissionName]);
    } else {
      setData('permissions', data.permissions.filter((p: string) => p !== permissionName));
    }
  };

  const isModuleFullySelected = (module: string) => {
    const modulePermissions = permissions[module]?.map(p => p.name) || [];
    return modulePermissions.length > 0 && modulePermissions.every(p => data.permissions.includes(p));
  };

  const isModulePartiallySelected = (module: string) => {
    const modulePermissions = permissions[module]?.map(p => p.name) || [];
    return modulePermissions.some(p => data.permissions.includes(p)) && !isModuleFullySelected(module);
  };

  return (
    <AppLayout>
      <Head title="Novo Role" />

      <div className="space-y-6">
        {/* Header */}
        <div className="flex items-center gap-4">
          <Button variant="outline" size="sm" asChild>
            <Link href={route('admin.roles.index')}>
              <ArrowLeft className="w-4 h-4 mr-2" />
              Voltar
            </Link>
          </Button>
          <div>
            <h1 className="text-3xl font-bold tracking-tight">Novo Role</h1>
            <p className="text-muted-foreground">
              Crie um novo role e defina suas permissões
            </p>
          </div>
        </div>

        <form onSubmit={handleSubmit} className="grid gap-6 lg:grid-cols-3">
          {/* Basic Information */}
          <div className="lg:col-span-1">
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Crown className="w-5 h-5" />
                  Informações Básicas
                </CardTitle>
                <CardDescription>
                  Defina o nome e descrição do role
                </CardDescription>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="space-y-2">
                  <Label htmlFor="name">
                    Nome do Role *
                  </Label>
                  <Input
                    id="name"
                    placeholder="Ex: Editor, Moderador"
                    value={data.name}
                    onChange={(e) => setData('name', e.target.value)}
                    required
                  />
                  {errors.name && (
                    <p className="text-sm text-destructive">{errors.name}</p>
                  )}
                </div>

                <div className="space-y-2">
                  <Label htmlFor="description">
                    Descrição
                  </Label>
                  <Textarea
                    id="description"
                    placeholder="Descreva as responsabilidades deste role..."
                    value={data.description}
                    onChange={(e) => setData('description', e.target.value)}
                    rows={4}
                  />
                  {errors.description && (
                    <p className="text-sm text-destructive">{errors.description}</p>
                  )}
                </div>

                <div className="pt-4">
                  <h4 className="font-medium text-sm mb-2">Resumo das Permissões</h4>
                  <Badge variant="secondary">
                    {data.permissions.length} permissão(ões) selecionada(s)
                  </Badge>
                </div>
              </CardContent>
            </Card>
          </div>

          {/* Permissions */}
          <div className="lg:col-span-2">
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Shield className="w-5 h-5" />
                  Permissões
                </CardTitle>
                <CardDescription>
                  Selecione as permissões que este role deve ter
                </CardDescription>
              </CardHeader>
              <CardContent>
                {errors.permissions && (
                  <p className="text-sm text-destructive mb-4">{errors.permissions}</p>
                )}
                
                <div className="space-y-4">
                  {Object.entries(permissions).map(([module, modulePermissions]) => (
                    <div key={module} className="border rounded-lg p-4">
                      <div className="flex items-center justify-between">
                        <div className="flex items-center space-x-3">
                          <Checkbox
                            id={`module-${module}`}
                            checked={isModuleFullySelected(module)}
                            onCheckedChange={(checked) => toggleModulePermissions(module, checked as boolean)}
                          />
                          <Label 
                            htmlFor={`module-${module}`}
                            className="text-base font-medium cursor-pointer"
                          >
                            {module}
                          </Label>
                          <Badge variant="outline">
                            {modulePermissions.length} permissão(ões)
                          </Badge>
                          {isModulePartiallySelected(module) && (
                            <Badge variant="secondary" className="text-xs">
                              Parcialmente selecionado
                            </Badge>
                          )}
                        </div>
                        <Button
                          type="button"
                          variant="ghost"
                          size="sm"
                          onClick={() => toggleModule(module)}
                        >
                          {expandedModules[module] ? 'Recolher' : 'Expandir'}
                        </Button>
                      </div>

                      {expandedModules[module] && (
                        <div className="mt-3 pl-6 space-y-2">
                          {modulePermissions.map((permission) => (
                            <div key={permission.id} className="flex items-center space-x-3">
                              <Checkbox
                                id={`permission-${permission.id}`}
                                checked={data.permissions.includes(permission.name)}
                                onCheckedChange={(checked) => togglePermission(permission.name, checked as boolean)}
                              />
                              <Label 
                                htmlFor={`permission-${permission.id}`}
                                className="text-sm cursor-pointer flex-1"
                              >
                                <span className="font-mono text-xs bg-muted px-1 rounded mr-2">
                                  {permission.name}
                                </span>
                                {permission.description && (
                                  <span className="text-muted-foreground">
                                    {permission.description}
                                  </span>
                                )}
                              </Label>
                            </div>
                          ))}
                        </div>
                      )}
                    </div>
                  ))}
                </div>
              </CardContent>
            </Card>
          </div>

          {/* Submit Button */}
          <div className="lg:col-span-3 flex justify-end gap-4">
            <Button type="button" variant="outline" asChild>
              <Link href={route('admin.roles.index')}>
                Cancelar
              </Link>
            </Button>
            <Button type="submit" disabled={processing}>
              <Save className="w-4 h-4 mr-2" />
              {processing ? 'Salvando...' : 'Salvar Role'}
            </Button>
          </div>
        </form>
      </div>
    </AppLayout>
  );
}