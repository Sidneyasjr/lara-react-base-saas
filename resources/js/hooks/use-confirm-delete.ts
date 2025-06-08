import { useState } from 'react';

export interface UseConfirmDeleteOptions {
  onConfirm: () => void;
  title?: string;
  description?: string;
  itemName?: string;
  cancelText?: string;
  confirmText?: string;
}

export function useConfirmDelete(options: UseConfirmDeleteOptions) {
  const [isOpen, setIsOpen] = useState(false);

  const openDialog = () => setIsOpen(true);
  const closeDialog = () => setIsOpen(false);

  const handleConfirm = () => {
    options.onConfirm();
    closeDialog();
  };

  return {
    isOpen,
    openDialog,
    closeDialog,
    handleConfirm,
    dialogProps: {
      open: isOpen,
      onOpenChange: setIsOpen,
      onConfirm: handleConfirm,
      title: options.title,
      description: options.description,
      itemName: options.itemName,
      cancelText: options.cancelText,
      confirmText: options.confirmText,
    },
  };
}
