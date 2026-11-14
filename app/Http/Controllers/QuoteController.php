<?php

namespace App\Http\Controllers;

use App\Models\BookPdf;
use App\Models\BookPdfHighlight;
use App\Models\QuoteCard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class QuoteController extends Controller
{
    public function store(Request $request, BookPdf $bookPdf, BookPdfHighlight $highlight): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        abort_unless($bookPdf->user_id === $request->user()->id, 404);
        abort_unless($highlight->book_pdf_id === $bookPdf->id && $highlight->user_id === $request->user()->id, 404);

        QuoteCard::firstOrCreate(
            ['book_pdf_highlight_id' => $highlight->id],
            [
                'user_id' => $request->user()->id,
                'share_token' => (string) Str::uuid(),
            ],
        );

        if ($request->expectsJson()) {
            return response()->json(['saved' => true], 201);
        }

        return back()->with('status', 'Saved to your quote collection.');
    }

    public function share(Request $request, QuoteCard $quote): RedirectResponse
    {
        abort_unless($quote->user_id === $request->user()->id, 404);
        $quote->forceFill(['shared_at' => $quote->shared_at ? null : now()])->save();

        return back()->with('status', $quote->shared_at ? 'Your quote link is ready to share.' : 'Quote sharing is turned off.');
    }

    public function destroy(Request $request, QuoteCard $quote): RedirectResponse
    {
        abort_unless($quote->user_id === $request->user()->id, 404);
        $book = $quote->highlight->bookPdf->book;
        $quote->delete();

        return to_route('books.show', $book)->with('status', 'Quote removed from your collection.');
    }

    public function showPublic(QuoteCard $quote): View
    {
        abort_unless($quote->shared_at !== null, 404);
        $quote->load(['user', 'highlight.bookPdf.book']);

        return view('quotes.public', compact('quote'));
    }
}
