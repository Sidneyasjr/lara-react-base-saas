import { usePage } from '@inertiajs/react';
import { useEffect } from 'react';
import { toast } from 'sonner';
import { type FlashMessages } from '@/types';

/**
 * Hook para exibir mensagens flash do Laravel como toast notifications
 */
export function useFlashMessages() {
  const { props } = usePage();
  const flash = props.flash as FlashMessages | undefined;

  useEffect(() => {
    if (flash?.success) {
      toast.success(flash.success);
    }

    if (flash?.error) {
      toast.error(flash.error);
    }

    if (flash?.warning) {
      toast.warning(flash.warning);
    }

    if (flash?.info) {
      toast.info(flash.info);
    }
  }, [flash]);
}

/**
 * Utilitários para exibir toast messages
 */
export const showToast = {
  success: (message: string) => toast.success(message),
  error: (message: string) => toast.error(message),
  warning: (message: string) => toast.warning(message),
  info: (message: string) => toast.info(message),
  loading: (message: string) => toast.loading(message),
  dismiss: (id?: string | number) => toast.dismiss(id),
  promise: <T>(
    promise: Promise<T>,
    messages: {
      loading: string;
      success: string | ((data: T) => string);
      error: string | ((error: Error) => string);
    }
  ) => toast.promise(promise, messages),
};
