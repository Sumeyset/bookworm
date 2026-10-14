<x-guest-layout>
    <div class="mb-6">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#a17861]">Your story starts here</p>
        <h1 class="mt-2 font-serif text-3xl font-semibold text-[#4c2d32]">{{ __('Create your account') }}</h1>
        <p class="mt-2 text-sm leading-6 text-[#806b60]">Join readers sharing the books they love.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="name" class="text-[#654c47]" :value="__('Name')" />
            <x-text-input id="name" class="mt-2 block w-full rounded-xl border-[#ddcbbc] bg-[#fffdfa] px-4 py-3 text-sm text-[#392a2b] shadow-sm focus:border-[#672638] focus:ring-[#672638]/20" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="username" class="text-[#654c47]" :value="__('Username')" />
            <x-text-input id="username" class="mt-2 block w-full rounded-xl border-[#ddcbbc] bg-[#fffdfa] px-4 py-3 text-sm text-[#392a2b] shadow-sm focus:border-[#672638] focus:ring-[#672638]/20" type="text" name="username" :value="old('username')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('username')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" class="text-[#654c47]" :value="__('Email')" />
            <x-text-input id="email" class="mt-2 block w-full rounded-xl border-[#ddcbbc] bg-[#fffdfa] px-4 py-3 text-sm text-[#392a2b] shadow-sm focus:border-[#672638] focus:ring-[#672638]/20" type="email" name="email" :value="old('email')" required autocomplete="email" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" class="text-[#654c47]" :value="__('Password')" />
            <x-text-input id="password" class="mt-2 block w-full rounded-xl border-[#ddcbbc] bg-[#fffdfa] px-4 py-3 text-sm text-[#392a2b] shadow-sm focus:border-[#672638] focus:ring-[#672638]/20" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" class="text-[#654c47]" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" class="mt-2 block w-full rounded-xl border-[#ddcbbc] bg-[#fffdfa] px-4 py-3 text-sm text-[#392a2b] shadow-sm focus:border-[#672638] focus:ring-[#672638]/20" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <button type="submit" class="mt-2 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#672638] px-5 py-3.5 text-sm font-semibold text-[#fffaf4] shadow-[0_12px_24px_-14px_rgba(103,38,56,0.85)] transition duration-200 hover:-translate-y-0.5 hover:bg-[#7b3545] focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-[#9b655e]/40">
            {{ __('Create account') }}
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.69L10.22 5.03a.75.75 0 1 1 1.06-1.06l5.5 5.5a.75.75 0 0 1 0 1.06l-5.5 5.5a.75.75 0 1 1-1.06-1.06l4.22-4.22H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/></svg>
        </button>

        <p class="pt-1 text-center text-sm text-[#806b60]">
            Already have an account?
            <a href="{{ route('login') }}" class="font-semibold text-[#7a3948] underline decoration-[#c99f88] underline-offset-4 transition hover:text-[#4f2633]">Log in</a>
        </p>
    </form>
</x-guest-layout>
