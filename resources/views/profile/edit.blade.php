<x-app-layout>
    <x-slot name="title">Settings · Bookworm</x-slot>
    <x-slot name="header">
        <x-page-header eyebrow="Your account" title="Settings" subtitle="Your photo, bio and sign-in details.">
            <x-slot name="actions">
                <a href="{{ route('users.show', $user->username) }}" class="btn-ghost">View my profile <span aria-hidden="true">&rarr;</span></a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="page-section space-y-6">
        @foreach ([
            ['update-profile-information-form', 'Profile', 'How other readers see you: photo, name and a short bio.', 'M10 10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-7 8a7 7 0 0 1 14 0'],
            ['update-password-form', 'Password', 'Use a long, unique password to keep your shelf safe.', 'M6 9V6a4 4 0 1 1 8 0v3M5 9h10v8H5z'],
            ['delete-user-form', 'Delete account', 'Permanently remove your account, shelf, notes and PDFs.', 'M4 6h12M8 6V4h4v2m-6 0 1 11h6l1-11'],
        ] as [$partial, $heading, $copy, $icon])
            <section class="bw-card grid gap-6 p-6 sm:p-8 lg:grid-cols-[18rem_minmax(0,1fr)] lg:gap-12 {{ $partial === 'delete-user-form' ? 'border-red-200/70 dark:border-red-900/40' : '' }}">
                <div>
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl {{ $partial === 'delete-user-form' ? 'bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-300' : 'bg-burgundy/10 text-burgundy dark:bg-white/10 dark:text-[#E9A2AE]' }}">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $icon }}"/></svg>
                    </span>
                    <h2 class="font-display mt-4 text-3xl text-ink dark:text-[#F4E9E2] font-bold">{{ $heading }}</h2>
                    <p class="mt-2 text-sm leading-6 text-walnut dark:text-[#d4c2b9]">{{ $copy }}</p>
                </div>
                <div class="max-w-xl settings-form">
                    @include('profile.partials.' . $partial)
                </div>
            </section>
        @endforeach
    </div>
</x-app-layout>
