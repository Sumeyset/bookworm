<x-app-layout>
    <x-slot name="header">
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-tan dark:text-[#c7a491]">Notes from your reading circle</p>
        <h1 class="mt-1 font-serif text-3xl font-semibold text-ink dark:text-[#F4E9E2]">Reader activity</h1>
    </x-slot>

    <div class="mx-auto max-w-3xl space-y-5 px-4 py-8 sm:px-6">
        <p class="text-sm leading-6 text-walnut dark:text-[#d4c2b9]">Fresh reviews from the readers you follow. Good recommendations tend to travel.</p>
        @forelse ($reviews as $review)
            <article class="bookplate rounded-xl p-5 sm:p-6">
                <div class="flex items-start gap-3">
                    <x-avatar :src="$review->user->profile_photo_url" :name="$review->user->name" size="reader" />
                    <div class="min-w-0 flex-1">
                        <p class="text-sm text-tan dark:text-[#c7a491]">
                            <a href="{{ route('users.show', $review->user->username) }}" class="focus-ring rounded-sm font-semibold text-burgundy hover:underline dark:text-burgundy-dark">{{ $review->user->name }}</a>
                            {{ $review->body ? 'left a note about' : 'rated' }}
                            <span class="text-xs">{{ $review->created_at->diffForHumans() }}</span>
                        </p>
                        <div class="mt-3 flex gap-4">
                            @if ($review->book->cover_url)
                                <img src="{{ $review->book->cover_url }}" alt="Cover of {{ $review->book->title }}" loading="lazy" class="aspect-[2/3] w-16 shrink-0 rounded-md object-cover shadow-sm">
                            @else
                                <div class="flex aspect-[2/3] w-16 shrink-0 items-center justify-center rounded-md bg-parchment p-1 text-center font-serif text-xs text-walnut dark:bg-[#382329] dark:text-[#F4E9E2]">{{ $review->book->title }}</div>
                            @endif
                            <div class="min-w-0">
                                <h2 class="font-serif text-xl font-semibold text-ink dark:text-[#F4E9E2]"><a href="{{ route('books.show', $review->book) }}" class="focus-ring rounded-sm hover:text-burgundy dark:hover:text-burgundy-dark">{{ $review->book->title }}</a></h2>
                                <p class="mt-1 text-sm text-tan dark:text-[#c7a491]">{{ $review->book->author_name ?? 'Unknown author' }}</p>
                                <div class="mt-2"><x-star-display :rating="$review->rating" /></div>
                                @if ($review->body)
                                    <p class="mt-3 whitespace-pre-line break-words font-serif text-base italic leading-7 text-walnut dark:text-[#e3d3ca]">{{ $review->body }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </article>
        @empty
            <x-empty-state title="Your reading circle is quiet" message="Follow a few readers to see their latest reviews here.">
                <a href="{{ route('users.index') }}" class="focus-ring inline-flex rounded-full bg-burgundy px-5 py-2.5 text-sm font-semibold text-white hover:bg-burgundy-hover">Find readers</a>
            </x-empty-state>
        @endforelse
        @if ($reviews->hasPages())
            <div>{{ $reviews->links() }}</div>
        @endif
    </div>
</x-app-layout>
