@props(['rating' => 0])

<span class="inline-flex items-center" aria-label="{{ number_format($rating, 1) }} out of 5 stars">
    @for ($i = 1; $i <= 5; $i++)
        <span class="relative inline-block h-5 w-5">
            <svg class="absolute inset-0 h-5 w-5 text-gray-300 dark:text-gray-600"
                 fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path d="M9.05 2.93c.3-.92 1.6-.92 1.9 0l1.07 3.29a1 1 0 00.95.69h3.46c.97 0 1.37 1.24.59 1.81l-2.8 2.03a1 1 0 00-.36 1.12l1.07 3.29c.3.92-.76 1.69-1.54 1.12l-2.8-2.03a1 1 0 00-1.18 0l-2.8 2.03c-.78.57-1.84-.2-1.54-1.12l1.07-3.29a1 1 0 00-.36-1.12L2.98 8.72c-.78-.57-.38-1.81.59-1.81h3.46a1 1 0 00.95-.69l1.07-3.29z"/>
            </svg>
            <span class="absolute inset-y-0 left-0 overflow-hidden text-amber-400"
                  style="width: {{ $rating >= $i ? '100%' : ($rating >= $i - 0.5 ? '50%' : '0%') }}">
                <svg class="h-5 w-5 max-w-none" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path d="M9.05 2.93c.3-.92 1.6-.92 1.9 0l1.07 3.29a1 1 0 00.95.69h3.46c.97 0 1.37 1.24.59 1.81l-2.8 2.03a1 1 0 00-.36 1.12l1.07 3.29c.3.92-.76 1.69-1.54 1.12l-2.8-2.03a1 1 0 00-1.18 0l-2.8 2.03c-.78.57-1.84-.2-1.54-1.12l1.07-3.29a1 1 0 00-.36-1.12L2.98 8.72c-.78-.57-.38-1.81.59-1.81h3.46a1 1 0 00.95-.69l1.07-3.29z"/>
                </svg>
            </span>
        </span>
    @endfor
</span>
