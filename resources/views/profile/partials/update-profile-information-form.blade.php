<section>
    <header>
        <h2 class="font-serif text-xl font-semibold text-ink dark:text-[#F4E9E2]">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm leading-6 text-walnut dark:text-[#d4c2b9]">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6"
          x-data="{ photoPreview: null, photoName: '', previewPhoto(event) { const file = event.target.files[0]; this.photoName = file ? file.name : ''; if (this.photoPreview) URL.revokeObjectURL(this.photoPreview); this.photoPreview = file ? URL.createObjectURL(file) : null; } }">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="profile_photo" :value="__('Profile photo')" />
            <div class="mt-2 flex flex-wrap items-center gap-4">
                <img x-show="photoPreview" x-bind:src="photoPreview" alt="{{ $user->name }}'s selected profile photo preview"
                     class="h-16 w-16 rounded-full object-cover" x-cloak>
                @if ($user->profile_photo_url)
                    <img x-show="!photoPreview" src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}'s current profile photo"
                         class="h-16 w-16 rounded-full object-cover">
                @else
                    <span x-show="!photoPreview" class="flex h-16 w-16 items-center justify-center rounded-full bg-parchment font-serif text-2xl font-semibold text-burgundy dark:bg-[#493536] dark:text-burgundy-dark" aria-hidden="true">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </span>
                @endif
                <div class="min-w-0 flex-1">
                    <input id="profile_photo" name="profile_photo" type="file" accept="image/jpeg,image/png,image/webp"
                           aria-describedby="profile-photo-help profile-photo-selection profile-photo-error"
                           x-on:change="previewPhoto($event)"
                           class="focus-ring block min-h-11 w-full max-w-sm rounded-lg text-sm text-walnut file:mr-4 file:rounded-full file:border-0 file:bg-parchment file:px-4 file:py-2 file:font-semibold file:text-burgundy hover:file:bg-rosetint dark:text-[#d4c2b9] dark:focus:ring-offset-raised">
                    <p id="profile-photo-selection" class="mt-2 text-sm text-walnut dark:text-[#d4c2b9]" aria-live="polite" x-text="photoName || 'No new photo selected'"></p>
                </div>
            </div>
            <p id="profile-photo-help" class="mt-2 text-sm text-walnut dark:text-[#d4c2b9]">
                {{ __('Choose a JPG, PNG, or WebP image up to 5 MB. Your current photo stays in place until you save.') }}
            </p>
            <div id="profile-photo-error" role="alert" aria-live="polite">
                @if ($errors->has('profile_photo'))
                    <x-input-error class="mt-2" :messages="$errors->get('profile_photo')" />
                @endif
            </div>
        </div>

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="mt-2 text-sm text-ink dark:text-[#F4E9E2]">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="focus-ring rounded-sm text-sm text-burgundy underline hover:text-burgundy-hover dark:text-burgundy-dark">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600 dark:text-green-400">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save profile') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-walnut dark:text-[#d4c2b9]"
                    role="status"
                    aria-live="polite"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
