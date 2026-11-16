<x-app-layout>
    <x-slot name="title">Tree shop · Bookworm</x-slot>
    <x-slot name="header">
        <x-page-header eyebrow="Spend what you’ve read" title="Tree shop" subtitle="Every book you read earns acorns. Spend them on decorations for your shelves — they show on every shelf style, and you can switch them on or off any time.">
            <x-slot name="actions">
                <a href="{{ route('users.show', auth()->user()->username) }}#bookshelf" class="btn-ghost">See my tree &rarr;</a>
            </x-slot>
        </x-page-header>
    </x-slot>


    <div class="page-section grid gap-8 lg:grid-cols-[minmax(0,1fr)_20rem]">
        <div class="min-w-0 space-y-6">

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach (\App\Support\Acorns::ITEMS as $key => [$name, $emoji, $price, $description, $window])
                    @php
                        $has = $owned->has($key);
                        $on = $has && $owned[$key];
                        $sale = \App\Support\Acorns::onSale($key);
                        $short = max(0, $price - $balance);
                    @endphp
                    <article class="shop-item {{ $has ? 'is-owned' : '' }} {{ $on ? 'is-on' : '' }}">
                        <div class="shop-item__art" aria-hidden="true"><span>{{ $emoji }}</span></div>
                        <div class="flex items-start justify-between gap-2">
                            <h2 class="font-serif text-lg font-semibold text-ink dark:text-[#f4e9e2]">{{ $name }}</h2>
                            @if ($window)
                                <span class="shrink-0 rounded-full bg-[#fff1d6] px-2 py-0.5 text-[11px] font-semibold text-[#8a5a12] dark:bg-white/10 dark:text-[#f2c38b]">{{ $sale ? 'Seasonal' : 'Out of season' }}</span>
                            @endif
                        </div>
                        <p class="mt-1 flex-1 text-sm text-walnut dark:text-[#d4c2b9]">{{ $description }}</p>

                        @if ($has)
                            <form method="POST" action="{{ route('shop.toggle', $key) }}" class="mt-4">
                                @csrf
                                <button class="shop-toggle" role="switch" aria-checked="{{ $on ? 'true' : 'false' }}">
                                    <span class="shop-toggle__track"><span class="shop-toggle__dot"></span></span>
                                    {{ $on ? 'On your tree' : 'Put away' }}
                                </button>
                            </form>
                        @elseif (! $sale)
                            <p class="mt-4 text-sm font-semibold text-tan">Back in season soon · 🌰 {{ $price }}</p>
                        @else
                            <form method="POST" action="{{ route('shop.buy', $key) }}" class="mt-4">
                                @csrf
                                <button class="btn-primary w-full min-h-0 py-2" @disabled($short > 0)>
                                    {{ $short > 0 ? $short . ' more acorns needed' : 'Buy for 🌰 ' . $price }}
                                </button>
                            </form>
                        @endif
                        @if ($has && $gifts->has($key))
                            <p class="mt-2 text-xs font-semibold text-tan">🎁 A gift from {{ $gifts[$key]->giver?->name ?? 'a friend' }}</p>
                        @endif
                        @if ($sale && $friends->isNotEmpty())
                            <details class="shop-gift mt-3">
                                <summary>🎁 Gift to a friend</summary>
                                <form method="POST" action="{{ route('shop.gift', $key) }}" class="mt-2 flex gap-2">
                                    @csrf
                                    <label for="gift-{{ $key }}" class="sr-only">Friend</label>
                                    <select id="gift-{{ $key }}" name="friend_id" required class="bw-input min-w-0 flex-1 py-1.5 text-sm">
                                        <option value="">Choose a friend…</option>
                                        @foreach ($friends as $friend)
                                            <option value="{{ $friend->id }}">{{ $friend->name }}</option>
                                        @endforeach
                                    </select>
                                    <button class="btn-ghost min-h-0 shrink-0 py-1.5 text-sm" @disabled($balance < $price)>🌰 {{ $price }}</button>
                                </form>
                            </details>
                        @endif
                    </article>
                @endforeach
            </div>
        </div>

        <aside class="space-y-5 lg:sticky lg:top-28 lg:self-start">
            <div class="rounded-3xl bg-gradient-to-br from-[#5a3a1e] via-[#7a4f2a] to-[#a8743c] p-5 text-[#fff8ef] shadow-bookplate">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#f2d49a]">Your acorns</p>
                <p class="mt-1 font-display text-6xl font-bold leading-none">🌰 {{ number_format($balance) }}</p>
                <p class="mt-2 text-sm text-[#f1d9c4]">{{ $streak > 0 ? '🔥 ' . $streak . '-day reading streak' : 'Read today to start a streak.' }}</p>
            </div>

            <div id="streak-freeze" class="bw-card scroll-mt-28 p-5">
                <div class="flex items-start gap-3">
                    <span class="text-3xl" aria-hidden="true">🧊</span>
                    <div class="min-w-0">
                        <h2 class="font-serif text-xl font-semibold text-ink dark:text-[#f4e9e2]">Streak freeze</h2>
                        <p class="mt-1 text-sm text-walnut dark:text-[#d4c2b9]">Covers one day you miss, all by itself, so your streak keeps going. Hold up to {{ \App\Support\Streak::MAX_FREEZES }}.</p>
                    </div>
                </div>
                @php $freezes = (int) auth()->user()->streak_freezes; @endphp
                <p class="mt-3 text-sm font-semibold text-tan">You have {{ $freezes }} of {{ \App\Support\Streak::MAX_FREEZES }}</p>
                <form method="POST" action="{{ route('shop.freeze') }}" class="mt-3">
                    @csrf
                    <button class="btn-primary w-full min-h-0 py-2" @disabled($freezes >= \App\Support\Streak::MAX_FREEZES || $balance < \App\Support\Streak::FREEZE_PRICE)>
                        {{ $freezes >= \App\Support\Streak::MAX_FREEZES ? 'You’re all set' : 'Buy for 🌰 ' . \App\Support\Streak::FREEZE_PRICE }}
                    </button>
                </form>
                <a href="{{ route('profile.edit') }}" class="mt-2 block text-center text-xs font-semibold text-tan underline underline-offset-2">Streak reminders</a>
            </div>

            <div class="bw-card p-5">
                <h2 class="font-serif text-xl font-semibold text-ink dark:text-[#f4e9e2]">How to earn</h2>
                <ul class="mt-3 space-y-2 text-sm text-walnut dark:text-[#d4c2b9]">
                    <li class="flex justify-between gap-3"><span>📖 Finish a book</span><b>50 + up to 50</b></li>
                    <li class="flex justify-between gap-3"><span>🔖 Read 10 pages</span><b>1 <span class="font-normal text-tan">(30/day)</span></b></li>
                    <li class="flex justify-between gap-3"><span>☀️ Read on a day</span><b>5</b></li>
                    <li class="flex justify-between gap-3"><span>🔥 Every 7-day streak</span><b>25</b></li>
                    <li class="flex justify-between gap-3"><span>✍️ Write a review</span><b>15</b></li>
                    <li class="flex justify-between gap-3"><span>🎯 Halfway / full yearly goal</span><b>100 / 250</b></li>
                </ul>
                <p class="mt-3 text-xs text-tan">Longer books earn more (1 extra per 20 pages). Each book pays once, up to 3 finished books a day. Imported books don’t count.</p>
            </div>

            <div class="bw-card p-5">
                <h2 class="font-serif text-xl font-semibold text-ink dark:text-[#f4e9e2]">Recent acorns</h2>
                @forelse ($history as $event)
                    <p class="mt-2 flex items-baseline justify-between gap-3 text-sm">
                        <span class="min-w-0 truncate text-walnut dark:text-[#d4c2b9]">{{ ucfirst($event->note ?? $event->reason) }}</span>
                        <b class="shrink-0 {{ $event->amount < 0 ? 'text-tan' : 'text-[#4f7a46] dark:text-[#9fd3a8]' }}">{{ $event->amount > 0 ? '+' : '' }}{{ $event->amount }}</b>
                    </p>
                @empty
                    <p class="mt-2 text-sm text-tan">Nothing yet — move your bookmark or finish a book!</p>
                @endforelse
            </div>
        </aside>
    </div>
</x-app-layout>
