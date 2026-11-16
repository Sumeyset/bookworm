<section>
    <p class="text-sm leading-6 text-walnut dark:text-[#d4c2b9]">
        Signed in on a shared or lost phone or computer? Sign out everywhere except this device. You’ll stay signed in here.
    </p>

    <form method="post" action="{{ route('other-devices.destroy') }}" class="mt-5 space-y-5">
        @csrf
        @method('delete')
        <div>
            <x-input-label for="other_devices_password" value="Your password" />
            <x-text-input id="other_devices_password" name="current_password" type="password" class="mt-1 block w-full" autocomplete="current-password" required />
            <x-input-error :messages="$errors->otherDevices->get('current_password')" class="mt-2" />
        </div>
        <div class="flex flex-wrap items-center gap-4">
            <x-primary-button>Sign out other devices</x-primary-button>
            @if (session('status') === 'other-devices-logged-out')
                <p class="text-sm font-semibold text-[#4f6b3f] dark:text-[#b9d3a6]" role="status">Done — every other device is signed out.</p>
            @endif
        </div>
    </form>
</section>
