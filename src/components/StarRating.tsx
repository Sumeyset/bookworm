import React, { useState } from 'react';
import { Star } from 'lucide-react';

interface StarRatingProps {
  rating: number; // 0 to 5, can be 0.5 increments
  maxStars?: number;
  interactive?: boolean;
  onRate?: (rating: number) => void;
  size?: 'sm' | 'md' | 'lg';
  showLabel?: boolean;
}

export const StarRating: React.FC<StarRatingProps> = ({
  rating,
  maxStars = 5,
  interactive = false,
  onRate,
  size = 'md',
  showLabel = false,
}) => {
  const [hoverRating, setHoverRating] = useState<number | null>(null);

  const displayRating = hoverRating !== null ? hoverRating : rating;

  const starSizes = {
    sm: 'w-3.5 h-3.5',
    md: 'w-4 h-4',
    lg: 'w-6 h-6',
  };

  return (
    <div className="inline-flex items-center gap-1.5" role="group" aria-label={`Rating: ${rating} out of ${maxStars} stars`}>
      <div className="flex items-center">
        {Array.from({ length: maxStars }).map((_, idx) => {
          const starValue = idx + 1;
          const isFilled = displayRating >= starValue;
          const isHalf = !isFilled && displayRating >= starValue - 0.5;

          return (
            <div
              key={idx}
              className={`relative ${interactive ? 'cursor-pointer p-0.5' : ''}`}
              onMouseLeave={() => interactive && setHoverRating(null)}
            >
              {/* Left half button for 0.5 rating */}
              {interactive && (
                <>
                  <button
                    type="button"
                    aria-label={`${starValue - 0.5} stars`}
                    className="absolute left-0 top-0 w-1/2 h-full z-10"
                    onMouseEnter={() => setHoverRating(starValue - 0.5)}
                    onClick={() => onRate && onRate(starValue - 0.5)}
                  />
                  <button
                    type="button"
                    aria-label={`${starValue} stars`}
                    className="absolute right-0 top-0 w-1/2 h-full z-10"
                    onMouseEnter={() => setHoverRating(starValue)}
                    onClick={() => onRate && onRate(starValue)}
                  />
                </>
              )}

              <div className="relative">
                {/* Background empty star */}
                <Star className={`${starSizes[size]} text-[#d4c3b3] dark:text-[#523d40] stroke-current`} />

                {/* Filled overlay */}
                {isFilled && (
                  <Star className={`absolute inset-0 ${starSizes[size]} text-gold fill-gold stroke-gold`} />
                )}

                {/* Half star overlay */}
                {isHalf && (
                  <div className="absolute inset-0 overflow-hidden w-1/2">
                    <Star className={`${starSizes[size]} text-gold fill-gold stroke-gold`} />
                  </div>
                )}
              </div>
            </div>
          );
        })}
      </div>

      {showLabel && (
        <span className="text-xs font-semibold text-walnut dark:text-[#d4c2b9] ml-1">
          {rating ? rating.toFixed(1) : 'Unrated'}
        </span>
      )}
    </div>
  );
};
