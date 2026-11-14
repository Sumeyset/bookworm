<x-app-layout>
    <x-slot name="title">{{ $profile->name }}’s living library · Bookworm</x-slot>

    <div class="page-section space-y-8">
        <section class="flex flex-col items-start gap-5 sm:flex-row sm:items-end sm:justify-between">
            <div class="flex items-center gap-4">
                <x-avatar :src="$profile->profile_photo_url" :name="$profile->name" size="lg" />
                <div>
                    <p class="eyebrow">A Bookworm reader</p>
                    <h1 class="mt-1 font-display text-5xl font-bold leading-none text-ink dark:text-[#F4E9E2] sm:text-6xl">{{ $profile->name }}’s living library</h1>
                    <p class="mt-2 text-walnut dark:text-[#d4c2b9]">{{ $treeBookCount }} {{ Str::plural('book', $treeBookCount) }} on the tree · {{ $readCount }} finished</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('year.show', $profile->username) }}" class="btn-ghost">{{ now()->year }} in books</a>
                @guest
                    <a href="{{ route('register') }}" class="btn-primary">Grow your own tree</a>
                @endguest
            </div>
        </section>
        @if ($profile->bio)
            <p class="max-w-2xl font-serif text-xl italic leading-8 text-walnut dark:text-[#e3d3ca]">{{ $profile->bio }}</p>
        @endif
        @include('profile.partials.tree-bookshelf')
    </div>
</x-app-layout>
