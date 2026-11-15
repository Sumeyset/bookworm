<?php

use App\Models\AcornEvent;
use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use App\Support\Acorns;
use Illuminate\Support\Carbon;

function acornBook(string $id, string $title = 'A Book'): Book
{
    return Book::create(['ol_id' => $id, 'title' => $title]);
}

test('finishing a book earns acorns once, with a bonus for long books', function () {
    $me = User::factory()->create();
    $item = $me->shelfItems()->create(['book_id' => acornBook('OL-A1')->id, 'status' => 'reading', 'total_pages' => 400]);

    $item->update(['status' => 'read', 'current_page' => 400]);
    // 50 + 400/20 for finishing, 5 for reading today, 30 (capped) for the 400 pages
    expect(AcornEvent::where('reason', 'finish')->sum('amount'))->toBe(70)
        ->and(AcornEvent::where('reason', 'day')->sum('amount'))->toBe(5)
        ->and(AcornEvent::where('reason', 'pages')->sum('amount'))->toBe(30)
        ->and($me->fresh()->acorns)->toBe(105);

    // Marking it unread and read again pays nothing more for the book.
    $item->update(['status' => 'reading']);
    $item->update(['status' => 'read']);
    expect(AcornEvent::where('reason', 'finish')->count())->toBe(1);
});

test('only three finished books a day pay, and pages pay 1 per 10 up to 30', function () {
    $me = User::factory()->create();
    foreach (range(1, 5) as $i) {
        $me->shelfItems()->create(['book_id' => acornBook("OL-B$i")->id, 'status' => 'read']);
    }
    expect(AcornEvent::where('reason', 'finish')->count())->toBe(3);

    $other = User::factory()->create();
    $item = $other->shelfItems()->create(['book_id' => acornBook('OL-B9')->id, 'status' => 'reading', 'total_pages' => 900]);
    $item->update(['current_page' => 25]);
    expect(AcornEvent::where('user_id', $other->id)->where('reason', 'pages')->sum('amount'))->toBe(2);
    $item->update(['current_page' => 60]);
    expect(AcornEvent::where('user_id', $other->id)->where('reason', 'pages')->sum('amount'))->toBe(6);
    $item->update(['current_page' => 800]);
    expect(AcornEvent::where('user_id', $other->id)->where('reason', 'pages')->sum('amount'))->toBe(30);
    expect(AcornEvent::where('user_id', $other->id)->where('reason', 'day')->count())->toBe(1);
});

test('reviews and a 7-day streak earn acorns; imports earn nothing', function () {
    $me = User::factory()->create();
    $book = acornBook('OL-C1');
    Review::create(['user_id' => $me->id, 'book_id' => $book->id, 'rating' => 4]);
    expect(AcornEvent::where('reason', 'review')->count())->toBe(0);
    Review::where('user_id', $me->id)->first()->update(['body' => 'Lovely.']);
    expect(AcornEvent::where('reason', 'review')->sum('amount'))->toBe(15);

    foreach (range(6, 1) as $daysAgo) {
        \App\Models\ReadingSession::create(['user_id' => $me->id, 'book_id' => $book->id, 'read_on' => today()->subDays($daysAgo), 'pages_read' => 5]);
    }
    $item = $me->shelfItems()->create(['book_id' => $book->id, 'status' => 'reading', 'total_pages' => 100]);
    $item->update(['current_page' => 5]);
    expect(Acorns::streak($me))->toBe(7)
        ->and(AcornEvent::where('reason', 'streak')->sum('amount'))->toBe(25);

    Acorns::$quiet = true;
    $quietItem = $me->shelfItems()->create(['book_id' => acornBook('OL-C2')->id, 'status' => 'read']);
    Acorns::$quiet = false;
    expect(AcornEvent::where('key', 'finish:' . $quietItem->book_id)->exists())->toBeFalse();
});

test('the shop sells decorations, which show on the tree and can be switched off', function () {
    $me = User::factory()->create(['acorns' => 160]);

    $this->actingAs($me)->get(route('shop.index'))->assertOk()->assertSee('Fairy lights')->assertSee('Owl');
    $this->post(route('shop.buy', 'owl'))->assertSessionHasErrors('shop');
    $this->post(route('shop.buy', 'lights'))->assertSessionHasNoErrors();
    expect($me->fresh()->acorns)->toBe(10)
        ->and(AcornEvent::where('reason', 'shop')->sum('amount'))->toBe(-150);
    $this->post(route('shop.buy', 'lights'))->assertSessionHasErrors('shop');

    $this->get(route('users.show', $me->username))->assertSee('decor-lights', false);
    $this->post(route('shop.toggle', 'lights'));
    $this->get(route('users.show', $me->username))->assertDontSee('decor-lights', false);

    $this->post(route('shop.toggle', 'owl'))->assertNotFound();
});

test('seasonal decorations are only for sale in season', function () {
    expect(Acorns::onSale('snow', Carbon::parse('2026-12-20')))->toBeTrue()
        ->and(Acorns::onSale('snow', Carbon::parse('2027-01-03')))->toBeTrue()
        ->and(Acorns::onSale('snow', Carbon::parse('2026-07-01')))->toBeFalse()
        ->and(Acorns::onSale('autumn', Carbon::parse('2026-10-01')))->toBeTrue()
        ->and(Acorns::onSale('autumn', Carbon::parse('2026-03-01')))->toBeFalse()
        ->and(Acorns::onSale('owl', Carbon::parse('2026-03-01')))->toBeTrue();
});
