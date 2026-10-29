<x-app-layout>
    <x-slot name="header">
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-tan dark:text-[#c7a491]">A few pages about you</p>
        <h1 class="mt-1 font-serif text-3xl font-semibold text-ink dark:text-[#F4E9E2]">{{ $user->onboarding_completed_at ? 'Update your reading nook' : 'Set up your reading nook' }}</h1>
    </x-slot>

    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6">
        <form method="POST" action="{{ route('onboarding.store') }}" class="bookplate rounded-xl p-5 sm:p-8" data-onboarding>
            @csrf
            <ol class="mb-8 grid grid-cols-3 gap-2 text-center" aria-label="Onboarding progress">
                <li data-step-indicator="0" aria-current="step" class="rounded-full bg-rosetint px-2 py-2 text-xs font-semibold text-burgundy dark:bg-[#382329] dark:text-burgundy-dark">01 · Genres</li>
                <li data-step-indicator="1" class="rounded-full bg-parchment px-2 py-2 text-xs font-semibold text-tan dark:bg-[#201317] dark:text-[#c7a491]">02 · Goal</li>
                <li data-step-indicator="2" class="rounded-full bg-parchment px-2 py-2 text-xs font-semibold text-tan dark:bg-[#201317] dark:text-[#c7a491]">03 · Ready</li>
            </ol>
            <section data-onboarding-step="0" aria-labelledby="genres-heading">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-tan dark:text-[#c7a491]">01 · What do you reach for?</p>
                <h2 id="genres-heading" class="mt-2 font-serif text-2xl font-semibold text-ink dark:text-[#F4E9E2]">Pick a few shelves to start from.</h2>
                <p class="mt-2 text-sm text-walnut dark:text-[#d4c2b9]">Choose up to five. These help us shape recommendations; you can change them later.</p>
                <div class="mt-5 grid grid-cols-2 gap-2 sm:grid-cols-3">
                    @foreach ($genres as $genre)
                        <label class="cursor-pointer">
                            <input type="checkbox" name="preferred_genres[]" value="{{ $genre }}" @checked(in_array($genre, old('preferred_genres', $user->preferred_genres ?? []), true)) class="peer sr-only">
                            <span class="focus-ring block rounded-lg border border-warmborder bg-paper px-3 py-3 text-center text-sm font-medium text-walnut transition peer-checked:border-burgundy peer-checked:bg-rosetint peer-checked:text-burgundy peer-focus-visible:outline peer-focus-visible:outline-2 peer-focus-visible:outline-gold dark:border-[#4B3035] dark:bg-[#201317] dark:text-[#d4c2b9] dark:peer-checked:bg-[#382329] dark:peer-checked:text-burgundy-dark">{{ $genre }}</span>
                        </label>
                    @endforeach
                </div>
                <x-input-error :messages="$errors->get('preferred_genres')" class="mt-2" />
                @php($genreErrors = collect($errors->getBag('default')->getMessages())->filter(fn ($messages, $key) => str_starts_with($key, 'preferred_genres.'))->flatten()->all())
                <x-input-error :messages="$genreErrors" class="mt-2" />
                <div class="mt-7 flex justify-end">
                    <button type="button" data-step-next class="focus-ring rounded-full bg-burgundy px-6 py-3 text-sm font-semibold text-white hover:bg-burgundy-hover">Choose a goal <span aria-hidden="true">&rarr;</span></button>
                </div>
            </section>

            <section data-onboarding-step="1" aria-labelledby="goal-heading" hidden>
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-tan dark:text-[#c7a491]">02 · A gentle intention</p>
                <h2 id="goal-heading" class="mt-2 font-serif text-2xl font-semibold text-ink dark:text-[#F4E9E2]">How many books would you like to read this year?</h2>
                <p class="mt-2 text-sm text-walnut dark:text-[#d4c2b9]">A goal is a bookmark, not a deadline. Set one—or skip this part.</p>
                <label for="yearly-reading-goal" class="mt-5 block text-sm font-semibold text-ink dark:text-[#F4E9E2]">Books this year</label>
                <input id="yearly-reading-goal" name="yearly_reading_goal" type="number" min="1" max="365" value="{{ old('yearly_reading_goal', $user->yearly_reading_goal ?? 12) }}" required class="mt-2 w-full max-w-xs rounded-lg border-warmborder bg-paper px-4 py-3 text-ink focus:border-burgundy focus:ring-burgundy/20 dark:border-[#4B3035] dark:bg-[#201317] dark:text-[#F4E9E2]">
                <x-input-error :messages="$errors->get('yearly_reading_goal')" class="mt-2" />
                <div class="mt-7 flex justify-between gap-3">
                    <button type="button" data-step-previous class="focus-ring rounded-full px-4 py-3 text-sm font-semibold text-tan underline underline-offset-4 dark:text-[#c7a491]">Back</button>
                    <button type="button" data-step-next class="focus-ring rounded-full bg-burgundy px-6 py-3 text-sm font-semibold text-white hover:bg-burgundy-hover">Review my nook <span aria-hidden="true">&rarr;</span></button>
                </div>
            </section>

            <section data-onboarding-step="2" aria-labelledby="ready-heading" hidden>
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-tan dark:text-[#c7a491]">03 · All set</p>
                <h2 id="ready-heading" class="mt-2 font-serif text-2xl font-semibold text-ink dark:text-[#F4E9E2]">Your next good read is waiting.</h2>
                <p class="mt-2 text-sm text-walnut dark:text-[#d4c2b9]">We’ll remember these preferences. You can change them whenever your reading mood does.</p>
                <div class="mt-5 rounded-lg bg-parchment/70 p-4 dark:bg-[#201317]">
                    <p class="text-xs font-semibold uppercase tracking-wide text-tan dark:text-[#c7a491]">Your chosen genres</p>
                    <p data-selected-genres class="mt-2 text-sm font-semibold text-burgundy dark:text-burgundy-dark">No genres selected yet</p>
                    <p class="mt-4 text-xs font-semibold uppercase tracking-wide text-tan dark:text-[#c7a491]">Your yearly goal</p>
                    <p><span data-selected-goal class="font-serif text-3xl font-semibold text-ink dark:text-[#F4E9E2]">{{ old('yearly_reading_goal', $user->yearly_reading_goal ?? 12) }}</span> <span class="text-sm text-tan dark:text-[#c7a491]">books</span></p>
                </div>
                <div class="mt-7 flex flex-wrap items-center justify-between gap-3">
                    <button type="button" data-step-previous class="focus-ring rounded-full px-4 py-3 text-sm font-semibold text-tan underline underline-offset-4 dark:text-[#c7a491]">Back</button>
                    <div class="flex flex-wrap gap-3">
                        <button type="submit" class="focus-ring rounded-full bg-burgundy px-6 py-3 text-sm font-semibold text-white hover:bg-burgundy-hover">Save my preferences</button>
                        <button type="submit" form="skip-onboarding" class="focus-ring rounded-full px-4 py-3 text-sm font-semibold text-tan underline underline-offset-4 hover:text-burgundy dark:text-[#c7a491] dark:hover:text-burgundy-dark">Skip for now</button>
                    </div>
                </div>
            </section>
        </form>
        <form id="skip-onboarding" method="POST" action="{{ route('onboarding.skip') }}">@csrf</form>
    </div>
    <script>
        const onboarding = document.querySelector('[data-onboarding]');
        if (onboarding) {
            const steps = [...onboarding.querySelectorAll('[data-onboarding-step]')];
            const indicators = [...onboarding.querySelectorAll('[data-step-indicator]')];
            let currentStep = {{ $errors->has('yearly_reading_goal') ? 1 : 0 }};
            const showStep = (step) => {
                currentStep = Math.max(0, Math.min(steps.length - 1, step));
                steps.forEach((section, index) => {
                    section.hidden = index !== currentStep;
                });
                indicators.forEach((indicator, index) => {
                    if (index === currentStep) {
                        indicator.setAttribute('aria-current', 'step');
                        indicator.className = 'rounded-full bg-rosetint px-2 py-2 text-xs font-semibold text-burgundy dark:bg-[#382329] dark:text-burgundy-dark';
                    } else {
                        indicator.removeAttribute('aria-current');
                        indicator.className = 'rounded-full bg-parchment px-2 py-2 text-xs font-semibold text-tan dark:bg-[#201317] dark:text-[#c7a491]';
                    }
                });
                if (currentStep === 2) {
                    const selected = [...onboarding.querySelectorAll('input[name="preferred_genres[]"]:checked')].map((input) => input.value);
                    onboarding.querySelector('[data-selected-genres]').textContent = selected.length ? selected.join(' · ') : 'No genres selected yet';
                    onboarding.querySelector('[data-selected-goal]').textContent = onboarding.querySelector('[name="yearly_reading_goal"]').value;
                }
            };
            onboarding.querySelectorAll('[data-step-next]').forEach((button) => button.addEventListener('click', () => showStep(currentStep + 1)));
            onboarding.querySelectorAll('[data-step-previous]').forEach((button) => button.addEventListener('click', () => showStep(currentStep - 1)));
            onboarding.querySelectorAll('input[name="preferred_genres[]"]').forEach((input) => {
                input.addEventListener('change', () => {
                    const checked = onboarding.querySelectorAll('input[name="preferred_genres[]"]:checked');
                    if (checked.length > 5) input.checked = false;
                });
            });
            showStep(currentStep);
        }
    </script>
</x-app-layout>
