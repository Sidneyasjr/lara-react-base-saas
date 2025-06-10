// Utilitário de desenvolvimento para monitorar performance do menu
// Este arquivo deve ser removido em produção

if (process.env.NODE_ENV === 'development') {
  // Monitor de performance do menu
  let menuLoadStartTime: number;
  
  const originalFetch = window.fetch;
  window.fetch = function(...args) {
    const url = args[0];
    if (typeof url === 'string' && url.includes('/api/menu')) {
      menuLoadStartTime = performance.now();
      console.group('🍽️ Menu API Call');
      console.log('URL:', url);
      console.log('Start Time:', new Date().toISOString());
    }
    
    return originalFetch.apply(this, args).then(response => {
      if (typeof url === 'string' && url.includes('/api/menu')) {
        const loadTime = performance.now() - menuLoadStartTime;
        console.log('⏱️ Load Time:', `${loadTime.toFixed(2)}ms`);
        console.log('✅ Response Status:', response.status);
        console.groupEnd();
      }
      return response;
    });
  };

  // Observer de mudanças no DOM do menu
  const observeMenuChanges = () => {
    const targetNode = document.querySelector('[data-menu-container]');
    if (!targetNode) return;

    const observer = new MutationObserver((mutations) => {
      mutations.forEach((mutation) => {
        if (mutation.type === 'childList') {
          console.log('📱 Menu DOM atualizado:', {
            addedNodes: mutation.addedNodes.length,
            removedNodes: mutation.removedNodes.length,
            timestamp: new Date().toISOString()
          });
        }
      });
    });

    observer.observe(targetNode, {
      childList: true,
      subtree: true
    });
  };

  // Inicia o observer quando o DOM estiver pronto
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', observeMenuChanges);
  } else {
    observeMenuChanges();
  }

  console.log('🛠️ Menu Performance Monitor ativo');
}
