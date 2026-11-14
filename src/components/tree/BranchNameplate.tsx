import React from 'react';

interface BranchNameplateProps {
  numberRoman: string;
  title: string;
  bookCount: number;
  isFocused?: boolean;
  onClick?: () => void;
  className?: string;
}

export const BranchNameplate: React.FC<BranchNameplateProps> = ({
  numberRoman,
  title,
  bookCount,
  isFocused = false,
  onClick,
  className = '',
}) => {
  return (
    <button
      type="button"
      onClick={onClick}
      className={`group relative flex items-center gap-1.5 rounded-sm px-2 py-0.5 text-[8.5px] font-semibold tracking-wider uppercase transition-all duration-200 select-none shadow-[0_2px_5px_rgba(0,0,0,0.45)] cursor-pointer ${
        isFocused
          ? 'bg-gradient-to-r from-[#FFF5D0] via-[#E8C86A] to-[#C99E32] text-[#1E1106] ring-1.5 ring-amber-400 scale-105'
          : 'bg-gradient-to-r from-[#DFBA5A] via-[#C99E32] to-[#99731C] text-[#241306] hover:brightness-110 hover:scale-102'
      } ${className}`}
      style={{
        border: '1px solid rgba(100, 70, 15, 0.7)',
      }}
      title={`Branch ${numberRoman}: ${title} (${bookCount} ${bookCount === 1 ? 'book' : 'books'}) — Click to focus`}
    >
      {/* Tiny brass screw pin left */}
      <span className="w-1 h-1 rounded-full bg-[#523A0F] ring-0.5 ring-[#FFEBAC] shrink-0" aria-hidden="true" />

      {/* Roman numeral branch index */}
      <span className="font-serif font-bold text-[#3B1E05]">{numberRoman}</span>
      <span className="truncate max-w-[100px] font-medium text-[#261508]">{title}</span>
      <span className="opacity-75 font-mono text-[7.5px] text-[#42270E]">({bookCount})</span>

      {/* Tiny brass screw pin right */}
      <span className="w-1 h-1 rounded-full bg-[#523A0F] ring-0.5 ring-[#FFEBAC] shrink-0" aria-hidden="true" />
    </button>
  );
};
