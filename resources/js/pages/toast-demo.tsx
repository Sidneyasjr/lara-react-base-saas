import React from 'react';
import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { showToast } from '@/hooks/use-toast';
import { CheckCircle, XCircle, AlertTriangle, Info, Loader2 } from 'lucide-react';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Toast Demo',
    href: '/toast-demo',
  },
];

export default function ToastDemo() {
  const handleSuccessToast = () => {
    showToast.success('Operação realizada com sucesso!');
  };

  const handleErrorToast = () => {
    showToast.error('Ocorreu um erro durante a operação.');
  };

  const handleWarningToast = () => {
    showToast.warning('Atenção: Esta ação pode ter consequências.');
  };

  const handleInfoToast = () => {
    showToast.info('Informação importante para o usuário.');
  };

  const handleLoadingToast = () => {
    const toastId = showToast.loading('Processando...');
    
    // Simular uma operação assíncrona
    setTimeout(() => {
      showToast.dismiss(toastId);
      showToast.success('Operação concluída!');
    }, 3000);
  };

  const handlePromiseToast = () => {
    const promise = new Promise((resolve, reject) => {
      setTimeout(() => {
        // Simular sucesso ou erro aleatoriamente
        const success = Math.random() > 0.5;
        if (success) {
          resolve('Dados carregados');
        } else {
          reject(new Error('Falha no carregamento'));
        }
      }, 2000);
    });

    showToast.promise(promise, {
      loading: 'Carregando dados...',
      success: (data) => `${data} com sucesso!`,
      error: 'Erro ao carregar dados',
    });
  };

  const handleCustomToast = () => {
    showToast.success('Toast customizado com duração personalizada!');
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Toast Demo" />

      <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
        <div>
          <h1 className="text-3xl font-bold tracking-tight">Toast Messages Demo</h1>
          <p className="text-muted-foreground">
            Demonstração dos diferentes tipos de notificações toast disponíveis.
          </p>
        </div>

        <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <CheckCircle className="h-5 w-5 text-green-600" />
                Success Toast
              </CardTitle>
            </CardHeader>
            <CardContent>
              <p className="text-sm text-muted-foreground mb-4">
                Exibe uma mensagem de sucesso para confirmar que uma operação foi realizada.
              </p>
              <Button onClick={handleSuccessToast} className="w-full">
                Mostrar Sucesso
              </Button>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <XCircle className="h-5 w-5 text-red-600" />
                Error Toast
              </CardTitle>
            </CardHeader>
            <CardContent>
              <p className="text-sm text-muted-foreground mb-4">
                Informa sobre erros que ocorreram durante uma operação.
              </p>
              <Button onClick={handleErrorToast} variant="destructive" className="w-full">
                Mostrar Erro
              </Button>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <AlertTriangle className="h-5 w-5 text-yellow-600" />
                Warning Toast
              </CardTitle>
            </CardHeader>
            <CardContent>
              <p className="text-sm text-muted-foreground mb-4">
                Alerta sobre situações que requerem atenção do usuário.
              </p>
              <Button onClick={handleWarningToast} variant="outline" className="w-full">
                Mostrar Aviso
              </Button>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Info className="h-5 w-5 text-blue-600" />
                Info Toast
              </CardTitle>
            </CardHeader>
            <CardContent>
              <p className="text-sm text-muted-foreground mb-4">
                Fornece informações importantes ou dicas para o usuário.
              </p>
              <Button onClick={handleInfoToast} variant="secondary" className="w-full">
                Mostrar Info
              </Button>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Loader2 className="h-5 w-5 text-purple-600" />
                Loading Toast
              </CardTitle>
            </CardHeader>
            <CardContent>
              <p className="text-sm text-muted-foreground mb-4">
                Indica que uma operação está em progresso.
              </p>
              <Button onClick={handleLoadingToast} variant="outline" className="w-full">
                Mostrar Loading
              </Button>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <CheckCircle className="h-5 w-5 text-indigo-600" />
                Promise Toast
              </CardTitle>
            </CardHeader>
            <CardContent>
              <p className="text-sm text-muted-foreground mb-4">
                Gerencia automaticamente loading, sucesso e erro para promises.
              </p>
              <Button onClick={handlePromiseToast} variant="outline" className="w-full">
                Testar Promise
              </Button>
            </CardContent>
          </Card>
        </div>

        <Card>
          <CardHeader>
            <CardTitle>Toast Customizado</CardTitle>
          </CardHeader>
          <CardContent>
            <p className="text-sm text-muted-foreground mb-4">
              Exemplo de toast com descrição adicional e duração personalizada.
            </p>
            <Button onClick={handleCustomToast} className="w-full">
              Mostrar Toast Customizado
            </Button>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Exemplos de Uso</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div>
              <h4 className="font-medium">Toast simples:</h4>
              <code className="text-sm bg-muted p-2 rounded block mt-1">
                showToast.success('Mensagem de sucesso!')
              </code>
            </div>
            
            <div>
              <h4 className="font-medium">Toast com promise:</h4>
              <code className="text-sm bg-muted p-2 rounded block mt-1">
                {`showToast.promise(promise, {
  loading: 'Carregando...',
  success: 'Sucesso!',
  error: 'Erro!'
})`}
              </code>
            </div>

            <div>
              <h4 className="font-medium">Toast com loading manual:</h4>
              <code className="text-sm bg-muted p-2 rounded block mt-1">
                {`const id = showToast.loading('Processando...')
// ... fazer operação
showToast.dismiss(id)
showToast.success('Concluído!')`}
              </code>
            </div>

            <div>
              <h4 className="font-medium">Flash messages do Laravel:</h4>
              <p className="text-sm text-muted-foreground mt-1">
                As flash messages do Laravel (success, error, warning, info) são automaticamente 
                convertidas em toasts quando você utiliza redirect()-&gt;with().
              </p>
            </div>
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  );
}
