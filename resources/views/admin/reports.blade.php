<x-app-layout>
    <x-slot name="title">Reports · Bookworm admin</x-slot>
    <x-slot name="header">
        <x-page-header eyebrow="Admin" title="Reports" subtitle="What readers flagged. Remove content, suspend accounts that break the rules, or dismiss." />
    </x-slot>

    <div class="page-section space-y-6">
        <nav class="flex flex-wrap gap-2" aria-label="Report status">
            @foreach (['open' => 'Open', 'actioned' => 'Actioned', 'dismissed' => 'Dismissed'] as $key => $label)
                <a href="{{ route('admin.reports', ['status' => $key]) }}" class="bw-chip {{ $status === $key ? '!border-burgundy !bg-burgundy !text-[#FFF8EF]' : '' }}">{{ $label }} · {{ $counts[$key] ?? 0 }}</a>
            @endforeach
        </nav>

        @forelse ($reports as $report)
            <article class="bw-card p-5">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-tan">{{ str_replace('_', ' ', $report->reportable_type) }} · {{ \App\Models\Report::REASONS[$report->reason] ?? $report->reason }} · {{ $report->created_at->diffForHumans() }}</p>
                        <p class="mt-1 text-sm text-walnut dark:text-[#d4c2b9]">
                            Reported by <b>{{ $report->reporter?->name ?? 'a deleted account' }}</b>
                            @if ($report->reportedUser) · about <a href="{{ route('users.show', $report->reportedUser->username) }}" class="font-semibold text-burgundy underline dark:text-[#F0A8B4]">{{ '@' . $report->reportedUser->username }}</a>{{ $report->reportedUser->suspended_at ? ' (suspended)' : '' }}@endif
                        </p>
                    </div>
                </div>
                @if ($report->excerpt)
                    <blockquote class="mt-3 rounded-2xl bg-parchment/70 px-4 py-3 text-sm text-ink dark:bg-white/5 dark:text-[#F4E9E2]">{{ $report->excerpt }}</blockquote>
                @endif
                @if ($report->details)
                    <p class="mt-2 text-sm text-walnut dark:text-[#d4c2b9]"><b>Reporter says:</b> {{ $report->details }}</p>
                @endif
                @if ($report->status === 'open')
                    <div class="mt-4 flex flex-wrap gap-2">
                        @if ($report->reportable_type !== 'user')
                            <form method="POST" action="{{ route('admin.reports.resolve', $report) }}" onsubmit="return confirm('Remove this content?')">@csrf<input type="hidden" name="action" value="remove"><button class="btn-primary min-h-0 py-2">Remove content</button></form>
                        @endif
                        @if ($report->reportedUser && ! $report->reportedUser->suspended_at)
                            <form method="POST" action="{{ route('admin.reports.resolve', $report) }}" onsubmit="return confirm('Suspend this account?')">@csrf<input type="hidden" name="action" value="suspend"><button class="btn-ghost min-h-0 py-2 !text-red-700 dark:!text-red-300">Suspend account</button></form>
                        @endif
                        <form method="POST" action="{{ route('admin.reports.resolve', $report) }}">@csrf<input type="hidden" name="action" value="dismiss"><button class="btn-ghost min-h-0 py-2">Dismiss</button></form>
                    </div>
                @elseif ($report->reportedUser?->suspended_at)
                    <form method="POST" action="{{ route('admin.reports.resolve', $report) }}" class="mt-4">@csrf<input type="hidden" name="action" value="unsuspend"><button class="btn-ghost min-h-0 py-2">Lift suspension</button></form>
                @endif
            </article>
        @empty
            <p class="text-walnut dark:text-[#d4c2b9]">Nothing here. 🌿</p>
        @endforelse

        {{ $reports->links() }}
    </div>
</x-app-layout>
