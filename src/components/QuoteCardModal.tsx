import React, { useState } from 'react';
import { QuoteCard } from '../types';
import { StorageService } from '../services/storage';
import { Check, Copy, Share2, Trash2, X } from 'lucide-react';

interface QuoteCardModalProps {
  quote: QuoteCard | null;
  isOpen: boolean;
  onClose: () => void;
  onQuoteUpdated?: () => void;
}

export const QuoteCardModal: React.FC<QuoteCardModalProps> = ({
  quote,
  isOpen,
  onClose,
  onQuoteUpdated,
}) => {
  const [copied, setCopied] = useState(false);

  if (!isOpen || !quote) return null;

  const shareUrl = `${window.location.origin}#quote=${quote.share_token}`;

  const handleCopyLink = () => {
    navigator.clipboard.writeText(shareUrl);
    setCopied(true);
    setTimeout(() => setCopied(false), 2500);
  };

  const handleToggleShare = () => {
    StorageService.toggleShareQuote(quote.id);
    if (onQuoteUpdated) onQuoteUpdated();
  };

  const handleDelete = () => {
    if (window.confirm('Remove this quote from your collection?')) {
      StorageService.deleteQuote(quote.id);
      if (onQuoteUpdated) onQuoteUpdated();
      onClose();
    }
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
      <div className="relative w-full max-w-lg rounded-3xl border border-warmborder bg-paper p-6 sm:p-8 shadow-2xl dark:border-[#4B3035] dark:bg-raised">
        <button
          type="button"
          onClick={onClose}
          className="absolute right-4 top-4 rounded-full p-1 text-tan hover:bg-parchment dark:hover:bg-[#382329]"
          aria-label="Close"
        >
          <X className="h-5 w-5" />
        </button>

        <p className="text-xs font-semibold uppercase tracking-widest text-tan dark:text-[#c7a491]">
          Reading Journal Card
        </p>

        {/* The Bookplate Quote Card */}
        <article className="bookplate relative overflow-hidden rounded-2xl p-6 sm:p-8 mt-4 text-center">
          <span
            className="absolute left-0 top-6 bottom-6 w-1 bg-gradient-to-b from-transparent via-gold/80 to-transparent"
            aria-hidden="true"
          />

          <span className="block font-serif text-5xl leading-none text-burgundy dark:text-[#A3324A] opacity-60">
            “
          </span>

          <blockquote className="-mt-3 font-serif text-xl sm:text-2xl italic leading-relaxed text-ink dark:text-[#F4E9E2]">
            {quote.quoteText}
          </blockquote>

          <div className="mx-auto mt-6 h-px w-14 bg-gold/70" />

          <p className="mt-4 font-serif text-sm font-semibold text-walnut dark:text-[#d4c2b9]">
            {quote.bookTitle}
          </p>
          {quote.bookAuthor && (
            <p className="text-xs text-tan dark:text-[#c7a491]">by {quote.bookAuthor}</p>
          )}

          <p className="mt-4 font-hand text-lg text-burgundy dark:text-gold">
            Saved by {quote.userName || 'Reader'}
          </p>
        </article>

        {/* Sharing Controls */}
        <div className="mt-6 flex flex-wrap items-center justify-between gap-3 border-t border-warmborder/70 dark:border-[#4B3035] pt-4">
          <div className="flex items-center gap-2">
            <button
              type="button"
              onClick={handleToggleShare}
              className={`inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-semibold transition ${
                quote.shared_at
                  ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300'
                  : 'bg-parchment text-walnut dark:bg-[#342427] dark:text-[#d4c2b9]'
              }`}
            >
              <Share2 className="h-3.5 w-3.5" />
              <span>{quote.shared_at ? 'Publicly shareable' : 'Private'}</span>
            </button>

            {quote.shared_at && (
              <button
                type="button"
                onClick={handleCopyLink}
                className="inline-flex items-center gap-1.5 rounded-full border border-warmborder px-3 py-1.5 text-xs font-semibold text-burgundy dark:text-gold hover:bg-rosetint/50"
              >
                {copied ? <Check className="h-3.5 w-3.5 text-emerald-600" /> : <Copy className="h-3.5 w-3.5" />}
                <span>{copied ? 'Link Copied!' : 'Copy Link'}</span>
              </button>
            )}
          </div>

          <button
            type="button"
            onClick={handleDelete}
            className="inline-flex items-center gap-1 text-xs text-red-700 dark:text-red-400 hover:underline"
          >
            <Trash2 className="h-3.5 w-3.5" />
            <span>Remove</span>
          </button>
        </div>
      </div>
    </div>
  );
};
