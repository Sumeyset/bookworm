import React, { useState, useEffect, useRef } from 'react';
import { Book, BookPdf, BookPdfHighlight, HighlightRectangle } from '../types';
import { StorageService } from '../services/storage';
import { useAuth } from '../context/AuthContext';
import {
  BookOpen,
  ChevronLeft,
  ChevronRight,
  Highlighter,
  Quote,
  Trash2,
  X,
  ZoomIn,
  ZoomOut,
  Upload,
  CheckCircle,
} from 'lucide-react';

interface PdfReaderModalProps {
  book: Book;
  isOpen: boolean;
  onClose: () => void;
  onHighlightCreated?: () => void;
}

// Built-in classic literature pages for reading when a custom PDF is not uploaded
const SAMPLE_BOOK_PAGES: Record<string, string[]> = {
  default: [
    `Chapter 1: The Opening Solitude\n\nIt is a truth universally acknowledged, that a single man in possession of a good fortune, must be in want of a wife. However little known the feelings or views of such a man may be on his first entering a neighbourhood, this truth is so well fixed in the minds of the surrounding families, that he is considered as the rightful property of some one or other of their daughters.\n\n"My dear Mr. Bennet," said his lady to him one day, "have you heard that Netherfield Park is let at last?" Mr. Bennet replied that he had not.\n\n"But it is," returned she; "for Mrs. Long has just been here, and she told me all about it."`,
    `Chapter 2: Letters and Conversations\n\nMr. Bennet was among the earliest of those who waited on Mr. Bingley. He had always intended to visit him, though to the last always assuring his wife that he should not go; and till the evening after the visit was paid she had no knowledge of it.\n\n"I declare after all there is no enjoyment like reading! How much sooner one tires of any thing than of a book! When I have a house of my own, I shall be miserable if I have not an excellent library."\n\nMiss Bingley turned her eyes again to her book and could not attend to anything else for several minutes.`,
    `Chapter 3: The Gathering at the Assembly\n\nNot all that Mrs. Bennet, however, with the assistance of her five daughters, could ask on the subject, was sufficient to draw from her husband any satisfactory description of Mr. Bingley. They attacked him in various ways—with barefaced questions, ingenious suppositions, and distant surmises; but he eluded the skill of them all.\n\nMr. Darcy danced only once with Mrs. Hurst and once with Miss Bingley, declined being introduced to any other lady, and spent the rest of the evening in walking about the room, speaking occasionally to one of his own party.`,
    `Chapter 4: A Quiet Sisterhood\n\nWhen Jane and Elizabeth were alone, the former, who had been cautious in her praise of Mr. Bingley before, expressed to her sister how very much she admired him.\n\n"He is just what a young man ought to be," said she, "sensible, good-humoured, lively; and I never saw such happy manners!—so much ease, with such perfect good breeding!"\n\n"He is also handsome," replied Elizabeth, "which a young man ought likewise to be, if he possibly can. His character is thereby complete."`,
  ],
};

export const PdfReaderModal: React.FC<PdfReaderModalProps> = ({
  book,
  isOpen,
  onClose,
  onHighlightCreated,
}) => {
  const { user } = useAuth();
  const [currentPage, setCurrentPage] = useState(1);
  const [totalPages, setTotalPages] = useState(4);
  const [zoom, setZoom] = useState(100);
  const [highlights, setHighlights] = useState<BookPdfHighlight[]>([]);
  const [selectedText, setSelectedText] = useState('');
  const [activePdf, setActivePdf] = useState<BookPdf | null>(null);
  const [statusMessage, setStatusMessage] = useState('Select text on the page to highlight.');
  const [customPages, setCustomPages] = useState<string[]>(SAMPLE_BOOK_PAGES.default);
  const textContainerRef = useRef<HTMLDivElement>(null);
  const fileInputRef = useRef<HTMLInputElement>(null);

  // Load existing PDFs and highlights
  useEffect(() => {
    if (!isOpen || !user) return;

    const pdfs = StorageService.getPdfs(user.id, book.id);
    let chosenPdf: BookPdf;
    if (pdfs.length > 0) {
      chosenPdf = pdfs[0];
    } else {
      // Create a default PDF record for reading this book
      chosenPdf = StorageService.addPdf(
        user.id,
        book.id,
        `${book.title.replace(/\s+/g, '_')}_Reader.pdf`,
        '#builtin'
      );
    }
    setActivePdf(chosenPdf);

    const savedHighlights = StorageService.getHighlights(chosenPdf.id);
    setHighlights(savedHighlights);

    // Load user's shelf item to resume page
    const shelves = StorageService.getUserShelf(user.id);
    const shelfItem = shelves.find((s) => s.book_id === book.id);
    if (shelfItem?.current_page) {
      setCurrentPage(Math.min(shelfItem.current_page, 4));
    }
  }, [isOpen, book.id, user]);

  if (!isOpen) return null;

  const currentBookPages = customPages;

  const handleSelection = () => {
    const sel = window.getSelection();
    if (!sel || sel.isCollapsed) {
      setSelectedText('');
      return;
    }
    const text = sel.toString().trim();
    if (text.length > 0 && textContainerRef.current?.contains(sel.anchorNode)) {
      setSelectedText(text);
    }
  };

  const handleCreateHighlight = () => {
    if (!selectedText || !user || !activePdf) return;

    const newHl = StorageService.addHighlight(
      user.id,
      activePdf.id,
      currentPage,
      selectedText,
      [{ x: 0.1, y: 0.1, width: 0.8, height: 0.1 }]
    );
    setHighlights([...highlights, newHl]);
    setSelectedText('');
    window.getSelection()?.removeAllRanges();
    setStatusMessage('Highlight saved to your private library.');
    if (onHighlightCreated) onHighlightCreated();
  };

  const handleRemoveHighlight = (hlId: string) => {
    StorageService.deleteHighlight(hlId);
    setHighlights(highlights.filter((h) => h.id !== hlId));
    setStatusMessage('Highlight removed.');
  };

  const handleSaveAsQuote = (hl: BookPdfHighlight) => {
    if (!user) return;
    StorageService.createQuoteCard(user.id, hl.id);
    setStatusMessage('Quote card created! View it on the book page or your shelf.');
  };

  const handlePageChange = (newPage: number) => {
    if (newPage < 1 || newPage > totalPages) return;
    setCurrentPage(newPage);
    if (user) {
      StorageService.updateShelfStatus(
        user.id,
        book.id,
        'reading',
        newPage,
        totalPages
      );
    }
  };

  const handleFileUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file || !user) return;

    const originalName = file.name;
    const fileUrl = URL.createObjectURL(file);
    const newPdf = StorageService.addPdf(user.id, book.id, originalName, fileUrl);
    setActivePdf(newPdf);
    setStatusMessage(`Uploaded "${originalName}". Reader ready.`);

    // Read simple text if available or setup pages
    const reader = new FileReader();
    reader.onload = () => {
      // In browser preview, split file content or sample into pages
      setCustomPages([
        `Document: ${originalName}\n\n[Uploaded into Bookworm private library]\n\nPage 1 excerpt ready for reading and marginalia annotations.`,
        `Page 2\n\nContinue noting observations and highlighting key thoughts.`,
      ]);
      setTotalPages(2);
      setCurrentPage(1);
    };
    reader.readAsArrayBuffer(file);
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-2 sm:p-4 bg-black/70 backdrop-blur-xs">
      <div className="relative flex h-[92vh] w-full max-w-6xl flex-col rounded-3xl border border-warmborder bg-paper shadow-2xl dark:border-[#4B3035] dark:bg-raised overflow-hidden">
        {/* Top Header */}
        <div className="flex flex-wrap items-center justify-between gap-3 border-b border-warmborder bg-parchment/60 px-5 py-3 dark:border-[#4B3035] dark:bg-[#201719]">
          <div className="flex items-center gap-3 min-w-0">
            <BookOpen className="h-5 w-5 text-burgundy dark:text-gold shrink-0" />
            <div className="min-w-0">
              <p className="text-xs font-semibold uppercase tracking-wider text-tan dark:text-[#c7a491] truncate">
                {book.title}
              </p>
              <h2 className="font-serif text-sm sm:text-base font-semibold text-ink dark:text-[#f4e9e2] truncate">
                {activePdf?.original_name || 'Bookworm Reader'}
              </h2>
            </div>
          </div>

          <div className="flex items-center gap-2">
            <input
              type="file"
              ref={fileInputRef}
              accept="application/pdf"
              className="hidden"
              onChange={handleFileUpload}
            />
            <button
              type="button"
              onClick={() => fileInputRef.current?.click()}
              className="inline-flex items-center gap-1.5 rounded-full border border-warmborder px-3 py-1.5 text-xs font-medium text-walnut dark:text-[#d4c2b9] hover:bg-white dark:hover:bg-[#342427]"
              title="Upload your personal PDF copy"
            >
              <Upload className="h-3.5 w-3.5" />
              <span className="hidden sm:inline">Upload PDF</span>
            </button>

            <button
              type="button"
              onClick={onClose}
              className="rounded-full p-1.5 text-tan hover:bg-parchment dark:hover:bg-[#382329]"
              aria-label="Close reader"
            >
              <X className="h-5 w-5" />
            </button>
          </div>
        </div>

        {/* Toolbar Controls */}
        <div className="flex flex-wrap items-center justify-between gap-2 border-b border-warmborder/80 bg-paper px-5 py-2.5 dark:border-[#4B3035] dark:bg-raised">
          <div className="flex items-center gap-1.5 sm:gap-2">
            <button
              type="button"
              disabled={currentPage <= 1}
              onClick={() => handlePageChange(currentPage - 1)}
              className="inline-flex items-center gap-1 rounded-lg border border-warmborder px-3 py-1.5 text-xs font-semibold text-burgundy dark:text-[#f4e9e2] disabled:opacity-40"
            >
              <ChevronLeft className="h-4 w-4" />
              <span className="hidden sm:inline">Previous</span>
            </button>

            <span className="px-2 text-xs font-medium text-walnut dark:text-[#d4c2b9]">
              Page <span className="font-bold text-ink dark:text-white">{currentPage}</span> of{' '}
              <span>{totalPages}</span>
            </span>

            <button
              type="button"
              disabled={currentPage >= totalPages}
              onClick={() => handlePageChange(currentPage + 1)}
              className="inline-flex items-center gap-1 rounded-lg border border-warmborder px-3 py-1.5 text-xs font-semibold text-burgundy dark:text-[#f4e9e2] disabled:opacity-40"
            >
              <span className="hidden sm:inline">Next</span>
              <ChevronRight className="h-4 w-4" />
            </button>
          </div>

          <div className="flex items-center gap-2">
            <button
              type="button"
              onClick={() => setZoom(Math.max(80, zoom - 10))}
              className="rounded-lg border border-warmborder p-1.5 text-walnut dark:border-[#4B3035] dark:text-[#f4e9e2]"
              title="Zoom out"
            >
              <ZoomOut className="h-3.5 w-3.5" />
            </button>
            <span className="text-xs text-tan w-10 text-center">{zoom}%</span>
            <button
              type="button"
              onClick={() => setZoom(Math.min(150, zoom + 10))}
              className="rounded-lg border border-warmborder p-1.5 text-walnut dark:border-[#4B3035] dark:text-[#f4e9e2]"
              title="Zoom in"
            >
              <ZoomIn className="h-3.5 w-3.5" />
            </button>

            <button
              type="button"
              disabled={!selectedText}
              onClick={handleCreateHighlight}
              className="ml-2 inline-flex items-center gap-1.5 rounded-full bg-burgundy px-4 py-1.5 text-xs font-semibold text-[#FFF8EF] shadow-xs disabled:opacity-40 transition hover:bg-burgundy-hover"
            >
              <Highlighter className="h-3.5 w-3.5" />
              <span>Highlight selection</span>
            </button>
          </div>
        </div>

        {/* Status prompt */}
        <div className="bg-[#fcf8f3] dark:bg-[#201518] px-5 py-1 text-xs text-tan dark:text-[#c7a491] border-b border-warmborder/40">
          {statusMessage}
        </div>

        {/* Reader Body: Page Viewport + Saved Highlights Sidebar */}
        <div className="flex-1 grid grid-cols-1 lg:grid-cols-[1fr_300px] overflow-hidden">
          {/* Main Book Page Viewport */}
          <div
            className="overflow-auto p-4 sm:p-8 bg-[#EFE3D3]/60 dark:bg-[#1E1416] flex justify-center"
            onMouseUp={handleSelection}
            onKeyUp={handleSelection}
          >
            <div
              ref={textContainerRef}
              className="bookplate max-w-2xl w-full min-h-[500px] rounded-xl p-8 sm:p-12 shadow-lg leading-relaxed select-text transition-all duration-150"
              style={{ fontSize: `${(zoom / 100) * 1.05}rem` }}
            >
              <div className="border-b border-warmborder/80 pb-4 mb-6 flex justify-between items-center text-xs text-tan">
                <span className="font-serif italic">{book.title}</span>
                <span>Page {currentPage}</span>
              </div>

              {/* Render Page Content with Highlight markers */}
              <div className="font-serif text-ink dark:text-[#f4e9e2] whitespace-pre-wrap leading-relaxed">
                {currentBookPages[currentPage - 1] || 'No further pages.'}
              </div>

              {/* Active page highlights list summary */}
              {highlights.filter((h) => h.page_number === currentPage).length > 0 && (
                <div className="mt-8 border-t border-gold/40 pt-4">
                  <p className="text-[11px] font-semibold uppercase tracking-wider text-tan">
                    Highlights on this page:
                  </p>
                  <div className="mt-2 space-y-1.5">
                    {highlights
                      .filter((h) => h.page_number === currentPage)
                      .map((h) => (
                        <div
                          key={h.id}
                          className="rounded-md bg-yellow-100/70 dark:bg-yellow-950/40 p-2 text-xs italic text-walnut dark:text-[#f0d8c1] border-l-2 border-gold"
                        >
                          “{h.text}”
                        </div>
                      ))}
                  </div>
                </div>
              )}
            </div>
          </div>

          {/* Highlights Sidebar */}
          <div className="border-t lg:border-t-0 lg:border-l border-warmborder bg-paper p-4 dark:border-[#4B3035] dark:bg-raised overflow-y-auto">
            <h3 className="font-serif text-base font-semibold text-ink dark:text-[#f4e9e2]">
              Saved Highlights ({highlights.length})
            </h3>
            <p className="mt-1 text-xs text-tan dark:text-[#c7a491]">
              Select any passage in the text to bookmark it or save it as a quote card.
            </p>

            <div className="mt-4 space-y-3">
              {highlights.length === 0 ? (
                <div className="rounded-xl border border-dashed border-[#d9c6b6] dark:border-[#4B3035] p-5 text-center text-xs text-tan">
                  No highlights saved yet. Highlight any line to keep it close.
                </div>
              ) : (
                highlights.map((hl) => (
                  <div
                    key={hl.id}
                    className="rounded-xl border border-warmborder bg-white/70 p-3 shadow-2xs dark:border-[#4B3035] dark:bg-[#201719]"
                  >
                    <p className="font-serif text-xs italic text-walnut dark:text-[#e3d3ca] line-clamp-3">
                      “{hl.text}”
                    </p>
                    <div className="mt-2.5 flex items-center justify-between gap-1 text-[11px] text-tan border-t border-warmborder/60 pt-2">
                      <button
                        type="button"
                        onClick={() => handlePageChange(hl.page_number)}
                        className="text-burgundy dark:text-[#e4b3a5] font-semibold hover:underline"
                      >
                        Go to page {hl.page_number}
                      </button>

                      <div className="flex items-center gap-1.5">
                        <button
                          type="button"
                          onClick={() => handleSaveAsQuote(hl)}
                          className="rounded-md p-1 text-gold hover:bg-parchment"
                          title="Create shareable quote card"
                        >
                          <Quote className="h-3.5 w-3.5" />
                        </button>
                        <button
                          type="button"
                          onClick={() => handleRemoveHighlight(hl.id)}
                          className="rounded-md p-1 text-red-600 hover:bg-red-50 dark:hover:bg-red-950/30"
                          title="Remove highlight"
                        >
                          <Trash2 className="h-3.5 w-3.5" />
                        </button>
                      </div>
                    </div>
                  </div>
                ))
              )}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};
