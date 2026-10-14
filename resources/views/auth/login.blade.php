<x-guest-layout>
    <div class="mb-7">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#a17861]">Welcome back</p>
        <h1 class="mt-2 font-serif text-3xl font-semibold text-[#4c2d32]">{{ __('Log in to Bookworm') }}</h1>
        <p class="mt-2 text-sm leading-6 text-[#806b60]">Pick up where your last chapter left off.</p>
    </div>

    <x-auth-session-status class="mb-5" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div>
            <x-input-label for="email" class="text-[#654c47]" :value="__('Email')" />
            <x-text-input id="email" class="mt-2 block w-full rounded-xl border-[#ddcbbc] bg-[#fffdfa] px-4 py-3 text-sm text-[#392a2b] shadow-sm placeholder:text-[#aa9889] focus:border-[#672638] focus:ring-[#672638]/20" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-5">
            <x-input-label for="password" class="text-[#654c47]" :value="__('Password')" />
            <x-text-input id="password" class="mt-2 block w-full rounded-xl border-[#ddcbbc] bg-[#fffdfa] px-4 py-3 text-sm text-[#392a2b] shadow-sm placeholder:text-[#aa9889] focus:border-[#672638] focus:ring-[#672638]/20" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-5 flex flex-wrap items-center justify-between gap-3">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-[#cfbbaa] text-[#672638] shadow-sm focus:ring-[#672638]/30" name="remember">
                <span class="ms-2 text-sm text-[#806b60]">{{ __('Remember me') }}</span>
            </label>
            @if (Route::has('password.request'))
                <a class="rounded-md text-sm font-medium text-[#7a3948] underline decoration-[#c99f88] underline-offset-4 transition hover:text-[#4f2633] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#9b655e]/50" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif
        </div>

        <button type="submit" class="mt-7 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#672638] px-5 py-3.5 text-sm font-semibold text-[#fffaf4] shadow-[0_12px_24px_-14px_rgba(103,38,56,0.85)] transition duration-200 hover:-translate-y-0.5 hover:bg-[#7b3545] focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-[#9b655e]/40">
            {{ __('Log in') }}
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.69L10.22 5.03a.75.75 0 1 1 1.06-1.06l5.5 5.5a.75.75 0 0 1 0 1.06l-5.5 5.5a.75.75 0 1 1-1.06-1.06l4.22-4.22H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/></svg>
        </button>

        <p class="mt-6 text-center text-sm text-[#806b60]">
            New to Bookworm?
            <a href="{{ route('register') }}" class="font-semibold text-[#7a3948] underline decoration-[#c99f88] underline-offset-4 transition hover:text-[#4f2633]">Create an account</a>
        </p>
    </form>
</x-guest-layout>
