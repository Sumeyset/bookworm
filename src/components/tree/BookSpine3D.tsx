import React from 'react';
import { Book, UserBookShelf } from '../../types';
import { sounds } from '../../utils/soundEffects';

export interface BookSpine3DProps {
  item: UserBookShelf & { book: Book; rating?: number; review_body?: string };
  index: number;
  totalInBranch: number;
  isStacked?: boolean;
  onClick: () => void;
}

// Hyper-realistic heritage bookbinding palettes with authentic cloth & leather textures
export const BINDING_PALETTES = [
  {
    name: 'Morocco Crimson Leather',
    spineBg: 'linear-gradient(90deg, #2A080E 0%, #50111D 16%, #781A2A 50%, #50111D 84%, #2A080E 100%)',
    boardCover: '#581320',
    goldFoil: 'linear-gradient(135deg, #FFF8DC 0%, #E6C25A 45%, #9E7D23 100%)',
    goldText: '#E6C25A',
    boardEdge: '#2E0A10',
    headband: '#C94A4A',
    ribbon: '#C94A4A',
    accentFleuron: '⚜',
  },
  {
    name: 'Victorian Forest Buckram',
    spineBg: 'linear-gradient(90deg, #0A160F 0%, #162B1D 16%, #22422C 50%, #162B1D 84%, #0A160F 100%)',
    boardCover: '#1B3523',
    goldFoil: 'linear-gradient(135deg, #FFFBEB 0%, #DFBA51 45%, #8A6E1E 100%)',
    goldText: '#DFBA51',
    boardEdge: '#0D1710',
    headband: '#4AB87B',
    ribbon: '#4AB87B',
    accentFleuron: '❖',
  },
  {
    name: 'Midnight Oxford Buckram',
    spineBg: 'linear-gradient(90deg, #09101A 0%, #132236 16%, #1E3452 50%, #132236 84%, #09101A 100%)',
    boardCover: '#172942',
    goldFoil: 'linear-gradient(135deg, #FFF9D2 0%, #E5C158 45%, #9E7D23 100%)',
    goldText: '#E5C158',
    boardEdge: '#0A1017',
    headband: '#4A7FB8',
    ribbon: '#4A7FB8',
    accentFleuron: '✦',
  },
  {
    name: 'Antique Tooled Calfskin',
    spineBg: 'linear-gradient(90deg, #1F1008 0%, #3B2011 16%, #54301B 50%, #3B2011 84%, #1F1008 100%)',
    boardCover: '#452615',
    goldFoil: 'linear-gradient(135deg, #FFFBEB 0%, #E2BE50 45%, #8C6D1F 100%)',
    goldText: '#E2BE50',
    boardEdge: '#1C0F08',
    headband: '#B8893B',
    ribbon: '#B8893B',
    accentFleuron: '❦',
  },
  {
    name: 'Imperial Tyrian Plum',
    spineBg: 'linear-gradient(90deg, #1A0717 0%, #361030 16%, #4C1943 50%, #361030 84%, #1A0717 100%)',
    boardCover: '#3F1437',
    goldFoil: 'linear-gradient(135deg, #FFF8DC 0%, #E8C86A 45%, #A68329 100%)',
    goldText: '#E8C86A',
    boardEdge: '#1A0816',
    headband: '#A259C9',
    ribbon: '#A259C9',
    accentFleuron: '⚜',
  },
  {
    name: 'Burnished Tan Vellum',
    spineBg: 'linear-gradient(90deg, #2B1D0E 0%, #4D341B 16%, #694726 50%, #4D341B 84%, #2B1D0E 100%)',
    boardCover: '#593D20',
    goldFoil: 'linear-gradient(135deg, #FFFFFF 0%, #F5D77F 45%, #A88328 100%)',
    goldText: '#F5D77F',
    boardEdge: '#26190A',
    headband: '#D97706',
    ribbon: '#D97706',
    accentFleuron: '❖',
  },
  {
    name: 'Petroleum Slate Buckram',
    spineBg: 'linear-gradient(90deg, #091517 0%, #132629 16%, #1F3C40 50%, #132629 84%, #091517 100%)',
    boardCover: '#193033',
    goldFoil: 'linear-gradient(135deg, #FFFDF0 0%, #E6C86E 45%, #9E7D23 100%)',
    goldText: '#E6C86E',
    boardEdge: '#0C1B1D',
    headband: '#38A3A5',
    ribbon: '#38A3A5',
    accentFleuron: '✦',
  },
];

export const BookSpine3D: React.FC<BookSpine3DProps> = ({
  item,
  index,
  totalInBranch,
  isStacked = false,
  onClick,
}) => {
  const palette = BINDING_PALETTES[index % BINDING_PALETTES.length];

  // Natural organic shelf resting physics:
  // Slight tilt variations between books, upright centers, leaning edges
  const tiltAngles = [0, -3.2, 2.6, -1.8, 3.0, -2.2, 1.8];
  const tilt = !isStacked && totalInBranch > 1 ? tiltAngles[index % tiltAngles.length] : 0;

  // Realistic dimensions based on real book page count
  const totalPages = item.total_pages || item.book.total_pages || 320;
  const spineThickness = totalPages > 500 ? 46 : totalPages > 320 ? 40 : totalPages > 200 ? 34 : 28;
  const bookHeight = 78 + (index % 4) * 6; // 78px, 84px, 90px, 96px

  const progress =
    item.status === 'reading' && item.total_pages
      ? Math.min(100, Math.round(((item.current_page || 0) / item.total_pages) * 100))
      : null;

  const handleClick = (e: React.MouseEvent) => {
    e.stopPropagation();
    sounds.playBookSlide();
    onClick();
  };

  // -------------------------------------------------------------
  // HORIZONTAL STACKED TOMES VARIANT
  // -------------------------------------------------------------
  if (isStacked) {
    const stackWidth = 72 + (index % 3) * 10;
    const stackHeight = 19;

    return (
      <div
        onClick={handleClick}
        onMouseEnter={() => sounds.playPageTurn()}
        style={{
          width: `${stackWidth}px`,
          height: `${stackHeight}px`,
          marginBottom: '2px',
        }}
        className="group relative flex flex-none flex-col justify-center cursor-pointer transition-all duration-300 ease-out hover:-translate-y-2 hover:scale-105 hover:z-40 select-none"
      >
        {/* Hover info tag */}
        <div className="pointer-events-none absolute -top-14 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-xl bg-[#241316]/95 border border-gold/40 text-[#FFF8EF] px-3 py-1.5 text-center shadow-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-200 z-50">
          <p className="font-serif text-xs font-semibold leading-tight">{item.book.title}</p>
          <p className="text-[10px] text-[#E8CFA6] font-serif italic mt-0.5">{item.book.author_name}</p>
          <div className="mt-1 flex items-center justify-center gap-2 text-[9px] text-tan">
            <span>{totalPages} pages</span>
            {progress !== null && <span className="text-amber-300 font-mono font-bold">· {progress}%</span>}
          </div>
        </div>

        {/* Horizontal Tome Spine lying flat */}
        <div
          className="relative w-full h-full rounded-[2px] shadow-[0_4px_12px_rgba(15,8,4,0.7)] ring-1 ring-black/60 flex items-center justify-between px-2 overflow-hidden transition-shadow"
          style={{ background: palette.spineBg }}
        >
          {/* Top & bottom cylindrical specular edge */}
          <div className="pointer-events-none absolute inset-x-0 top-0 h-1 bg-gradient-to-b from-white/25 via-white/10 to-transparent" />
          <div className="pointer-events-none absolute inset-x-0 bottom-0 h-1 bg-gradient-to-t from-black/70 to-transparent" />

          {/* Gilded fleuron */}
          <span className="text-[8px] text-gold/80 font-serif">{palette.accentFleuron}</span>

          {/* Title printed horizontally */}
          <span
            className="font-serif font-bold text-[8px] truncate px-1 text-center select-none"
            style={{
              backgroundImage: palette.goldFoil,
              WebkitBackgroundClip: 'text',
              WebkitTextFillColor: 'transparent',
              filter: 'drop-shadow(0 1px 1px rgba(0,0,0,0.9))',
            }}
          >
            {item.book.title}
          </span>

          <span className="text-[8px] text-gold/80 font-serif">{palette.accentFleuron}</span>

          {/* Progress bar inset at bottom */}
          {progress !== null && (
            <div className="absolute inset-x-0 bottom-0 h-0.5 bg-black/80">
              <div
                className="h-full bg-gradient-to-r from-amber-400 via-gold to-amber-300"
                style={{ width: `${progress}%` }}
              />
            </div>
          )}
        </div>

        {/* Contact shadow on shelf underneath stack */}
        <div
          className="pointer-events-none absolute -bottom-1 inset-x-1 h-1.5 rounded-[50%] bg-[#120803]/80 blur-[2px]"
          aria-hidden="true"
        />
      </div>
    );
  }

  // -------------------------------------------------------------
  // UPRIGHT 3D HARDCOVER SPINE WITH TRUE PERSPECTIVE
  // -------------------------------------------------------------
  return (
    <div
      onClick={handleClick}
      onMouseEnter={() => sounds.playPageTurn()}
      style={{
        transform: `rotate(${tilt}deg)`,
        transformOrigin: 'bottom center',
        width: `${spineThickness}px`,
        height: `${bookHeight}px`,
      }}
      className="group relative flex flex-none flex-col items-center justify-end cursor-pointer transition-all duration-300 ease-out hover:-translate-y-4 hover:rotate-0 hover:z-40 select-none [perspective:800px]"
    >
      {/* Refined floating book tag on hover */}
      <div className="pointer-events-none absolute -top-14 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-xl bg-[#241316]/95 border border-gold/40 text-[#FFF8EF] px-3 py-1.5 text-center shadow-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-200 z-50">
        <p className="font-serif text-xs font-semibold leading-tight">{item.book.title}</p>
        <p className="text-[10px] text-[#E8CFA6] font-serif italic mt-0.5">{item.book.author_name}</p>
        <div className="mt-1 flex items-center justify-center gap-2 text-[9px] text-tan">
          <span>{totalPages} pages</span>
          {progress !== null && (
            <span className="text-amber-300 font-mono font-bold">· {progress}%</span>
          )}
        </div>
      </div>

      {/* Real Silk Ribbon Bookmark peeking out from top and dangling */}
      {item.status === 'reading' && (
        <div
          className="absolute -top-3.5 left-2.5 w-1.5 h-4.5 rounded-b-xs shadow-xs z-30 transition-transform duration-300 group-hover:scale-y-125"
          style={{ backgroundColor: palette.ribbon }}
          aria-hidden="true"
        >
          {/* V-cut notch at ribbon tail */}
          <div className="absolute bottom-0 inset-x-0 h-0.5 bg-black/40" />
        </div>
      )}

      {/* Gilded Top Laurel Crest for finished reading */}
      {item.status === 'read' && (
        <span
          className="absolute -top-2 right-1 w-3.5 h-3.5 rounded-full bg-gradient-to-br from-[#FFEAA7] via-[#D4AF37] to-[#8C6D1F] ring-1 ring-black/40 shadow-xs z-30 flex items-center justify-center text-[7.5px] text-[#2A1A1D] font-bold"
          title="Finished Reading"
        >
          ★
        </span>
      )}

      {/* Realistic Top Book Head: Paper Block & Headband */}
      <div className="w-full h-1.5 rounded-t-[3px] bg-[#EBE1CF] border-x border-t border-[#7A5A45]/60 relative z-20 flex items-center justify-center overflow-hidden shadow-2xs">
        {/* Authentic fine paper leaf lines texture */}
        <div
          className="absolute inset-0 opacity-40 pointer-events-none"
          style={{
            backgroundImage:
              'repeating-linear-gradient(90deg, #3A2010 0px, transparent 1px, transparent 2px)',
          }}
        />
        {/* Tiny woven two-tone fabric headband tucked inside top spine */}
        <div
          className="w-3/5 h-0.5 rounded-full opacity-90 shadow-2xs z-10"
          style={{ backgroundColor: palette.headband }}
        />
      </div>

      {/* The 3D Hardcover Curved Spine Container with Perspective Pull-Out */}
      <div
        className="relative w-full flex-1 rounded-b-[2px] shadow-[2px_6px_16px_rgba(20,9,5,0.7)] ring-1 ring-black/50 group-hover:shadow-[4px_16px_32px_rgba(20,9,5,0.95)] group-hover:ring-gold/70 overflow-hidden flex flex-col justify-between py-1 px-0.5 transition-all duration-300 group-hover:[transform:rotateY(-12deg)_translateZ(18px)]"
        style={{ background: palette.spineBg }}
      >
        {/* Textured Buckram Cloth / Leather Fine Noise Overlay */}
        <div
          className="pointer-events-none absolute inset-0 opacity-15 mix-blend-overlay z-10"
          style={{
            backgroundImage: `radial-gradient(circle at 1px 1px, rgba(255,255,255,0.4) 1px, transparent 0)`,
            backgroundSize: '3px 3px',
          }}
        />

        {/* Cylindrical 3D Lighting Gradients */}
        {/* Deep left French groove (hinge joint crevice) */}
        <div className="pointer-events-none absolute inset-y-0 left-0 w-1 bg-gradient-to-r from-black/90 via-black/45 to-transparent z-20" />
        {/* Specular curved spine cylinder highlight */}
        <div className="pointer-events-none absolute inset-y-0 left-1 w-1.5 bg-gradient-to-r from-white/25 via-white/12 to-transparent z-20" />
        {/* Right edge shadow */}
        <div className="pointer-events-none absolute inset-y-0 right-0 w-1 bg-gradient-to-l from-black/70 to-transparent z-20" />

        {/* Authentic Raised Horizontal Leather Cords (Ribs) with 3D Depth */}
        <div className="pointer-events-none absolute inset-x-0 top-3 h-0.5 bg-black/75 shadow-xs z-20">
          <div className="h-px w-full bg-white/30" />
        </div>
        <div className="pointer-events-none absolute inset-x-0 top-1/2 -translate-y-1/2 h-0.5 bg-black/75 shadow-xs z-20">
          <div className="h-px w-full bg-white/30" />
        </div>
        <div className="pointer-events-none absolute inset-x-0 bottom-4 h-0.5 bg-black/75 shadow-xs z-20">
          <div className="h-px w-full bg-white/30" />
        </div>

        {/* Top Gold Leaf Fleuron Tooling */}
        <div className="text-center pt-2 z-10">
          <span className="text-[7.5px] text-amber-200/90 leading-none select-none drop-shadow-xs">
            {palette.accentFleuron}
          </span>
          <div
            className="mx-auto w-3.5 h-px mt-0.5 shadow-xs opacity-75"
            style={{ background: palette.goldFoil }}
          />
        </div>

        {/* Vertical Embossed Book Title with Gold Foil Texture */}
        <div className="flex-1 flex items-center justify-center overflow-hidden px-0.5 py-1 z-10">
          <span
            className="font-serif font-bold text-[8.5px] leading-tight text-center line-clamp-3 select-none"
            style={{
              backgroundImage: palette.goldFoil,
              WebkitBackgroundClip: 'text',
              WebkitTextFillColor: 'transparent',
              filter: 'drop-shadow(0 1px 1px rgba(0,0,0,0.9))',
              letterSpacing: '0.03em',
            }}
          >
            {item.book.title}
          </span>
        </div>

        {/* Author Debossing at Base of Spine */}
        <div className="text-center pb-1.5 z-10">
          <span
            className="block text-[6.5px] tracking-widest uppercase font-sans font-semibold truncate opacity-85"
            style={{
              backgroundImage: palette.goldFoil,
              WebkitBackgroundClip: 'text',
              WebkitTextFillColor: 'transparent',
            }}
          >
            {item.book.author_name.split(' ').pop()}
          </span>
          <div
            className="mx-auto w-3.5 h-px mt-0.5 shadow-xs opacity-75"
            style={{ background: palette.goldFoil }}
          />
        </div>

        {/* Reading Progress Line seamlessly inset at foot of spine */}
        {progress !== null && (
          <div className="absolute inset-x-0 bottom-0 h-1 bg-black/85 z-30">
            <div
              className="h-full bg-gradient-to-r from-amber-400 via-gold to-amber-300"
              style={{ width: `${progress}%` }}
            />
          </div>
        )}
      </div>

      {/* Realistic Ambient Occlusion Shelf Contact Shadow */}
      <div
        className="pointer-events-none -bottom-1 h-1.5 w-[110%] rounded-[50%] bg-[#120803]/85 blur-[2px] transition-all group-hover:scale-95 group-hover:opacity-40"
        aria-hidden="true"
      />
    </div>
  );
};
