<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ $book->title }}</h2>
            <a href="{{ route('books.index') }}" class="text-sm font-medium text-[#713640] hover:underline dark:text-[#e4b3a5]">
                &larr; Search books
            </a>
        </div>
    </x-slot>

    <div class="py-8 sm:py-10">
        <div class="mx-auto max-w-4xl space-y-8 px-4">
            @if (session('status'))
                <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-900 dark:bg-green-950 dark:text-green-200" role="status">
                    {{ session('status') }}
                </div>
            @endif

            <section class="flex flex-col gap-6 rounded-2xl border border-[#eadfd4] bg-[#fffdf9] p-5 shadow-sm sm:flex-row sm:p-7 dark:border-[#453538] dark:bg-[#291f20]">
                @if ($book->cover_url)
                    <img src="{{ $book->cover_url }}" alt="Cover of {{ $book->title }}" class="mx-auto w-36 shrink-0 self-start rounded-xl object-cover shadow-md sm:mx-0">
                @else
                    <div class="flex aspect-[2/3] w-36 shrink-0 items-center justify-center self-start rounded-xl bg-gradient-to-br from-[#f3e6d7] to-[#dfc7b5] p-4 text-center font-handwritten text-xl font-semibold text-[#72584d] dark:from-[#423334] dark:to-[#302526] dark:text-[#ddc9be]">
                        {{ $book->title }}
                    </div>
                @endif

                <div class="flex-1 self-center text-center sm:text-left">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#9b7466] dark:text-[#c7a491]">Book details</p>
                    <h1 class="mt-2 text-3xl font-semibold leading-tight text-[#392b2a] dark:text-[#f4e9e2]">{{ $book->title }}</h1>
                    <p class="mt-2 text-sm text-[#8a7065] dark:text-[#c1aaa0]">
                        {{ $book->author_name ?? 'Unknown author' }}
                        @if ($book->first_publish_year)
                            <span aria-hidden="true">&middot;</span> {{ $book->first_publish_year }}
                        @endif
                    </p>
                    <div class="mt-5 inline-flex items-center gap-2 rounded-full bg-[#faf2e9] px-3 py-2 dark:bg-[#382c2d]">
                        @if ($count)
                            <x-star-display :rating="$average" />
                            <span class="text-sm font-semibold text-[#392b2a] dark:text-[#f4e9e2]">{{ number_format($average, 1) }}</span>
                            <span class="text-xs text-[#8a7065] dark:text-[#c1aaa0]">{{ $count }} {{ Str::plural('review', $count) }}</span>
                        @else
                            <span class="text-sm text-[#8a7065] dark:text-[#c1aaa0]">No reviews yet</span>
                        @endif
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-[#eadfd4] bg-[#fffdf9] p-5 shadow-sm sm:p-6 dark:border-[#453538] dark:bg-[#291f20]">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#9b7466] dark:text-[#c7a491]">Your review</p>
                <h2 class="mt-1 text-xl font-semibold text-[#392b2a] dark:text-[#f4e9e2]">
                    {{ $myReview ? 'Edit your review' : 'Rate this book' }}
                </h2>
                <form method="POST" action="{{ route('reviews.store', $book) }}" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <x-input-label value="Your rating" />
                        <div class="mt-2"><x-star-input :value="old('rating', $myReview->rating ?? 0)" /></div>
                        <x-input-error :messages="$errors->get('rating')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="review-body" value="Your thoughts (optional)" />
                        <textarea id="review-body" name="body" rows="4" maxlength="3000"
                                  class="mt-1 block w-full rounded-xl border-[#dfd2c6] bg-[#fffdf9] text-sm text-[#392b2a] shadow-sm focus:border-[#9b5b54] focus:ring-[#9b5b54] dark:border-[#594749] dark:bg-[#20191a] dark:text-[#f4e9e2]">{{ old('body', $myReview->body ?? '') }}</textarea>
                        <x-input-error :messages="$errors->get('body')" class="mt-2" />
                    </div>
                    <x-primary-button>{{ $myReview ? 'Update review' : 'Save review' }}</x-primary-button>
                </form>
                @if ($myReview)
                    <form method="POST" action="{{ route('reviews.destroy', $myReview) }}" class="mt-3" onsubmit="return confirm('Delete your review?')">
                        @csrf
                        @method('DELETE')
                        <button class="text-sm text-red-600 underline">Delete my review</button>
                    </form>
                @endif
            </section>

            <section aria-labelledby="private-pdfs-heading" class="rounded-2xl border border-[#eadfd4] bg-[#fffdf9] p-5 shadow-sm sm:p-6 dark:border-[#453538] dark:bg-[#291f20]">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#9b7466] dark:text-[#c7a491]">Private to your account</p>
                <h2 id="private-pdfs-heading" class="mt-1 text-xl font-semibold text-[#392b2a] dark:text-[#f4e9e2]">Your PDFs for this book</h2>
                <p id="book-pdf-help" class="mt-2 text-sm text-[#8a7065] dark:text-[#c1aaa0]">
                    Upload up to 5 PDF files at a time (20 MB each). Only you can view, download, or delete your files.
                </p>

                <form method="POST" action="{{ route('books.pdfs.store', $book) }}" enctype="multipart/form-data" class="mt-4 space-y-3">
                    @csrf
                    <div>
                        <label for="book-pdfs" class="block text-sm font-medium text-[#392b2a] dark:text-[#f4e9e2]">Choose PDF files</label>
                        <input id="book-pdfs" name="pdfs[]" type="file" accept="application/pdf,.pdf" multiple required
                               aria-describedby="book-pdf-help book-pdf-errors"
                               class="mt-1 block min-h-11 w-full rounded-md text-sm text-gray-700 file:mr-4 file:rounded-full file:border-0 file:bg-[#f1e3d6] file:px-4 file:py-2 file:font-semibold file:text-[#713640] hover:file:bg-[#ead5c2] focus:outline-none focus:ring-2 focus:ring-[#713640] focus:ring-offset-2 dark:text-gray-300 dark:focus:ring-offset-gray-800">
                    </div>
                    <div id="book-pdf-errors" role="alert" aria-live="polite">
                        @foreach ($errors->get('pdfs') as $error)
                            <p class="text-sm text-red-600 dark:text-red-400">{{ $error }}</p>
                        @endforeach
                        @foreach ($errors->getMessages() as $field => $messages)
                            @if (str_starts_with($field, 'pdfs.'))
                                @foreach ($messages as $error)
                                    <p class="text-sm text-red-600 dark:text-red-400">{{ $error }}</p>
                                @endforeach
                            @endif
                        @endforeach
                    </div>
                    <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-full bg-[#5b1f2b] px-5 py-2 text-sm font-semibold text-[#fff8ef] transition hover:bg-[#713640] focus:outline-none focus:ring-2 focus:ring-[#b98768] focus:ring-offset-2 dark:focus:ring-offset-[#291f20]">
                        Upload PDFs
                    </button>
                </form>

                @if ($myPdfs->isNotEmpty())
                    <ul class="mt-5 divide-y divide-[#f0e8df] dark:divide-[#453538]" aria-label="Your private PDF files">
                        @foreach ($myPdfs as $pdf)
                            <li class="flex flex-wrap items-center justify-between gap-3 py-3 first:pt-0 last:pb-0">
                                <div class="min-w-0">
                                    <p class="break-words text-sm font-medium text-[#392b2a] dark:text-[#f4e9e2]">{{ $pdf->original_name }}</p>
                                    <p class="mt-1 text-xs text-[#8a7065] dark:text-[#c1aaa0]">Added {{ $pdf->created_at->diffForHumans() }}</p>
                                </div>
                                <div class="flex shrink-0 items-center gap-4">
                                    <a href="{{ route('books.pdfs.reader', [$book, $pdf]) }}"
                                       class="inline-flex min-h-11 items-center rounded-md px-2 text-sm font-semibold text-[#713640] underline hover:text-[#542331] focus:outline-none focus:ring-2 focus:ring-[#b98768] dark:text-[#e4b3a5]">
                                        Read
                                        <span class="sr-only">{{ $pdf->original_name }}</span>
                                    </a>
                                    <a href="{{ route('books.pdfs.download', [$book, $pdf]) }}"
                                       class="inline-flex min-h-11 items-center rounded-md px-2 text-sm font-semibold text-[#713640] underline hover:text-[#542331] focus:outline-none focus:ring-2 focus:ring-[#b98768] dark:text-[#e4b3a5]">
                                        Download
                                        <span class="sr-only">{{ $pdf->original_name }}</span>
                                    </a>
                                    <form method="POST" action="{{ route('books.pdfs.destroy', [$book, $pdf]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex min-h-11 items-center rounded-md px-2 text-sm font-semibold text-red-700 underline hover:text-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 dark:text-red-300 dark:hover:text-red-200">
                                            Delete
                                            <span class="sr-only">{{ $pdf->original_name }}</span>
                                        </button>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="mt-5 rounded-xl border border-dashed border-[#d9c6b6] px-4 py-5 text-center text-sm text-[#8a7065] dark:border-[#594749] dark:text-[#c1aaa0]">
                        You haven’t uploaded any PDFs for this book yet.
                    </p>
                @endif
            </section>

            <section class="rounded-2xl border border-[#eadfd4] bg-[#fffdf9] p-5 shadow-sm sm:p-6 dark:border-[#453538] dark:bg-[#291f20]">
                <div class="flex items-baseline justify-between gap-3">
                    <h2 class="text-lg font-semibold text-[#392b2a] dark:text-[#f4e9e2]">Reader reviews</h2>
                    <span class="text-sm text-[#8a7065] dark:text-[#c1aaa0]">{{ $count }} {{ Str::plural('review', $count) }}</span>
                </div>
                <div class="mt-4 divide-y divide-[#f0e8df] dark:divide-[#453538]">
                    @forelse ($reviews as $review)
                        <article class="py-4 first:pt-0 last:pb-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <a href="{{ route('users.show', $review->user->username) }}"
                                   class="text-sm font-semibold text-[#713640] hover:underline focus:outline-none focus:ring-2 focus:ring-[#b98768] dark:text-[#e4b3a5]">
                                    {{ '@' . $review->user->username }}
                                </a>
                                <x-star-display :rating="$review->rating" />
                                <span class="text-xs text-[#9a8579] dark:text-[#b39d93]">{{ $review->created_at->diffForHumans() }}</span>
                            </div>
                            @if ($review->body)
                                <p class="mt-2 whitespace-pre-line break-words text-sm leading-6 text-[#564541] dark:text-[#e3d3ca]">{{ $review->body }}</p>
                            @endif
                        </article>
                    @empty
                        <p class="py-5 text-sm text-[#8a7065] dark:text-[#c1aaa0]">Be the first to review this book.</p>
                    @endforelse
                </div>
                @if ($reviews->hasPages())
                    <div class="mt-4 border-t border-[#f0e8df] pt-4 dark:border-[#453538]">{{ $reviews->links() }}</div>
                @endif
            </section>

        </div>
    </div>
</x-app-layout>
