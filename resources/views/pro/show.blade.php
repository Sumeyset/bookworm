<x-app-layout>
    <x-slot name="title">Bookworm Pro</x-slot>

    <div class="page-section space-y-8">
        <section class="pro-hero">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[#F2C38B]">✦ Bookworm Pro</p>
            <h1 class="mt-2 font-display text-5xl font-bold leading-none sm:text-7xl">More of the good stuff</h1>
            <p class="mx-auto mt-4 max-w-xl text-[#F1D9C4]">Pro helps keep Bookworm running for everyone — and gives you a few lovely extras.</p>
            @if ($welcome && $pro)
                <p class="mx-auto mt-6 max-w-md rounded-2xl bg-white/10 px-4 py-3 font-semibold">Welcome to Pro! Thank you for supporting Bookworm. 💛</p>
            @endif
        </section>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['🪱', 'Barnaby, all day', \App\Support\Pro::BARNABY_PER_DAY . ' questions a day instead of ' . config('barnaby.per_day') . '.'],
                ['🌰', '25% more acorns', 'Every book, page and streak earns a quarter more for the Tree shop.'],
                ['🧊', 'More streak freezes', 'Hold up to ' . \App\Support\Pro::FREEZES . ' freezes instead of ' . \App\Support\Streak::MAX_FREEZES . '.'],
                ['✦', 'A Pro badge', 'A little gold mark on your profile.'],
            ] as [$emoji, $title, $text])
                <div class="bw-card p-5">
                    <p class="text-3xl" aria-hidden="true">{{ $emoji }}</p>
                    <h2 class="mt-2 font-serif text-xl font-semibold text-ink dark:text-[#f4e9e2]">{{ $title }}</h2>
                    <p class="mt-1 text-sm text-walnut dark:text-[#d4c2b9]">{{ $text }}</p>
                </div>
            @endforeach
        </div>

        <section class="bw-card mx-auto max-w-xl p-6 text-center">
            @if ($pro)
                <p class="font-serif text-2xl font-semibold text-ink dark:text-[#f4e9e2]">You’re Pro ✦</p>
                <p class="mt-1 text-sm text-tan">Until {{ auth()->user()->pro_until->format('j F Y') }}{{ auth()->user()->stripe_subscription_id ? ' — renews automatically' : '' }}.</p>
                @if ($configured && auth()->user()->stripe_customer_id)
                    <form method="POST" action="{{ route('pro.portal') }}" class="mt-4">@csrf<button class="btn-ghost">Manage subscription</button></form>
                @endif
            @elseif ($configured)
                <p class="font-serif text-2xl font-semibold text-ink dark:text-[#f4e9e2]">Choose your plan</p>
                <div class="mt-4 flex flex-wrap justify-center gap-3">
                    <form method="POST" action="{{ route('pro.checkout') }}">@csrf<input type="hidden" name="plan" value="monthly"><button class="btn-primary">Monthly</button></form>
                    @if ($hasYearly)
                        <form method="POST" action="{{ route('pro.checkout') }}">@csrf<input type="hidden" name="plan" value="yearly"><button class="btn-primary">Yearly · best value</button></form>
                    @endif
                </div>
                <p class="mt-3 text-xs text-tan">Secure payment by Stripe. Cancel any time.</p>
            @else
                <p class="font-serif text-2xl font-semibold text-ink dark:text-[#f4e9e2]">Coming soon</p>
                <p class="mt-1 text-sm text-tan">Payments aren’t switched on yet.</p>
            @endif
        </section>
    </div>
</x-app-layout>
