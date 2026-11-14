<x-app-layout>
    <x-slot name="title">{{ $item->user->name }} on Bookworm</x-slot>

    <div class="page-section">
        <div class="mx-auto max-w-[30rem] space-y-4">
            <a href="{{ route('feed.index') }}" class="btn-ghost min-h-0 py-2"><span aria-hidden="true">&larr;</span> Back to the feed</a>
            @include('feed.partials.card', ['item' => $item])
        </div>
    </div>
</x-app-layout>
