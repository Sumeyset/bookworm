<x-app-layout>
    <x-slot name="title">Reading challenges · Bookworm</x-slot>
    <x-slot name="header">
        <x-page-header eyebrow="{{ now()->format('F Y') }}" title="Reading challenges" subtitle="Four new challenges every month. Finish one to earn acorns and a badge for your profile.">
            <x-slot name="actions">
                <a href="{{ route('shop.index') }}" class="btn-ghost">🌰 {{ number_format(auth()->user()->acorns) }} acorns · Tree shop</a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="page-section space-y-10">
        <section aria-labelledby="this-month">
            <div class="mb-4 flex flex-wrap items-baseline justify-between gap-2">
                <h2 id="this-month" class="font-display text-3xl font-bold text-ink dark:text-[#f4e9e2]">This month</h2>
                <p class="text-sm text-tan">{{ $daysLeft }} {{ Str::plural('day', $daysLeft) }} left</p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach ($challenges as $c)
                    @php $pct = (int) round($c['progress'] / max(1, $c['goal']) * 100); @endphp
                    <article class="challenge-card {{ $c['done'] ? 'is-done' : '' }}">
                        <div class="flex items-start gap-4">
                            <span class="challenge-card__emoji" aria-hidden="true">{{ $c['emoji'] }}</span>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-2">
                                    <h3 class="font-serif text-xl font-semibold text-ink dark:text-[#f4e9e2]">{{ $c['name'] }}</h3>
                                    <span class="shrink-0 rounded-full bg-[#fff1d6] px-2.5 py-0.5 text-xs font-semibold text-[#8a5a12] dark:bg-white/10 dark:text-[#f2c38b]">🌰 {{ $c['acorns'] }}</span>
                                </div>
                                <p class="mt-1 text-sm text-walnut dark:text-[#d4c2b9]">{{ $c['description'] }}</p>
                                <div class="challenge-card__bar mt-3" role="progressbar" aria-valuemin="0" aria-valuemax="{{ $c['goal'] }}" aria-valuenow="{{ $c['progress'] }}" aria-label="{{ $c['name'] }} progress">
                                    <span style="width: {{ $pct }}%"></span>
                                </div>
                                <p class="mt-1.5 text-xs font-semibold text-tan">
                                    @if ($c['done'])
                                        ✓ Done — badge earned
                                    @else
                                        {{ number_format($c['progress']) }} / {{ number_format($c['goal']) }}
                                    @endif
                                </p>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
            <p class="mt-4 text-sm text-tan">Next month: {{ $nextMonth->map(fn ($c) => $c[1] . ' ' . $c[0])->implode(' · ') }}</p>
        </section>

        <section aria-labelledby="milestones">
            <h2 id="milestones" class="mb-4 font-display text-3xl font-bold text-ink dark:text-[#f4e9e2]">Milestones</h2>
            <ul class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                @foreach ($milestones as $m)
                    <li class="badge-tile {{ $m['earned'] ? '' : 'is-locked' }}" title="{{ $m['description'] }}">
                        <span class="badge-tile__emoji" aria-hidden="true">{{ $m['emoji'] }}</span>
                        <span class="badge-tile__name">{{ $m['name'] }}</span>
                        <span class="badge-tile__desc">{{ $m['earned'] ? $m['description'] : 'Locked · ' . $m['description'] }}</span>
                    </li>
                @endforeach
            </ul>
        </section>

        <section aria-labelledby="past">
            <h2 id="past" class="mb-4 font-display text-3xl font-bold text-ink dark:text-[#f4e9e2]">Challenge badges</h2>
            @if ($badges->isEmpty())
                <p class="text-walnut dark:text-[#d4c2b9]">Your challenge badges will collect here. Start with one from this month!</p>
            @else
                <ul class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                    @foreach ($badges as $b)
                        <li class="badge-tile" title="{{ $b->description }}">
                            <span class="badge-tile__emoji" aria-hidden="true">{{ $b->emoji }}</span>
                            <span class="badge-tile__name">{{ $b->name }}</span>
                            <span class="badge-tile__desc">{{ $b->created_at->format('F Y') }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
</x-app-layout>
