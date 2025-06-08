import { FormEvent } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { showToast } from '@/hooks/use-toast';
import AppLayout from '@/layouts/app-layout';
import { ArrowLeft, Save, Shield } from 'lucide-react';

interface ExistingModule {
  value: string;
  label: string;
}

interface PermissionCreateProps {
  existingModules: ExistingModule[];
}

export default function PermissionCreate({ existingModules }: PermissionCreateProps) {
  
  const { data, setData, post, processing, errors, reset } = useForm({
    name: '',
    module: '',
    description: '',
  });

  const handleSubmit = (e: FormEvent) => {
    e.preventDefault();
    
    post(route('admin.permissions.store'), {
      onSuccess: () => {
        showToast.success('Permissão criada com sucesso!');
        reset();
      },
      onError: () => {
        showToast.error('Erro ao criar permissão. Verifique os dados e tente novamente.');
      },
    });
  };

  const generatePermissionName = (module: string, action: string) => {
    if (module && action) {
      return `${module}.${action}`;
    }
    return '';
  };

  const commonActions = ['view', 'create', 'edit', 'delete'];

  return (
    <AppLayout>
      <Head title="Nova Permissão" />

      <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        {/* Header */}
        <div className="flex items-center gap-4">
          <Button variant="outline" size="sm" asChild>
            <Link href={route('admin.permissions.index')}>
              <ArrowLeft className="w-4 h-4 mr-2" />
              Voltar
            </Link>
          </Button>
          <div>
            <h1 className="text-3xl font-bold tracking-tight">Nova Permissão</h1>
            <p className="text-muted-foreground">
              Crie uma nova permissão para o sistema
            </p>
          </div>
        </div>

        <div className="grid gap-6 lg:grid-cols-3">
          {/* Form */}
          <div className="lg:col-span-2">
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Shield className="w-5 h-5" />
                  Dados da Permissão
                </CardTitle>
                <CardDescription>
                  Preencha as informações da nova permissão
                </CardDescription>
              </CardHeader>
              <CardContent>
                <form onSubmit={handleSubmit} className="space-y-6">
                  <div className="grid gap-4 md:grid-cols-2">
                    <div className="space-y-2">
                      <Label htmlFor="module">
                        Módulo *
                      </Label>
                      <Select value={data.module} onValueChange={(value) => setData('module', value)}>
                        <SelectTrigger>
                          <SelectValue placeholder="Selecione ou digite um módulo" />
                        </SelectTrigger>
                        <SelectContent>
                          {existingModules.map((module) => (
                            <SelectItem key={module.value} value={module.value}>
                              {module.label}
                            </SelectItem>
                          ))}
                        </SelectContent>
                      </Select>
                      <Input
                        placeholder="Ou digite um novo módulo"
                        value={data.module}
                        onChange={(e) => setData('module', e.target.value)}
                      />
                      {errors.module && (
                        <p className="text-sm text-destructive">{errors.module}</p>
                      )}
                    </div>

                    <div className="space-y-2">
                      <Label htmlFor="name">
                        Nome da Permissão *
                      </Label>
                      <Input
                        id="name"
                        placeholder="Ex: users.view, posts.create"
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                      />
                      {errors.name && (
                        <p className="text-sm text-destructive">{errors.name}</p>
                      )}
                    </div>
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="description">
                      Descrição
                    </Label>
                    <Textarea
                      id="description"
                      placeholder="Descreva para que serve esta permissão..."
                      value={data.description}
                      onChange={(e) => setData('description', e.target.value)}
                      rows={3}
                    />
                    {errors.description && (
                      <p className="text-sm text-destructive">{errors.description}</p>
                    )}
                  </div>

                  <div className="flex justify-end gap-4">
                    <Button type="button" variant="outline" asChild>
                      <Link href={route('admin.permissions.index')}>
                        Cancelar
                      </Link>
                    </Button>
                    <Button type="submit" disabled={processing}>
                      <Save className="w-4 h-4 mr-2" />
                      {processing ? 'Salvando...' : 'Salvar Permissão'}
                    </Button>
                  </div>
                </form>
              </CardContent>
            </Card>
          </div>

          {/* Helper Panel */}
          <div className="space-y-6">
            <Card>
              <CardHeader>
                <CardTitle className="text-lg">Ações Comuns</CardTitle>
                <CardDescription>
                  Clique em uma ação para gerar o nome automaticamente
                </CardDescription>
              </CardHeader>
              <CardContent>
                <div className="grid gap-2">
                  {commonActions.map((action) => (
                    <Button
                      key={action}
                      variant="outline"
                      size="sm"
                      type="button"
                      onClick={() => setData('name', generatePermissionName(data.module, action))}
                      disabled={!data.module}
                    >
                      {action}
                    </Button>
                  ))}
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle className="text-lg">Convenções</CardTitle>
              </CardHeader>
              <CardContent className="space-y-3">
                <div>
                  <h4 className="font-medium text-sm">Formato do Nome</h4>
                  <p className="text-sm text-muted-foreground">
                    Use o formato: <code className="bg-muted px-1 rounded">módulo.ação</code>
                  </p>
                </div>
                <div>
                  <h4 className="font-medium text-sm">Exemplos</h4>
                  <ul className="text-sm text-muted-foreground space-y-1">
                    <li>• <code className="bg-muted px-1 rounded">users.view</code></li>
                    <li>• <code className="bg-muted px-1 rounded">posts.create</code></li>
                    <li>• <code className="bg-muted px-1 rounded">settings.edit</code></li>
                  </ul>
                </div>
                <div>
                  <h4 className="font-medium text-sm">Módulos Comuns</h4>
                  <ul className="text-sm text-muted-foreground space-y-1">
                    <li>• users, roles, permissions</li>
                    <li>• posts, pages, categories</li>
                    <li>• settings, reports, analytics</li>
                  </ul>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </AppLayout>
  );
}