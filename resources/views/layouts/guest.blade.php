<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#672638">
        <title>Bookworm — a home for readers</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=caveat:500,600,700|figtree:400,500,600,700&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[#e9ded4] font-sans text-[#392a2b] antialiased">
        <div class="relative isolate flex min-h-screen items-center justify-center overflow-hidden bg-[radial-gradient(ellipse_at_10%_10%,rgba(210,170,115,0.35),transparent_35%),radial-gradient(ellipse_at_90%_90%,rgba(137,63,71,0.24),transparent_40%),linear-gradient(135deg,#f2e9dc_0%,#e9ded4_52%,#dfcfc4_100%)] px-4 py-8 sm:px-6 lg:px-8">
            <div class="pointer-events-none absolute -left-40 -top-40 -z-10 h-[32rem] w-[32rem] rounded-full border border-[#8a5142]/20"></div>
            <div class="pointer-events-none absolute -bottom-48 -right-32 -z-10 h-[34rem] w-[34rem] rounded-full border border-[#672638]/20"></div>

            <main class="w-full max-w-5xl overflow-hidden rounded-[2rem] border border-[#cbb5a3]/70 bg-[#fffaf4] shadow-[0_32px_100px_-40px_rgba(65,35,35,0.48)] sm:rounded-[2.5rem]">
                <div class="grid min-h-[36rem] lg:grid-cols-[0.88fr_1.12fr]">
                    <aside class="relative flex min-h-[9rem] flex-col justify-center overflow-hidden bg-gradient-to-br from-[#713345] via-[#672638] to-[#48242f] px-7 py-5 text-[#fff8ef] sm:min-h-[10rem] sm:px-10 sm:py-6 lg:min-h-[39rem] lg:justify-between lg:px-12 lg:py-11">
                        <div class="pointer-events-none absolute -right-20 -top-20 h-64 w-64 rounded-full border border-[#e4c6a6]/20"></div>
                        <div class="pointer-events-none absolute -right-10 -top-10 h-44 w-44 rounded-full border border-[#e4c6a6]/15"></div>
                        <div class="pointer-events-none absolute -bottom-24 -left-20 h-64 w-64 rounded-full bg-[#c58766]/20 blur-3xl"></div>

                        <a href="{{ route('home') }}" class="group relative z-10 inline-flex w-fit items-center gap-3 rounded-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#e6c795]">
                            <svg class="h-12 w-12 transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-3 sm:h-14 sm:w-14" viewBox="0 0 140 140" fill="none" aria-hidden="true">
                                <path d="M18 55c15-8 33-7 50 3v58c-17-9-35-10-50-3V55Z" fill="#F2DCC4"/>
                                <path d="M122 55c-15-8-33-7-50 3v58c17-9 35-10 50-3V55Z" fill="#E3C5A5"/>
                                <path d="M24 61c12-5 25-3 38 4v40c-13-6-26-8-38-4V61Z" fill="#FFF8EF"/>
                                <path d="M116 61c-12-5-25-3-38 4v40c13-6 26-8 38-4V61Z" fill="#F6EBDD"/>
                                <path d="M70 57v59" stroke="#B58771" stroke-width="3.5" stroke-linecap="round"/>
                                <path d="M70 59c-8-7-7-15-2-20 5-4 12-7 11-14" stroke="#D39A6D" stroke-width="10" stroke-linecap="round"/>
                                <circle cx="79" cy="19" r="11" fill="#D7A477"/>
                                <circle cx="75.5" cy="16.5" r="1.7" fill="#392A2B"/>
                                <circle cx="82.5" cy="16.5" r="1.7" fill="#392A2B"/>
                                <path d="M76.5 22c1.5 1.5 4.5 1.5 6 0" stroke="#392A2B" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                            <span>
                                <span class="block font-handwritten text-4xl font-bold leading-none sm:text-5xl">Bookworm</span>
                                <span class="mt-1 block text-[9px] font-medium uppercase tracking-[0.22em] text-[#efd9c4]/80">A home for readers</span>
                            </span>
                        </a>

                        <div class="relative z-10 mt-7 hidden lg:block">
                            <p class="font-serif text-3xl leading-tight text-[#fff4e5]">Every reader<br>belongs here.</p>
                            <p class="mt-4 max-w-xs text-sm leading-6 text-[#f0dbca]/80">Find a story that stays with you, and share the ones you can’t stop thinking about.</p>
                            <div class="mt-10 flex h-32 items-end gap-2" aria-hidden="true">
                                <div class="h-20 w-12 -rotate-6 rounded-t-md border border-[#f0d5b4]/40 bg-gradient-to-b from-[#9d5b59] to-[#753543] shadow-lg"></div>
                                <div class="h-28 w-14 rotate-2 rounded-t-md border border-[#efd3a6]/50 bg-gradient-to-b from-[#bc8560] to-[#8b5142] shadow-lg"></div>
                                <div class="h-24 w-12 rotate-6 rounded-t-md border border-[#e6c69c]/40 bg-gradient-to-b from-[#806052] to-[#513a3c] shadow-lg"></div>
                                <span class="mb-3 ml-3 font-handwritten text-2xl text-[#e7c795]">your next chapter</span>
                            </div>
                        </div>

                        <p class="relative z-10 mt-4 hidden text-xs tracking-wide text-[#f0dbca]/65 lg:block">A little more magic in every chapter.</p>
                    </aside>

                    <section class="flex items-center px-7 py-8 sm:px-10 sm:py-10 lg:px-14 lg:py-12">
                        <div class="mx-auto w-full max-w-md">
                            {{ $slot }}
                        </div>
                    </section>
                </div>
            </main>
        </div>
    </body>
</html>
