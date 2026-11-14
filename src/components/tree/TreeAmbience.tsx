import React from 'react';

export type TreeMood = 'autumn' | 'midnight' | 'spring' | 'cozy';

interface TreeAmbienceProps {
  mood: TreeMood;
  onMoodChange: (mood: TreeMood) => void;
}

export const MOOD_CONFIG: Record<
  TreeMood,
  {
    label: string;
    icon: string;
    filterClass: string;
    overlayGradient: string;
    particleEmoji: string[];
  }
> = {
  autumn: {
    label: 'Golden Autumn',
    icon: '🍂',
    filterClass: 'sepia-[0.15] contrast-[1.05]',
    overlayGradient: 'from-amber-500/10 via-transparent to-orange-950/20',
    particleEmoji: ['🍂', '🍁', '✨'],
  },
  midnight: {
    label: 'Midnight Nook',
    icon: '🌙',
    filterClass: 'brightness-90 contrast-[1.1] hue-rotate-[15deg]',
    overlayGradient: 'from-indigo-950/35 via-blue-900/15 to-purple-950/40',
    particleEmoji: ['✨', '⭐', '🌟'],
  },
  spring: {
    label: 'Spring Blossom',
    icon: '🌸',
    filterClass: 'brightness-[1.02] saturate-[1.15]',
    overlayGradient: 'from-pink-300/15 via-transparent to-emerald-900/15',
    particleEmoji: ['🌸', '🍃', '✨'],
  },
  cozy: {
    label: 'Candlelit Study',
    icon: '🕯️',
    filterClass: 'sepia-[0.25] brightness-95',
    overlayGradient: 'from-amber-600/20 via-yellow-500/5 to-amber-950/30',
    particleEmoji: ['🕯️', '✨', '📖'],
  },
};

export const TreeAmbience: React.FC<TreeAmbienceProps> = ({ mood, onMoodChange }) => {
  return (
    <div className="flex items-center gap-1.5 rounded-full bg-parchment/80 dark:bg-[#201719]/80 border border-warmborder dark:border-[#4B3035] p-1 text-xs shadow-2xs">
      {(Object.keys(MOOD_CONFIG) as TreeMood[]).map((key) => {
        const item = MOOD_CONFIG[key];
        const isActive = mood === key;
        return (
          <button
            key={key}
            type="button"
            onClick={() => onMoodChange(key)}
            className={`flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold transition ${
              isActive
                ? 'bg-burgundy text-[#FFF8EF] shadow-xs scale-105'
                : 'text-walnut dark:text-[#d4c2b9] hover:bg-white/60 dark:hover:bg-[#342427]'
            }`}
            title={`Switch tree ambience to ${item.label}`}
          >
            <span>{item.icon}</span>
            <span className="hidden sm:inline">{item.label}</span>
          </button>
        );
      })}
    </div>
  );
};
