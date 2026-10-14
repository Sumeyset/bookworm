@props(['value' => 0, 'name' => 'rating', 'submitOnSelect' => false, 'compact' => false])

<div x-data="{ rating: {{ (float) $value }}, hover: 0, submitOnSelect: @js($submitOnSelect) }"
     class="flex flex-col items-center gap-1">
    <input type="hidden" name="{{ $name }}" :value="rating">

    <div class="flex items-center {{ $compact ? 'gap-0' : 'gap-1' }}" @mouseleave="hover = 0">
        @for ($i = 1; $i <= 5; $i++)
            <div class="relative {{ $compact ? 'h-6 w-6' : 'h-8 w-8' }}">
                <svg class="absolute inset-0 h-full w-full text-[#d8c9b9] dark:text-[#5b4b4a]"
                     fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path d="M9.05 2.93c.3-.92 1.6-.92 1.9 0l1.07 3.29a1 1 0 00.95.69h3.46c.97 0 1.37 1.24.59 1.81l-2.8 2.03a1 1 0 00-.36 1.12l1.07 3.29c.3.92-.76 1.69-1.54 1.12l-2.8-2.03a1 1 0 00-1.18 0l-2.8 2.03c-.78.57-1.84-.2-1.54-1.12l1.07-3.29a1 1 0 00-.36-1.12L2.98 8.72c-.78-.57-.38-1.81.59-1.81h3.46a1 1 0 00.95-.69l1.07-3.29z"/>
                </svg>
                <svg class="absolute inset-0 h-full w-full overflow-hidden text-[#c58a35] transition-colors"
                     :style="{ clipPath: 'inset(0 ' + ((hover || rating) >= {{ $i }} ? 0 : ((hover || rating) >= {{ $i }} - 0.5 ? 50 : 100)) + '% 0 0)' }"
                     fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path d="M9.05 2.93c.3-.92 1.6-.92 1.9 0l1.07 3.29a1 1 0 00.95.69h3.46c.97 0 1.37 1.24.59 1.81l-2.8 2.03a1 1 0 00-.36 1.12l1.07 3.29c.3.92-.76 1.69-1.54 1.12l-2.8-2.03a1 1 0 00-1.18 0l-2.8 2.03c-.78.57-1.84-.2-1.54-1.12l1.07-3.29a1 1 0 00-.36-1.12L2.98 8.72c-.78-.57-.38-1.81.59-1.81h3.46a1 1 0 00.95-.69l1.07-3.29z"/>
                </svg>
                <button type="button"
                        @click="rating = {{ $i }} - 0.5; if (submitOnSelect) $nextTick(() => $el.closest('form').requestSubmit())"
                        @mouseenter="hover = {{ $i }} - 0.5"
                        class="absolute inset-y-0 left-0 z-10 w-1/2 rounded-l focus:outline-none focus-visible:ring-2 focus-visible:ring-[#9b5b54]"
                        aria-label="Rate {{ $i - 0.5 }} stars"></button>
                <button type="button"
                        @click="rating = {{ $i }}; if (submitOnSelect) $nextTick(() => $el.closest('form').requestSubmit())"
                        @mouseenter="hover = {{ $i }}"
                        class="absolute inset-y-0 right-0 z-10 w-1/2 rounded-r focus:outline-none focus-visible:ring-2 focus-visible:ring-[#9b5b54]"
                        aria-label="Rate {{ $i }} stars"></button>
            </div>
        @endfor
    </div>
    <span class="text-xs font-medium text-[#8a7065] dark:text-[#c1aaa0]"
          x-text="rating ? rating + ' / 5' : 'Choose a rating'"
          aria-live="polite"></span>
</div>
