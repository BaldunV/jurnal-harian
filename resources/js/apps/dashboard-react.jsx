import { createRoot } from "react-dom/client";
import { motion, AnimatePresence } from "framer-motion";
import { useCallback, useEffect, useRef, useState } from "react";
import HabitCard from "../components/HabitCard";
import ProgressRing from "../components/ProgressRing";
import ToastNotification from "../components/ToastNotification";
import BackgroundOrbs from "../components/BackgroundOrbs";

function launchConfetti() {
  if (typeof confetti !== "function") return;
  confetti({
    particleCount: 40,
    spread: 75,
    startVelocity: 28,
    ticks: 90,
    gravity: 0.6,
    scalar: 1.6,
    origin: { y: 0.62 },
    shapes: ["⭐", "✨", "🌟"].map((s) => confetti.shapeFromText({ text: s, scalar: 2 })),
  });
}

function readFormState(baseHabits) {
  return baseHabits.map((habit) => {
    if (habit.key === "beribadah") {
      const prayers = Array.from(document.querySelectorAll(".prayer-checkbox"));
      return { ...habit, checked: prayers.length > 0 && prayers.every((input) => input.checked) };
    }

    const input = document.querySelector(`#journal-form input[name="${habit.key}"]`);
    return { ...habit, checked: Boolean(input?.checked) };
  });
}

function DashboardApp({ initialData }) {
  const baseHabits = initialData.habits || [];
  const [habits, setHabits] = useState(() => readFormState(baseHabits));
  const [toasts, setToasts] = useState([]);
  const [allDoneNotified, setAllDoneNotified] = useState(false);
  const toastId = useRef(0);

  const completed = habits.filter((habit) => habit.checked).length;
  const isSubmitted = Boolean(initialData.isSubmitted);

  const addToast = useCallback((message, type = "success") => {
    const id = ++toastId.current;
    setToasts((items) => [...items, { id, message, type }]);
  }, []);

  const removeToast = useCallback((id) => {
    setToasts((items) => items.filter((item) => item.id !== id));
  }, []);

  const refreshFromForm = useCallback(() => {
    const next = readFormState(baseHabits);
    const nextCompleted = next.filter((habit) => habit.checked).length;
    setHabits(next);

    if (nextCompleted === 7 && !allDoneNotified) {
      setAllDoneNotified(true);
      launchConfetti();
      addToast("Luar biasa! Semua kebiasaan selesai hari ini.");
    }
  }, [addToast, allDoneNotified, baseHabits]);

  useEffect(() => {
    const form = document.getElementById("journal-form");
    if (!form) return undefined;

    const onChange = () => window.setTimeout(refreshFromForm, 30);
    form.addEventListener("change", onChange);
    refreshFromForm();

    return () => form.removeEventListener("change", onChange);
  }, [refreshFromForm]);

  const handleToggle = useCallback((key, checked) => {
    if (isSubmitted || key === "beribadah") {
      if (key === "beribadah") {
        document.getElementById("ibadah-status-badge")?.scrollIntoView({ behavior: "smooth", block: "center" });
      }
      return;
    }

    const input = document.querySelector(`#journal-form input[name="${key}"]`);
    if (!input) return;
    input.checked = checked;
    input.dispatchEvent(new Event("change", { bubbles: true }));
  }, [isSubmitted]);

  return (
    <>
      <BackgroundOrbs />
      <div className="fixed top-4 right-4 z-[100] flex flex-col gap-2 items-end">
        <AnimatePresence>
          {toasts.map((toast) => (
            <ToastNotification key={toast.id} message={toast.message} type={toast.type} onClose={() => removeToast(toast.id)} />
          ))}
        </AnimatePresence>
      </div>

      <motion.section
        initial={{ opacity: 0, y: 18 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.55, ease: "easeOut" }}
        className="relative z-10 bg-white/75 backdrop-blur-xl rounded-3xl p-5 sm:p-6 border border-white/70 shadow-xl shadow-emerald-900/5"
      >
        <div className="flex flex-col xl:flex-row gap-5 xl:items-center">
          <ProgressRing completed={completed} total={7} />
          <div className="flex-1 min-w-0">
            <div className="flex items-center justify-between gap-3 mb-3">
              <div>
                <h3 className="font-extrabold text-slate-800 text-base flex items-center gap-2">
                  <i className="fa-solid fa-wand-magic-sparkles text-emerald-500" />
                  <span>Checklist Interaktif</span>
                </h3>
                <p className="text-xs text-slate-500 font-medium mt-1">Klik kartu untuk mengisi checklist utama. Detail ibadah tetap mengikuti checklist sholat/doa di form.</p>
              </div>
              <span className="text-xs font-black text-emerald-700 bg-emerald-50 border border-emerald-100 rounded-full px-3 py-1.5 shrink-0">{completed}/7</span>
            </div>
            <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
              {habits.map((habit, index) => (
                <HabitCard
                  key={habit.key}
                  habit={habit}
                  checked={habit.checked}
                  onToggle={handleToggle}
                  disabled={isSubmitted}
                  index={index}
                />
              ))}
            </div>
          </div>
        </div>
      </motion.section>
    </>
  );
}

const container = document.getElementById("react-dashboard-habits");
if (container) {
  const raw = container.getAttribute("data-props");
  createRoot(container).render(<DashboardApp initialData={raw ? JSON.parse(raw) : {}} />);
}
