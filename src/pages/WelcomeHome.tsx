import React, { useState, useEffect } from 'react';
import { useAuth } from '../context/AuthContext';
import { StorageService } from '../services/storage';
import { DailyQuote } from '../types';
import { BookOpen, Sparkles, ArrowRight, CheckCircle2, Bookmark, Compass } from 'lucide-react';

interface WelcomeHomeProps {
  onExploreBooks: () => void;
  onOpenMyShelf: () => void;
  onOpenOnboarding: () => void;
  onSelectBook: (bookId: string) => void;
}

const FALLBACK_QUOTES: DailyQuote[] = [
  {
    text: 'A room without books is like a body without a soul.',
    author: 'Marcus Tullius Cicero',
  },
  {
    text: 'I declare after all there is no enjoyment like reading! How much sooner one tires of any thing than of a book!',
    author: 'Jane Austen, Pride and Prejudice',
  },
  {
    text: 'There is no friend as loyal as a book.',
    author: 'Ernest Hemingway',
  },
  {
    text: 'Books are a uniquely portable magic.',
    author: 'Stephen King',
  },
  {
    text: 'We read to know that we are not alone.',
    author: 'C. S. Lewis',
  },
];

export const WelcomeHome: React.FC<WelcomeHomeProps> = ({
  onExploreBooks,
  onOpenMyShelf,
  onOpenOnboarding,
  onSelectBook,
}) => {
  const { user } = useAuth();
  const [quote, setQuote] = useState<DailyQuote>(FALLBACK_QUOTES[1]);
  const [isLoadingQuote, setIsLoadingQuote] = useState(false);

  // Fetch daily quote or pick curated
  useEffect(() => {
    let isMounted = true;
    async function fetchQuote() {
      setIsLoadingQuote(true);
      try {
        // Try fetching quote, with graceful fallback
        const res = await fetch('https://zenquotes.io/api/today').catch(() => null);
        if (res && res.ok) {
          const data = await res.json();
          if (data && data[0]?.q && data[0]?.a && isMounted) {
            setQuote({ text: data[0].q, author: data[0].a });
            return;
          }
        }
      } catch (err) {
        // fallback
      } finally {
        if (isMounted) setIsLoadingQuote(false);
      }
      // Day-based fallback for consistent quote of the day
      const dayIndex = new Date().getDate() % FALLBACK_QUOTES.length;
      if (isMounted) setQuote(FALLBACK_QUOTES[dayIndex]);
    }

    fetchQuote();
    return () => {
      isMounted = false;
    };
  }, []);

  const now = new Date();
  const currentYear = now.getFullYear();
  const todayFormatted = now.toLocaleDateString('en-US', {
    weekday: 'long',
    month: 'long',
    day: 'numeric',
  });

  const userShelf = user ? StorageService.getUserShelf(user.id) : [];
  const booksReadThisYear = userShelf.filter(
    (item) => item.status === 'read' && item.read_at && new Date(item.read_at).getFullYear() === currentYear
  ).length;
  const currentlyReading = userShelf.filter((item) => item.status === 'reading');
  const yearlyGoal = user?.yearly_reading_goal || 12;
  const goalPercent = Math.min(100, Math.round((booksReadThisYear / yearlyGoal) * 100));

  return (
    <div className="px-4 py-8 sm:px-6 sm:py-12 max-w-5xl mx-auto space-y-8">
      {/* Top Welcome Header */}
      <div className="flex flex-wrap items-end justify-between gap-4 border-b border-warmborder/80 dark:border-[#4B3035] pb-6">
        <div>
          <p className="text-xs font-semibold uppercase tracking-[0.18em] text-tan dark:text-[#c7a491]">
            A little thought for today
          </p>
          <h1 className="mt-1 font-serif text-3xl font-semibold text-ink dark:text-[#F4E9E2]">
            Welcome, {user?.name || 'Reader'}.
          </h1>
        </div>

        <button
          type="button"
          onClick={onExploreBooks}
          className="focus-ring flex items-center gap-1.5 text-sm font-semibold text-burgundy dark:text-gold hover:underline"
        >
          <span>Find a book to keep</span>
          <ArrowRight className="h-4 w-4" />
        </button>
      </div>

      {/* Onboarding Callout if incomplete */}
      {user && !user.onboarding_completed_at && (
        <aside className="flex flex-col justify-between gap-4 rounded-2xl border border-burgundy/20 bg-parchment/70 p-5 dark:border-burgundy-dark/30 dark:bg-[#271519] sm:flex-row sm:items-center">
          <div>
            <p className="font-serif text-lg font-semibold text-ink dark:text-[#F4E9E2]">
              Make this reading nook yours.
            </p>
            <p className="mt-1 text-sm text-walnut dark:text-[#d4c2b9]">
              Choose a few favorite genres and set a gentle yearly goal.
            </p>
          </div>
          <button
            type="button"
            onClick={onOpenOnboarding}
            className="focus-ring inline-flex shrink-0 rounded-full bg-burgundy px-5 py-2.5 text-sm font-semibold text-[#FFF8EF] shadow-xs hover:bg-burgundy-hover"
          >
            Set my preferences
          </button>
        </aside>
      )}

      {/* Daily Thought / Quote Card */}
      <article
        className="bookplate relative overflow-hidden rounded-2xl px-6 py-10 sm:px-12 sm:py-14 text-center"
        aria-labelledby="daily-quote-title"
      >
        <span
          className="absolute left-0 top-10 bottom-10 w-1 bg-gradient-to-b from-transparent via-gold/80 to-transparent"
          aria-hidden="true"
        />

        <p className="text-xs font-semibold uppercase tracking-[0.2em] text-tan dark:text-[#c7a491]">
          {todayFormatted}
        </p>

        <h2 id="daily-quote-title" className="mt-3 font-serif text-2xl font-semibold text-ink dark:text-[#F4E9E2] sm:text-3xl">
          A little thought for today
        </h2>

        <blockquote className="mt-8 font-serif text-2xl italic leading-relaxed text-walnut dark:text-[#e3d3ca] sm:text-3xl max-w-3xl mx-auto">
          <span className="block font-serif text-6xl leading-none text-burgundy dark:text-[#A3324A] opacity-75" aria-hidden="true">
            “
          </span>
          <span className="-mt-5 block">{quote.text}</span>
        </blockquote>

        <div className="mx-auto mt-7 h-px w-16 bg-gold/70" aria-hidden="true" />
        <p className="mt-4 font-hand text-2xl text-burgundy dark:text-gold">— {quote.author}</p>

        <footer className="mt-9 border-t border-warmborder pt-5 dark:border-[#4B3035]">
          <p className="text-xs text-tan dark:text-[#c7a491]">
            Inspirational words for readers &amp; book collectors.
          </p>
        </footer>
      </article>

      {/* Reading Progress & Shelf Quick Access */}
      <div className="grid gap-6 sm:grid-cols-2">
        {/* Yearly Goal Card */}
        <div className="rounded-2xl border border-warmborder bg-paper p-6 dark:border-[#4B3035] dark:bg-raised shadow-xs">
          <div className="flex items-center justify-between">
            <span className="text-xs font-semibold uppercase tracking-wider text-tan dark:text-[#c7a491]">
              {currentYear} Reading Goal
            </span>
            <span className="font-serif text-lg font-bold text-burgundy dark:text-gold">
              {booksReadThisYear} / {yearlyGoal} books
            </span>
          </div>

          <div className="mt-4 h-3 w-full rounded-full bg-[#f1e4d5] dark:bg-[#3d292c] overflow-hidden">
            <div
              className="h-full rounded-full bg-gradient-to-r from-gold to-burgundy transition-all duration-500"
              style={{ width: `${goalPercent}%` }}
            />
          </div>

          <div className="mt-3 flex items-center justify-between text-xs text-walnut dark:text-[#d4c2b9]">
            <span>{goalPercent}% complete</span>
            <button
              type="button"
              onClick={onOpenOnboarding}
              className="text-burgundy dark:text-gold hover:underline"
            >
              Adjust goal
            </button>
          </div>
        </div>

        {/* Tree Bookshelf Quick CTA */}
        <div className="rounded-2xl border border-warmborder bg-paper p-6 dark:border-[#4B3035] dark:bg-raised shadow-xs flex flex-col justify-between">
          <div>
            <div className="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-tan dark:text-[#c7a491]">
              <Bookmark className="h-4 w-4 text-gold" />
              <span>Living Tree Bookshelf</span>
            </div>
            <p className="mt-2 font-serif text-lg font-semibold text-ink dark:text-[#F4E9E2]">
              {userShelf.length} {userShelf.length === 1 ? 'title' : 'titles'} growing on your tree
            </p>
            <p className="mt-1 text-xs text-walnut dark:text-[#d4c2b9]">
              {currentlyReading.length > 0
                ? `Currently reading: ${currentlyReading[0].book.title}`
                : 'Visit your profile to arrange your branch shelves.'}
            </p>
          </div>

          <button
            type="button"
            onClick={onOpenMyShelf}
            className="mt-4 inline-flex items-center justify-center gap-1.5 rounded-full bg-burgundy px-4 py-2 text-xs font-semibold text-[#FFF8EF] hover:bg-burgundy-hover transition self-start"
          >
            <span>Open tree bookshelf</span>
            <ArrowRight className="h-3.5 w-3.5" />
          </button>
        </div>
      </div>

      {/* Currently Reading Carousel / Quick List */}
      {currentlyReading.length > 0 && (
        <section className="rounded-2xl border border-warmborder bg-paper/60 dark:bg-raised/60 dark:border-[#4B3035] p-6">
          <h3 className="font-serif text-lg font-semibold text-ink dark:text-[#F4E9E2]">
            Keep reading where you left off
          </h3>
          <div className="mt-4 grid gap-4 sm:grid-cols-2">
            {currentlyReading.map((item) => (
              <div
                key={item.id}
                onClick={() => onSelectBook(item.book.id)}
                className="flex items-center gap-4 rounded-xl border border-warmborder/80 bg-white dark:bg-[#201719] p-3 shadow-2xs cursor-pointer hover:border-burgundy/40 transition"
              >
                {item.book.cover_url ? (
                  <img
                    src={item.book.cover_url}
                    alt={item.book.title}
                    className="h-16 w-11 rounded object-cover shadow-xs shrink-0"
                  />
                ) : (
                  <div className="h-16 w-11 rounded bg-burgundy/10 flex items-center justify-center shrink-0">
                    <BookOpen className="h-5 w-5 text-burgundy" />
                  </div>
                )}
                <div className="min-w-0 flex-1">
                  <h4 className="font-serif text-sm font-semibold text-ink dark:text-[#f4e9e2] truncate">
                    {item.book.title}
                  </h4>
                  <p className="text-xs text-tan truncate">{item.book.author_name}</p>
                  <p className="mt-1.5 text-xs text-walnut dark:text-[#d4c2b9]">
                    Page {item.current_page || 0} of {item.total_pages || item.book.total_pages || 300}
                  </p>
                </div>
              </div>
            ))}
          </div>
        </section>
      )}
    </div>
  );
};
