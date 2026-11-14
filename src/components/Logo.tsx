import React from 'react';

interface LogoProps {
  variant?: 'full' | 'icon';
  tone?: 'light' | 'dark';
  className?: string;
  size?: 'sm' | 'md' | 'lg';
}

export const Logo: React.FC<LogoProps> = ({
  variant = 'full',
  tone = 'dark',
  className = '',
  size = 'md',
}) => {
  const isLight = tone === 'light';
  const sizeClasses = {
    sm: 'h-8',
    md: 'h-10 sm:h-11',
    lg: 'h-12 sm:h-14',
  }[size];

  return (
    <span className={`inline-flex items-center gap-3 select-none ${className}`}>
      {/* Bespoke Modern Literary Tree & Open Book Emblem */}
      <svg
        className={`${sizeClasses} w-auto shrink-0 drop-shadow-xs`}
        viewBox="0 0 88 88"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
        aria-hidden="true"
      >
        <defs>
          <linearGradient id="bookCoverGrad" x1="8" y1="52" x2="80" y2="82" gradientUnits="userSpaceOnUse">
            <stop offset="0%" stopColor="#4A1220" />
            <stop offset="50%" stopColor="#6D1A2B" />
            <stop offset="100%" stopColor="#2A0B12" />
          </linearGradient>
          <linearGradient id="treeGold" x1="44" y1="6" x2="44" y2="60" gradientUnits="userSpaceOnUse">
            <stop offset="0%" stopColor="#F5D07A" />
            <stop offset="45%" stopColor="#D4A045" />
            <stop offset="100%" stopColor="#9E6E24" />
          </linearGradient>
          <linearGradient id="leafAmber" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0%" stopColor="#FDE68A" />
            <stop offset="100%" stopColor="#D97706" />
          </linearGradient>
          <linearGradient id="pageGlow" x1="44" y1="50" x2="44" y2="76" gradientUnits="userSpaceOnUse">
            <stop offset="0%" stopColor="#FFFDF9" />
            <stop offset="100%" stopColor="#EFE3D3" />
          </linearGradient>
        </defs>

        {/* Circular Warm Ambient Halo */}
        <circle cx="44" cy="44" r="41" fill={isLight ? 'rgba(255,255,255,0.08)' : 'rgba(184,137,59,0.09)'} />
        <circle cx="44" cy="44" r="40" stroke={isLight ? 'rgba(244,233,226,0.2)' : 'rgba(184,137,59,0.28)'} strokeWidth="1" strokeDasharray="3 3" />

        {/* Open Book Base with Craftsman Leather Binding */}
        <path
          d="M12 60.5C22 55.5 33 56.5 44 61.5C55 56.5 66 55.5 76 60.5V74C66 69 55 70 44 75C33 70 22 69 12 74V60.5Z"
          fill="url(#bookCoverGrad)"
          stroke="#B8893B"
          strokeWidth="1.2"
        />

        {/* Illuminated Parchment Pages Fan */}
        <path
          d="M15 58C24 53.5 34 54.5 44 59C54 54.5 64 53.5 73 58V70.5C64 66 54 67 44 71.5C34 67 24 66 15 70.5V58Z"
          fill="url(#pageGlow)"
        />
        {/* Book Spine Center Gilded Seam */}
        <line x1="44" y1="58.5" x2="44" y2="73" stroke="#B8893B" strokeWidth="2" strokeLinecap="round" />

        {/* Stately Living Oak Tree Rising Out of the Book */}
        <path
          d="M44 58.5V36M44 43C40 37 32 35 25 38M44 38C49 32 57 31 63 35M44 32C41 26 35 23 29 24M44 28C48 22 54 21 59 23M44 25V14"
          stroke="url(#treeGold)"
          strokeWidth="3.2"
          strokeLinecap="round"
          strokeLinejoin="round"
        />

        {/* Delicate Golden Leaves / Buds on Branches */}
        <circle cx="24" cy="38" r="3.2" fill="url(#leafAmber)" />
        <circle cx="64" cy="35" r="3.2" fill="url(#leafAmber)" />
        <circle cx="28" cy="24" r="2.8" fill="url(#leafAmber)" />
        <circle cx="60" cy="22" r="2.8" fill="url(#leafAmber)" />
        <circle cx="44" cy="11" r="3.5" fill="url(#leafAmber)" />
        <circle cx="36" cy="18" r="2.2" fill="url(#leafAmber)" />
        <circle cx="52" cy="17" r="2.2" fill="url(#leafAmber)" />

        {/* Golden Literary Star at the Crest of the Tree */}
        <path
          d="M44 6L45.4 9.6L49 11L45.4 12.4L44 16L42.6 12.4L39 11L42.6 9.6L44 6Z"
          fill="#FDE68A"
        />

        {/* Dangling Silk Bookmark Ribbon with Gilded Swallowtail */}
        <path
          d="M44 73V81L41.5 79L39 81V73.5"
          fill="#8B1E34"
          stroke="#D4A045"
          strokeWidth="0.8"
        />
      </svg>

      {/* Brand Typography */}
      {variant === 'full' && (
        <span className="flex min-w-0 flex-col text-left">
          <span className="flex items-center gap-1.5 font-serif text-2xl font-bold tracking-tight leading-none text-[#2A1A1D] dark:text-[#FFF8EF]">
            <span>Bookworm</span>
            <span className="h-1.5 w-1.5 rounded-full bg-[#B8893B] inline-block" />
          </span>
          <span className="mt-1 font-serif text-[11px] uppercase tracking-[0.2em] font-medium text-[#7C5A48] dark:text-[#C7A491] leading-none">
            Living Library
          </span>
        </span>
      )}
    </span>
  );
};
