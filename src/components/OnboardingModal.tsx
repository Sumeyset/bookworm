import React, { useState } from 'react';
import { useAuth } from '../context/AuthContext';
import { Sparkles, Check, X } from 'lucide-react';

interface OnboardingModalProps {
  isOpen: boolean;
  onClose: () => void;
}

const AVAILABLE_GENRES = [
  'Classics',
  'Literary Fiction',
  'Poetry',
  'Historical Fiction',
  'Science Fiction',
  'Fantasy',
  'Mystery & Crime',
  'Philosophy',
  'Memoir & Biography',
  'Gothic & Dark Academia',
  'Essays & Culture',
  'Plays & Drama',
];

export const OnboardingModal: React.FC<OnboardingModalProps> = ({ isOpen, onClose }) => {
  const { user, completeOnboarding } = useAuth();
  const [selectedGenres, setSelectedGenres] = useState<string[]>(
    user?.preferred_genres?.length ? user.preferred_genres : ['Classics', 'Literary Fiction']
  );
  const [goal, setGoal] = useState<number>(user?.yearly_reading_goal || 12);

  if (!isOpen) return null;

  const toggleGenre = (genre: string) => {
    if (selectedGenres.includes(genre)) {
      setSelectedGenres(selectedGenres.filter((g) => g !== genre));
    } else {
      setSelectedGenres([...selectedGenres, genre]);
    }
  };

  const handleSave = () => {
    completeOnboarding(selectedGenres, goal);
    onClose();
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

        <div className="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-tan dark:text-[#c7a491]">
          <Sparkles className="h-4 w-4 text-gold" />
          <span>Make this nook yours</span>
        </div>

        <h2 className="mt-2 font-serif text-2xl font-semibold text-ink dark:text-[#F4E9E2]">
          Set your reading intentions
        </h2>
        <p className="mt-1 text-sm text-walnut dark:text-[#d4c2b9]">
          Choose the kinds of stories you love returning to, and pick a gentle goal for the year.
        </p>

        {/* Yearly Goal Picker */}
        <div className="mt-6 rounded-2xl border border-warmborder/80 bg-parchment/50 p-4 dark:border-[#4B3035] dark:bg-[#201719]/50">
          <div className="flex items-center justify-between">
            <label htmlFor="goal-slider" className="text-sm font-semibold text-ink dark:text-[#F4E9E2]">
              Yearly reading goal
            </label>
            <span className="font-serif text-xl font-bold text-burgundy dark:text-gold">
              {goal} {goal === 1 ? 'book' : 'books'}
            </span>
          </div>
          <input
            id="goal-slider"
            type="range"
            min="1"
            max="100"
            value={goal}
            onChange={(e) => setGoal(Number(e.target.value))}
            className="mt-3 w-full accent-burgundy cursor-pointer"
          />
          <div className="mt-1 flex justify-between text-[11px] text-tan">
            <span>A leisurely 4</span>
            <span>A steady 12</span>
            <span>An ambitious 50+</span>
          </div>
        </div>

        {/* Favorite Genres Selection */}
        <div className="mt-6">
          <p className="text-xs font-semibold uppercase tracking-wider text-tan dark:text-[#c7a491]">
            Favorite literary fields
          </p>
          <div className="mt-3 flex flex-wrap gap-2">
            {AVAILABLE_GENRES.map((genre) => {
              const isSelected = selectedGenres.includes(genre);
              return (
                <button
                  key={genre}
                  type="button"
                  onClick={() => toggleGenre(genre)}
                  className={`inline-flex items-center gap-1.5 rounded-full px-3.5 py-1.5 text-xs font-medium transition ${
                    isSelected
                      ? 'bg-burgundy text-[#FFF8EF] shadow-xs'
                      : 'border border-warmborder bg-white dark:bg-[#271519] dark:border-[#4B3035] text-walnut dark:text-[#d4c2b9] hover:border-burgundy/50'
                  }`}
                >
                  {isSelected && <Check className="h-3 w-3" />}
                  <span>{genre}</span>
                </button>
              );
            })}
          </div>
        </div>

        <div className="mt-8 flex justify-end gap-3 border-t border-warmborder/70 dark:border-[#4B3035] pt-5">
          <button
            type="button"
            onClick={onClose}
            className="rounded-full px-4 py-2 text-xs font-semibold text-tan hover:text-ink"
          >
            Skip for now
          </button>
          <button
            type="button"
            onClick={handleSave}
            className="rounded-full bg-burgundy px-6 py-2.5 text-xs font-semibold text-[#FFF8EF] shadow-xs transition hover:bg-burgundy-hover"
          >
            Save preferences
          </button>
        </div>
      </div>
    </div>
  );
};
