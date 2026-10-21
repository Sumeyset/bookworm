<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookPdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BookPdfController extends Controller
{
    public function store(Request $request, Book $book): RedirectResponse
    {
        $validated = $request->validate([
            'pdfs' => ['required', 'array', 'min:1', 'max:5'],
            'pdfs.*' => ['required', 'file', 'mimes:pdf', 'max:20480'],
        ], [
            'pdfs.*.uploaded' => 'The PDF could not be uploaded. Check that it is no larger than 20 MB and that your PHP upload limits allow the request, then try again.',
            'pdfs.*.max' => 'Each PDF must be 20 MB or smaller.',
        ]);

        foreach ($validated['pdfs'] as $pdf) {
            $path = $pdf->store('book-pdfs/' . $request->user()->id . '/' . $book->id, 'local');

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

        return to_route('books.show', $book)->with('status', 'Your PDF files were uploaded privately.');
    }

    public function download(Request $request, Book $book, BookPdf $bookPdf): StreamedResponse
    {
        $this->authorizePdf($request, $book, $bookPdf);
        abort_unless(Storage::disk('local')->exists($bookPdf->path), 404);

        return Storage::disk('local')->download($bookPdf->path, $bookPdf->original_name, [
            'Content-Type' => 'application/pdf',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function reader(Request $request, Book $book, BookPdf $bookPdf): View
    {
        $this->authorizePdf($request, $book, $bookPdf);
        abort_unless(Storage::disk('local')->exists($bookPdf->path), 404);

        return view('books.pdfs.reader', [
            'book' => $book,
            'bookPdf' => $bookPdf,
            'fileUrl' => route('books.pdfs.file', [$book, $bookPdf], false),
            'highlightsUrl' => route('books.pdfs.highlights.index', [$book, $bookPdf], false),
            'storeHighlightUrl' => route('books.pdfs.highlights.store', [$book, $bookPdf], false),
        ]);
    }

    public function file(Request $request, Book $book, BookPdf $bookPdf): BinaryFileResponse
    {
        $this->authorizePdf($request, $book, $bookPdf);
        abort_unless(Storage::disk('local')->exists($bookPdf->path), 404);

        return response()->file(Storage::disk('local')->path($bookPdf->path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="book.pdf"',
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
