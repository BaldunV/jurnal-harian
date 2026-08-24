import { createRoot } from 'react-dom/client';
import { HabitRadarChart, HabitTrendChart } from '../components/StatisticsCharts';

function StatisticsApp({ stats = [], trendData = [] }) {
  return (
    <div className='grid grid-cols-1 xl:grid-cols-2 gap-5'>
      <HabitRadarChart stats={stats} />
      <HabitTrendChart trendData={trendData} />
    </div>
  );
}

let root = null;
let mountedContainer = null;

function mountStatistics() {
  const container = document.getElementById('react-statistics-charts');
  if (!container) {
    if (root) {
      root.unmount();
      root = null;
      mountedContainer = null;
    }
    return;
  }
  if (root && mountedContainer !== container) {
    root.unmount();
    root = null;
  }
  if (!root) {
    root = createRoot(container);
    mountedContainer = container;
  }
  const raw = container.getAttribute('data-props');
  root.render(<StatisticsApp {...(raw ? JSON.parse(raw) : {})} />);
}

mountStatistics();
document.addEventListener('livewire:navigated', mountStatistics);

