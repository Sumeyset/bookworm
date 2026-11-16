@php
    $sourceName = \App\Support\ReadingImport::SOURCES[$preview['source']] ?? 'your file';
    $labels = ['read' => 'Read', 'reading' => 'Reading now', 'want_to_read' => 'Want to read'];
@endphp
<x-app-layout>
    <x-slot name="title">Check your import · Bookworm</x-slot>
    <x-slot name="header">
        <x-page-header eyebrow="Import · step 2 of 2" title="Looks good?" subtitle="Here’s what we found in {{ $sourceName === 'your spreadsheet' ? 'your spreadsheet' : 'your ' . $sourceName . ' export' }}. Nothing is saved until you press Import." />
    </x-slot>

    <div class="page-section grid gap-8 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <div class="space-y-6">
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                @foreach ([['New books', $preview['new'], '📚'], ['Already on your shelf', $preview['duplicates'], '✓'], ['With ratings', $preview['ratings'], '⭐'], ['With reviews', $preview['reviews'], '✍️']] as [$label, $value, $emoji])
                    <div class="bw-card p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-tan">{{ $emoji }} {{ $label }}</p>
                        <p class="mt-1 font-display text-5xl font-bold leading-none text-ink dark:text-[#F4E9E2]">{{ number_format($value) }}</p>
                    </div>
                @endforeach
            </div>

            <section class="bw-card p-5">
                <h2 class="font-serif text-xl font-semibold text-ink dark:text-[#F4E9E2]">Where they’ll go</h2>
                <ul class="mt-3 grid gap-2 sm:grid-cols-3">
                    @foreach ($preview['statuses'] as $status => $count)
                        <li class="rounded-2xl bg-parchment/70 px-4 py-3 dark:bg-white/5">
                            <span class="block text-sm text-walnut dark:text-[#d4c2b9]">{{ $labels[$status] }}</span>
                            <span class="font-display text-3xl font-bold text-ink dark:text-[#F4E9E2]">{{ $count }}</span>
                        </li>
                    @endforeach
                </ul>
                @if ($preview['skipped'])
                    <p class="mt-3 text-sm text-tan">{{ $preview['skipped'] }} {{ Str::plural('row', $preview['skipped']) }} without a title will be skipped.</p>
                @endif
            </section>

            @if ($preview['sample'])
                <section class="bw-card p-5">
                    <h2 class="font-serif text-xl font-semibold text-ink dark:text-[#F4E9E2]">A few of them</h2>
                    <ul class="mt-3 divide-y divide-dashed divide-warmborder dark:divide-[#4B3035]">
                        @foreach ($preview['sample'] as $book)
                            <li class="flex items-baseline justify-between gap-3 py-2">
                                <span class="min-w-0">
                                    <span class="block truncate font-semibold text-ink dark:text-[#F4E9E2]">{{ $book['title'] }}</span>
                                    @if ($book['author'])<span class="block truncate text-xs text-tan">{{ $book['author'] }}</span>@endif
                                </span>
                                <span class="shrink-0 text-xs text-walnut dark:text-[#d4c2b9]">{{ $labels[$book['status']] }}{{ $book['rating'] ? ' · ' . rtrim(rtrim(number_format($book['rating'], 1), '0'), '.') . '★' : '' }}</span>
                            </li>
                        @endforeach
                    </ul>
                    @if ($preview['new'] > count($preview['sample']))
                        <p class="mt-2 text-sm text-tan">…and {{ $preview['new'] - count($preview['sample']) }} more.</p>
                    @endif
                </section>
            @endif
        </div>

        <aside class="space-y-4 lg:sticky lg:top-28 lg:self-start">
            <div class="bw-card p-5">
                @if ($preview['new'] > 0)
                    <p class="text-sm text-walnut dark:text-[#d4c2b9]">{{ $preview['new'] }} {{ Str::plural('book', $preview['new']) }} will be added to your shelf. Your followers won’t get a notification for each one, and imported books don’t earn acorns.</p>
                    <form method="POST" action="{{ route('import.confirm', $token) }}" class="mt-4">
                        @csrf
                        <button class="btn-primary w-full">Import {{ $preview['new'] }} {{ Str::plural('book', $preview['new']) }}</button>
                    </form>
                @else
                    <p class="text-sm text-walnut dark:text-[#d4c2b9]">Everything in this file is already on your shelf — there’s nothing new to import.</p>
                @endif
                <form method="POST" action="{{ route('import.cancel', $token) }}" class="mt-2">
                    @csrf @method('DELETE')
                    <button class="btn-ghost w-full">Cancel</button>
                </form>
            </div>
        </aside>
    </div>
</x-app-layout>
