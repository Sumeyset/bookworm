<x-guest-layout>
    <div class="mb-7">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-tan dark:text-[#c7a491]">Welcome back</p>
        <h1 class="mt-2 font-serif text-3xl font-semibold text-ink dark:text-[#F4E9E2]">{{ __('Log in to Bookworm') }}</h1>
        <p class="mt-2 text-sm leading-6 text-walnut dark:text-[#d4c2b9]">Pick up where your last chapter left off.</p>
    </div>

    <x-auth-session-status class="mb-5" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="mt-2 block w-full px-4 py-3" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-5">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="mt-2 block w-full px-4 py-3" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-5 flex flex-wrap items-center justify-between gap-3">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-warmborder text-burgundy shadow-sm focus:ring-burgundy/30" name="remember">
                <span class="ms-2 text-sm text-walnut dark:text-[#d4c2b9]">{{ __('Remember me') }}</span>
            </label>
            @if (Route::has('password.request'))
                <a class="focus-ring rounded-sm text-sm font-medium text-burgundy underline decoration-gold underline-offset-4 transition hover:text-burgundy-hover dark:text-burgundy-dark" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif
        </div>

        <button type="submit" class="focus-ring mt-7 inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-full bg-burgundy px-5 py-3.5 text-sm font-semibold text-[#fffaf4] shadow-sm transition duration-200 hover:-translate-y-0.5 hover:bg-burgundy-hover">
            {{ __('Log in') }}
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.69L10.22 5.03a.75.75 0 1 1 1.06-1.06l5.5 5.5a.75.75 0 0 1 0 1.06l-5.5 5.5a.75.75 0 1 1-1.06-1.06l4.22-4.22H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/></svg>
        </button>

        <p class="mt-6 text-center text-sm text-walnut dark:text-[#d4c2b9]">
            New to Bookworm?
            <a href="{{ route('register') }}" class="font-semibold text-burgundy underline decoration-gold underline-offset-4 transition hover:text-burgundy-hover dark:text-burgundy-dark">Create an account</a>
        </p>
    </form>
</x-guest-layout>
