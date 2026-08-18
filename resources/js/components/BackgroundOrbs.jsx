export default function BackgroundOrbs() {
  return (
    <div className="fixed inset-0 overflow-hidden pointer-events-none z-0" aria-hidden="true">
      {/* Orb 1 - emerald top-left */}
      <div
        className="absolute -top-32 -left-32 w-96 h-96 rounded-full opacity-20"
        style={{
          background: "radial-gradient(circle, #10b981 0%, #059669 50%, transparent 70%)",
          animation: "orbFloat1 12s ease-in-out infinite",
          filter: "blur(40px)",
        }}
      />
      {/* Orb 2 - blue top-right */}
      <div
        className="absolute -top-20 right-10 w-80 h-80 rounded-full opacity-15"
        style={{
          background: "radial-gradient(circle, #6366f1 0%, #8b5cf6 50%, transparent 70%)",
          animation: "orbFloat2 16s ease-in-out infinite",
          filter: "blur(50px)",
        }}
      />
      {/* Orb 3 - amber center-bottom */}
      <div
        className="absolute bottom-10 left-1/2 -translate-x-1/2 w-72 h-72 rounded-full opacity-10"
        style={{
          background: "radial-gradient(circle, #f59e0b 0%, #f97316 50%, transparent 70%)",
          animation: "orbFloat3 20s ease-in-out infinite",
          filter: "blur(60px)",
        }}
      />
      {/* Orb 4 - teal bottom-right */}
      <div
        className="absolute bottom-1/4 -right-20 w-64 h-64 rounded-full opacity-12"
        style={{
          background: "radial-gradient(circle, #14b8a6 0%, #0d9488 50%, transparent 70%)",
          animation: "orbFloat4 14s ease-in-out infinite",
          filter: "blur(45px)",
        }}
      />

      <style>{`
        @keyframes orbFloat1 {
          0%, 100% { transform: translate(0, 0) scale(1); }
          33%       { transform: translate(40px, 30px) scale(1.08); }
          66%       { transform: translate(-20px, 50px) scale(0.95); }
        }
        @keyframes orbFloat2 {
          0%, 100% { transform: translate(0, 0) scale(1); }
          40%       { transform: translate(-50px, 20px) scale(1.1); }
          70%       { transform: translate(20px, 40px) scale(0.92); }
        }
        @keyframes orbFloat3 {
          0%, 100% { transform: translateX(-50%) scale(1); }
          50%       { transform: translateX(calc(-50% + 60px)) scale(1.12); }
        }
        @keyframes orbFloat4 {
          0%, 100% { transform: translate(0, 0) scale(1); }
          45%       { transform: translate(-30px, -40px) scale(1.06); }
        }
      `}</style>
    </div>
  );
}
