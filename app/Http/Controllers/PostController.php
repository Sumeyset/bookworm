<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\QuoteCard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Illuminate\View\View;

class PostController extends Controller
{
    public function show(Request $request, Post $post): View
    {
        $post = Post::query()->whereKey($post->id)->with(['user', 'book', 'photos'])->withReactions($request->user())->firstOrFail();

        return view('feed.show', ['item' => $post]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'type' => ['required', Rule::in(Post::TYPES)],
            'body' => ['nullable', 'string', 'max:2000'],
            'book_id' => ['nullable', 'integer', 'exists:books,id'],
            'quote_card_id' => ['nullable', 'integer', Rule::exists('quote_cards', 'id')->where('user_id', $user->id)],
            'quote_text' => ['nullable', 'string', 'max:1000'],
            'quote_page' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'card_style' => ['nullable', 'string', Rule::in(Post::CARD_STYLES)],
            'card_font' => ['nullable', 'string', Rule::in(Post::CARD_FONTS)],
            'photos' => ['nullable', 'array', 'max:' . Post::MAX_PHOTOS, 'prohibited_if:type,quote'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp,gif', 'max:10240'],
            'photo_ratio' => ['nullable', 'string', Rule::in(Post::PHOTO_RATIOS)],
            'photo_meta' => ['nullable', 'array'],
            'photo_meta.*.focus_x' => ['nullable', 'integer', 'between:0,100'],
            'photo_meta.*.focus_y' => ['nullable', 'integer', 'between:0,100'],
            'photo_meta.*.filter' => ['nullable', 'string', Rule::in(Post::PHOTO_FILTERS)],
        ], [
            'photos.max' => 'You can add up to ' . Post::MAX_PHOTOS . ' photos to a post.',
            'photos.*.image' => 'Each photo must be an image (JPG, PNG, WebP or GIF).',
            'photos.*.mimes' => 'Each photo must be an image (JPG, PNG, WebP or GIF).',
            'photos.*.max' => 'Each photo must be smaller than 10 MB.',
        ]);

        $photos = $request->file('photos', []);
        if ($validated['type'] === 'post' && blank($validated['body'] ?? null) && empty($photos)) {
            throw ValidationException::withMessages(['body' => 'Write something or add a photo to post.']);
        }

        $post = new Post([
            'user_id' => $user->id,
            'type' => $validated['type'],
            'body' => trim((string) ($validated['body'] ?? '')) ?: null,
            'card_style' => $validated['card_style'] ?? null,
            'card_font' => $validated['card_font'] ?? null,
            'photo_ratio' => $photos ? ($validated['photo_ratio'] ?? 'square') : null,
        ]);

        if ($validated['type'] === 'quote') {
            if (! empty($validated['quote_card_id'])) {
                // Share one of your saved highlights.
                $card = QuoteCard::with('highlight.bookPdf')->findOrFail($validated['quote_card_id']);
                $post->fill([
                    'quote_card_id' => $card->id,
                    'quote_text' => $card->highlight->text,
                    'quote_page' => $card->highlight->page_number,
                    'book_id' => $card->highlight->bookPdf->book_id,
                ]);
                $card->shared_at ??= now();
                $card->save();
            } else {
                $request->validate(['quote_text' => ['required']], ['quote_text.required' => 'Add the quote you want to share.']);
                $post->fill([
                    'quote_text' => trim($validated['quote_text']),
                    'quote_page' => $validated['quote_page'] ?? null,
                    'book_id' => $validated['book_id'] ?? null,
                ]);
            }
        } else {
            $post->book_id = $validated['book_id'] ?? null;
        }

        DB::transaction(function () use ($post, $photos, $validated, $user) {
            $post->save();

            foreach (array_values($photos) as $position => $file) {
                $path = $file->store('post-photos/' . $user->id, 'public');
                if ($path === false) {
                    throw new RuntimeException('Failed to store an uploaded photo.');
                }
                [$width, $height] = @getimagesize($file->getRealPath()) ?: [null, null];
                $meta = $validated['photo_meta'][$position] ?? [];

                $post->photos()->create([
                    'path' => $path,
                    'width' => $width,
                    'height' => $height,
                    'position' => $position,
                    'focus_x' => $meta['focus_x'] ?? 50,
                    'focus_y' => $meta['focus_y'] ?? 50,
                    'filter' => ($meta['filter'] ?? 'normal') === 'normal' ? null : $meta['filter'],
                ]);
            }
        });

        if ($request->expectsJson()) {
            return response()->json([
                'id' => $post->id,
                'url' => route('posts.show', $post),
                'share_url' => $post->quoteCard ? route('quotes.public', $post->quoteCard->share_token) : null,
            ], 201);
        }

        return to_route('feed.index')->with('status', $post->isQuote() ? 'Your quote is on the feed.' : ($photos ? 'Your photos are on the feed.' : 'Posted to your reading circle.'));
    }

    public function destroy(Request $request, Post $post): RedirectResponse
    {
        abort_unless($post->user_id === $request->user()->id, 403);
        $post->delete();

        return back()->with('status', 'Post deleted.');
    }
}
