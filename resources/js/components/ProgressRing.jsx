import { motion, useSpring, useTransform, useMotionValue } from "framer-motion";
import { useEffect } from "react";

function AnimatedNumber({ value }) {
  const motionVal = useMotionValue(0);
  const rounded = useTransform(motionVal, (v) => Math.round(v));
  const spring = useSpring(motionVal, { stiffness: 100, damping: 30, restDelta: 0.5 });

  useEffect(() => {
    spring.set(value);
  }, [value, spring]);

  return <motion.span>{rounded}</motion.span>;
}

export default function ProgressRing({ completed, total = 7 }) {
  const radius = 52;
  const stroke = 9;
  const normalizedRadius = radius - stroke / 2;
  const circumference = normalizedRadius * 2 * Math.PI;
  const percent = Math.min((completed / total) * 100, 100);
  const offset = circumference - (percent / 100) * circumference;

  // Color based on progress
  const getColor = (p) => {
    if (p >= 100) return "#10b981"; // emerald
    if (p >= 70) return "#14b8a6";  // teal
    if (p >= 40) return "#f59e0b";  // amber
    return "#ef4444";               // red
  };

  const getGlow = (p) => {
    if (p >= 100) return "drop-shadow(0 0 14px rgba(16,185,129,0.7))";
    if (p >= 70) return "drop-shadow(0 0 10px rgba(20,184,166,0.5))";
    if (p >= 40) return "drop-shadow(0 0 10px rgba(245,158,11,0.5))";
    return "drop-shadow(0 0 8px rgba(239,68,68,0.4))";
  };

  const color = getColor(percent);

  return (
    <div className="flex flex-col items-center justify-center gap-3">
      <div className="relative w-[120px] h-[120px]">
        <svg width="120" height="120" className="-rotate-90">
          {/* Track */}
          <circle
            cx="60" cy="60" r={normalizedRadius}
            fill="none"
            stroke="#f1f5f9"
            strokeWidth={stroke}
          />
          {/* Progress arc */}
          <motion.circle
            cx="60" cy="60" r={normalizedRadius}
            fill="none"
            stroke={color}
            strokeWidth={stroke}
            strokeDasharray={circumference}
            strokeLinecap="round"
            initial={{ strokeDashoffset: circumference }}
            animate={{ strokeDashoffset: offset, filter: getGlow(percent) }}
            transition={{ duration: 1, ease: "easeOut", delay: 0.2 }}
          />
        </svg>

        {/* Center text */}
        <div className="absolute inset-0 flex flex-col items-center justify-center">
          <span className="text-3xl font-black text-slate-800 leading-none">
            <AnimatedNumber value={completed} />
          </span>
          <span className="text-xs font-bold text-slate-400 mt-0.5">/ {total}</span>
        </div>

        {/* Done badge */}
        {percent >= 100 && (
          <motion.div
            initial={{ scale: 0, rotate: -30 }}
            animate={{ scale: 1, rotate: 0 }}
            transition={{ type: "spring", stiffness: 400, damping: 20, delay: 0.8 }}
            className="absolute -top-2 -right-2 w-8 h-8 bg-amber-400 rounded-full flex items-center justify-center text-base shadow-lg"
          >
            ⭐
          </motion.div>
        )}
      </div>

      <div className="text-center">
        <div className="text-sm font-extrabold text-slate-700">
          {percent >= 100 ? "Sempurna! 🎉" : `${Math.round(percent)}% Selesai`}
        </div>
        <div className="text-xs text-slate-400 font-medium mt-0.5">Progress Hari Ini</div>
      </div>
    </div>
  );
}
