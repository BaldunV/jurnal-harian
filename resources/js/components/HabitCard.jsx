import { motion, AnimatePresence } from "framer-motion";
import { useEffect, useState } from "react";
import NeonCheckbox from "./NeonCheckbox";

const categoryColors = {
  emerald: { bg: "bg-emerald-500", light: "bg-emerald-50", border: "border-emerald-300", text: "text-emerald-600", glow: "shadow-emerald-400/40" },
  sky: { bg: "bg-sky-500", light: "bg-sky-50", border: "border-sky-300", text: "text-sky-600", glow: "shadow-sky-400/40" },
  violet: { bg: "bg-violet-500", light: "bg-violet-50", border: "border-violet-300", text: "text-violet-600", glow: "shadow-violet-400/40" },
  amber: { bg: "bg-amber-500", light: "bg-amber-50", border: "border-amber-300", text: "text-amber-600", glow: "shadow-amber-400/40" },
  blue: { bg: "bg-blue-500", light: "bg-blue-50", border: "border-blue-300", text: "text-blue-600", glow: "shadow-blue-400/40" },
  teal: { bg: "bg-teal-500", light: "bg-teal-50", border: "border-teal-300", text: "text-teal-600", glow: "shadow-teal-400/40" },
  indigo: { bg: "bg-indigo-500", light: "bg-indigo-50", border: "border-indigo-300", text: "text-indigo-600", glow: "shadow-indigo-400/40" },
};

export default function HabitCard({ habit, checked, onToggle, disabled, index }) {
  const [localChecked, setLocalChecked] = useState(checked);
  const color = categoryColors[habit.color] || categoryColors.emerald;

  useEffect(() => {
    setLocalChecked(checked);
  }, [checked]);

  const handleClick = () => {
    if (disabled) return;
    const next = !localChecked;
    setLocalChecked(next);
    onToggle(habit.key, next);
  };

  const handleCheckboxChange = (next) => {
    if (disabled) return;
    setLocalChecked(next);
    onToggle(habit.key, next);
  };

  return (
    <motion.div
      layout
      initial={{ opacity: 0, y: 24 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ delay: index * 0.07, type: "spring", stiffness: 300, damping: 28 }}
      whileHover={!disabled ? { y: -3, scale: 1.015 } : {}}
      onClick={handleClick}
      className={`
        relative rounded-2xl p-4 border-2 cursor-pointer select-none
        transition-all duration-300 overflow-hidden
        ${localChecked
          ? `${color.light} ${color.border} shadow-lg ${color.glow}`
          : "bg-white border-slate-200/80 shadow-sm hover:shadow-md hover:border-slate-300"
        }
        ${disabled ? "cursor-not-allowed opacity-75" : ""}
      `}
    >
      {/* Glow background when checked */}
      <AnimatePresence>
        {localChecked && (
          <motion.div
            key="glow"
            initial={{ opacity: 0, scale: 0.8 }}
            animate={{ opacity: 1, scale: 1 }}
            exit={{ opacity: 0 }}
            className={`absolute inset-0 ${color.bg} opacity-5 rounded-2xl`}
          />
        )}
      </AnimatePresence>

      <div className="relative z-10 flex items-center gap-4">
        {/* Emoji Icon with bounce */}
        <motion.div
          animate={localChecked ? { rotate: [0, -12, 12, 0], scale: [1, 1.25, 1] } : { rotate: 0, scale: 1 }}
          transition={{ duration: 0.45, type: "spring" }}
          className={`
            w-12 h-12 rounded-2xl flex items-center justify-center text-2xl shrink-0 shadow-sm
            ${localChecked ? `${color.bg} shadow-md` : "bg-slate-100"}
          `}
        >
          {habit.icon}
        </motion.div>

        {/* Label & note */}
        <div className="flex-1 min-w-0">
          <p className={`font-extrabold text-sm leading-tight ${localChecked ? color.text : "text-slate-800"}`}>
            {habit.label}
          </p>
          {habit.description && (
            <p className="text-[11px] text-slate-400 font-medium mt-0.5 truncate">{habit.description}</p>
          )}
        </div>

        {/* Neon checkbox */}
        <div className="shrink-0" onClick={(e) => e.stopPropagation()}>
          <NeonCheckbox checked={localChecked} onChange={handleCheckboxChange} size={36} />
        </div>
      </div>

      {/* Star shine effect */}
      <AnimatePresence>
        {localChecked && (
          <motion.div
            key="sparkle"
            initial={{ opacity: 0 }}
            animate={{ opacity: [0, 1, 0] }}
            transition={{ duration: 0.6 }}
            className="absolute top-2 right-2 text-amber-400 text-xs pointer-events-none"
          >
            +
          </motion.div>
        )}
      </AnimatePresence>
    </motion.div>
  );
}
