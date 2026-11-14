<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#4A1220">

        <title>{{ $title ?? 'Bookworm — a home for readers' }}</title>
        <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

        @include('layouts.partials.head-assets')
        @stack('scripts')
    </head>
    <body id="top" class="bg-ivory font-sans text-ink antialiased selection:bg-burgundy/20 dark:bg-wineblack dark:text-[#f4e9e2]">
        <div class="paper-grain flex min-h-screen flex-col bg-ivory dark:bg-wineblack">
            {{-- soft page glow behind the header --}}
            <div class="pointer-events-none absolute inset-x-0 top-0 h-[28rem] bg-[radial-gradient(60rem_22rem_at_15%_-10%,rgba(109,26,43,0.10),transparent_70%),radial-gradient(40rem_20rem_at_95%_0%,rgba(184,137,59,0.12),transparent_70%)] dark:bg-[radial-gradient(60rem_22rem_at_15%_-10%,rgba(163,50,74,0.18),transparent_70%)]" aria-hidden="true"></div>

            @include('layouts.navigation')

            @isset($header)
                <header class="page-shell relative pt-10 sm:pt-14">
                    {{ $header }}
                    <div class="mt-8 h-px bg-gradient-to-r from-gold/50 via-warmborder to-transparent dark:from-gold/40 dark:via-[#4B3035]" aria-hidden="true"></div>
                </header>
            @endisset

            <main class="relative flex-1">
                {{ $slot }}
            </main>

            <x-site-footer class="mb-24 md:mb-0" />

            @php
                $statusMessages = [
                    'profile-updated' => 'Your profile has been saved.',
                    'password-updated' => 'Your password has been updated.',
                    'verification-link-sent' => 'A new verification link is on its way.',
                ];
                $status = session('status');
            @endphp
            @if ($status)
                <span data-flash="{{ $statusMessages[$status] ?? $status }}" hidden></span>
            @endif
            @if ($errors->any())
                <span data-flash="{{ $errors->first() }}" data-tone="error" hidden></span>
            @endif

            @auth
                @php
                    $mobileNav = [
                        ['label' => 'Books', 'url' => route('books.index'), 'active' => request()->routeIs('books.*'), 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M5 4.75A2.75 2.75 0 0 1 7.75 2H20v17H7.75A2.75 2.75 0 0 0 5 21.75v-17Z"/><path stroke-linecap="round" d="M5 18.5A2.5 2.5 0 0 1 7.5 16H20"/>'],
                        ['label' => 'My tree', 'url' => route('users.show', Auth::user()->username), 'active' => request()->routeIs('users.show') && Auth::user()->username === request()->route('user')?->username, 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-7m0 0c-3.9 0-7-2.7-7-6.2C5 4.6 8.1 2 12 2s7 2.6 7 5.8c0 3.5-3.1 6.2-7 6.2Zm0 0-3-3m3 3 3-3"/>'],
                        ['label' => 'Feed', 'url' => route('feed.index'), 'active' => request()->routeIs('feed.*'), 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M5 5.5h14M5 12h10M5 18.5h7"/><circle cx="18" cy="17" r="3"/>'],
                        ['label' => 'Readers', 'url' => route('users.index'), 'active' => request()->routeIs('users.index'), 'icon' => '<circle cx="9" cy="8" r="3.2"/><path stroke-linecap="round" stroke-linejoin="round" d="M3.5 19a5.5 5.5 0 0 1 11 0m2-8.5a3 3 0 1 0 0-6m1 9a5 5 0 0 1 3 4.5"/>'],
                    ];
                @endphp
                <nav class="fixed inset-x-3 bottom-3 z-40 grid grid-cols-4 rounded-2xl border border-warmborder/80 bg-paper/90 p-1.5 shadow-[0_18px_40px_-18px_rgba(26,15,18,0.6)] backdrop-blur-md dark:border-[#4B3035] dark:bg-raised/90 md:hidden" aria-label="Mobile navigation">
                    @foreach ($mobileNav as $item)
                        <a href="{{ $item['url'] }}" @if ($item['active']) aria-current="page" @endif class="{{ $item['active'] ? 'bg-burgundy text-[#FFF8EF] dark:bg-burgundy-dark' : 'text-tan dark:text-[#c7a491]' }} focus-ring flex flex-col items-center gap-0.5 rounded-xl py-1.5 text-[11px] font-semibold transition">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">{!! $item['icon'] !!}</svg>
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </nav>
            @endauth
        </div>
    </body>
</html>
