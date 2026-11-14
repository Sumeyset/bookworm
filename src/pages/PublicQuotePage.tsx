import React, { useState, useEffect } from 'react';
import { StorageService } from '../services/storage';
import { QuoteCard } from '../types';
import { Logo } from '../components/Logo';
import { ArrowLeft, BookOpen, Share2 } from 'lucide-react';

interface PublicQuotePageProps {
  shareToken: string;
  onExploreBooks: () => void;
  onHome: () => void;
}

export const PublicQuotePage: React.FC<PublicQuotePageProps> = ({
  shareToken,
  onExploreBooks,
  onHome,
}) => {
  const [quote, setQuote] = useState<QuoteCard | undefined>(() =>
    StorageService.getQuoteByShareToken(shareToken)
  );

  useEffect(() => {
    const q = StorageService.getQuoteByShareToken(shareToken);
    setQuote(q);
  }, [shareToken]);

  if (!quote) {
    return (
      <div className="mx-auto max-w-xl px-4 py-20 text-center">
        <p className="font-serif text-2xl text-walnut dark:text-[#f4e9e2]">Quote card not found</p>
        <button
          type="button"
          onClick={onHome}
          className="mt-4 rounded-full bg-burgundy px-5 py-2 text-xs font-semibold text-white"
        >
          Return to Bookworm
        </button>
      </div>
    );
  }

  return (
    <div className="paper-grain min-h-[80vh] flex flex-col items-center justify-center px-4 py-12">
      <div className="max-w-xl w-full">
        {/* Back Link */}
        <button
          type="button"
          onClick={onHome}
          className="mb-6 inline-flex items-center gap-1.5 text-xs font-semibold text-burgundy dark:text-gold hover:underline"
        >
          <ArrowLeft className="h-4 w-4" />
          <span>Home to Bookworm</span>
        </button>

        {/* The Card */}
        <article className="bookplate relative overflow-hidden rounded-3xl p-8 sm:p-12 text-center shadow-xl">
          <span
            className="absolute left-0 top-10 bottom-10 w-1.5 bg-gradient-to-b from-transparent via-gold to-transparent"
            aria-hidden="true"
          />

          <span className="block font-serif text-7xl leading-none text-burgundy dark:text-gold opacity-60">
            “
          </span>

          <blockquote className="-mt-4 font-serif text-2xl sm:text-3xl italic leading-relaxed text-ink dark:text-[#F4E9E2]">
            {quote.quoteText}
          </blockquote>

          <div className="mx-auto mt-8 h-px w-20 bg-gold/70" />

          <p className="mt-6 font-serif text-lg font-semibold text-walnut dark:text-[#d4c2b9]">
            {quote.bookTitle}
          </p>
          {quote.bookAuthor && (
            <p className="text-sm text-tan dark:text-[#c7a491]">by {quote.bookAuthor}</p>
          )}

          <p className="mt-6 font-hand text-2xl text-burgundy dark:text-gold">
            Saved by {quote.userName || 'a Bookworm reader'}
          </p>

          <div className="mt-8 pt-6 border-t border-warmborder dark:border-[#4B3035] flex items-center justify-between text-xs text-tan">
            <span>Bookworm Marginalia</span>
            <button
              type="button"
              onClick={onExploreBooks}
              className="font-semibold text-burgundy dark:text-gold hover:underline"
            >
              Explore this book &rarr;
            </button>
          </div>
        </article>
      </div>
    </div>
  );
};
