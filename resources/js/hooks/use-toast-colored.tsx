import { usePage } from '@inertiajs/react';
import { useEffect } from 'react';
import { toast } from 'sonner';
import { type FlashMessages } from '@/types';
import { CheckCircle, XCircle, AlertTriangle, Info, Loader2 } from 'lucide-react';
import React from 'react';

type ToastOptions = {
  description?: string;
  duration?: number;
};

/**
 * Hook para exibir mensagens flash do Laravel como toast notifications
 */
export function useFlashMessages() {
  const { props } = usePage();
  const flash = props.flash as FlashMessages | undefined;

  useEffect(() => {
    if (flash?.success) {
      toast.success(flash.success, {
        icon: React.createElement(CheckCircle, { className: "h-4 w-4" }),
      });
    }

    if (flash?.error) {
      toast.error(flash.error, {
        icon: React.createElement(XCircle, { className: "h-4 w-4" }),
      });
    }

    if (flash?.warning) {
      toast(flash.warning, {
        icon: React.createElement(AlertTriangle, { className: "h-4 w-4" }),
      });
    }

    if (flash?.info) {
      toast.info(flash.info, {
        icon: React.createElement(Info, { className: "h-4 w-4" }),
      });
    }
  }, [flash]);
}

/**
 * Toast colorido personalizado com ícones
 */
export const coloredToast = {
  success: (message: string, options?: ToastOptions) => {
    return toast.success(message, {
      icon: React.createElement(CheckCircle, { className: "h-4 w-4" }),
      ...options,
    });
  },
  
  error: (message: string, options?: ToastOptions) => {
    return toast.error(message, {
      icon: React.createElement(XCircle, { className: "h-4 w-4" }),
      ...options,
    });
  },
  
  warning: (message: string, options?: ToastOptions) => {
    return toast(message, {
      icon: React.createElement(AlertTriangle, { className: "h-4 w-4" }),
      ...options,
    });
  },
  
  info: (message: string, options?: ToastOptions) => {
    return toast.info(message, {
      icon: React.createElement(Info, { className: "h-4 w-4" }),
      ...options,
    });
  },
  
  loading: (message: string, options?: ToastOptions) => {
    return toast.loading(message, {
      icon: React.createElement(Loader2, { className: "h-4 w-4 animate-spin" }),
      ...options,
    });
  },
    
  dismiss: (id?: string | number) => toast.dismiss(id),
};
