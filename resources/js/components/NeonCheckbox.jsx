import React from 'react';
import styled from 'styled-components';

// Supports both Day (Light) & Night (Dark) mode via props or parent class!
const StyledWrapper = styled.div`
  .neon-checkbox {
    --primary: #10b981;
    --size: ${props => props.$size ? `${props.$size}px` : '36px'};
    
    /* Default / Day Mode (Light Theme) */
    --box-bg: #ffffff;
    --box-border: #cbd5e1;
    --box-checked-bg: #f0fdf4;
    --box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);

    position: relative;
    width: var(--size);
    height: var(--size);
    cursor: pointer;
    -webkit-tap-highlight-color: transparent;
    display: inline-block;
  }

  /* Night Mode (Dark Theme Support) */
  .dark .neon-checkbox,
  .neon-checkbox--dark {
    --box-bg: #0f172a;
    --box-border: #334155;
    --box-checked-bg: rgba(16, 185, 129, 0.15);
    --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
  }

  .neon-checkbox input {
    display: none;
  }

  .neon-checkbox__frame {
    position: relative;
    width: 100%;
    height: 100%;
  }

  .neon-checkbox__box {
    position: absolute;
    inset: 0;
    background: var(--box-bg);
    border-radius: 8px;
    border: 2px solid var(--box-border);
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    box-shadow: var(--box-shadow);
  }

  .neon-checkbox__check-container {
    position: absolute;
    inset: 2px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .neon-checkbox__check {
    width: 80%;
    height: 80%;
    fill: none;
    stroke: var(--primary);
    stroke-width: 3.5;
    stroke-linecap: round;
    stroke-linejoin: round;
    stroke-dasharray: 40;
    stroke-dashoffset: 40;
    transform-origin: center;
    transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
  }

  .neon-checkbox__borders {
    position: absolute;
    inset: 0;
    border-radius: 8px;
    overflow: hidden;
    pointer-events: none;
  }

  .neon-checkbox__borders span {
    position: absolute;
    width: 40px;
    height: 2px;
    background: var(--primary);
    opacity: 0;
    transition: opacity 0.3s ease;
  }

  .neon-checkbox__borders span:nth-child(1) { top: 0; left: -100%; animation: borderFlow1 2s linear infinite; }
  .neon-checkbox__borders span:nth-child(2) { top: -100%; right: 0; width: 2px; height: 40px; animation: borderFlow2 2s linear infinite; }
  .neon-checkbox__borders span:nth-child(3) { bottom: 0; right: -100%; animation: borderFlow3 2s linear infinite; }
  .neon-checkbox__borders span:nth-child(4) { bottom: -100%; left: 0; width: 2px; height: 40px; animation: borderFlow4 2s linear infinite; }

  .neon-checkbox__particles span {
    position: absolute; width: 4px; height: 4px; background: var(--primary);
    border-radius: 50%; opacity: 0; pointer-events: none; top: 50%; left: 50%;
  }

  .neon-checkbox__rings {
    position: absolute; inset: -16px; pointer-events: none;
  }

  .neon-checkbox__rings .ring {
    position: absolute; inset: 0; border-radius: 50%;
    border: 1.5px solid var(--primary); opacity: 0; transform: scale(0);
  }

  .neon-checkbox__sparks span {
    position: absolute; width: 18px; height: 1.5px;
    background: linear-gradient(90deg, var(--primary), transparent); opacity: 0;
  }

  /* Hover & Checked State */
  .neon-checkbox:hover .neon-checkbox__box {
    border-color: var(--primary);
    transform: scale(1.04);
  }

  .neon-checkbox input:checked ~ .neon-checkbox__frame .neon-checkbox__box {
    border-color: var(--primary);
    background: var(--box-checked-bg);
  }

  .neon-checkbox input:checked ~ .neon-checkbox__frame .neon-checkbox__check {
    stroke-dashoffset: 0;
    transform: scale(1.08);
  }

  .neon-checkbox input:checked ~ .neon-checkbox__frame .neon-checkbox__borders span { opacity: 0.8; }
  .neon-checkbox input:checked ~ .neon-checkbox__frame .neon-checkbox__particles span { animation: particleExplosion 0.65s cubic-bezier(0.12, 0.8, 0.32, 1) forwards; }
  .neon-checkbox input:checked ~ .neon-checkbox__frame .neon-checkbox__rings .ring { animation: ringPulse 0.65s cubic-bezier(0.12, 0.8, 0.32, 1) forwards; }
  .neon-checkbox input:checked ~ .neon-checkbox__frame .neon-checkbox__sparks span { animation: sparkFlash 0.65s cubic-bezier(0.12, 0.8, 0.32, 1) forwards; }

  @keyframes borderFlow1 { 0% { transform: translateX(0); } 100% { transform: translateX(200%); } }
  @keyframes borderFlow2 { 0% { transform: translateY(0); } 100% { transform: translateY(200%); } }
  @keyframes borderFlow3 { 0% { transform: translateX(0); } 100% { transform: translateX(-200%); } }
  @keyframes borderFlow4 { 0% { transform: translateY(0); } 100% { transform: translateY(-200%); } }

  @keyframes particleExplosion {
    0% { transform: translate(-50%, -50%) scale(1); opacity: 0; }
    20% { opacity: 1; }
    100% { transform: translate(calc(-50% + var(--x, 20px)), calc(-50% + var(--y, 20px))) scale(0); opacity: 0; }
  }

  @keyframes ringPulse { 0% { transform: scale(0); opacity: 0.8; } 100% { transform: scale(2.2); opacity: 0; } }
  @keyframes sparkFlash { 0% { transform: rotate(var(--r, 0deg)) translateX(0) scale(1); opacity: 1; } 100% { transform: rotate(var(--r, 0deg)) translateX(32px) scale(0); opacity: 0; } }

  .neon-checkbox__particles span:nth-child(1) { --x: 28px; --y: -28px; }
  .neon-checkbox__particles span:nth-child(2) { --x: -28px; --y: -28px; }
  .neon-checkbox__particles span:nth-child(3) { --x: 28px; --y: 28px; }
  .neon-checkbox__particles span:nth-child(4) { --x: -28px; --y: 28px; }
  .neon-checkbox__particles span:nth-child(5) { --x: 38px; --y: 0px; }
  .neon-checkbox__particles span:nth-child(6) { --x: -38px; --y: 0px; }
  .neon-checkbox__particles span:nth-child(7) { --x: 0px; --y: 38px; }
  .neon-checkbox__particles span:nth-child(8) { --x: 0px; --y: -38px; }
  .neon-checkbox__particles span:nth-child(9) { --x: 22px; --y: -34px; }
  .neon-checkbox__particles span:nth-child(10) { --x: -22px; --y: 34px; }
  .neon-checkbox__particles span:nth-child(11) { --x: 34px; --y: 22px; }
  .neon-checkbox__particles span:nth-child(12) { --x: -34px; --y: -22px; }

  .neon-checkbox__sparks span:nth-child(1) { --r: 0deg; top: 50%; left: 50%; }
  .neon-checkbox__sparks span:nth-child(2) { --r: 90deg; top: 50%; left: 50%; }
  .neon-checkbox__sparks span:nth-child(3) { --r: 180deg; top: 50%; left: 50%; }
  .neon-checkbox__sparks span:nth-child(4) { --r: 270deg; top: 50%; left: 50%; }

  .neon-checkbox__rings .ring:nth-child(1) { animation-delay: 0s; }
  .neon-checkbox__rings .ring:nth-child(2) { animation-delay: 0.08s; }
  .neon-checkbox__rings .ring:nth-child(3) { animation-delay: 0.16s; }

  @media (prefers-reduced-motion: reduce) {
    .neon-checkbox * {
      animation: none !important;
    }
  }
`;

const AdaptiveNeonCheckbox = ({ checked = false, onChange, size = 36, color = '#10b981' }) => {
  return (
    <StyledWrapper $size={size} style={{ '--primary': color }}>
      <label className="neon-checkbox">
        <input
          type="checkbox"
          checked={checked}
          onChange={(e) => onChange && onChange(e.target.checked)}
        />
        <div className="neon-checkbox__frame">
          <div className="neon-checkbox__box">
            <div className="neon-checkbox__check-container">
              <svg viewBox="0 0 24 24" className="neon-checkbox__check">
                <path d="M3,12.5l7,7L21,5" />
              </svg>
            </div>
            <div className="neon-checkbox__borders">
              <span /><span /><span /><span />
            </div>
          </div>
          <div className="neon-checkbox__effects">
            <div className="neon-checkbox__particles">
              <span /><span /><span /><span />
              <span /><span /><span /><span />
              <span /><span /><span /><span />
            </div>
            <div className="neon-checkbox__rings">
              <div className="ring" />
              <div className="ring" />
              <div className="ring" />
            </div>
            <div className="neon-checkbox__sparks">
              <span /><span /><span /><span />
            </div>
          </div>
        </div>
      </label>
    </StyledWrapper>
  );
};

export const NeonCheckbox = AdaptiveNeonCheckbox;
export { AdaptiveNeonCheckbox };
export default AdaptiveNeonCheckbox;

