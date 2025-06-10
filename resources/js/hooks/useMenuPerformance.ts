import { useEffect, useState } from 'react';

interface MenuPerformanceProps {
  onLoadTimeChange: (loadTime: number) => void;
}

export function useMenuPerformance() {
  const [loadTimes, setLoadTimes] = useState<number[]>([]);
  const [averageLoadTime, setAverageLoadTime] = useState<number>(0);

  const recordLoadTime = (startTime: number) => {
    const endTime = performance.now();
    const loadTime = endTime - startTime;
    
    setLoadTimes(prev => {
      const newTimes = [...prev, loadTime].slice(-10); // Mantém apenas os últimos 10
      const average = newTimes.reduce((sum, time) => sum + time, 0) / newTimes.length;
      setAverageLoadTime(average);
      return newTimes;
    });
    
    return loadTime;
  };

  return {
    recordLoadTime,
    loadTimes,
    averageLoadTime,
    lastLoadTime: loadTimes[loadTimes.length - 1] || 0,
  };
}
