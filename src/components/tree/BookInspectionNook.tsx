import React from 'react';
import { Book, ShelfStatus, UserBookShelf } from '../../types';
import { sounds } from '../../utils/soundEffects';
import { StarRating } from '../StarRating';
import {
  BookOpen,
  CheckCircle,
  ExternalLink,
  Sparkles,
  X,
} from 'lucide-react';

interface BookInspectionNookProps {
  item: (UserBookShelf & { book: Book; rating?: number; review_body?: string }) | null;
  isOpen: boolean;
  onClose: () => void;
  onUpdateProgress: (bookId: string, status: ShelfStatus, currentPage: number, totalPages: number) => void;
  onOpenPdfReader: (bookId: string) => void;
  onNavigateToBook: (bookId: string) => void;
}

export const BookInspectionNook: React.FC<BookInspectionNookProps> = ({
  item,
  isOpen,
  onClose,
  onUpdateProgress,
  onOpenPdfReader,
  onNavigateToBook,
}) => {
  if (!isOpen || !item) return null;

  const totalPages = item.total_pages || item.book.total_pages || 320;
  const currentPage = item.current_page || 0;
  const progressPercent = Math.min(100, Math.round((currentPage / totalPages) * 100));

  const handleAddPages = (amount: number) => {
    const next = Math.min(totalPages, Math.max(0, currentPage + amount));
    const nextStatus = next >= totalPages ? 'read' : 'reading';
    sounds.playPageTurn();
    if (next >= totalPages) {
      sounds.playChime();
    }
    onUpdateProgress(item.book.id, nextStatus, next, totalPages);
  };

  const handleMarkAsFinished = () => {
    sounds.playChime();
    onUpdateProgress(item.book.id, 'read', totalPages, totalPages);
  };

  const getWhimsicalCritique = () => {
    if (item.status === 'read') {
      return "Barnaby says: 'You conquered this literary mountain! Even the book spine looks proud.'";
    }
    if (progressPercent > 75) {
      return "Barnaby says: 'Home stretch! Cancel all evening plans. The plot is about to peak!'";
    }
    if (progressPercent > 25) {
      return "Barnaby says: 'Cruising through the middle chapters. Watch out for sudden betrayals!'";
    }
    return "Barnaby says: 'Every grand journey begins with turning past the copyright page.'";
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-5 bg-black/70 backdrop-blur-xs">
      {/* Physical Open Folio / Desk Presentation */}
      <div className="relative w-full max-w-2xl rounded-[2rem] bg-[#2A1612] p-2.5 sm:p-4 shadow-[0_30px_90px_rgba(0,0,0,0.85)] ring-1 ring-gold/30 animate-in fade-in zoom-in-95">
        {/* Close Button on Desk Edge */}
        <button
          type="button"
          onClick={onClose}
          className="absolute -top-3 -right-3 h-8 w-8 rounded-full bg-[#3B1E19] text-[#E8CFA6] border border-gold/40 flex items-center justify-center shadow-lg hover:scale-110 transition z-50"
          aria-label="Close book inspection"
        >
          <X className="h-4 w-4" />
        </button>

        {/* The Open Book Spread (Two-Page Folio) */}
        <div className="grid grid-cols-1 md:grid-cols-2 rounded-[1.6rem] bg-[#FAF5EE] dark:bg-[#1D1115] overflow-hidden shadow-inner border border-[#E6D9D0] dark:border-[#4B3035] relative">
          {/* Deep Book Gutter Crease Down Center */}
          <div className="hidden md:block pointer-events-none absolute inset-y-0 left-1/2 -translate-x-1/2 w-8 bg-gradient-to-r from-black/25 via-black/40 to-black/25 shadow-2xs z-30 opacity-70" />

          {/* Red Ribbon Bookmark dangling down the spine gutter */}
          <div
            className="hidden md:block pointer-events-none absolute -top-1 left-1/2 -translate-x-1/2 w-2 h-44 rounded-b-sm bg-gradient-to-r from-[#A32A2A] via-[#C94A4A] to-[#8C1F1F] shadow-md z-40"
            aria-hidden="true"
          />

          {/* LEFT PAGE: Illustrated Cover Plate & Edition Details */}
          <div className="p-6 sm:p-7 border-b md:border-b-0 md:border-r border-[#E6D9D0]/80 dark:border-[#4B3035] flex flex-col justify-between relative bg-gradient-to-br from-[#FFFDF9] via-[#FAF5EE] to-[#F3EBE0] dark:from-[#221519] dark:to-[#180D10]">
            <div>
              <div className="flex items-center gap-1.5 text-[10px] font-semibold uppercase tracking-widest text-tan dark:text-[#C7A491]">
                <Sparkles className="h-3 w-3 text-gold" />
                <span>Ex Libris · Shelf Volume</span>
              </div>

              {/* Cover Plate Inset */}
              <div className="mt-4 flex gap-4 items-start">
                <div className="relative aspect-[2/3] w-24 sm:w-28 rounded-lg overflow-hidden shadow-md ring-1 ring-black/20 shrink-0 bg-[#381B16]">
                  {item.book.cover_url ? (
                    <img
                      src={item.book.cover_url}
                      alt={item.book.title}
                      className="h-full w-full object-cover"
                    />
                  ) : (
                    <div className="flex h-full w-full items-center justify-center p-2 text-center font-serif text-[10px] text-[#F5E0B0]">
                      {item.book.title}
                    </div>
                  )}
                </div>

                <div className="min-w-0 flex-1">
                  <h3 className="font-serif text-lg sm:text-xl font-bold text-[#2A1A1D] dark:text-[#F4E9E2] leading-tight">
                    {item.book.title}
                  </h3>
                  <p className="mt-1 font-serif text-xs text-walnut dark:text-[#D4C2B9]">
                    by <span className="font-semibold text-burgundy dark:text-gold">{item.book.author_name}</span>
                  </p>
                  {item.book.first_publish_year && (
                    <p className="mt-1 text-[10px] text-tan">Published {item.book.first_publish_year}</p>
                  )}

                  {item.rating && (
                    <div className="mt-2.5">
                      <StarRating rating={item.rating} size="sm" showLabel />
                    </div>
                  )}
                </div>
              </div>
            </div>

            {/* Shelf Placement Selector on Left Page */}
            <div className="mt-6 pt-4 border-t border-[#E6D9D0]/70 dark:border-[#4B3035]">
              <span className="block text-[10px] font-semibold uppercase tracking-wider text-tan mb-2">
                Reading Shelf Placement
              </span>
              <div className="flex gap-1.5">
                {(['want_to_read', 'reading', 'read'] as const).map((st) => (
                  <button
                    key={st}
                    type="button"
                    onClick={() => {
                      sounds.playWoodTap();
                      onUpdateProgress(
                        item.book.id,
                        st,
                        st === 'read' ? totalPages : currentPage,
                        totalPages
                      );
                    }}
                    className={`flex-1 rounded-lg py-1.5 text-[11px] font-semibold transition ${
                      item.status === st
                        ? 'bg-burgundy text-[#FFF8EF] shadow-xs'
                        : 'border border-[#E6D9D0] bg-white/70 dark:bg-[#201719] text-[#5A3A2E] dark:text-[#D4C2B9] hover:bg-parchment'
                    }`}
                  >
                    {st === 'want_to_read' ? 'To Read' : st === 'reading' ? 'Reading' : 'Finished'}
                  </button>
                ))}
              </div>
            </div>
          </div>

          {/* RIGHT PAGE: Tactile Page Progression & Reader Actions */}
          <div className="p-6 sm:p-7 flex flex-col justify-between bg-gradient-to-bl from-[#FFFDF9] via-[#FAF5EE] to-[#F3EBE0] dark:from-[#221519] dark:to-[#180D10]">
            <div>
              {/* Barnaby's Handwritten Marginalia */}
              <div className="rounded-xl border border-warmborder/80 bg-amber-50/60 dark:bg-[#271519]/70 p-3 shadow-2xs">
                <p className="font-hand text-sm sm:text-base text-burgundy dark:text-gold leading-tight">
                  {getWhimsicalCritique()}
                </p>
              </div>

              {/* Tactile Page Turner */}
              {item.status === 'reading' ? (
                <div className="mt-5 rounded-xl border border-warmborder/80 bg-white/80 dark:bg-[#251518]/80 p-4 shadow-xs">
                  <div className="flex items-center justify-between text-xs text-walnut dark:text-[#D4C2B9] mb-1.5">
                    <span className="font-semibold font-serif">Page Bookmark</span>
                    <span>
                      Page <strong className="text-ink dark:text-white font-mono">{currentPage}</strong> of{' '}
                      <span className="font-mono">{totalPages}</span> ({progressPercent}%)
                    </span>
                  </div>

                  {/* Visual Gold Foil Progress Bar with Interactive Slider */}
                  <div className="mb-3.5 space-y-2">
                    <input
                      type="range"
                      min="0"
                      max={totalPages}
                      value={currentPage}
                      onChange={(e) => {
                        const next = parseInt(e.target.value, 10) || 0;
                        const nextStatus = next >= totalPages ? 'read' : 'reading';
                        sounds.playPageTurn();
                        if (next >= totalPages) {
                          sounds.playChime();
                        }
                        onUpdateProgress(item.book.id, nextStatus, next, totalPages);
                      }}
                      className="w-full accent-burgundy cursor-pointer h-2 bg-[#EFE3D3] dark:bg-[#3D292C] rounded-lg"
                      title="Slide to jump to page"
                    />
                  </div>

                  {/* Realistic Quick Page Advancement Buttons */}
                  <div className="flex flex-wrap items-center justify-between gap-2">
                    <div className="flex items-center gap-1.5">
                      <button
                        type="button"
                        onClick={() => handleAddPages(-10)}
                        disabled={currentPage <= 0}
                        className="rounded-md border border-warmborder px-2 py-1 text-[11px] font-medium text-walnut dark:text-[#D4C2B9] hover:bg-parchment disabled:opacity-40 transition"
                      >
                        -10 pgs
                      </button>
                      <button
                        type="button"
                        onClick={() => handleAddPages(10)}
                        disabled={currentPage >= totalPages}
                        className="rounded-md border border-warmborder px-2 py-1 text-[11px] font-medium text-walnut dark:text-[#D4C2B9] hover:bg-parchment disabled:opacity-40 transition"
                      >
                        +10 pgs
                      </button>
                      <button
                        type="button"
                        onClick={() => handleAddPages(25)}
                        disabled={currentPage >= totalPages}
                        className="rounded-md border border-warmborder px-2 py-1 text-[11px] font-medium text-walnut dark:text-[#D4C2B9] hover:bg-parchment disabled:opacity-40 transition"
                      >
                        +25 pgs
                      </button>
                    </div>

                    <button
                      type="button"
                      onClick={handleMarkAsFinished}
                      className="inline-flex items-center gap-1 rounded-full bg-emerald-700 hover:bg-emerald-800 text-white px-3 py-1 text-xs font-semibold shadow-xs transition"
                    >
                      <CheckCircle className="h-3 w-3" />
                      <span>Finished!</span>
                    </button>
                  </div>
                </div>
              ) : (
                <div className="mt-5 p-4 rounded-xl border border-dashed border-[#E6D9D0] dark:border-[#4B3035] text-center text-xs text-tan">
                  {item.status === 'read'
                    ? 'This volume is completely read and archived on your living tree.'
                    : 'Place this book in your "Reading" shelf to start logging pages.'}
                </div>
              )}
            </div>

            {/* Launch Actions */}
            <div className="mt-6 pt-4 border-t border-[#E6D9D0]/70 dark:border-[#4B3035] flex items-center justify-between gap-2">
              <button
                type="button"
                onClick={() => {
                  onClose();
                  onOpenPdfReader(item.book.id);
                }}
                className="inline-flex items-center gap-1.5 rounded-full border border-warmborder bg-white dark:bg-[#201719] px-3.5 py-1.5 text-xs font-semibold text-walnut dark:text-[#D4C2B9] hover:bg-rosetint/50 transition"
              >
                <BookOpen className="h-3.5 w-3.5 text-burgundy" />
                <span>Open Reader</span>
              </button>

              <button
                type="button"
                onClick={() => {
                  onClose();
                  onNavigateToBook(item.book.id);
                }}
                className="inline-flex items-center gap-1.5 rounded-full bg-burgundy px-4 py-1.5 text-xs font-semibold text-[#FFF8EF] shadow-xs hover:bg-burgundy-hover transition"
              >
                <span>Full Volume</span>
                <ExternalLink className="h-3 w-3" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};
