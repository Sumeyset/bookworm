import React, { useState, useEffect } from 'react';
import { StorageService } from '../services/storage';
import { useAuth } from '../context/AuthContext';
import { StarRating } from '../components/StarRating';
import { BookOpen, Feather, MessageSquare, Users } from 'lucide-react';

interface FeedPageProps {
  onSelectBook: (bookId: string) => void;
  onViewReaderProfile: (username: string) => void;
  onExploreReaders: () => void;
}

export const FeedPage: React.FC<FeedPageProps> = ({
  onSelectBook,
  onViewReaderProfile,
  onExploreReaders,
}) => {
  const { user } = useAuth();
  const [feedItems, setFeedItems] = useState<ReturnType<typeof StorageService.getFeedReviews>>([]);

  useEffect(() => {
    if (user) {
      setFeedItems(StorageService.getFeedReviews(user.id));
    }
  }, [user]);

  return (
    <div className="mx-auto max-w-4xl px-4 py-8 sm:px-6 sm:py-12 space-y-8">
      {/* Feed Header */}
      <div className="border-b border-warmborder pb-6 dark:border-[#4B3035]">
        <p className="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-tan dark:text-[#c7a491]">
          <Feather className="h-4 w-4 text-gold" />
          <span>The Reading Circle</span>
        </p>
        <h1 className="mt-1 font-serif text-3xl sm:text-4xl font-semibold text-ink dark:text-[#f4e9e2]">
          Reading Feed
        </h1>
        <p className="mt-1 text-sm text-walnut dark:text-[#d4c2b9]">
          Recent notes, ratings, and thoughts shared by the readers you follow.
        </p>
      </div>

      {feedItems.length === 0 ? (
        <div className="rounded-3xl border border-dashed border-warmborder bg-paper/60 p-12 text-center dark:border-[#4B3035] dark:bg-raised/60">
          <Users className="mx-auto h-12 w-12 text-tan opacity-70 mb-3" />
          <h2 className="font-serif text-xl font-semibold text-ink dark:text-[#f4e9e2]">
            Your reading circle is quiet right now
          </h2>
          <p className="mt-2 text-xs text-tan dark:text-[#c7a491] max-w-md mx-auto">
            Follow fellow readers to see their latest bookshelf additions, margin notes, and ratings here.
          </p>
          <button
            type="button"
            onClick={onExploreReaders}
            className="mt-6 rounded-full bg-burgundy px-6 py-2.5 text-xs font-semibold text-[#FFF8EF] shadow-xs hover:bg-burgundy-hover transition"
          >
            Meet Fellow Readers
          </button>
        </div>
      ) : (
        <div className="space-y-6">
          {feedItems.map((item) => (
            <article
              key={item.id}
              className="rounded-3xl border border-warmborder bg-paper p-6 sm:p-7 shadow-xs dark:border-[#4B3035] dark:bg-raised transition hover:shadow-md"
            >
              {/* Reader Header */}
              <div className="flex items-center justify-between gap-3 border-b border-warmborder/60 pb-4 dark:border-[#4B3035]">
                <div className="flex items-center gap-3">
                  <button
                    type="button"
                    onClick={() => onViewReaderProfile(item.user.username)}
                    className="h-10 w-10 rounded-full bg-burgundy text-[#FFF8EF] font-serif font-bold text-sm flex items-center justify-center uppercase shadow-2xs hover:opacity-90"
                  >
                    {item.user.name.charAt(0)}
                  </button>
                  <div>
                    <button
                      type="button"
                      onClick={() => onViewReaderProfile(item.user.username)}
                      className="font-serif text-sm sm:text-base font-semibold text-ink dark:text-[#f4e9e2] hover:text-burgundy"
                    >
                      {item.user.name}
                    </button>
                    <p className="text-xs text-tan">@{item.user.username} · {new Date(item.created_at).toLocaleDateString()}</p>
                  </div>
                </div>

                <StarRating rating={item.rating} size="sm" />
              </div>

              {/* Book Details and Review Snippet */}
              <div className="mt-4 flex gap-4 items-start">
                <div
                  onClick={() => onSelectBook(item.book.id)}
                  className="cursor-pointer shrink-0"
                >
                  {item.book.cover_url ? (
                    <img
                      src={item.book.cover_url}
                      alt={item.book.title}
                      className="h-24 w-16 rounded-lg object-cover shadow-xs hover:opacity-90 transition"
                    />
                  ) : (
                    <div className="h-24 w-16 rounded-lg bg-burgundy/10 flex items-center justify-center">
                      <BookOpen className="h-6 w-6 text-burgundy" />
                    </div>
                  )}
                </div>

                <div className="min-w-0 flex-1">
                  <button
                    type="button"
                    onClick={() => onSelectBook(item.book.id)}
                    className="font-serif text-base font-semibold text-ink dark:text-[#f4e9e2] hover:text-burgundy text-left line-clamp-1"
                  >
                    {item.book.title}
                  </button>
                  <p className="text-xs text-tan mb-3">by {item.book.author_name}</p>

                  {item.body ? (
                    <p className="font-serif text-sm leading-relaxed text-walnut dark:text-[#d4c2b9]">
                      “{item.body}”
                    </p>
                  ) : (
                    <p className="text-xs italic text-tan">Rated this book {item.rating} stars</p>
                  )}
                </div>
              </div>
            </article>
          ))}
        </div>
      )}
    </div>
  );
};
