import React, { useState, useEffect } from 'react';
import { Book, ShelfStatus, UserBookShelf } from '../types';
import { StorageService } from '../services/storage';
import { sounds } from '../utils/soundEffects';
import {
  BookOpen,
  Edit2,
  Plus,
  X,
  Sparkles,
  ArrowUp,
  Move,
  Check,
  ExternalLink,
  Layers,
  ChevronDown,
} from 'lucide-react';

interface TreeBookshelfProps {
  items: (UserBookShelf & { book: Book; rating?: number; review_body?: string })[];
  isOwnProfile?: boolean;
  userId?: string;
  onSelectBook: (bookId: string) => void;
  onUpdateProgress?: (bookId: string, status: ShelfStatus, currentPage: number, totalPages: number) => void;
  onOpenPdfReader?: (bookId: string) => void;
  onExploreMore?: () => void;
}

// Master branch coordinates dictionary across tree growth tiers
// Sized with generous headroom so book tops (and status badges) NEVER hit or clip the container borders!
interface BranchDefinition {
  slot: number;
  tier: number; // 1, 2, 3, 4
  side: 'left' | 'right';
  left: number; // percentage
  width: number; // percentage
  bottom: number; // percentage
  defaultName: string;
}

const ALL_TREE_BRANCHES: BranchDefinition[] = [
  // --- Tier 1: Young Trunk (4 baseline branches) ---
  { slot: 3, tier: 1, side: 'left', left: 16.0, width: 34.0, bottom: 44.0, defaultName: 'Cozy Mystery' },
  { slot: 4, tier: 1, side: 'right', left: 53.5, width: 34.0, bottom: 35.5, defaultName: 'Heart & Hearth' },
  { slot: 5, tier: 1, side: 'left', left: 17.0, width: 34.0, bottom: 26.5, defaultName: 'Non-Fiction & Life' },
  { slot: 6, tier: 1, side: 'right', left: 52.5, width: 33.5, bottom: 18.0, defaultName: 'Poetry & Bedside' },

  // --- Tier 2: Flourishing Oak (Adds upper mid branches - slots 0, 1, 2) ---
  // Notice: slot 0 & 1 are placed at 70% & 62% bottom with generous 25%+ top clearance!
  { slot: 0, tier: 2, side: 'right', left: 52.5, width: 34.5, bottom: 69.5, defaultName: 'Fav Thrillers' },
  { slot: 1, tier: 2, side: 'left', left: 16.5, width: 34.5, bottom: 61.0, defaultName: 'Currently Reading' },
  { slot: 2, tier: 2, side: 'right', left: 53.0, width: 34.5, bottom: 52.5, defaultName: 'All-Time Favorites' },

  // --- Tier 3: Majestic Canopy Tree (Adds slots 7 & 8 when library expands to 13+ books) ---
  { slot: 7, tier: 3, side: 'left', left: 18.0, width: 33.0, bottom: 78.5, defaultName: 'Canopy Classics' },
  { slot: 8, tier: 3, side: 'right', left: 51.5, width: 33.0, bottom: 78.5, defaultName: 'Sci-Fi & Futures' },

  // --- Tier 4: Grand Ancient Arbor (Adds slots 9 & 10 when library reaches 20+ books) ---
  { slot: 9, tier: 4, side: 'left', left: 22.0, width: 32.0, bottom: 87.0, defaultName: 'Ancient Wisdom' },
  { slot: 10, tier: 4, side: 'right', left: 49.0, width: 32.0, bottom: 87.0, defaultName: 'Crown Manuscripts' },
];

export const TreeBookshelf: React.FC<TreeBookshelfProps> = ({
  items,
  isOwnProfile = false,
  userId = 'u-sumeyset',
  onSelectBook,
  onExploreMore,
}) => {
  const [shelfTitles, setShelfTitles] = useState<Record<number, string>>(() =>
    StorageService.getTreeShelfNames(userId)
  );
  const [editingSlot, setEditingSlot] = useState<number | null>(null);
  const [editTitleValue, setEditTitleValue] = useState<string>('');
  const [addingToSlot, setAddingToSlot] = useState<number | null>(null);
  const [availableBooks, setAvailableBooks] = useState<Book[]>([]);

  // Drag and drop state
  const [draggedBookId, setDraggedBookId] = useState<string | null>(null);
  const [dragOverSlot, setDragOverSlot] = useState<number | null>(null);
  const [quickMoveBookId, setQuickMoveBookId] = useState<string | null>(null);

  // Manual Tree Growth Override for testing / instant upgrade
  const [unlockedTierBonus, setUnlockedTierBonus] = useState<number>(0);

  // Calculate dynamic tree level & height based on book count + bonuses
  const totalShelvedBooks = items.length;
  const computedTier = Math.min(
    4,
    Math.max(
      1,
      totalShelvedBooks >= 20 ? 4 : totalShelvedBooks >= 13 ? 3 : totalShelvedBooks >= 6 ? 2 : 1
    ) + unlockedTierBonus
  );
  const activeTreeTier = Math.min(4, Math.max(1, computedTier));

  // Filter branches for the active tier
  const activeBranches = ALL_TREE_BRANCHES.filter((b) => b.tier <= activeTreeTier).sort(
    (a, b) => a.slot - b.slot
  );

  // Aspect ratio grows taller as the tree ascends higher!
  const canvasAspectRatio =
    activeTreeTier === 1
      ? '4 / 3.0'
      : activeTreeTier === 2
      ? '4 / 3.25'
      : activeTreeTier === 3
      ? '4 / 3.65'
      : '4 / 4.1';

  const tierLabels = [
    '',
    'Tier 1: Sapling Grove (4 Branches)',
    'Tier 2: Flourishing Oak (7 Branches)',
    'Tier 3: High Canopy Tree (9 Branches)',
    'Tier 4: Ancient Grand Arbor (11 Branches)',
  ];

  const nextTierThresholds = [0, 6, 13, 20, 999];
  const nextTarget = nextTierThresholds[activeTreeTier];
  const progressPercent = Math.min(
    100,
    Math.round((totalShelvedBooks / (nextTarget || 20)) * 100)
  );

  useEffect(() => {
    setShelfTitles(StorageService.getTreeShelfNames(userId));
    setAvailableBooks(StorageService.getBooks());
  }, [userId, items]);

  // Rename title
  const handleOpenEditTitle = (slot: number) => {
    sounds.click();
    setEditingSlot(slot);
    setEditTitleValue(shelfTitles[slot] || `Shelf ${slot + 1}`);
  };

  const handleSaveTitle = (e?: React.FormEvent) => {
    if (e) e.preventDefault();
    if (editingSlot === null || !editTitleValue.trim()) return;
    const updated = StorageService.setTreeShelfTitle(userId, editingSlot, editTitleValue.trim());
    setShelfTitles(updated);
    setEditingSlot(null);
    sounds.shelfTap();
  };

  // Add book to slot
  const handleAddBookToSlot = (bookId: string, slot: number) => {
    sounds.bookTurn();
    const res = StorageService.assignBookToShelfSlot(userId, bookId, slot);
    if (!res.success && res.error) {
      alert(res.error);
    } else {
      setAddingToSlot(null);
      if (onSelectBook) onSelectBook(bookId);
    }
  };

  // Drag and Drop Handlers
  const handleDragStart = (e: React.DragEvent, bookId: string) => {
    setDraggedBookId(bookId);
    e.dataTransfer.setData('text/plain', bookId);
    e.dataTransfer.effectAllowed = 'move';
    sounds.bookSlide();
  };

  const handleDragOver = (e: React.DragEvent, slot: number) => {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    if (dragOverSlot !== slot) {
      setDragOverSlot(slot);
    }
  };

  const handleDragLeave = (e: React.DragEvent, slot: number) => {
    if (dragOverSlot === slot) {
      setDragOverSlot(null);
    }
  };

  const handleDropOnBranch = (e: React.DragEvent, targetSlot: number, targetBookId?: string) => {
    e.preventDefault();
    setDragOverSlot(null);
    const bookId = e.dataTransfer.getData('text/plain') || draggedBookId;
    if (!bookId) return;

    sounds.bookTurn();
    const res = StorageService.moveOrSwapBook(userId, bookId, targetSlot, targetBookId);
    if (!res.success && res.error) {
      alert(res.error);
    } else {
      setDraggedBookId(null);
      window.location.reload();
    }
  };

  const handleQuickMove = (bookId: string, targetSlot: number) => {
    sounds.bookTurn();
    const res = StorageService.assignBookToShelfSlot(userId, bookId, targetSlot);
    if (!res.success && res.error) {
      alert(res.error);
    } else {
      setQuickMoveBookId(null);
      window.location.reload();
    }
  };

  // Group books by shelf slot, max 3 books per shelf
  const shelvesData = activeBranches.map((branch) => {
    let slotBooks = items.filter((item) => item.shelf_slot === branch.slot);

    // Fallback unassigned distribution
    if (slotBooks.length === 0) {
      const explicitIds = new Set(
        items.filter((i) => i.shelf_slot !== undefined).map((i) => i.id)
      );
      const unassigned = items.filter((i) => !explicitIds.has(i.id));
      const startIdx = branch.slot * 3;
      slotBooks = unassigned.slice(startIdx, startIdx + 3);
    }

    return {
      slot: branch.slot,
      title: shelfTitles[branch.slot] || branch.defaultName,
      books: slotBooks.slice(0, 3),
      coord: branch,
    };
  });

  return (
    <div className="rounded-3xl border border-[#eadfd0] bg-gradient-to-br from-[#fffdfa] via-[#fdf7ee] to-[#f7eedf] p-4 shadow-[0_24px_60px_rgba(80,45,28,0.1)] dark:border-[#4B3035] dark:from-[#2a1c20] dark:via-[#221619] dark:to-[#1a0f12] sm:p-6 transition-all">
      {/* Modern Top Header with Tree Level & Growth Progress */}
      <div className="mb-6 flex flex-col gap-4 rounded-2xl border border-[#ede0d0] bg-white/85 p-4 shadow-xs backdrop-blur-xs dark:border-[#4B3035] dark:bg-[#201215]/85 sm:flex-row sm:items-center sm:justify-between">
        <div className="min-w-0">
          <div className="flex items-center gap-2">
            <span className="inline-flex items-center gap-1 rounded-full bg-[#6D1A2B]/10 px-2.5 py-0.5 text-xs font-semibold text-[#6D1A2B] dark:bg-[#6D1A2B]/30 dark:text-[#f2c2cb]">
              <Sparkles className="h-3.5 w-3.5" />
              Living Tree Growth
            </span>
            <span className="text-xs font-medium text-[#7C5A48] dark:text-[#c7a491]">
              {tierLabels[activeTreeTier]}
            </span>
          </div>
          <h3 className="mt-1 font-serif text-xl font-bold tracking-tight text-[#2A1A1D] dark:text-[#f4e9e2]">
            Ascending Library Tree
          </h3>
          <p className="mt-0.5 text-xs text-[#80695d] dark:text-[#bda497]">
            As you add books, the tree reaches higher and sprouts new branches! Drag books between
            shelves to curate your reading world.
          </p>
        </div>

        {/* Tree Growth Badge & Manual Growth Affordance */}
        <div className="flex shrink-0 flex-wrap items-center gap-2">
          <div className="rounded-xl border border-[#e3d2be] bg-[#fbf5eb] px-3 py-1.5 dark:border-[#4B3035] dark:bg-[#2b181c]">
            <div className="flex items-center justify-between gap-3 text-[11px] font-semibold text-[#6E4833] dark:text-[#f0d8c1]">
              <span>Height: {activeTreeTier * 25}%</span>
              <span>{totalShelvedBooks} Books Shelved</span>
            </div>
            <div className="mt-1 h-1.5 w-32 overflow-hidden rounded-full bg-[#ebdcd0] dark:bg-[#432a2f]">
              <div
                className="h-full rounded-full bg-gradient-to-r from-[#B8893B] to-[#6D1A2B] transition-all duration-500"
                style={{ width: `${progressPercent}%` }}
              />
            </div>
          </div>

          {/* Test / Accelerate Tree Growth Button */}
          {activeTreeTier < 4 && (
            <button
              type="button"
              onClick={() => {
                sounds.chime();
                setUnlockedTierBonus((b) => b + 1);
              }}
              title="Grow tree to unlock next canopy tier!"
              className="inline-flex items-center gap-1.5 rounded-full bg-gradient-to-r from-[#B8893B] to-[#9E6E24] px-3.5 py-2 text-xs font-semibold text-white shadow-xs transition hover:scale-105 hover:shadow-md"
            >
              <ArrowUp className="h-3.5 w-3.5" />
              Grow Tree +Branch
            </button>
          )}
        </div>
      </div>

      {/* The Dynamic Ascending Tree Canvas */}
      <div
        role="region"
        aria-label="Interactive Growing Tree Bookshelf"
        className="overflow-x-auto rounded-[2rem] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#8B1E34]"
      >
        <div
          className="mx-auto min-w-[760px] max-w-[1120px] overflow-hidden rounded-[2rem] shadow-[0_30px_70px_-20px_rgba(70,35,20,0.5)] ring-1 ring-[#e5d5be] dark:ring-[#4B3035] transition-all duration-700"
          style={{ containerType: 'inline-size' }}
        >
          <div
            className="relative w-full select-none pt-12 pb-6 transition-all duration-700"
            style={{ aspectRatio: canvasAspectRatio }}
          >
            {/* Tree Background Image with Natural Vertical Growth Extension */}
            <div className="absolute inset-0 z-0 overflow-hidden">
              <img
                src="/images/tree/bookshelf-background.jpg"
                alt="Living tree bookshelf"
                className="h-full w-full object-cover object-bottom transition-transform duration-700"
                onError={(e) => {
                  (e.target as HTMLImageElement).src =
                    'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?auto=format&fit=crop&w=1200&q=80';
                }}
              />
              {/* Foliage Canopy Overlay that darkens smoothly toward the roots */}
              <div
                className="pointer-events-none absolute inset-0 bg-gradient-to-b from-[#2A150D]/20 via-transparent to-[#1a0f12]/30"
                aria-hidden="true"
              />

              {/* Natural Sunlight Shafts from Window */}
              <div
                className="pointer-events-none absolute inset-0 bg-gradient-to-tr from-transparent via-amber-100/10 to-amber-200/15"
                aria-hidden="true"
              />
            </div>

            {/* Render the Active Shelves on the Tree */}
            {shelvesData.map((shelf) => {
              const { slot, title, books, coord } = shelf;
              const isDragTarget = dragOverSlot === slot;
              const emptySlots = Math.max(0, 3 - books.length);

              return (
                <div
                  key={slot}
                  role="group"
                  aria-label={title}
                  onDragOver={(e) => handleDragOver(e, slot)}
                  onDragLeave={(e) => handleDragLeave(e, slot)}
                  onDrop={(e) => handleDropOnBranch(e, slot)}
                  className={`absolute z-10 flex flex-col items-center transition-all duration-300 ${
                    isDragTarget
                      ? 'scale-[1.03] ring-2 ring-[#B8893B] rounded-2xl bg-black/20 p-1.5'
                      : ''
                  }`}
                  style={{
                    left: `${coord.left}%`,
                    width: `${coord.width}%`,
                    bottom: `${coord.bottom}%`,
                  }}
                >
                  {/* 3-Book Row Resting on the Live-Edge Branch */}
                  <div className="relative flex w-full items-end justify-center gap-[1.1cqw] px-[1cqw]">
                    {/* Books (up to 3) */}
                    {books.map((item) => {
                      const book = item.book;
                      const statusKey = item.status || 'want_to_read';
                      const isDraggingThis = draggedBookId === book.id;

                      const ribbonBg =
                        statusKey === 'reading'
                          ? 'bg-[#c9964f] text-[#3d2310]'
                          : statusKey === 'read'
                          ? 'bg-[#6d9670] text-[#18361b]'
                          : 'bg-[#82a4bf] text-[#1b3142]';
                      const statusLabel =
                        statusKey === 'reading'
                          ? 'Reading'
                          : statusKey === 'read'
                          ? 'Read'
                          : 'To Read';

                      return (
                        <article
                          key={item.id}
                          draggable={isOwnProfile}
                          onDragStart={(e) => handleDragStart(e, book.id)}
                          onDragOver={(e) => handleDragOver(e, slot)}
                          onDrop={(e) => {
                            e.stopPropagation();
                            handleDropOnBranch(e, slot, book.id);
                          }}
                          className={`group/book relative flex flex-col items-center select-none transition-all duration-200 cursor-grab active:cursor-grabbing ${
                            isDraggingThis ? 'opacity-40 scale-95' : 'hover:z-30'
                          }`}
                          style={{ width: '9.2cqw' }}
                        >
                          {/* Top Status Tag */}
                          <span
                            className={`mb-[0.4cqw] inline-flex items-center rounded-full px-[0.5cqw] py-[0.12cqw] font-bold uppercase tracking-wider shadow-xs ${ribbonBg}`}
                            style={{
                              fontSize: 'clamp(7px, 0.8cqw, 9.5px)',
                              lineHeight: 1.1,
                            }}
                          >
                            {statusLabel}
                          </span>

                          {/* Book Container with Full Cover Image */}
                          <div
                            onClick={() => {
                              sounds.bookSlide();
                              onSelectBook(book.id);
                            }}
                            title={`${book.title} by ${book.author_name || 'Unknown'}\n(Drag to move to another branch)`}
                            className="relative block aspect-[2/3] w-full rounded-[3px] bg-[#3B2418] shadow-[0_6px_16px_rgba(20,10,5,0.5)] ring-1 ring-black/25 transition-all duration-300 ease-out hover:-translate-y-2.5 hover:shadow-[0_14px_28px_rgba(20,10,5,0.65)] hover:rotate-[-1.5deg]"
                          >
                            {/* Spine shadow on left edge */}
                            <span
                              className="pointer-events-none absolute inset-y-0 left-0 z-20 w-[0.8cqw] bg-gradient-to-r from-black/60 via-white/10 to-transparent"
                              aria-hidden="true"
                            />

                            {/* Book Cover Art */}
                            {book.cover_url ? (
                              <img
                                src={book.cover_url}
                                alt={book.title}
                                loading="lazy"
                                className="absolute inset-0 z-10 h-full w-full rounded-[2px] object-cover transition-transform duration-300 group-hover/book:scale-[1.02]"
                              />
                            ) : null}

                            {/* Fallback Cloth Binding if no cover image */}
                            <div className="absolute inset-0 z-0 flex flex-col items-center justify-between rounded-[2px] border border-[#d8b577]/35 bg-gradient-to-br from-[#5a1c27] via-[#40121d] to-[#250810] p-[0.6cqw] text-center text-[#f7e6c4]">
                              <span className="w-full truncate text-[0.7cqw] uppercase tracking-widest text-[#d8b577]/80">
                                Classic
                              </span>
                              <span
                                className="line-clamp-3 font-serif font-semibold leading-tight text-[#fff6e3]"
                                style={{ fontSize: 'clamp(7.5px, 0.95cqw, 12px)' }}
                              >
                                {book.title}
                              </span>
                              <span
                                className="line-clamp-1 text-[#e8cda2]/90"
                                style={{ fontSize: 'clamp(6.5px, 0.75cqw, 9.5px)' }}
                              >
                                {book.author_name || 'Author'}
                              </span>
                            </div>

                            {/* Sheen highlight */}
                            <span
                              className="pointer-events-none absolute inset-0 z-20 rounded-[2px] bg-gradient-to-tr from-transparent via-white/5 to-white/15"
                              aria-hidden="true"
                            />

                            {/* Reading progress bar */}
                            {item.total_pages && item.status === 'reading' && (
                              <div className="absolute inset-x-0 bottom-0 z-30 h-[0.5cqw] rounded-b-[2px] bg-black/60">
                                <div
                                  className="h-full rounded-b-[2px] bg-gradient-to-r from-[#d5a469] to-[#f3cf7a]"
                                  style={{
                                    width: `${Math.min(
                                      100,
                                      Math.round(((item.current_page || 0) / item.total_pages) * 100)
                                    )}%`,
                                  }}
                                />
                              </div>
                            )}

                            {/* Drag Grip Indicator on Hover */}
                            {isOwnProfile && (
                              <div className="pointer-events-none absolute inset-0 z-30 hidden items-center justify-center rounded-[2px] bg-black/35 backdrop-blur-[0.5px] text-white opacity-0 transition group-hover/book:flex group-hover/book:opacity-100">
                                <span className="inline-flex items-center gap-1 rounded-full bg-black/70 px-2 py-0.5 text-[0.65cqw] font-semibold tracking-wider uppercase text-amber-200">
                                  <Move className="h-2.5 w-2.5" /> Drag
                                </span>
                              </div>
                            )}
                          </div>

                          {/* Shadow cast on the wood branch plank */}
                          <span
                            className="pointer-events-none -mt-[0.2cqw] block h-[0.4cqw] w-[80%] rounded-[50%] bg-black/40 blur-[2px]"
                            aria-hidden="true"
                          />
                        </article>
                      );
                    })}

                    {/* Empty Slots: Completes the 3-book shelf & serves as drop zones */}
                    {isOwnProfile &&
                      Array.from({ length: emptySlots }).map((_, slotIdx) => (
                        <div
                          key={slotIdx}
                          onDragOver={(e) => handleDragOver(e, slot)}
                          onDrop={(e) => handleDropOnBranch(e, slot)}
                          onClick={() => {
                            sounds.click();
                            setAddingToSlot(slot);
                          }}
                          title={`Drop a book here, or click to add a book to “${title}”`}
                          className={`group/slot flex flex-col items-center justify-center rounded-[3px] border border-dashed transition-all cursor-pointer ${
                            isDragTarget
                              ? 'border-[#B8893B] bg-amber-500/25 scale-105'
                              : 'border-[#e6d0b5]/50 bg-black/20 hover:border-[#f3d08f] hover:bg-black/35'
                          }`}
                          style={{ width: '9.2cqw', aspectRatio: '2/3' }}
                        >
                          <div className="flex h-[2.2cqw] w-[2.2cqw] items-center justify-center rounded-full bg-white/10 text-[#f3d08f] transition group-hover/slot:scale-110 group-hover/slot:bg-white/25">
                            <Plus className="h-[1.2cqw] w-[1.2cqw]" />
                          </div>
                          <span
                            className="mt-[0.3cqw] font-serif text-[#e8cda2]/80 group-hover/slot:text-[#fff6e3]"
                            style={{ fontSize: 'clamp(6.5px, 0.75cqw, 9.5px)' }}
                          >
                            Add / Drop
                          </span>
                        </div>
                      ))}
                  </div>

                  {/* Sturdy Live-Edge Wooden Plank */}
                  <div className="mt-[0.2cqw] h-[0.55cqw] w-[98%] rounded-full bg-gradient-to-r from-[#3e2014]/80 via-[#693922] to-[#3e2014]/80 shadow-[0_2px_6px_rgba(0,0,0,0.65)]" />

                  {/* Clean Non-Overlapping Shelf Nameplate: Mounted Safely Below Plank */}
                  <div className="mt-[0.4cqw] flex items-center justify-center">
                    {isOwnProfile ? (
                      <button
                        type="button"
                        onClick={() => handleOpenEditTitle(slot)}
                        title="Click to rename this shelf (e.g. Fav Thrillers)"
                        className="group/tag inline-flex items-center gap-[0.35cqw] rounded-full border border-[#d8b577]/75 bg-gradient-to-r from-[#2c140a] via-[#3b1c10] to-[#2c140a] px-[1.1cqw] py-[0.22cqw] shadow-[0_2px_8px_rgba(0,0,0,0.45)] transition-all hover:scale-105 hover:border-[#f5d590] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#f5d590]"
                      >
                        <span
                          className="font-serif font-semibold tracking-wide text-[#f7e6c4]"
                          style={{ fontSize: 'clamp(8px, 1.05cqw, 13px)' }}
                        >
                          {title}
                        </span>
                        <Edit2 className="h-[0.8cqw] w-[0.8cqw] text-[#d8b577] opacity-60 transition group-hover/tag:opacity-100" />
                      </button>
                    ) : (
                      <div className="inline-flex items-center rounded-full border border-[#d8b577]/60 bg-[#2c140a]/90 px-[1.1cqw] py-[0.2cqw] shadow-xs">
                        <span
                          className="font-serif font-semibold tracking-wide text-[#f7e6c4]"
                          style={{ fontSize: 'clamp(8px, 1.05cqw, 13px)' }}
                        >
                          {title}
                        </span>
                      </div>
                    )}
                  </div>
                </div>
              );
            })}
          </div>
        </div>
      </div>

      {/* Rename Shelf Modal */}
      {editingSlot !== null && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs">
          <div className="w-full max-w-md rounded-2xl border border-[#e6d5c4] bg-[#FFFDF9] p-6 shadow-2xl dark:border-[#4B3035] dark:bg-[#271519]">
            <div className="flex items-center justify-between border-b border-[#ebdcd0] pb-3 dark:border-[#4B3035]">
              <h4 className="font-serif text-lg font-semibold text-[#3B2418] dark:text-[#f4e9e2]">
                Rename Shelf {editingSlot + 1}
              </h4>
              <button
                type="button"
                onClick={() => setEditingSlot(null)}
                className="rounded-lg p-1 text-[#8b7569] hover:bg-[#f2e6d8] dark:hover:bg-[#3d272c]"
              >
                <X className="h-5 w-5" />
              </button>
            </div>

            <form onSubmit={handleSaveTitle} className="mt-4">
              <label
                htmlFor="shelfTitleInput"
                className="block text-xs font-semibold uppercase tracking-wider text-[#7a5845] dark:text-[#d4b9a8]"
              >
                Shelf Title (e.g. Fav Thrillers, Cozy Mystery)
              </label>
              <input
                id="shelfTitleInput"
                type="text"
                autoFocus
                required
                maxLength={50}
                value={editTitleValue}
                onChange={(e) => setEditTitleValue(e.target.value)}
                placeholder="e.g. Fav Thrillers"
                className="mt-2 w-full rounded-xl border border-[#d8c4b2] bg-white px-4 py-2.5 font-serif text-sm text-[#3B2418] focus:border-[#6D1A2B] focus:ring-2 focus:ring-[#6D1A2B]/20 dark:border-[#4B3035] dark:bg-[#1f1013] dark:text-[#f4e9e2]"
              />

              <div className="mt-5 flex items-center justify-end gap-3">
                <button
                  type="button"
                  onClick={() => setEditingSlot(null)}
                  className="rounded-full px-4 py-2 text-xs font-semibold text-[#8b7569] hover:bg-[#f2e6d8] dark:hover:bg-[#3d272c]"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="rounded-full bg-[#6D1A2B] px-5 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-[#8B2A3D]"
                >
                  Save Shelf Name
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Add Book to Slot Picker Modal */}
      {addingToSlot !== null && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs">
          <div className="w-full max-w-xl max-h-[85vh] flex flex-col rounded-2xl border border-[#e6d5c4] bg-[#FFFDF9] p-6 shadow-2xl dark:border-[#4B3035] dark:bg-[#271519]">
            <div className="flex items-center justify-between border-b border-[#ebdcd0] pb-3 dark:border-[#4B3035]">
              <div>
                <h4 className="font-serif text-lg font-semibold text-[#3B2418] dark:text-[#f4e9e2]">
                  Add Book to “{shelfTitles[addingToSlot] || `Shelf ${addingToSlot + 1}`}”
                </h4>
                <p className="text-xs text-[#8b7569] dark:text-[#c1aaa0]">
                  Select a book from your library to place on this branch (maximum 3 books).
                </p>
              </div>
              <button
                type="button"
                onClick={() => setAddingToSlot(null)}
                className="rounded-lg p-1 text-[#8b7569] hover:bg-[#f2e6d8] dark:hover:bg-[#3d272c]"
              >
                <X className="h-5 w-5" />
              </button>
            </div>

            <div className="mt-4 flex-1 overflow-y-auto space-y-2 pr-1">
              {availableBooks.map((b) => (
                <div
                  key={b.id}
                  className="flex items-center justify-between rounded-xl border border-[#ede3d7] bg-white p-3 hover:border-[#6D1A2B]/40 dark:border-[#4B3035] dark:bg-[#1f1013]"
                >
                  <div className="flex items-center gap-3 min-w-0">
                    <img
                      src={b.cover_url}
                      alt={b.title}
                      className="h-14 w-10 rounded-sm object-cover shadow-xs shrink-0"
                    />
                    <div className="min-w-0">
                      <p className="truncate font-serif text-sm font-semibold text-[#3B2418] dark:text-[#f4e9e2]">
                        {b.title}
                      </p>
                      <p className="truncate text-xs text-[#8b7569] dark:text-[#c1aaa0]">
                        {b.author_name} · {b.first_publish_year || 'Unknown year'}
                      </p>
                    </div>
                  </div>
                  <button
                    type="button"
                    onClick={() => handleAddBookToSlot(b.id, addingToSlot)}
                    className="ml-3 shrink-0 rounded-full bg-[#6D1A2B] px-3.5 py-1.5 text-xs font-semibold text-white shadow-xs hover:bg-[#8B2A3D]"
                  >
                    Place on Shelf
                  </button>
                </div>
              ))}
            </div>

            <div className="mt-4 border-t border-[#ebdcd0] pt-3 flex items-center justify-between dark:border-[#4B3035]">
              <span className="text-xs text-[#8b7569] dark:text-[#c1aaa0]">
                Looking for more books?
              </span>
              <button
                type="button"
                onClick={() => {
                  setAddingToSlot(null);
                  if (onExploreMore) onExploreMore();
                }}
                className="inline-flex items-center gap-1.5 text-xs font-semibold text-[#6D1A2B] hover:underline dark:text-[#e4a8b5]"
              >
                Search OpenLibrary Catalog <ExternalLink className="h-3.5 w-3.5" />
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};
