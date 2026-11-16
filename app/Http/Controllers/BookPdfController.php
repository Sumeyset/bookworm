<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookPdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BookPdfController extends Controller
{
    public function store(Request $request, Book $book): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'pdfs' => ['required', 'array', 'min:1', 'max:5'],
            'pdfs.*' => ['required', 'file', 'extensions:pdf,epub', 'mimetypes:application/pdf,application/epub+zip,application/zip,application/octet-stream', 'max:20480'],
        ], [
            'pdfs.*.extensions' => 'You can upload PDF or EPUB files.',
            'pdfs.*.mimetypes' => 'That file doesn’t look like a PDF or EPUB.',
            'pdfs.*.uploaded' => 'The PDF could not be uploaded. Check that it is no larger than 20 MB and that your PHP upload limits allow the request, then try again.',
            'pdfs.*.max' => 'Each PDF must be 20 MB or smaller.',
        ]);

        foreach ($validated['pdfs'] as $pdf) {
            $extension = strtolower($pdf->getClientOriginalExtension()) === 'epub' ? 'epub' : 'pdf';
            $path = $pdf->storeAs('book-pdfs/' . $request->user()->id . '/' . $book->id, \Illuminate\Support\Str::random(40) . '.' . $extension, 'local');

            if ($path === false) {
                throw new RuntimeException('Failed to store the uploaded book PDF.');
            }

            $originalName = basename(str_replace('\\', '/', $pdf->getClientOriginalName()));
            $originalName = preg_replace('/[\x00-\x1F\x7F]/u', '', $originalName) ?: 'book.pdf';

            $request->user()->bookPdfs()->create([
                'book_id' => $book->id,
                'path' => $path,
                'original_name' => mb_substr($originalName, 0, 255),
            ]);
        }

        $message = count($validated['pdfs']) === 1 ? 'Your book file was uploaded privately.' : 'Your book files were uploaded privately.';

        if ($request->expectsJson()) {
            session()->flash('status', $message);

            return response()->json(['uploaded' => count($validated['pdfs']), 'redirect' => route('books.show', $book) . '#private-pdfs'], 201);
        }

        return to_route('books.show', $book)->with('status', $message);
    }

    public function download(Request $request, Book $book, BookPdf $bookPdf): StreamedResponse
    {
        $this->authorizePdf($request, $book, $bookPdf);
        abort_unless(Storage::disk('local')->exists($bookPdf->path), 404);

        return Storage::disk('local')->download($bookPdf->path, $bookPdf->original_name, [
            'Content-Type' => $bookPdf->mime(),
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function reader(Request $request, Book $book, BookPdf $bookPdf): View
    {
        $this->authorizePdf($request, $book, $bookPdf);
        abort_unless(Storage::disk('local')->exists($bookPdf->path), 404);

        // "Where was I?": back after a few days away, Barnaby offers a recap.
        $lastRead = \App\Models\ReadingSession::where('user_id', $request->user()->id)->where('book_id', $book->id)->max('read_on');
        $daysAway = $lastRead ? (int) \Illuminate\Support\Carbon::parse($lastRead)->diffInDays(today()) : 0;

        return view($bookPdf->isEpub() ? 'books.pdfs.epub' : 'books.pdfs.reader', [
            'daysAway' => $daysAway,
            'book' => $book,
            'bookPdf' => $bookPdf,
            'fileUrl' => route('books.pdfs.file', [$book, $bookPdf], false),
            'highlightsUrl' => route('books.pdfs.highlights.index', [$book, $bookPdf], false),
            'storeHighlightUrl' => route('books.pdfs.highlights.store', [$book, $bookPdf], false),
            'readingStatus' => $request->user()->shelfItems()->where('book_id', $book->id)->value('status') === 'read' ? 'read' : 'reading',
            'initialPage' => $request->user()->shelfItems()->where('book_id', $book->id)->value('current_page') ?? 1,
            'progressUrl' => route('books.shelf.update', $book, false),
        ]);
    }

    public function file(Request $request, Book $book, BookPdf $bookPdf): BinaryFileResponse
    {
        $this->authorizePdf($request, $book, $bookPdf);
        abort_unless(Storage::disk('local')->exists($bookPdf->path), 404);

        return response()->file(Storage::disk('local')->path($bookPdf->path), [
            'Content-Type' => $bookPdf->mime(),
            'Content-Disposition' => 'inline; filename="book.' . ($bookPdf->isEpub() ? 'epub' : 'pdf') . '"',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function destroy(Request $request, Book $book, BookPdf $bookPdf): RedirectResponse
    {
        $this->authorizePdf($request, $book, $bookPdf);

        if (! Storage::disk('local')->delete($bookPdf->path)) {
            throw new RuntimeException('Failed to remove the stored book PDF.');
        }

        $bookPdf->delete();

        return to_route('books.show', $book)->with('status', 'Your PDF was deleted.');
    }

    private function authorizePdf(Request $request, Book $book, BookPdf $bookPdf): void
    {
        abort_unless($bookPdf->book_id === $book->id, 404);
        abort_unless($bookPdf->user_id === $request->user()->id, 404);
    }
}
