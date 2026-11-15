<?php

use App\Models\Book;
use App\Models\User;
use App\Support\ShelfScene;
use App\Support\ShelfStyle;
use Illuminate\Support\Carbon;

test('a reader can switch between the tree and the photo shelves, and visitors see it', function () {
    $me = User::factory()->create(['is_public' => true]);
    $book = Book::create(['ol_id' => 'OL-STYLE-1', 'title' => 'A Shelved Book', 'author_name' => 'Some Author']);
    $me->shelfItems()->create(['book_id' => $book->id, 'status' => 'read']);

    foreach (ShelfStyle::keys() as $style) {
        $this->actingAs($me)->post(route('shelf.style'), ['style' => $style])->assertRedirect();
        $page = $this->get(route('users.show', $me->username))->assertOk()->assertSee('A Shelved Book');
        if (ShelfScene::has($style)) {
            $page->assertSee('photo-shelf--' . $style, false)->assertSee(ShelfScene::SCENES[$style]['image']);
        } else {
            $page->assertSee('tree-shelf.webp');
        }
    }

    $this->post(route('shelf.style'), ['style' => 'neon-disco'])->assertSessionHasErrors('style');

    $this->post(route('shelf.style'), ['style' => 'window']);
    auth()->logout();
    $this->get(route('share.tree', $me->username))->assertOk()->assertSee('photo-shelf--window', false)->assertSee('data-window-scene', false);
});

test('photo shelves show every scene shelf, and extra books go to "More shelves"', function () {
    $me = User::factory()->create(['shelf_style' => 'christmas']);
    $slots = ShelfScene::slotCount('christmas');
    $book = Book::create(['ol_id' => 'OL-STYLE-2', 'title' => 'Far Away Book']);
    $me->shelfItems()->create(['book_id' => $book->id, 'status' => 'read', 'shelf_slot' => $slots + 2, 'shelf_order' => 0]);

    $page = $this->actingAs($me)->get(route('users.show', $me->username))->assertOk();
    $page->assertSee('More shelves')->assertSee('Far Away Book');
    expect(substr_count($page->getContent(), 'photo-shelf__shelf '))->toBe($slots);
});

test('the window view follows the season', function () {
    expect(ShelfStyle::season(Carbon::parse('2026-12-15')))->toBe('winter')
        ->and(ShelfStyle::season(Carbon::parse('2026-04-10')))->toBe('spring')
        ->and(ShelfStyle::season(Carbon::parse('2026-07-01')))->toBe('summer')
        ->and(ShelfStyle::season(Carbon::parse('2026-10-20')))->toBe('autumn');

    foreach (['spring', 'summer', 'autumn', 'winter'] as $season) {
        foreach (['dawn', 'day', 'dusk', 'night'] as $part) {
            expect(public_path("images/shelves/window/{$season}-{$part}.webp"))->toBeFile();
        }
    }
});

test('the christmas tree triangles hold one book each', function () {
    $caps = \App\Support\ShelfScene::capacities('christmas');
    expect([$caps[6], $caps[9], $caps[10], $caps[13]])->toBe([1, 1, 1, 1])
        ->and($caps[0])->toBe(\App\Models\UserBookShelf::MAX_BOOKS_PER_SHELF);

    $user = \App\Models\User::factory()->create(['shelf_style' => 'christmas']);
    expect(\App\Support\ShelfScene::capacityFor($user, 6))->toBe(1)
        ->and(\App\Support\ShelfScene::capacityFor($user->forceFill(['shelf_style' => 'tree']), 6))->toBe(3);
});
