<x-app-layout>
    <x-slot name="title">Import your books · Bookworm</x-slot>
    <x-slot name="header">
        <x-page-header eyebrow="Bring your library" title="Import your books" subtitle="Move your shelves from Goodreads or StoryGraph in one go — statuses, ratings, reviews and finish dates included." />
    </x-slot>

    <div class="page-section grid gap-8 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <form method="POST" action="{{ route('import.store') }}" enctype="multipart/form-data" class="bw-card space-y-5 p-6 sm:p-8"
              x-data="{ name: '', over: false }">
            @csrf
            <label for="import-file"
                   class="flex cursor-pointer flex-col items-center justify-center rounded-3xl border-2 border-dashed px-6 py-14 text-center transition"
                   :class="over ? 'border-burgundy bg-rosetint/50' : 'border-warmborder hover:border-burgundy/40 dark:border-[#4B3035]'"
                   @dragover.prevent="over = true" @dragleave="over = false"
                   @drop.prevent="over = false; $refs.file.files = $event.dataTransfer.files; name = $event.dataTransfer.files[0]?.name || ''">
                <span class="text-5xl" aria-hidden="true">📚</span>
                <span class="mt-4 font-display text-3xl font-bold text-ink dark:text-[#F4E9E2]" x-text="name || 'Drop your CSV here'">Drop your CSV here</span>
                <span class="mt-1 text-sm text-tan">or click to choose the file · up to 5 MB</span>
                <input id="import-file" x-ref="file" type="file" name="file" accept=".csv,text/csv" required class="sr-only" @change="name = $event.target.files[0]?.name || ''">
            </label>
            <x-input-error :messages="$errors->get('file')" />
            <div class="flex flex-wrap items-center justify-between gap-3">
                <p class="text-sm text-tan">Books already on your tree are skipped, so it’s safe to import again.</p>
                <button type="submit" class="btn-primary">Import my books</button>
            </div>
        </form>

        <aside class="space-y-5">
            <div class="bw-card p-5">
                <h2 class="font-serif text-xl font-semibold text-ink dark:text-[#F4E9E2]">From Goodreads</h2>
                <ol class="mt-3 list-decimal space-y-1.5 pl-5 text-sm leading-6 text-walnut dark:text-[#d4c2b9]">
                    <li>Open <b>My Books</b> on goodreads.com.</li>
                    <li>Choose <b>Import and export</b> (left side, under Tools).</li>
                    <li>Click <b>Export Library</b>, then download the CSV.</li>
                </ol>
            </div>
            <div class="bw-card p-5">
                <h2 class="font-serif text-xl font-semibold text-ink dark:text-[#F4E9E2]">From StoryGraph</h2>
                <ol class="mt-3 list-decimal space-y-1.5 pl-5 text-sm leading-6 text-walnut dark:text-[#d4c2b9]">
                    <li>Open your <b>Preferences</b> on thestorygraph.com.</li>
                    <li>Under <b>Manage your data</b>, choose <b>Export StoryGraph library</b>.</li>
                    <li>Download the CSV from the email or page.</li>
                </ol>
            </div>
        </aside>
    </div>
</x-app-layout>
