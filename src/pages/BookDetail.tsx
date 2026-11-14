import React, { useState, useEffect } from 'react';
import { Book, BookPdf, QuoteCard, Review, ShelfStatus } from '../types';
import { StorageService } from '../services/storage';
import { useAuth } from '../context/AuthContext';
import { StarRating } from '../components/StarRating';
import { PdfReaderModal } from '../components/PdfReaderModal';
import { QuoteCardModal } from '../components/QuoteCardModal';
import {
  ArrowLeft,
  BookOpen,
  Calendar,
  FileText,
  Quote,
  Share2,
  Trash2,
  Upload,
} from 'lucide-react';

interface BookDetailProps {
  bookId: string;
  onBack: () => void;
  onViewReaderProfile: (username: string) => void;
}

export const BookDetail: React.FC<BookDetailProps> = ({
  bookId,
  onBack,
  onViewReaderProfile,
}) => {
  const { user } = useAuth();
  const [book, setBook] = useState<Book | undefined>(() => StorageService.getBookById(bookId));
  const [reviews, setReviews] = useState<ReturnType<typeof StorageService.getBookReviews>>([]);
  const [myReview, setMyReview] = useState<Review | undefined>();
  const [myShelfItem, setMyShelfItem] = useState<ReturnType<typeof StorageService.getUserShelf>[0] | undefined>();
  const [ratingInput, setRatingInput] = useState<number>(5);
  const [reviewBody, setReviewBody] = useState<string>('');
  const [isEditingReview, setIsEditingReview] = useState(false);
  const [pdfModalOpen, setPdfModalOpen] = useState(false);
  const [activeQuote, setActiveQuote] = useState<QuoteCard | null>(null);
  const [quoteCards, setQuoteCards] = useState<QuoteCard[]>([]);
  const [bookPdfs, setBookPdfs] = useState<BookPdf[]>([]);
  const [statusMessage, setStatusMessage] = useState<string | null>(null);

  const reloadData = () => {
    const currentBook = StorageService.getBookById(bookId);
    setBook(currentBook);
    const revs = StorageService.getBookReviews(bookId);
    setReviews(revs);
    if (user) {
      const myRev = revs.find((r) => r.user_id === user.id);
      setMyReview(myRev);
      if (myRev) {
        setRatingInput(myRev.rating);
        setReviewBody(myRev.body || '');
      }
      const shelves = StorageService.getUserShelf(user.id);
      setMyShelfItem(shelves.find((s) => s.book_id === bookId));
      setBookPdfs(StorageService.getPdfs(user.id, bookId));
    }
    setQuoteCards(StorageService.getBookQuotes(bookId));
  };

  useEffect(() => {
    reloadData();
  }, [bookId, user]);

  if (!book) {
    return (
      <div className="mx-auto max-w-4xl px-4 py-16 text-center">
        <p className="font-serif text-2xl text-walnut dark:text-[#f4e9e2]">Book not found in library</p>
        <button
          type="button"
          onClick={onBack}
          className="mt-4 rounded-full bg-burgundy px-5 py-2 text-xs font-semibold text-white"
        >
          Return to books
        </button>
      </div>
    );
  }

  const avgRating = reviews.length
    ? reviews.reduce((sum, r) => sum + r.rating, 0) / reviews.length
    : 0;

  const handleShelfChange = (status: ShelfStatus) => {
    if (!user) return;
    StorageService.updateShelfStatus(
      user.id,
      book.id,
      status,
      myShelfItem?.current_page || 0,
      myShelfItem?.total_pages || book.total_pages || 300
    );
    setStatusMessage(`Added to your "${status === 'want_to_read' ? 'Want to read' : status === 'reading' ? 'Reading' : 'Read'}" shelf.`);
    reloadData();
    setTimeout(() => setStatusMessage(null), 3000);
  };

  const handleProgressChange = (currentPage: number, totalPages: number) => {
    if (!user) return;
    StorageService.updateShelfStatus(
      user.id,
      book.id,
      'reading',
      currentPage,
      totalPages
    );
    reloadData();
  };

  const handleSaveReview = (e: React.FormEvent) => {
    e.preventDefault();
    if (!user) return;
    StorageService.saveReview(user.id, book.id, ratingInput, reviewBody);
    setIsEditingReview(false);
    setStatusMessage('Your rating & review have been saved.');
    reloadData();
    setTimeout(() => setStatusMessage(null), 3000);
  };

  const handleDeleteReview = (reviewId: string) => {
    if (window.confirm('Delete your review for this book?')) {
      StorageService.deleteReview(reviewId);
      setStatusMessage('Review deleted.');
      reloadData();
      setTimeout(() => setStatusMessage(null), 3000);
    }
  };

  const handleUploadPdf = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file || !user) return;
    const url = URL.createObjectURL(file);
    StorageService.addPdf(user.id, book.id, file.name, url);
    setStatusMessage(`Uploaded PDF: ${file.name}`);
    reloadData();
    setTimeout(() => setStatusMessage(null), 3000);
  };

  return (
    <div className="mx-auto max-w-5xl px-4 py-8 sm:px-6 sm:py-12 space-y-10">
      {/* Back button */}
      <div>
        <button
          type="button"
          onClick={onBack}
          className="inline-flex items-center gap-1.5 text-xs font-semibold text-burgundy dark:text-gold hover:underline"
        >
          <ArrowLeft className="h-4 w-4" />
          <span>Back to library</span>
        </button>
      </div>

      {statusMessage && (
        <div className="rounded-xl border border-emerald-200 bg-emerald-50 dark:bg-emerald-950/40 dark:border-emerald-900 p-3 text-xs text-emerald-800 dark:text-emerald-200">
          {statusMessage}
        </div>
      )}

      {/* Hero Book Information */}
      <section className="grid gap-8 md:grid-cols-[240px_1fr] items-start">
        {/* Cover & Quick Reader CTA */}
        <div className="flex flex-col items-center">
          <div className="relative aspect-[2/3] w-full max-w-[240px] overflow-hidden rounded-2xl bg-parchment shadow-xl ring-1 ring-warmborder dark:ring-[#4B3035]">
            {book.cover_url ? (
              <img
                src={book.cover_url}
                alt={book.title}
                className="h-full w-full object-cover"
              />
            ) : (
              <div className="flex h-full w-full items-center justify-center p-4 text-center font-serif text-sm text-walnut">
                {book.title}
              </div>
            )}
          </div>

          <button
            type="button"
            onClick={() => setPdfModalOpen(true)}
            className="focus-ring mt-4 flex w-full max-w-[240px] items-center justify-center gap-2 rounded-full bg-burgundy px-4 py-3 text-xs font-semibold text-[#FFF8EF] shadow-md hover:bg-burgundy-hover transition"
          >
            <BookOpen className="h-4 w-4" />
            <span>Open in Book Reader</span>
          </button>
        </div>

        {/* Book Details */}
        <div className="space-y-4">
          <div>
            <h1 className="font-serif text-3xl sm:text-4xl font-semibold text-ink dark:text-[#f4e9e2] leading-tight">
              {book.title}
            </h1>
            <p className="mt-1 font-serif text-lg text-walnut dark:text-[#d4c2b9]">
              by <span className="font-semibold text-burgundy dark:text-gold">{book.author_name}</span>
            </p>
            {book.first_publish_year && (
              <p className="mt-1 flex items-center gap-1.5 text-xs text-tan dark:text-[#c7a491]">
                <Calendar className="h-3.5 w-3.5" />
                <span>First published in {book.first_publish_year}</span>
              </p>
            )}
          </div>

          {/* Aggregate Rating Display */}
          <div className="flex items-center gap-3 border-y border-warmborder/80 dark:border-[#4B3035] py-3">
            <StarRating rating={avgRating} size="md" />
            <span className="text-sm font-semibold text-ink dark:text-[#f4e9e2]">
              {avgRating > 0 ? avgRating.toFixed(1) : 'Not rated yet'}
            </span>
            <span className="text-xs text-tan">
              ({reviews.length} {reviews.length === 1 ? 'review' : 'reviews'})
            </span>
          </div>

          {book.description && (
            <p className="text-sm leading-relaxed text-walnut dark:text-[#d4c2b9]">
              {book.description}
            </p>
          )}

          {/* Reading Shelf & Progress Controls */}
          {user && (
            <div className="rounded-2xl border border-warmborder bg-paper dark:bg-raised p-5 space-y-4">
              <div className="flex flex-wrap items-center justify-between gap-3">
                <span className="text-xs font-semibold uppercase tracking-wider text-tan dark:text-[#c7a491]">
                  My Shelf Placement
                </span>

                <div className="flex gap-2">
                  {(['want_to_read', 'reading', 'read'] as const).map((st) => (
                    <button
                      key={st}
                      type="button"
                      onClick={() => handleShelfChange(st)}
                      className={`rounded-full px-4 py-1.5 text-xs font-semibold transition ${
                        myShelfItem?.status === st
                          ? 'bg-burgundy text-white shadow-xs'
                          : 'border border-warmborder bg-parchment/30 dark:bg-[#201719] dark:border-[#4B3035] text-walnut dark:text-[#d4c2b9] hover:bg-rosetint/50'
                      }`}
                    >
                      {st === 'want_to_read' ? 'Want to read' : st === 'reading' ? 'Currently reading' : 'Read'}
                    </button>
                  ))}
                </div>
              </div>

              {/* Progress Slider if Reading */}
              {myShelfItem?.status === 'reading' && (
                <div className="border-t border-warmborder/70 dark:border-[#4B3035] pt-3">
                  <div className="flex items-center justify-between text-xs text-walnut dark:text-[#d4c2b9] mb-1">
                    <span>Reading Progress</span>
                    <span>
                      Page {myShelfItem.current_page || 0} of {myShelfItem.total_pages || book.total_pages || 300} (
                      {Math.round(
                        ((myShelfItem.current_page || 0) / (myShelfItem.total_pages || book.total_pages || 300)) * 100
                      )}
                      %)
                    </span>
                  </div>
                  <input
                    type="range"
                    min="0"
                    max={myShelfItem.total_pages || book.total_pages || 300}
                    value={myShelfItem.current_page || 0}
                    onChange={(e) =>
                      handleProgressChange(
                        Number(e.target.value),
                        myShelfItem.total_pages || book.total_pages || 300
                      )
                    }
                    className="w-full accent-burgundy"
                  />
                </div>
              )}
            </div>
          )}
        </div>
      </section>

      {/* PDF Files Section */}
      {user && (
        <section className="rounded-2xl border border-warmborder bg-paper p-6 dark:border-[#4B3035] dark:bg-raised space-y-4">
          <div className="flex items-center justify-between">
            <div>
              <h2 className="font-serif text-lg font-semibold text-ink dark:text-[#f4e9e2]">
                Personal PDF Editions
              </h2>
              <p className="text-xs text-tan dark:text-[#c7a491]">
                Upload your private book PDF to read with in-browser highlights and marginalia.
              </p>
            </div>

            <label className="focus-ring cursor-pointer inline-flex items-center gap-1.5 rounded-full border border-warmborder bg-parchment/50 dark:bg-[#201719] px-4 py-2 text-xs font-semibold text-walnut dark:text-[#d4c2b9] hover:bg-rosetint/50">
              <Upload className="h-3.5 w-3.5" />
              <span>Attach PDF</span>
              <input
                type="file"
                accept="application/pdf"
                className="hidden"
                onChange={handleUploadPdf}
              />
            </label>
          </div>

          <div className="grid gap-3 sm:grid-cols-2">
            {bookPdfs.map((pdf) => (
              <div
                key={pdf.id}
                className="flex items-center justify-between rounded-xl border border-warmborder/80 bg-white dark:bg-[#201719] p-3 shadow-2xs"
              >
                <div className="flex items-center gap-2.5 min-w-0">
                  <FileText className="h-5 w-5 text-burgundy shrink-0" />
                  <span className="font-serif text-xs font-semibold text-ink dark:text-[#f4e9e2] truncate">
                    {pdf.original_name}
                  </span>
                </div>

                <div className="flex items-center gap-2">
                  <button
                    type="button"
                    onClick={() => setPdfModalOpen(true)}
                    className="rounded-full bg-burgundy/10 px-3 py-1 text-xs font-semibold text-burgundy dark:text-gold hover:bg-burgundy/20"
                  >
                    Read
                  </button>
                  <button
                    type="button"
                    onClick={() => {
                      StorageService.deletePdf(pdf.id);
                      reloadData();
                    }}
                    className="p-1 text-tan hover:text-red-700"
                    title="Delete PDF"
                  >
                    <Trash2 className="h-3.5 w-3.5" />
                  </button>
                </div>
              </div>
            ))}
          </div>
        </section>
      )}

      {/* Saved Quotes from Reader Highlights */}
      {quoteCards.length > 0 && (
        <section className="space-y-4">
          <div className="flex items-center gap-2">
            <Quote className="h-4 w-4 text-gold" />
            <h2 className="font-serif text-xl font-semibold text-ink dark:text-[#f4e9e2]">
              Quotes &amp; Marginalia ({quoteCards.length})
            </h2>
          </div>

          <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            {quoteCards.map((quote) => (
              <div
                key={quote.id}
                onClick={() => setActiveQuote(quote)}
                className="bookplate cursor-pointer rounded-2xl p-5 shadow-xs hover:shadow-md transition flex flex-col justify-between"
              >
                <blockquote className="font-serif text-sm italic text-walnut dark:text-[#e3d3ca] line-clamp-4 leading-relaxed">
                  “{quote.quoteText}”
                </blockquote>
                <div className="mt-4 border-t border-warmborder/60 pt-2 flex items-center justify-between text-xs text-tan">
                  <span className="font-hand text-base text-burgundy dark:text-gold">
                    — {quote.userName}
                  </span>
                  <span className="flex items-center gap-1 text-[11px]">
                    <Share2 className="h-3 w-3" />
                    <span>View Card</span>
                  </span>
                </div>
              </div>
            ))}
          </div>
        </section>
      )}

      {/* Review Box for Current User */}
      {user && (
        <section className="rounded-3xl border border-warmborder bg-paper p-6 sm:p-8 dark:border-[#4B3035] dark:bg-raised shadow-xs space-y-4">
          <div className="flex items-center justify-between border-b border-warmborder/80 pb-4 dark:border-[#4B3035]">
            <h2 className="font-serif text-xl font-semibold text-ink dark:text-[#f4e9e2]">
              {myReview ? 'Your Reading Review' : 'Leave a Note in the Margin'}
            </h2>
            {myReview && !isEditingReview && (
              <div className="flex items-center gap-3">
                <button
                  type="button"
                  onClick={() => setIsEditingReview(true)}
                  className="text-xs font-semibold text-burgundy dark:text-gold hover:underline"
                >
                  Edit Review
                </button>
                <button
                  type="button"
                  onClick={() => handleDeleteReview(myReview.id)}
                  className="text-xs text-red-700 dark:text-red-400 hover:underline"
                >
                  Delete
                </button>
              </div>
            )}
          </div>

          {myReview && !isEditingReview ? (
            <div className="space-y-3">
              <StarRating rating={myReview.rating} size="md" showLabel />
              {myReview.body && (
                <p className="font-serif text-sm text-walnut dark:text-[#d4c2b9] leading-relaxed">
                  {myReview.body}
                </p>
              )}
            </div>
          ) : (
            <form onSubmit={handleSaveReview} className="space-y-4">
              <div>
                <label className="block text-xs font-semibold text-tan dark:text-[#c7a491] uppercase tracking-wider mb-2">
                  Your Star Rating (Click stars or half-stars)
                </label>
                <StarRating
                  rating={ratingInput}
                  size="lg"
                  interactive
                  onRate={(val) => setRatingInput(val)}
                  showLabel
                />
              </div>

              <div>
                <label
                  htmlFor="review-body"
                  className="block text-xs font-semibold text-tan dark:text-[#c7a491] uppercase tracking-wider mb-2"
                >
                  Your thoughts, impressions, or quotes
                </label>
                <textarea
                  id="review-body"
                  rows={4}
                  value={reviewBody}
                  onChange={(e) => setReviewBody(e.target.value)}
                  placeholder="What made this story stay with you? Write freely..."
                  className="w-full rounded-2xl border border-warmborder bg-parchment/30 p-3 text-sm text-ink placeholder-tan dark:bg-[#201719] dark:border-[#4B3035] dark:text-[#f4e9e2] focus:border-burgundy focus:outline-hidden"
                />
              </div>

              <div className="flex items-center justify-end gap-3">
                {isEditingReview && (
                  <button
                    type="button"
                    onClick={() => setIsEditingReview(false)}
                    className="rounded-full px-4 py-2 text-xs font-semibold text-tan hover:text-ink"
                  >
                    Cancel
                  </button>
                )}
                <button
                  type="submit"
                  className="rounded-full bg-burgundy px-6 py-2.5 text-xs font-semibold text-[#FFF8EF] shadow-xs hover:bg-burgundy-hover transition"
                >
                  Save Review
                </button>
              </div>
            </form>
          )}
        </section>
      )}

      {/* Community Reviews List */}
      <section className="space-y-6">
        <h2 className="font-serif text-2xl font-semibold text-ink dark:text-[#f4e9e2]">
          Reader Reviews ({reviews.length})
        </h2>

        {reviews.length === 0 ? (
          <div className="rounded-2xl border border-dashed border-warmborder bg-paper/50 p-8 text-center text-xs text-tan dark:border-[#4B3035]">
            No reviews yet. Be the first reader to leave a note on this story!
          </div>
        ) : (
          <div className="space-y-4">
            {reviews.map((rev) => (
              <article
                key={rev.id}
                className="rounded-2xl border border-warmborder bg-paper p-5 shadow-xs dark:border-[#4B3035] dark:bg-raised"
              >
                <div className="flex items-center justify-between gap-3">
                  <div className="flex items-center gap-3">
                    <button
                      type="button"
                      onClick={() => rev.user && onViewReaderProfile(rev.user.username)}
                      className="h-9 w-9 rounded-full bg-burgundy text-[#FFF8EF] font-serif font-bold text-xs flex items-center justify-center uppercase hover:opacity-90"
                    >
                      {rev.user?.name?.charAt(0) || 'R'}
                    </button>
                    <div>
                      <button
                        type="button"
                        onClick={() => rev.user && onViewReaderProfile(rev.user.username)}
                        className="font-serif text-sm font-semibold text-ink dark:text-[#f4e9e2] hover:text-burgundy"
                      >
                        {rev.user?.name || 'Reader'}
                      </button>
                      <p className="text-[11px] text-tan">
                        {new Date(rev.created_at).toLocaleDateString()}
                      </p>
                    </div>
                  </div>

                  <StarRating rating={rev.rating} size="sm" />
                </div>

                {rev.body && (
                  <p className="mt-3 font-serif text-sm leading-relaxed text-walnut dark:text-[#d4c2b9]">
                    {rev.body}
                  </p>
                )}
              </article>
            ))}
          </div>
        )}
      </section>

      {/* PDF Reader Modal */}
      <PdfReaderModal
        book={book}
        isOpen={pdfModalOpen}
        onClose={() => {
          setPdfModalOpen(false);
          reloadData();
        }}
        onHighlightCreated={reloadData}
      />

      {/* Quote Card Share Modal */}
      <QuoteCardModal
        quote={activeQuote}
        isOpen={Boolean(activeQuote)}
        onClose={() => setActiveQuote(null)}
        onQuoteUpdated={reloadData}
      />
    </div>
  );
};
