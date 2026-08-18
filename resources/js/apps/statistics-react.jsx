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

const container = document.getElementById('react-statistics-charts');
if (container) {
  const raw = container.getAttribute('data-props');
  createRoot(container).render(<StatisticsApp {...(raw ? JSON.parse(raw) : {})} />);
}

