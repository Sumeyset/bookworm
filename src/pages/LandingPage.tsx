import React from 'react';
import { Logo } from '../components/Logo';
import { ArrowDown, ArrowRight, BookOpen, Feather, Sparkles } from 'lucide-react';

interface LandingPageProps {
  onJoin: () => void;
  onLogin: () => void;
  onExplore: () => void;
}

export const LandingPage: React.FC<LandingPageProps> = ({ onJoin, onLogin, onExplore }) => {
  return (
    <div className="paper-grain min-h-screen">
      {/* Hero Section */}
      <section className="mx-auto grid max-w-7xl items-center gap-12 px-5 py-14 sm:px-8 sm:py-20 lg:grid-cols-[1.05fr_0.95fr] lg:gap-16 lg:px-12 lg:py-24">
        <div>
          <p className="flex items-center gap-3 text-xs font-semibold uppercase tracking-[0.22em] text-tan dark:text-[#c7a491]">
            <span className="h-px w-8 bg-gold" /> A quieter corner of the internet
          </p>

          <h1 className="mt-6 max-w-2xl font-serif text-5xl font-medium leading-[1.08] tracking-tight text-ink dark:text-[#F4E9E2] sm:text-6xl lg:text-7xl">
            Your reading life,<br />
            <span className="italic text-burgundy dark:text-gold">beautifully shelved.</span>
          </h1>

          <p className="mt-6 max-w-xl text-lg leading-8 text-walnut dark:text-[#d4c2b9]">
            Keep the books that stay with you. Rate a favorite, leave a note in the margin, and find readers who get it.
          </p>

          <div className="mt-8 flex flex-wrap items-center gap-4">
            <button
              type="button"
              onClick={onJoin}
              className="focus-ring rounded-full bg-burgundy px-7 py-3.5 text-sm font-semibold text-[#FFF8EF] shadow-md transition hover:-translate-y-0.5 hover:bg-burgundy-hover"
            >
              Get started free
            </button>
            <button
              type="button"
              onClick={onExplore}
              className="focus-ring flex items-center gap-2 rounded-full border border-warmborder px-6 py-3.5 text-sm font-semibold text-burgundy dark:text-[#F4E9E2] hover:bg-rosetint/50 dark:hover:bg-[#382329]"
            >
              <span>Explore the library</span>
              <ArrowRight className="h-4 w-4" />
            </button>
          </div>

          <p className="mt-5 text-xs text-tan dark:text-[#c7a491]">
            A shelf of your own. No rush, no reading challenge required.
          </p>
        </div>

        {/* Hero Visual: Literary Book Stack & Quotes */}
        <div
          className="relative mx-auto flex min-h-[26rem] w-full max-w-lg items-center justify-center"
          aria-label="Arrangement of favorite classic books"
        >
          <div className="absolute inset-x-7 bottom-8 h-5 rounded-sm bg-gradient-to-b from-[#9B7356] to-[#5A3A2E] shadow-[0_16px_22px_-12px_rgba(42,26,29,0.55)]" />

          {/* Book 1 */}
          <article className="absolute left-[8%] top-[15%] z-10 flex h-60 w-40 -rotate-6 flex-col justify-between rounded-r-sm border-l-4 border-[#E6D9D0]/80 bg-gradient-to-br from-burgundy via-[#8B2A3D] to-burgundy-deep p-4 text-[#FFF8EF] shadow-[0_22px_32px_-18px_rgba(42,26,29,0.6)] transition-transform duration-200 hover:-translate-y-2 hover:rotate-0">
            <span className="text-center font-serif text-xs uppercase tracking-[0.18em] text-[#E8CFA6]">
              Jane Austen
            </span>
            <span className="text-center font-serif text-2xl leading-tight">
              Pride &amp;<br />Prejudice
            </span>
            <span className="text-center text-[10px] uppercase tracking-[0.2em] text-[#E8CFA6]">
              A classic, still
            </span>
          </article>

          {/* Book 2 */}
          <article className="absolute left-[35%] top-[7%] z-20 flex h-72 w-44 flex-col justify-between rounded-r-sm border-l-4 border-[#E6D9D0]/80 bg-gradient-to-br from-[#63442D] via-[#8A6A55] to-[#3B2418] p-4 text-[#FFF8EF] shadow-[0_24px_36px_-18px_rgba(42,26,29,0.65)] transition-transform duration-200 hover:-translate-y-2">
            <span className="text-center font-serif text-xs uppercase tracking-[0.18em] text-[#E8CFA6]">
              J. R. R. Tolkien
            </span>
            <span className="text-center font-serif text-3xl leading-tight">
              The<br />Hobbit
            </span>
            <span className="text-center text-[10px] uppercase tracking-[0.2em] text-[#E8CFA6]">
              There and back again
            </span>
          </article>

          {/* Book 3 */}
          <article className="absolute right-[5%] top-[19%] z-10 flex h-56 w-36 rotate-6 flex-col justify-between rounded-r-sm border-l-4 border-[#E6D9D0]/80 bg-gradient-to-br from-[#54382A] via-[#72513A] to-[#3B2418] p-3 text-[#FFF8EF] shadow-[0_20px_30px_-18px_rgba(42,26,29,0.6)] transition-transform duration-200 hover:-translate-y-2 hover:rotate-0">
            <span className="text-center font-serif text-[10px] uppercase tracking-[0.16em] text-[#E8CFA6]">
              Louisa May Alcott
            </span>
            <span className="text-center font-serif text-2xl">
              Little<br />Women
            </span>
            <span className="text-center text-[10px] uppercase tracking-[0.2em] text-[#E8CFA6]">
              Since 1868
            </span>
          </article>

          {/* Note Card */}
          <blockquote className="absolute bottom-0 right-[2%] z-30 max-w-[17rem] rotate-2 border border-warmborder bg-paper px-5 py-4 shadow-bookplate dark:border-[#4B3035] dark:bg-raised">
            <span className="font-serif text-4xl leading-none text-burgundy dark:text-gold" aria-hidden="true">
              “
            </span>
            <p className="-mt-2 font-serif text-base italic leading-6 text-ink dark:text-[#F4E9E2]">
              A book is a door you can open again.
            </p>
            <cite className="mt-2 block text-right text-xs not-italic text-tan dark:text-[#c7a491]">
              — your reading journal
            </cite>
          </blockquote>
        </div>
      </section>

      {/* How it works Section */}
      <section className="border-y border-warmborder bg-paper/75 py-16 dark:border-[#4B3035] dark:bg-raised/60 sm:py-20">
        <div className="mx-auto max-w-7xl px-5 sm:px-8 lg:px-12">
          <div className="max-w-2xl">
            <p className="text-xs font-semibold uppercase tracking-[0.2em] text-tan dark:text-[#c7a491]">
              A reader’s kind of social
            </p>
            <h2 className="mt-3 font-serif text-3xl font-semibold text-ink dark:text-[#F4E9E2] sm:text-4xl">
              Make room for the books that matter.
            </h2>
          </div>

          <div className="mt-12 grid gap-8 md:grid-cols-3">
            <article className="border-t border-gold/50 pt-5">
              <p className="font-serif text-4xl text-burgundy dark:text-gold">01</p>
              <h3 className="mt-4 font-serif text-xl font-semibold text-ink dark:text-[#F4E9E2]">
                Keep your tree shelf
              </h3>
              <p className="mt-2 text-sm leading-6 text-walnut dark:text-[#d4c2b9]">
                Place books onto the tree branches. Track reading status, record pages read, and watch your shelf flourish.
              </p>
            </article>

            <article className="border-t border-gold/50 pt-5 md:mt-8">
              <p className="font-serif text-4xl text-burgundy dark:text-gold">02</p>
              <h3 className="mt-4 font-serif text-xl font-semibold text-ink dark:text-[#F4E9E2]">
                Read with company
              </h3>
              <p className="mt-2 text-sm leading-6 text-walnut dark:text-[#d4c2b9]">
                Follow readers whose reviews make you pause, smile, or add another title to your wishlist.
              </p>
            </article>

            <article className="border-t border-gold/50 pt-5">
              <p className="font-serif text-4xl text-burgundy dark:text-gold">03</p>
              <h3 className="mt-4 font-serif text-xl font-semibold text-ink dark:text-[#F4E9E2]">
                Keep the good lines
              </h3>
              <p className="mt-2 text-sm leading-6 text-walnut dark:text-[#d4c2b9]">
                Highlight unforgettable passages and turn them into elegant quote cards you can share with friends.
              </p>
            </article>
          </div>
        </div>
      </section>

      {/* Quote Banner */}
      <section className="bg-parchment/70 py-14 dark:bg-[#211317]">
        <div className="mx-auto flex max-w-7xl flex-col gap-4 px-5 sm:px-8 md:flex-row md:items-center md:justify-between lg:px-12">
          <div>
            <p className="font-serif text-2xl italic text-ink dark:text-[#F4E9E2]">
              “The right book finds you at the right time.”
            </p>
            <p className="mt-1 text-sm text-tan dark:text-[#c7a491]">A small reminder from one reader to another.</p>
          </div>
          <p className="font-hand text-2xl text-burgundy dark:text-gold">— the Bookworm readers</p>
        </div>
      </section>

      {/* Footer */}
      <footer className="border-t border-warmborder bg-paper dark:border-[#4B3035] dark:bg-raised py-8">
        <div className="mx-auto flex max-w-7xl flex-col gap-3 px-5 sm:flex-row sm:items-center sm:justify-between sm:px-8 lg:px-12">
          <Logo variant="full" tone="dark" />
          <p className="text-xs text-tan dark:text-[#c7a491]">
            For the love of stories, and the people who pass them along.
          </p>
        </div>
      </footer>
    </div>
  );
};
