import React from 'react';

interface WoodShelfPlankProps {
  className?: string;
}

export const WoodShelfPlank: React.FC<WoodShelfPlankProps> = ({ className = '' }) => {
  return (
    <div
      className={`relative w-full h-3 rounded-[3px] select-none pointer-events-none ${className}`}
      style={{
        background:
          'linear-gradient(180deg, #6B4329 0%, #4D2D1A 45%, #301A0E 100%)',
        boxShadow:
          'inset 0 1px 0 rgba(255, 230, 185, 0.45), inset 0 -1px 0 rgba(0, 0, 0, 0.8), 0 8px 18px rgba(12, 6, 2, 0.85)',
      }}
      aria-hidden="true"
    >
      {/* Woodgrain subtle striation line */}
      <div
        className="absolute inset-x-2 top-0.5 h-px opacity-30"
        style={{
          background:
            'linear-gradient(90deg, transparent 0%, #FFE6B9 20%, transparent 60%, #FFE6B9 85%, transparent 100%)',
        }}
      />

      {/* Ambient shadow gradient where books touch the top surface */}
      <div className="absolute inset-x-0 -top-1.5 h-1.5 bg-gradient-to-t from-black/50 to-transparent" />
    </div>
  );
};
