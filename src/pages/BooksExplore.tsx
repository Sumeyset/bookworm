import React, { useState, useEffect } from 'react';
import { Book, ShelfStatus } from '../types';
import { StorageService } from '../services/storage';
import { OpenLibraryService, OpenLibrarySearchResult } from '../services/openLibrary';
import { useAuth } from '../context/AuthContext';
import { StarRating } from '../components/StarRating';
import { BookOpen, Compass, Loader2, Plus, Search } from 'lucide-react';

interface BooksExploreProps {
  initialQuery?: string;
  onSelectBook: (bookId: string) => void;
}

const CATEGORIES = [
  'All Books',
  'Classics',
  'Literary Fiction',
  'Gothic & Mystery',
  'Science Fiction',
  'Poetry & Essays',
];

export const BooksExplore: React.FC<BooksExploreProps> = ({ initialQuery = '', onSelectBook }) => {
  const { user } = useAuth();
  const [searchQuery, setSearchQuery] = useState(initialQuery);
  const [activeCategory, setActiveCategory] = useState('All Books');
  const [libraryBooks, setLibraryBooks] = useState<Book[]>([]);
  const [searchResults, setSearchResults] = useState<OpenLibrarySearchResult[]>([]);
  const [isSearching, setIsSearching] = useState(false);
  const [searchError, setSearchError] = useState<string | null>(null);

  // Load local books
  useEffect(() => {
    setLibraryBooks(StorageService.getBooks());
  }, []);

  // Run search when query changes
  const performSearch = async (queryText: string) => {
    if (!queryText.trim()) {
      setSearchResults([]);
      setSearchError(null);
      return;
    }
    setIsSearching(true);
    setSearchError(null);
    try {
      const results = await OpenLibraryService.search(queryText);
      setSearchResults(results);
    } catch (err) {
      setSearchError('Open Library search is momentarily resting. Showing our curated titles.');
      setSearchResults([]);
    } finally {
      setIsSearching(false);
    }
  };

  useEffect(() => {
    if (initialQuery) {
      setSearchQuery(initialQuery);
      performSearch(initialQuery);
    }
  }, [initialQuery]);

  const handleSearchSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    performSearch(searchQuery);
  };

  const handleAddOrUpdateShelf = (book: Book, status: ShelfStatus) => {
    if (!user) return;
    StorageService.updateShelfStatus(user.id, book.id, status);
    setLibraryBooks(StorageService.getBooks());
  };

  const handleRateBook = (book: Book, rating: number) => {
    if (!user) return;
    StorageService.saveReview(user.id, book.id, rating);
    setLibraryBooks(StorageService.getBooks());
  };

  // Convert an OpenLibrary search result into a saved book
  const handleSelectSearchResult = (res: OpenLibrarySearchResult) => {
    let saved = StorageService.getBookByOlId(res.ol_id);
    if (!saved) {
      saved = StorageService.saveOrUpdateBook({
        ol_id: res.ol_id,
        title: res.title,
        author_name: res.author_name,
        cover_url: res.cover_url,
        first_publish_year: res.first_publish_year,
      });
    }
    onSelectBook(saved.id);
  };

  const userShelf = user ? StorageService.getUserShelf(user.id) : [];

  return (
    <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 sm:py-12">
      {/* Page Header */}
      <div className="flex flex-col md:flex-row md:items-end md:justify-between gap-4 border-b border-warmborder pb-6 dark:border-[#4B3035]">
        <div>
          <p className="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-tan dark:text-[#c7a491]">
            <Compass className="h-4 w-4 text-gold" />
            <span>Discover &amp; Keep</span>
          </p>
          <h1 className="mt-1 font-serif text-3xl sm:text-4xl font-semibold text-ink dark:text-[#f4e9e2]">
            Explore the Library
          </h1>
          <p className="mt-1 text-sm text-walnut dark:text-[#d4c2b9]">
            Search millions of works from Open Library or browse curated classics for your shelf.
          </p>
        </div>

        {/* Search Bar */}
        <form onSubmit={handleSearchSubmit} className="flex gap-2 max-w-md w-full">
          <div className="relative flex-1">
            <Search className="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-tan" />
            <input
              type="text"
              placeholder="Search by title, author, or keyword…"
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              className="w-full rounded-full border border-warmborder bg-paper dark:bg-[#271519] dark:border-[#4B3035] pl-10 pr-4 py-2.5 text-xs text-ink dark:text-[#f4e9e2] placeholder-tan focus:border-burgundy focus:outline-hidden focus:ring-1 focus:ring-burgundy"
            />
          </div>
          <button
            type="submit"
            disabled={isSearching}
            className="rounded-full bg-burgundy px-5 py-2.5 text-xs font-semibold text-[#FFF8EF] shadow-xs hover:bg-burgundy-hover disabled:opacity-50 transition"
          >
            {isSearching ? <Loader2 className="h-4 w-4 animate-spin" /> : 'Search'}
          </button>
        </form>
      </div>

      {/* Categories */}
      <div className="mt-6 flex flex-wrap gap-2">
        {CATEGORIES.map((cat) => (
          <button
            key={cat}
            type="button"
            onClick={() => {
              setActiveCategory(cat);
              setSearchQuery('');
              setSearchResults([]);
            }}
            className={`rounded-full px-4 py-1.5 text-xs font-medium transition ${
              activeCategory === cat && !searchQuery
                ? 'bg-burgundy text-white shadow-xs'
                : 'border border-warmborder bg-paper dark:bg-[#201719] dark:border-[#4B3035] text-walnut dark:text-[#d4c2b9] hover:bg-rosetint/50'
            }`}
          >
            {cat}
          </button>
        ))}
      </div>

      {/* Loading & Error notifications */}
      {isSearching && (
        <div className="my-12 flex flex-col items-center justify-center gap-3 text-tan">
          <Loader2 className="h-8 w-8 animate-spin text-burgundy" />
          <p className="text-sm">Fetching titles from Open Library…</p>
        </div>
      )}

      {searchError && (
        <div className="mt-6 rounded-xl border border-amber-200 bg-amber-50 p-4 text-xs text-amber-800 dark:bg-amber-950/40 dark:border-amber-900/60 dark:text-amber-200">
          {searchError}
        </div>
      )}

      {/* Search Results if active */}
      {searchResults.length > 0 && (
        <section className="mt-8">
          <h2 className="font-serif text-xl font-semibold text-ink dark:text-[#f4e9e2] mb-4">
            Search Results for “{searchQuery}” ({searchResults.length})
          </h2>
          <div className="grid gap-5 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
            {searchResults.map((result) => {
              const savedBook = libraryBooks.find((b) => b.ol_id === result.ol_id);
              const shelfItem = userShelf.find((s) => s.book_id === savedBook?.id);

              return (
                <div
                  key={result.ol_id}
                  className="group rounded-2xl border border-warmborder bg-paper p-4 shadow-xs dark:border-[#4B3035] dark:bg-raised flex flex-col justify-between hover:shadow-md transition"
                >
                  <div
                    onClick={() => handleSelectSearchResult(result)}
                    className="cursor-pointer"
                  >
                    <div className="relative aspect-[2/3] w-full overflow-hidden rounded-xl bg-parchment/60 shadow-xs mb-3">
                      {result.cover_url ? (
                        <img
                          src={result.cover_url}
                          alt={result.title}
                          className="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                          onError={(e) => {
                            (e.currentTarget as HTMLImageElement).src =
                              'https://images.unsplash.com/photo-1544947950-fa07a98d237f?auto=format&fit=crop&q=80&w=300';
                          }}
                        />
                      ) : (
                        <div className="flex h-full w-full items-center justify-center p-3 text-center font-serif text-xs text-walnut">
                          {result.title}
                        </div>
                      )}
                    </div>

                    <h3 className="font-serif text-base font-semibold text-ink dark:text-[#f4e9e2] group-hover:text-burgundy line-clamp-1">
                      {result.title}
                    </h3>
                    <p className="text-xs text-tan line-clamp-1">{result.author_name}</p>
                    {result.first_publish_year && (
                      <p className="text-[11px] text-tan/75 mt-0.5">Published {result.first_publish_year}</p>
                    )}
                  </div>

                  {/* Actions */}
                  <div className="mt-4 border-t border-warmborder/70 dark:border-[#4B3035] pt-3 flex items-center justify-between">
                    <button
                      type="button"
                      onClick={() => handleSelectSearchResult(result)}
                      className="text-xs font-semibold text-burgundy dark:text-gold hover:underline"
                    >
                      View Details &rarr;
                    </button>
                    {shelfItem && (
                      <span className="rounded-full bg-[#f1e4d5] dark:bg-[#3d292c] px-2.5 py-0.5 text-[10px] font-semibold text-walnut dark:text-[#e8cfa6]">
                        {shelfItem.status === 'reading'
                          ? 'Reading'
                          : shelfItem.status === 'read'
                          ? 'Read'
                          : 'To read'}
                      </span>
                    )}
                  </div>
                </div>
              );
            })}
          </div>
        </section>
      )}

      {/* Curated Library Collection */}
      {searchResults.length === 0 && !isSearching && (
        <section className="mt-8">
          <h2 className="font-serif text-xl font-semibold text-ink dark:text-[#f4e9e2] mb-4">
            Curated Classics &amp; Stories
          </h2>

          <div className="grid gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
            {libraryBooks.map((book) => {
              const shelfItem = userShelf.find((s) => s.book_id === book.id);
              const reviews = StorageService.getBookReviews(book.id);
              const avgRating = reviews.length
                ? reviews.reduce((sum, r) => sum + r.rating, 0) / reviews.length
                : 4.5;

              return (
                <div
                  key={book.id}
                  className="group rounded-2xl border border-warmborder bg-paper p-4 shadow-xs dark:border-[#4B3035] dark:bg-raised flex flex-col justify-between hover:shadow-md transition"
                >
                  <div onClick={() => onSelectBook(book.id)} className="cursor-pointer">
                    <div className="relative aspect-[2/3] w-full overflow-hidden rounded-xl bg-parchment/60 shadow-xs mb-3">
                      {book.cover_url ? (
                        <img
                          src={book.cover_url}
                          alt={book.title}
                          className="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                          onError={(e) => {
                            (e.currentTarget as HTMLImageElement).src =
                              'https://images.unsplash.com/photo-1544947950-fa07a98d237f?auto=format&fit=crop&q=80&w=300';
                          }}
                        />
                      ) : (
                        <div className="flex h-full w-full items-center justify-center p-3 text-center font-serif text-xs text-walnut">
                          {book.title}
                        </div>
                      )}
                    </div>

                    <h3 className="font-serif text-base font-semibold text-ink dark:text-[#f4e9e2] group-hover:text-burgundy line-clamp-1">
                      {book.title}
                    </h3>
                    <p className="text-xs text-tan line-clamp-1">{book.author_name}</p>

                    <div className="mt-2 flex items-center justify-between">
                      <StarRating rating={avgRating} size="sm" />
                      <span className="text-[11px] text-tan">{book.first_publish_year}</span>
                    </div>
                  </div>

                  {/* Shelf Controls */}
                  <div className="mt-4 border-t border-warmborder/70 dark:border-[#4B3035] pt-3">
                    <div className="flex items-center justify-between gap-1">
                      <select
                        value={shelfItem?.status || ''}
                        onChange={(e) => {
                          if (e.target.value) {
                            handleAddOrUpdateShelf(book, e.target.value as ShelfStatus);
                          }
                        }}
                        className="w-full rounded-lg border border-warmborder bg-parchment/40 dark:bg-[#201719] dark:border-[#4B3035] py-1.5 px-2 text-xs text-walnut dark:text-[#d4c2b9] focus:outline-hidden"
                      >
                        <option value="">+ Add to my shelf</option>
                        <option value="want_to_read">Want to read</option>
                        <option value="reading">Currently reading</option>
                        <option value="read">Finished reading</option>
                      </select>
                    </div>
                  </div>
                </div>
              );
            })}
          </div>
        </section>
      )}
    </div>
  );
};
