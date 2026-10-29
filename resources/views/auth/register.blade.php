<x-guest-layout>
    <div class="mb-6">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-tan dark:text-[#c7a491]">Your story starts here</p>
        <h1 class="mt-2 font-serif text-3xl font-semibold text-ink dark:text-[#F4E9E2]">{{ __('Create your account') }}</h1>
        <p class="mt-2 text-sm leading-6 text-walnut dark:text-[#d4c2b9]">Join readers sharing the books they love.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="mt-2 block w-full px-4 py-3" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="username" :value="__('Username')" />
            <x-text-input id="username" class="mt-2 block w-full px-4 py-3" type="text" name="username" :value="old('username')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('username')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="mt-2 block w-full px-4 py-3" type="email" name="email" :value="old('email')" required autocomplete="email" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="mt-2 block w-full px-4 py-3" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" class="mt-2 block w-full px-4 py-3" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <button type="submit" class="focus-ring mt-2 inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-full bg-burgundy px-5 py-3.5 text-sm font-semibold text-[#fffaf4] shadow-sm transition duration-200 hover:-translate-y-0.5 hover:bg-burgundy-hover">
            {{ __('Create account') }}
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.69L10.22 5.03a.75.75 0 1 1 1.06-1.06l5.5 5.5a.75.75 0 0 1 0 1.06l-5.5 5.5a.75.75 0 1 1-1.06-1.06l4.22-4.22H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/></svg>
        </button>

        <p class="pt-1 text-center text-sm text-walnut dark:text-[#d4c2b9]">
            Already have an account?
            <a href="{{ route('login') }}" class="font-semibold text-burgundy underline decoration-gold underline-offset-4 transition hover:text-burgundy-hover dark:text-burgundy-dark">Log in</a>
        </p>
    </form>
</x-guest-layout>
