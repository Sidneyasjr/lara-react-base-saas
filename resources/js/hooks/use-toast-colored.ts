import { usePage } from '@inertiajs/react';
import { useEffect } from 'react';
import { toast } from 'sonner';
import { type FlashMessages } from '@/types';
import { CheckCircle, XCircle, AlertTriangle, Info, Loader2 } from 'lucide-react';

/**
 * Hook para exibir mensagens flash do Laravel como toast notifications
 */
export function useFlashMessages() {
  const { props } = usePage();
  const flash = props.flash as FlashMessages | undefined;

  useEffect(() => {
    if (flash?.success) {
      toast.success(flash.success, {
        icon: <CheckCircle className="h-4 w-4" />,
      });
    }

    if (flash?.error) {
      toast.error(flash.error, {
        icon: <XCircle className="h-4 w-4" />,
      });
    }

    if (flash?.warning) {
      toast.warning(flash.warning, {
        icon: <AlertTriangle className="h-4 w-4" />,
      });
    }

    if (flash?.info) {
      toast.info(flash.info, {
        icon: <Info className="h-4 w-4" />,
      });
    }
  }, [flash]);
}

/**
 * Utilitários para exibir toast messages com ícones personalizados
 */
export const showToast = {
  success: (message: string, options?: { description?: string }) => 
    toast.success(message, {
      icon: <CheckCircle className="h-4 w-4" />,
      ...options,
    }),
    
  error: (message: string, options?: { description?: string }) => 
    toast.error(message, {
      icon: <XCircle className="h-4 w-4" />,
      ...options,
    }),
    
  warning: (message: string, options?: { description?: string }) => 
    toast.warning(message, {
      icon: <AlertTriangle className="h-4 w-4" />,
      ...options,
    }),
    
  info: (message: string, options?: { description?: string }) => 
    toast.info(message, {
      icon: <Info className="h-4 w-4" />,
      ...options,
    }),
    
  loading: (message: string, options?: { description?: string }) => 
    toast.loading(message, {
      icon: <Loader2 className="h-4 w-4 animate-spin" />,
      ...options,
    }),
    
  dismiss: (id?: string | number) => toast.dismiss(id),
  
  promise: <T>(
    promise: Promise<T>,
    messages: {
      loading: string;
      success: string | ((data: T) => string);
      error: string | ((error: Error) => string);
    }
  ) => toast.promise(promise, {
    loading: messages.loading,
    success: messages.success,
    error: messages.error,
  }),
};
