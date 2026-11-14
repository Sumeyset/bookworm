<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookPdf;
use App\Models\BookPdfHighlight;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookPdfHighlightController extends Controller
{
    public function index(Request $request, Book $book, BookPdf $bookPdf): JsonResponse
    {
        $this->authorizePdf($request, $book, $bookPdf);

        return response()->json(
            $bookPdf->highlights()
                ->where('user_id', $request->user()->id)
                ->orderBy('page_number')
                ->get(['id', 'page_number', 'text', 'rectangles']),
        );
    }

    public function store(Request $request, Book $book, BookPdf $bookPdf): JsonResponse
    {
        $this->authorizePdf($request, $book, $bookPdf);

        $validated = $request->validate([
            'page_number' => ['required', 'integer', 'min:1'],
            'text' => ['required', 'string', 'max:2000'],
            'rectangles' => ['required', 'array', 'min:1', 'max:100'],
            'rectangles.*' => ['required', 'array:x,y,width,height'],
            'rectangles.*.x' => ['required', 'numeric', 'between:0,1'],
            'rectangles.*.y' => ['required', 'numeric', 'between:0,1'],
            'rectangles.*.width' => ['required', 'numeric', 'gt:0', 'lte:1'],
            'rectangles.*.height' => ['required', 'numeric', 'gt:0', 'lte:1'],
        ]);

        $highlight = $bookPdf->highlights()->create([
            'user_id' => $request->user()->id,
            ...$validated,
        ]);

        return response()->json(
            $highlight->only(['id', 'page_number', 'text', 'rectangles']),
            201,
        );
    }

    public function destroy(
        Request $request,
        Book $book,
        BookPdf $bookPdf,
        BookPdfHighlight $highlight,
    ): JsonResponse {
        $this->authorizePdf($request, $book, $bookPdf);
        abort_unless($highlight->book_pdf_id === $bookPdf->id, 404);
        abort_unless($highlight->user_id === $request->user()->id, 404);

        $highlight->delete();

        return response()->json(['deleted' => true]);
    }

    private function authorizePdf(Request $request, Book $book, BookPdf $bookPdf): void
    {
        abort_unless($bookPdf->book_id === $book->id, 404);
        abort_unless($bookPdf->user_id === $request->user()->id, 404);
    }
}
