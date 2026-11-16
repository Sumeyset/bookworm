<x-app-layout>
    <x-slot name="title">Admin · Bookworm</x-slot>
    <x-slot name="header">
        <x-page-header eyebrow="Admin" title="Dashboard" subtitle="How Bookworm is doing: readers, activity, reports and the server." />
    </x-slot>

    <div class="page-section space-y-6">
        @if (session('status'))
            <p class="rounded-2xl bg-sage/15 px-4 py-3 text-sm font-semibold text-ink dark:text-[#F4E9E2]" role="status">{{ session('status') }}</p>
        @endif

        <section class="admin-stats" aria-label="Key numbers">
            @foreach ($stats as [$label, $value, $note])
                <div class="bw-card admin-stat">
                    <p class="admin-stat__label">{{ $label }}</p>
                    <p class="admin-stat__value">{{ number_format($value) }}</p>
                    <p class="admin-stat__note">{{ $note }}</p>
                </div>
            @endforeach
        </section>

        <div class="grid gap-6 lg:grid-cols-[1.4fr_1fr]">
            <section class="bw-card flex flex-col p-5" aria-labelledby="chart-title">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h2 id="chart-title" class="font-serif text-xl font-semibold text-ink dark:text-[#F4E9E2]">Last 14 days</h2>
                    <p class="admin-legend"><span class="admin-legend__dot admin-legend__dot--signups"></span>New readers <span class="admin-legend__dot admin-legend__dot--readers"></span>Readers who logged reading</p>
                </div>
                <div class="admin-chart" role="img" aria-label="Daily new readers and active readers over the last 14 days">
                    @foreach ($chart as $day)
                        <div class="admin-chart__day" title="{{ $day['label'] }}: {{ $day['signups'] }} new, {{ $day['readers'] }} reading">
                            <div class="admin-chart__bars">
                                <span class="admin-chart__bar admin-chart__bar--signups" style="height: {{ round($day['signups'] / $chartMax * 100) }}%"></span>
                                <span class="admin-chart__bar admin-chart__bar--readers" style="height: {{ round($day['readers'] / $chartMax * 100) }}%"></span>
                            </div>
                            <span class="admin-chart__label">{{ $day['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="bw-card p-5" aria-labelledby="health-title">
                <h2 id="health-title" class="font-serif text-xl font-semibold text-ink dark:text-[#F4E9E2]">Server health</h2>
                <ul class="mt-3 divide-y divide-warmborder/60 dark:divide-white/10">
                    @foreach ($health as [$label, $ok, $detail])
                        <li class="flex items-center justify-between gap-3 py-2 text-sm">
                            <span class="flex items-center gap-2 font-semibold text-ink dark:text-[#F4E9E2]"><span class="admin-dot {{ $ok ? 'admin-dot--ok' : 'admin-dot--warn' }}" aria-hidden="true"></span>{{ $label }}</span>
                            <span class="truncate text-right text-walnut dark:text-[#d4c2b9]">{{ $ok ? '' : '⚠ ' }}{{ $detail }}</span>
                        </li>
                    @endforeach
                </ul>
            </section>
        </div>

        <section class="bw-card p-5" aria-labelledby="users-title">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 id="users-title" class="font-serif text-xl font-semibold text-ink dark:text-[#F4E9E2]">{{ $search !== '' ? 'Readers matching “' . $search . '”' : 'Newest readers' }}</h2>
                <div class="flex flex-wrap items-center gap-2">
                    <form method="GET" action="{{ route('admin.index') }}" role="search">
                        <label for="admin-q" class="sr-only">Find a reader</label>
                        <input id="admin-q" name="q" value="{{ $search }}" placeholder="Name, username or email" class="bw-input min-h-0 py-2 text-sm">
                    </form>
                    <a href="{{ route('admin.reports') }}" class="btn-ghost min-h-0 py-2">Reports →</a>
                </div>
            </div>
            <div class="mt-4 overflow-x-auto">
                <table class="admin-table">
                    <thead><tr><th>Reader</th><th>Email</th><th>Joined</th><th>Status</th><th><span class="sr-only">Actions</span></th></tr></thead>
                    <tbody>
                        @forelse ($users as $u)
                            <tr>
                                <td><a href="{{ route('users.show', $u->username) }}" class="font-semibold text-burgundy dark:text-[#F0A8B4]">{{ $u->name }}</a> <span class="text-tan">{{ '@' . $u->username }}</span></td>
                                <td>{{ $u->email }}@if (! $u->email_verified_at) <span class="text-tan">(unverified)</span>@endif</td>
                                <td>{{ $u->created_at?->diffForHumans() }}</td>
                                <td>
                                    @if ($u->is_admin) <span class="bw-chip">Admin</span> @endif
                                    @if ($u->isPro()) <span class="pro-badge">Pro</span> @endif
                                    @if ($u->suspended_at) <span class="bw-chip !text-red-700 dark:!text-red-300">Suspended</span> @endif
                                </td>
                                <td class="text-right">
                                    @unless ($u->is_admin)
                                        <form method="POST" action="{{ route('admin.users.suspend', $u) }}" onsubmit="return confirm('{{ $u->suspended_at ? 'Lift the suspension?' : 'Suspend this account?' }}')">@csrf
                                            <button class="btn-ghost min-h-0 py-1.5 text-xs">{{ $u->suspended_at ? 'Unsuspend' : 'Suspend' }}</button>
                                        </form>
                                    @endunless
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-walnut">No readers found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
