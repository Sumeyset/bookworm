<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? 'Bookworm — a home for readers' }}</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=caveat:500,600,700|figtree:400,500,600,700|lora:400,500,600,700,400i,500i&display=swap" rel="stylesheet">
        <script>
            (() => {
                const saved = localStorage.getItem('bookworm-theme');
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                document.documentElement.classList.toggle('dark', saved ? saved === 'dark' : prefersDark);
            })();
        </script>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-ivory font-sans text-ink antialiased dark:bg-wineblack dark:text-[#f4e9e2]">
        <div class="paper-grain min-h-screen bg-ivory dark:bg-wineblack">
            @include('layouts.navigation')

            @isset($header)
                <header class="relative border-b border-warmborder bg-paper/95 shadow-sm dark:border-[#3e292e] dark:bg-raised/95">
                    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main class="pb-20 sm:pb-0">
                {{ $slot }}
            </main>
            @auth
                <nav class="fixed inset-x-0 bottom-0 z-40 grid grid-cols-4 border-t border-warmborder bg-paper/95 px-2 py-2 shadow-[0_-8px_24px_-20px_rgba(26,15,18,0.5)] backdrop-blur-sm dark:border-[#4B3035] dark:bg-raised/95 sm:hidden" aria-label="Mobile navigation">
                    <a href="{{ route('books.index') }}" @if (request()->routeIs('books.*')) aria-current="page" @endif class="{{ request()->routeIs('books.*') ? 'text-burgundy dark:text-burgundy-dark' : 'text-tan dark:text-[#c7a491]' }} focus-ring flex flex-col items-center gap-1 rounded-lg py-1 text-[11px] font-semibold">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 4.75A2.75 2.75 0 0 1 7.75 2H20v17H7.75A2.75 2.75 0 0 0 5 21.75v-17Z"/><path stroke-linecap="round" d="M5 18.5A2.5 2.5 0 0 1 7.5 16H20"/></svg>
                        Books
                    </a>
                    <a href="{{ route('users.show', Auth::user()->username) }}" @if (request()->routeIs('users.show') && Auth::user()->username === request()->route('user')?->username) aria-current="page" @endif class="{{ request()->routeIs('users.show') && Auth::user()->username === request()->route('user')?->username ? 'text-burgundy dark:text-burgundy-dark' : 'text-tan dark:text-[#c7a491]' }} focus-ring flex flex-col items-center gap-1 rounded-lg py-1 text-[11px] font-semibold">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 5.5c2.8-1 5.4-.7 8 1.1v13c-2.6-1.8-5.2-2.1-8-1.1v-13Zm16 0c-2.8-1-5.4-.7-8 1.1v13c2.6-1.8 5.2-2.1 8-1.1v-13Z"/></svg>
                        My shelf
                    </a>
                    <a href="{{ route('feed.index') }}" @if (request()->routeIs('feed.*')) aria-current="page" @endif class="{{ request()->routeIs('feed.*') ? 'text-burgundy dark:text-burgundy-dark' : 'text-tan dark:text-[#c7a491]' }} focus-ring flex flex-col items-center gap-1 rounded-lg py-1 text-[11px] font-semibold">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 5.5h14M5 12h10M5 18.5h7"/><circle cx="18" cy="17" r="3"/></svg>
                        Activity
                    </a>
                    <a href="{{ route('users.index') }}" @if (request()->routeIs('users.index')) aria-current="page" @endif class="{{ request()->routeIs('users.index') ? 'text-burgundy dark:text-burgundy-dark' : 'text-tan dark:text-[#c7a491]' }} focus-ring flex flex-col items-center gap-1 rounded-lg py-1 text-[11px] font-semibold">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><circle cx="9" cy="8" r="3.2"/><path stroke-linecap="round" stroke-linejoin="round" d="M3.5 19a5.5 5.5 0 0 1 11 0m2-8.5a3 3 0 1 0 0-6m1 9a5 5 0 0 1 3 4.5"/></svg>
                        Readers
                    </a>
                </nav>
            @endauth
        </div>
    </body>
</html>
