import React from 'react';
import { Button } from '@/components/ui/button';
import { router } from '@inertiajs/react';

interface PaginationLink {
  url: string | null;
  label: string;
  active: boolean;
}

interface PaginationData {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  links: PaginationLink[];
  from?: number;
  to?: number;
}

interface PaginationProps {
  data: PaginationData;
  /** Texto personalizado para mostrar informações da paginação */
  showingText?: (from: number, to: number, total: number) => string;
  /** Classe CSS adicional para o container */
  className?: string;
  /** Se deve mostrar as informações de contagem */
  showInfo?: boolean;
}

export function Pagination({ 
  data, 
  showingText,
  className = "",
  showInfo = true 
}: PaginationProps) {
  // Se há apenas uma página, não mostra a paginação
  if (data.last_page <= 1) {
    return null;
  }

  // Calcula o "from" e "to" se não estiverem disponíveis
  const from = data.from || ((data.current_page - 1) * data.per_page + 1);
  const to = data.to || Math.min(data.current_page * data.per_page, data.total);

  const defaultShowingText = (from: number, to: number, total: number) => 
    `Mostrando ${from} a ${to} de ${total} resultados`;

  const handlePageClick = (url: string | null) => {
    if (url) {
      router.get(url);
    }
  };

  return (
    <div className={`flex items-center justify-between space-x-2 py-4 ${className}`}>
      {showInfo && (
        <div className="text-sm text-muted-foreground">
          {showingText ? showingText(from, to, data.total) : defaultShowingText(from, to, data.total)}
        </div>
      )}
      
      <div className="flex items-center space-x-2">
        {data.links.map((link, index) => {
          // Botões Previous e Next
          if (link.label === 'Previous' || link.label === 'Next') {
            return (
              <Button
                key={index}
                variant="outline"
                size="sm"
                disabled={!link.url}
                onClick={() => handlePageClick(link.url)}
              >
                {link.label === 'Previous' ? 'Anterior' : 'Próximo'}
              </Button>
            );
          }
          
          // Separador ...
          if (link.label === '...') {
            return (
              <span key={index} className="px-3 py-1 text-muted-foreground">
                ...
              </span>
            );
          }
          
          // Botões de página numérica
          return (
            <Button
              key={index}
              variant={link.active ? 'default' : 'outline'}
              size="sm"
              onClick={() => handlePageClick(link.url)}
              disabled={link.active}
            >
              {link.label}
            </Button>
          );
        })}
      </div>
    </div>
  );
}

export default Pagination;
