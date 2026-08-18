import {
  RadarChart, Radar, PolarGrid, PolarAngleAxis, PolarRadiusAxis,
  ResponsiveContainer, Tooltip,
  AreaChart, Area, XAxis, YAxis, CartesianGrid,
} from "recharts";
import { motion } from "framer-motion";
import { useEffect, useState } from "react";

// Dark mode awareness for SVG charts (recharts colors are hardcoded)
function useIsDark() {
  const [isDark, setIsDark] = useState(
    typeof document !== "undefined" && document.documentElement.classList.contains("dark")
  );
  useEffect(() => {
    const observer = new MutationObserver(() =>
      setIsDark(document.documentElement.classList.contains("dark"))
    );
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ["class"] });
    return () => observer.disconnect();
  }, []);
  return isDark;
}

// Custom tooltip for radar
const RadarTooltip = ({ active, payload }) => {
  if (active && payload && payload.length) {
    return (
      <div className="bg-white/95 dark:bg-slate-800/95 backdrop-blur-md border border-slate-200 dark:border-slate-600 rounded-xl px-3 py-2 shadow-xl text-xs font-bold text-slate-700 dark:text-slate-200">
        {payload[0].payload.habit}: <span className="text-emerald-600">{payload[0].value}%</span>
      </div>
    );
  }
  return null;
};

// Custom tooltip for area chart
const AreaTooltip = ({ active, payload, label }) => {
  if (active && payload && payload.length) {
    return (
      <div className="bg-white/95 dark:bg-slate-800/95 backdrop-blur-md border border-slate-200 dark:border-slate-600 rounded-xl px-3 py-2 shadow-xl text-xs font-bold text-slate-700 dark:text-slate-200">
        <div className="text-slate-500 dark:text-slate-400 mb-1">{label}</div>
        <div className="text-emerald-600">{payload[0].value} / 7 kebiasaan</div>
      </div>
    );
  }
  return null;
};

export function HabitRadarChart({ stats }) {
  const isDark = useIsDark();
  const data = stats.map((s) => ({
    habit: s.shortName || s.name,
    fullName: s.name,
    value: s.percentage,
    icon: s.icon,
  }));

  return (
    <motion.div
      initial={{ opacity: 0, scale: 0.95 }}
      animate={{ opacity: 1, scale: 1 }}
      transition={{ duration: 0.6, ease: "easeOut" }}
      className="bg-white dark:bg-slate-800 dark:border-slate-700 rounded-3xl p-6 shadow-sm border border-slate-200/80"
    >
      <h3 className="font-extrabold text-slate-800 dark:text-slate-100 text-base mb-1 flex items-center gap-2">
        <i className="fa-solid fa-chart-simple text-emerald-500" />
        <span>Peta Kebiasaan (Radar)</span>
      </h3>
      <p className="text-xs text-slate-400 font-medium mb-5">Persentase kepatuhan tiap kebiasaan dalam periode ini</p>

      <ResponsiveContainer width="100%" height={280}>
        <RadarChart data={data} outerRadius="75%">
          <defs>
            <radialGradient id="radarGrad" cx="50%" cy="50%" r="50%">
              <stop offset="0%" stopColor="#10b981" stopOpacity={isDark ? 0.55 : 0.4} />
              <stop offset="100%" stopColor="#10b981" stopOpacity={isDark ? 0.08 : 0.05} />
            </radialGradient>
          </defs>
          <PolarGrid stroke={isDark ? "#334155" : "#e2e8f0"} />
          <PolarAngleAxis
            dataKey="habit"
            tick={{ fontSize: 11, fontWeight: 700, fill: isDark ? "#cbd5e1" : "#475569" }}
          />
          <PolarRadiusAxis
            angle={30}
            domain={[0, 100]}
            tick={{ fontSize: 9, fill: isDark ? "#64748b" : "#94a3b8" }}
            tickCount={4}
          />
          <Radar
            name="Kebiasaan"
            dataKey="value"
            stroke="#10b981"
            strokeWidth={2.5}
            fill="url(#radarGrad)"
            dot={{ fill: "#10b981", strokeWidth: 0, r: 4 }}
            activeDot={{ fill: "#059669", r: 6, strokeWidth: 2, stroke: isDark ? "#0f172a" : "#fff" }}
          />
          <Tooltip content={<RadarTooltip />} />
        </RadarChart>
      </ResponsiveContainer>
    </motion.div>
  );
}

export function HabitTrendChart({ trendData }) {
  const isDark = useIsDark();
  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.6, delay: 0.2, ease: "easeOut" }}
      className="bg-white dark:bg-slate-800 dark:border-slate-700 rounded-3xl p-6 shadow-sm border border-slate-200/80"
    >
      <h3 className="font-extrabold text-slate-800 dark:text-slate-100 text-base mb-1 flex items-center gap-2">
        <i className="fa-solid fa-chart-line text-blue-500" />
        <span>Tren Kebiasaan Harian</span>
      </h3>
      <p className="text-xs text-slate-400 font-medium mb-5">Jumlah kebiasaan yang diselesaikan per hari</p>

      <ResponsiveContainer width="100%" height={220}>
        <AreaChart data={trendData} margin={{ top: 5, right: 10, left: -20, bottom: 5 }}>
          <defs>
            <linearGradient id="trendGrad" x1="0" y1="0" x2="0" y2="1">
              <stop offset="5%" stopColor="#10b981" stopOpacity={isDark ? 0.55 : 0.4} />
              <stop offset="95%" stopColor="#10b981" stopOpacity={isDark ? 0.03 : 0.01} />
            </linearGradient>
          </defs>
          <CartesianGrid strokeDasharray="3 3" stroke={isDark ? "#1e293b" : "#f1f5f9"} />
          <XAxis
            dataKey="date"
            tick={{ fontSize: 10, fontWeight: 600, fill: isDark ? "#64748b" : "#94a3b8" }}
            tickLine={false}
            axisLine={false}
          />
          <YAxis
            domain={[0, 7]}
            tick={{ fontSize: 10, fontWeight: 600, fill: isDark ? "#64748b" : "#94a3b8" }}
            tickLine={false}
            axisLine={false}
          />
          <Tooltip content={<AreaTooltip />} />
          <Area
            type="monotone"
            dataKey="completed"
            stroke="#10b981"
            strokeWidth={2.5}
            fill="url(#trendGrad)"
            dot={{ fill: "#10b981", r: 3, strokeWidth: 0 }}
            activeDot={{ fill: "#059669", r: 5, strokeWidth: 2, stroke: isDark ? "#0f172a" : "#fff" }}
          />
        </AreaChart>
      </ResponsiveContainer>
    </motion.div>
  );
}
