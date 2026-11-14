import React, { useState, useEffect } from 'react';
import { User } from '../types';
import { StorageService } from '../services/storage';
import { useAuth } from '../context/AuthContext';
import { TreeBookshelf } from '../components/TreeBookshelf';
import { PdfReaderModal } from '../components/PdfReaderModal';
import {
  BookOpen,
  Calendar,
  Check,
  Flame,
  Plus,
  Sparkles,
  Trophy,
  UserCheck,
  UserPlus,
} from 'lucide-react';

interface ProfileBookshelfProps {
  username: string;
  onSelectBook: (bookId: string) => void;
  onOpenOnboarding: () => void;
  onExploreBooks?: () => void;
}

export const ProfileBookshelf: React.FC<ProfileBookshelfProps> = ({
  username,
  onSelectBook,
  onOpenOnboarding,
  onExploreBooks,
}) => {
  const { user: currentUser } = useAuth();
  const [profileUser, setProfileUser] = useState<User | undefined>(() =>
    StorageService.getUserByUsername(username)
  );
  const [userShelf, setUserShelf] = useState<ReturnType<typeof StorageService.getUserShelf>>([]);
  const [isFollowing, setIsFollowing] = useState(false);
  const [followersCount, setFollowersCount] = useState(0);
  const [followingCount, setFollowingCount] = useState(0);
  const [readerModalBookId, setReaderModalBookId] = useState<string | null>(null);

  const reloadProfile = () => {
    const target = StorageService.getUserByUsername(username);
    setProfileUser(target);
    if (target) {
      const items = StorageService.getUserShelf(target.id);
      setUserShelf(items);
      setFollowersCount(StorageService.getFollowersCount(target.id));
      setFollowingCount(StorageService.getFollowingCount(target.id));
      if (currentUser) {
        setIsFollowing(StorageService.isFollowing(currentUser.id, target.id));
      }
    }
  };

  useEffect(() => {
    reloadProfile();
  }, [username, currentUser]);

  if (!profileUser) {
    return (
      <div className="mx-auto max-w-4xl px-4 py-16 text-center">
        <p className="font-serif text-2xl text-walnut dark:text-[#f4e9e2]">Reader not found</p>
      </div>
    );
  }

  const isOwnProfile = currentUser?.id === profileUser.id;

  const handleToggleFollow = () => {
    if (!currentUser) return;
    const nowFollowing = StorageService.toggleFollow(currentUser.id, profileUser.id);
    setIsFollowing(nowFollowing);
    setFollowersCount(StorageService.getFollowersCount(profileUser.id));
  };

  const handleUpdateProgress = (
    bookId: string,
    status: any,
    currentPage: number,
    totalPages: number
  ) => {
    if (!currentUser || !isOwnProfile) return;
    StorageService.updateShelfStatus(currentUser.id, bookId, status, currentPage, totalPages);
    reloadProfile();
  };

  // Stats calculation
  const currentYear = new Date().getFullYear();
  const booksReadThisYear = userShelf.filter(
    (s) => s.status === 'read' && s.read_at && new Date(s.read_at).getFullYear() === currentYear
  ).length;
  const currentlyReadingCount = userShelf.filter((s) => s.status === 'reading').length;
  const totalPagesRead = userShelf
    .filter((s) => s.status === 'read' && s.total_pages)
    .reduce((sum, s) => sum + (s.total_pages || 0), 0);

  const yearlyGoal = profileUser.yearly_reading_goal || 12;
  const goalPercentage = Math.min(100, Math.round((booksReadThisYear / yearlyGoal) * 100));

  // Monthly breakdown for 12 months
  const monthlyReads: number[] = Array.from({ length: 12 }, () => 0);
  userShelf.forEach((s) => {
    if (s.status === 'read' && s.read_at) {
      const d = new Date(s.read_at);
      if (d.getFullYear() === currentYear) {
        monthlyReads[d.getMonth()] += 1;
      }
    }
  });

  const MONTH_NAMES = ['J', 'F', 'M', 'A', 'M', 'J', 'J', 'A', 'S', 'O', 'N', 'D'];

  const bookForModal = readerModalBookId ? StorageService.getBookById(readerModalBookId) : null;

  return (
    <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 sm:py-12 space-y-8">
      {/* Reader Profile Header */}
      <section className="rounded-3xl border border-warmborder bg-paper p-6 sm:p-8 dark:border-[#4B3035] dark:bg-raised shadow-xs">
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
          <div className="flex items-center gap-4 sm:gap-6">
            <div className="h-16 w-16 sm:h-20 sm:w-20 rounded-full bg-burgundy text-[#FFF8EF] font-serif text-2xl sm:text-3xl font-bold flex items-center justify-center uppercase shadow-md shrink-0">
              {profileUser.name.charAt(0)}
            </div>

            <div>
              <div className="flex items-center gap-3">
                <h1 className="font-serif text-2xl sm:text-3xl font-semibold text-ink dark:text-[#f4e9e2]">
                  {profileUser.name}
                </h1>
                <span className="text-xs text-tan dark:text-[#c7a491]">@{profileUser.username}</span>
              </div>

              {profileUser.bio && (
                <p className="mt-1 text-sm text-walnut dark:text-[#d4c2b9] max-w-xl">
                  {profileUser.bio}
                </p>
              )}

              {/* Preferred Genres Tags */}
              {profileUser.preferred_genres?.length > 0 && (
                <div className="mt-3 flex flex-wrap gap-1.5">
                  {profileUser.preferred_genres.map((g) => (
                    <span
                      key={g}
                      className="rounded-full bg-parchment/80 dark:bg-[#342427] px-2.5 py-0.5 text-[11px] font-medium text-walnut dark:text-[#d4c2b9]"
                    >
                      {g}
                    </span>
                  ))}
                </div>
              )}
            </div>
          </div>

          {/* Follow / Edit Button */}
          <div className="flex sm:flex-col items-center sm:items-end gap-3">
            {isOwnProfile ? (
              <button
                type="button"
                onClick={onOpenOnboarding}
                className="rounded-full border border-warmborder bg-parchment/60 dark:bg-[#342427] dark:border-[#4B3035] px-4 py-2 text-xs font-semibold text-walnut dark:text-[#d4c2b9] hover:bg-rosetint/50"
              >
                Edit Reading Preferences
              </button>
            ) : (
              currentUser && (
                <button
                  type="button"
                  onClick={handleToggleFollow}
                  className={`inline-flex items-center gap-1.5 rounded-full px-5 py-2 text-xs font-semibold transition ${
                    isFollowing
                      ? 'border border-warmborder bg-parchment text-walnut dark:bg-[#342427] dark:text-[#d4c2b9]'
                      : 'bg-burgundy text-[#FFF8EF] hover:bg-burgundy-hover shadow-xs'
                  }`}
                >
                  {isFollowing ? <UserCheck className="h-4 w-4" /> : <UserPlus className="h-4 w-4" />}
                  <span>{isFollowing ? 'Following' : 'Follow Reader'}</span>
                </button>
              )
            )}

            {/* Counts */}
            <div className="flex gap-4 text-xs text-tan dark:text-[#c7a491]">
              <span>
                <strong className="text-ink dark:text-[#f4e9e2]">{followersCount}</strong> Followers
              </span>
              <span>
                <strong className="text-ink dark:text-[#f4e9e2]">{followingCount}</strong> Following
              </span>
            </div>
          </div>
        </div>
      </section>

      {/* Reading Life Metrics & Stats */}
      <section className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {/* Metric 1 */}
        <div className="rounded-2xl border border-warmborder bg-paper p-5 dark:border-[#4B3035] dark:bg-raised shadow-xs">
          <div className="flex items-center justify-between text-xs font-semibold uppercase tracking-wider text-tan">
            <span>Books in {currentYear}</span>
            <Trophy className="h-4 w-4 text-gold" />
          </div>
          <p className="mt-2 font-serif text-3xl font-bold text-ink dark:text-[#f4e9e2]">
            {booksReadThisYear}
          </p>
          <p className="mt-1 text-xs text-tan">
            Goal: {yearlyGoal} ({goalPercentage}% complete)
          </p>
        </div>

        {/* Metric 2 */}
        <div className="rounded-2xl border border-warmborder bg-paper p-5 dark:border-[#4B3035] dark:bg-raised shadow-xs">
          <div className="flex items-center justify-between text-xs font-semibold uppercase tracking-wider text-tan">
            <span>Currently Reading</span>
            <BookOpen className="h-4 w-4 text-burgundy" />
          </div>
          <p className="mt-2 font-serif text-3xl font-bold text-ink dark:text-[#f4e9e2]">
            {currentlyReadingCount}
          </p>
          <p className="mt-1 text-xs text-tan">Open bookmarks on the tree</p>
        </div>

        {/* Metric 3 */}
        <div className="rounded-2xl border border-warmborder bg-paper p-5 dark:border-[#4B3035] dark:bg-raised shadow-xs">
          <div className="flex items-center justify-between text-xs font-semibold uppercase tracking-wider text-tan">
            <span>Pages Finished</span>
            <Flame className="h-4 w-4 text-amber-600" />
          </div>
          <p className="mt-2 font-serif text-3xl font-bold text-ink dark:text-[#f4e9e2]">
            {totalPagesRead.toLocaleString()}
          </p>
          <p className="mt-1 text-xs text-tan">From completed volumes</p>
        </div>

        {/* Metric 4: Monthly Bar Chart */}
        <div className="rounded-2xl border border-warmborder bg-paper p-5 dark:border-[#4B3035] dark:bg-raised shadow-xs flex flex-col justify-between">
          <div className="flex items-center justify-between text-xs font-semibold uppercase tracking-wider text-tan">
            <span>Monthly Reads</span>
            <Calendar className="h-4 w-4 text-gold" />
          </div>

          <div className="mt-3 flex items-end justify-between gap-1 h-12">
            {monthlyReads.map((cnt, idx) => {
              const maxMonthly = Math.max(1, ...monthlyReads);
              const heightPercent = Math.max(10, Math.round((cnt / maxMonthly) * 100));
              return (
                <div key={idx} className="flex-1 flex flex-col items-center gap-1">
                  <div
                    className="w-full rounded-t-sm bg-gradient-to-t from-gold/60 to-burgundy transition-all"
                    style={{ height: `${heightPercent}%` }}
                    title={`Month ${idx + 1}: ${cnt} books`}
                  />
                  <span className="text-[9px] text-tan">{MONTH_NAMES[idx]}</span>
                </div>
              );
            })}
          </div>
        </div>
      </section>

      {/* The Visual Tree Bookshelf */}
      <TreeBookshelf
        items={userShelf}
        userId={profileUser.id}
        isOwnProfile={isOwnProfile}
        onSelectBook={onSelectBook}
        onUpdateProgress={handleUpdateProgress}
        onOpenPdfReader={(bookId) => setReaderModalBookId(bookId)}
        onExploreMore={onExploreBooks}
      />

      {/* PDF Modal if triggered from shelf */}
      {bookForModal && (
        <PdfReaderModal
          book={bookForModal}
          isOpen={Boolean(readerModalBookId)}
          onClose={() => {
            setReaderModalBookId(null);
            reloadProfile();
          }}
          onHighlightCreated={reloadProfile}
        />
      )}
    </div>
  );
};
