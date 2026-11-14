@props(['reader'])

<article {{ $attributes->class(['bw-card group relative flex min-w-0 items-center gap-4 p-4 transition duration-200 hover:-translate-y-0.5 hover:shadow-[0_24px_40px_-24px_rgba(74,18,32,0.45)]']) }}>
    <x-avatar :src="$reader->profile_photo_url" :name="$reader->name" size="reader" />
    <div class="min-w-0 flex-1">
        <a href="{{ route('users.show', $reader->username) }}" class="focus-ring block truncate font-serif text-lg font-semibold text-ink after:absolute after:inset-0 after:rounded-3xl group-hover:text-burgundy dark:text-[#f4e9e2] dark:group-hover:text-[#E9A2AE]">{{ $reader->name }}</a>
        <p class="truncate text-sm text-tan dark:text-[#c1aaa0]">{{ '@' . $reader->username }}</p>
        @isset($reader->reviews_count)
            <p class="mt-1 text-xs text-tan/90 dark:text-[#b39d93]">
                @isset($reader->followers_count){{ $reader->followers_count }} {{ Str::plural('follower', $reader->followers_count) }} <span aria-hidden="true">&middot;</span> @endisset
                {{ $reader->reviews_count }} {{ Str::plural('review', $reader->reviews_count) }}
            </p>
        @endisset
    </div>
    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-parchment text-burgundy transition group-hover:bg-burgundy group-hover:text-[#FFF8EF] dark:bg-white/10 dark:text-[#E9A2AE]" aria-hidden="true">&rarr;</span>
</article>
