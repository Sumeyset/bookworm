import React, { useState, useEffect } from 'react';
import { User } from '../types';
import { StorageService } from '../services/storage';
import { useAuth } from '../context/AuthContext';
import { Search, UserCheck, UserPlus, Users } from 'lucide-react';

interface ReadersDirectoryProps {
  onViewReaderProfile: (username: string) => void;
}

export const ReadersDirectory: React.FC<ReadersDirectoryProps> = ({ onViewReaderProfile }) => {
  const { user: currentUser } = useAuth();
  const [readers, setReaders] = useState<User[]>([]);
  const [searchQuery, setSearchQuery] = useState('');

  const reloadReaders = () => {
    const all = StorageService.getUsers();
    setReaders(all);
  };

  useEffect(() => {
    reloadReaders();
  }, [currentUser]);

  const filteredReaders = readers.filter((r) => {
    if (!searchQuery.trim()) return true;
    const q = searchQuery.toLowerCase();
    return r.name.toLowerCase().includes(q) || r.username.toLowerCase().includes(q);
  });

  const handleToggleFollow = (targetId: string) => {
    if (!currentUser) return;
    StorageService.toggleFollow(currentUser.id, targetId);
    reloadReaders();
  };

  return (
    <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 sm:py-12 space-y-8">
      {/* Directory Header */}
      <div className="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 border-b border-warmborder pb-6 dark:border-[#4B3035]">
        <div>
          <p className="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-tan dark:text-[#c7a491]">
            <Users className="h-4 w-4 text-gold" />
            <span>Community of Readers</span>
          </p>
          <h1 className="mt-1 font-serif text-3xl sm:text-4xl font-semibold text-ink dark:text-[#f4e9e2]">
            Meet the Readers
          </h1>
          <p className="mt-1 text-sm text-walnut dark:text-[#d4c2b9]">
            Connect with kindred spirits, see what they are reading, and follow their tree bookshelves.
          </p>
        </div>

        {/* Reader Search */}
        <div className="relative max-w-xs w-full">
          <Search className="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-tan" />
          <input
            type="text"
            placeholder="Search readers by name or @handle…"
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            className="w-full rounded-full border border-warmborder bg-paper dark:bg-[#271519] dark:border-[#4B3035] pl-10 pr-4 py-2.5 text-xs text-ink dark:text-[#f4e9e2] placeholder-tan focus:border-burgundy focus:outline-hidden"
          />
        </div>
      </div>

      {/* Readers Grid */}
      <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        {filteredReaders.map((reader) => {
          const isMe = currentUser?.id === reader.id;
          const isFollowing = currentUser
            ? StorageService.isFollowing(currentUser.id, reader.id)
            : false;
          const followers = StorageService.getFollowersCount(reader.id);
          const shelfCount = StorageService.getUserShelf(reader.id).length;

          return (
            <div
              key={reader.id}
              className="rounded-3xl border border-warmborder bg-paper p-6 dark:border-[#4B3035] dark:bg-raised shadow-xs flex flex-col justify-between hover:shadow-md transition"
            >
              <div>
                <div className="flex items-start justify-between gap-4">
                  <div
                    onClick={() => onViewReaderProfile(reader.username)}
                    className="flex items-center gap-3 cursor-pointer"
                  >
                    <div className="h-12 w-12 rounded-full bg-burgundy text-[#FFF8EF] font-serif text-lg font-bold flex items-center justify-center uppercase shadow-xs">
                      {reader.name.charAt(0)}
                    </div>
                    <div>
                      <h3 className="font-serif text-base font-semibold text-ink dark:text-[#f4e9e2] hover:text-burgundy">
                        {reader.name}
                      </h3>
                      <p className="text-xs text-tan">@{reader.username}</p>
                    </div>
                  </div>

                  {!isMe && currentUser && (
                    <button
                      type="button"
                      onClick={() => handleToggleFollow(reader.id)}
                      className={`rounded-full p-2 text-xs transition ${
                        isFollowing
                          ? 'bg-parchment text-walnut dark:bg-[#342427] dark:text-[#d4c2b9]'
                          : 'bg-burgundy text-white hover:bg-burgundy-hover shadow-xs'
                      }`}
                      title={isFollowing ? 'Unfollow' : 'Follow'}
                    >
                      {isFollowing ? <UserCheck className="h-4 w-4" /> : <UserPlus className="h-4 w-4" />}
                    </button>
                  )}
                </div>

                {reader.bio && (
                  <p className="mt-3 font-serif text-xs leading-relaxed text-walnut dark:text-[#d4c2b9] line-clamp-2">
                    {reader.bio}
                  </p>
                )}

                {reader.preferred_genres?.length > 0 && (
                  <div className="mt-3 flex flex-wrap gap-1">
                    {reader.preferred_genres.slice(0, 3).map((g) => (
                      <span
                        key={g}
                        className="rounded-full bg-parchment/60 dark:bg-[#342427] px-2 py-0.5 text-[10px] text-walnut dark:text-[#d4c2b9]"
                      >
                        {g}
                      </span>
                    ))}
                  </div>
                )}
              </div>

              {/* Counts & Profile Link */}
              <div className="mt-5 border-t border-warmborder/70 dark:border-[#4B3035] pt-3 flex items-center justify-between text-xs text-tan">
                <span>{shelfCount} {shelfCount === 1 ? 'book' : 'books'} on shelf</span>
                <button
                  type="button"
                  onClick={() => onViewReaderProfile(reader.username)}
                  className="font-semibold text-burgundy dark:text-gold hover:underline"
                >
                  View tree shelf &rarr;
                </button>
              </div>
            </div>
          );
        })}
      </div>
    </div>
  );
};
