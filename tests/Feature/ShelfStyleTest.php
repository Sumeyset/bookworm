<?php

use App\Models\Book;
use App\Models\User;
use App\Support\ShelfStyle;
use Illuminate\Support\Carbon;

test('a reader can switch shelf styles, and visitors see the chosen style', function () {
    $me = User::factory()->create(['is_public' => true]);
    $book = Book::create(['ol_id' => 'OL-STYLE-1', 'title' => 'A Shelved Book', 'author_name' => 'Some Author']);
    $me->shelfItems()->create(['book_id' => $book->id, 'status' => 'read']);

    foreach (ShelfStyle::keys() as $style) {
        $this->actingAs($me)->post(route('shelf.style'), ['style' => $style])
            ->assertRedirect(route('users.show', $me->username) . '#bookshelf');
        expect($me->fresh()->shelf_style)->toBe($style);

        $page = $this->get(route('users.show', $me->username))->assertOk()->assertSee('A Shelved Book');
        $resolved = ShelfStyle::for($me->fresh());
        match ($resolved['layout']) {
            'booktree' => $page->assertSee('booktree__layer', false),
            'bookcase' => $page->assertSee('bookcase--' . $resolved['theme'], false),
            default => $page->assertSee('book-tree--' . $resolved['theme'], false),
        };
    }

    $this->post(route('shelf.style'), ['style' => 'neon-disco'])->assertSessionHasErrors('style');

    // The public share page uses the same style.
    $this->post(route('shelf.style'), ['style' => 'nook']);
    auth()->logout();
    $this->get(route('share.tree', $me->username))->assertOk()->assertSee('bookcase--nook', false);
});

test('"Follow the seasons" changes with the date', function () {
    expect(ShelfStyle::season(Carbon::parse('2026-12-15')))->toBe('christmas')
        ->and(ShelfStyle::season(Carbon::parse('2027-01-03')))->toBe('christmas')
        ->and(ShelfStyle::season(Carbon::parse('2026-10-20')))->toBe('autumn')
        ->and(ShelfStyle::season(Carbon::parse('2026-07-01')))->toBe('plain');

    $reader = User::factory()->create(['shelf_style' => 'seasons']);
    expect(ShelfStyle::for($reader, Carbon::parse('2026-12-24'))['theme'])->toBe('christmas');
});

test('the style picker lists every style, and only the owner sees it', function () {
    $me = User::factory()->create();
    $other = User::factory()->create();

    $page = $this->actingAs($me)->get(route('users.show', $me->username))->assertOk()->assertSee('shelf-style-dialog', false);
    foreach (ShelfStyle::STYLES as [$name]) {
        $page->assertSee($name);
    }

    $this->get(route('users.show', $other->username))->assertOk()->assertDontSee('shelf-style-dialog', false);
});
