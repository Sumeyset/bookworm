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

test('photo shelves keep every book in the picture, with no "More shelves"', function () {
    $me = User::factory()->create(['shelf_style' => 'christmas']);
    $slots = ShelfScene::slotCount('christmas');
    $book = Book::create(['ol_id' => 'OL-STYLE-2', 'title' => 'Far Away Book']);
    $me->shelfItems()->create(['book_id' => $book->id, 'status' => 'read', 'shelf_slot' => $slots + 2, 'shelf_order' => 0]);

    $page = $this->actingAs($me)->get(route('users.show', $me->username))->assertOk();
    $page->assertDontSee('More shelves')->assertSee('Far Away Book');
    expect(substr_count($page->getContent(), 'photo-shelf__shelf '))->toBe($slots)
        ->and($me->shelfItems()->first()->shelf_slot)->toBeLessThan($slots);
});

test('wall shelves and the window nook grow as books are added', function () {
    expect(ShelfScene::bandsFor('wall', 12))->toBe(0)
        ->and(ShelfScene::bandsFor('wall', 13))->toBe(1)
        ->and(ShelfScene::slotCount('wall', 2))->toBe(18)
        ->and(ShelfScene::bandsFor('window', 13))->toBe(1)->and(ShelfScene::bandsFor('window', 21))->toBe(3)
        ->and(ShelfScene::bandsFor('christmas', 60))->toBe(0);

    $me = User::factory()->create(['shelf_style' => 'wall']);
    foreach (range(1, 40) as $i) {
        $book = Book::create(['ol_id' => "OL-GROW-$i", 'title' => "Grow Book $i"]);
        $me->shelfItems()->create(['book_id' => $book->id, 'status' => 'read', 'shelf_slot' => intdiv($i - 1, 3), 'shelf_order' => ($i - 1) % 3]);
    }

    $page = $this->actingAs($me)->get(route('users.show', $me->username))->assertOk();
    $page->assertSee('Grow Book 40')->assertSee('photo-shelf__img--grown', false);
    expect(substr_count($page->getContent(), 'photo-shelf__shelf '))->toBe(15);
});

test('snow from the Christmas set shows on photo shelves too', function () {
    $me = User::factory()->create(['shelf_style' => 'wall']);
    $me->decorations()->create(['item' => 'snow', 'enabled' => true]);

    $this->actingAs($me)->get(route('users.show', $me->username))
        ->assertSee('decor-snow', false)->assertSee('tree-fx__snow', false);
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

test('the christmas tree shelves hold as many books as fit', function () {
    $caps = \App\Support\ShelfScene::capacities('christmas');
    expect([$caps[6], $caps[9], $caps[10], $caps[13]])->toBe([1, 1, 1, 1])
        ->and([$caps[0], $caps[2], $caps[4], $caps[7]])->toBe([2, 3, 4, 3]);

    $user = \App\Models\User::factory()->create(['shelf_style' => 'christmas']);
    expect(\App\Support\ShelfScene::capacityFor($user, 6))->toBe(1)
        ->and(\App\Support\ShelfScene::capacityFor($user->forceFill(['shelf_style' => 'tree']), 6))->toBe(3);
});

test('every shop decoration shows on the photo shelves', function () {
    foreach (['tree', 'wall', 'window', 'christmas'] as $style) {
        $me = User::factory()->create(['shelf_style' => $style]);
        foreach (array_keys(\App\Support\Acorns::ITEMS) as $item) {
            $me->decorations()->create(['item' => $item, 'enabled' => true]);
        }
        $page = $this->actingAs($me)->get(route('users.show', $me->username))->assertOk();
        foreach (['decor-lights', 'tree-ornament', 'tree-lantern', 'tree-creature--nest', 'tree-creature--owl', 'tree-creature--swing', 'tree-creature--squirrel', 'tree-butterfly', 'tree-firefly', 'tree-fall'] as $piece) {
            $page->assertSee($piece, false);
        }
    }
});

test('spine colours are read from the cover', function () {
    $path = tempnam(sys_get_temp_dir(), 'cover') . '.png';
    $image = imagecreatetruecolor(60, 90);
    imagefill($image, 0, 0, imagecolorallocate($image, 20, 60, 140)); // a deep blue cover
    imagefilledrectangle($image, 10, 10, 50, 30, imagecolorallocate($image, 250, 250, 250)); // white title box
    imagepng($image, $path);

    [$color, $ink] = \App\Support\SpineColor::fromImage($path);
    [$r, $g, $b] = sscanf($color, '#%02X%02X%02X');
    expect($b)->toBeGreaterThan($r + 60)->and($ink)->toBe('#FFF4DF'); // blue spine, light text
});
