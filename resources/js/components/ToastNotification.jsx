import { motion, AnimatePresence } from "framer-motion";
import { useEffect, useState } from "react";

export default function ToastNotification({ message, type = "success", onClose, duration = 4000 }) {
  const [visible, setVisible] = useState(true);
  const [progress, setProgress] = useState(100);

  useEffect(() => {
    const step = 50;
    const decrement = (step / duration) * 100;
    const timer = setInterval(() => {
      setProgress((p) => {
        if (p <= 0) { clearInterval(timer); setVisible(false); return 0; }
        return p - decrement;
      });
    }, step);
    return () => clearInterval(timer);
  }, [duration]);

  useEffect(() => {
    if (!visible) { setTimeout(onClose, 300); }
  }, [visible, onClose]);

  const configs = {
    success: {
      bg: "bg-white",
      border: "border-emerald-200",
      icon: "✅",
      bar: "bg-emerald-500",
      text: "text-slate-800",
      shadow: "shadow-emerald-100",
    },
    error: {
      bg: "bg-white",
      border: "border-rose-200",
      icon: "❌",
      bar: "bg-rose-500",
      text: "text-slate-800",
      shadow: "shadow-rose-100",
    },
    info: {
      bg: "bg-white",
      border: "border-blue-200",
      icon: "ℹ️",
      bar: "bg-blue-500",
      text: "text-slate-800",
      shadow: "shadow-blue-100",
    },
  };

  const cfg = configs[type] || configs.success;

  return (
    <AnimatePresence>
      {visible && (
        <motion.div
          initial={{ opacity: 0, y: -24, scale: 0.94 }}
          animate={{ opacity: 1, y: 0, scale: 1 }}
          exit={{ opacity: 0, y: -16, scale: 0.95 }}
          transition={{ type: "spring", stiffness: 400, damping: 30 }}
          className={`
            relative flex items-center gap-3 px-4 py-3.5 rounded-2xl border
            ${cfg.bg} ${cfg.border} shadow-xl ${cfg.shadow}
            min-w-[280px] max-w-sm overflow-hidden
          `}
        >
          <span className="text-xl shrink-0">{cfg.icon}</span>
          <p className={`text-sm font-semibold flex-1 leading-snug ${cfg.text}`}>{message}</p>
          <button
            onClick={() => setVisible(false)}
            className="text-slate-400 hover:text-slate-600 transition-colors shrink-0 text-base"
          >✕</button>

          {/* Progress bar */}
          <div className="absolute bottom-0 left-0 right-0 h-1 bg-slate-100 rounded-b-2xl overflow-hidden">
            <motion.div
              className={`h-full ${cfg.bar} rounded-b-2xl`}
              style={{ width: `${progress}%` }}
              transition={{ duration: 0.05 }}
            />
          </div>
        </motion.div>
      )}
    </AnimatePresence>
  );
}
