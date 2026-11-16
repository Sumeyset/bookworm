<?php

use App\Models\Book;
use App\Models\User;
use App\Support\Challenges;
use Illuminate\Support\Carbon;

test('every month has four challenges, never both 3 and 5 books', function () {
    foreach (range(0, 23) as $i) {
        $keys = Challenges::forMonth(Carbon::parse('2026-01-01')->addMonths($i));
        expect($keys)->toHaveCount(4)->and($keys)->toEqual(array_unique($keys))
            ->and(in_array('three', $keys) && in_array('five', $keys))->toBeFalse();
    }
});

test('finishing a chunky book completes the challenge, pays acorns and gives a badge', function () {
    // Pick a month whose challenges include "chunky".
    $month = collect(range(0, 36))->map(fn ($i) => Carbon::parse('2026-01-15')->addMonths($i))
        ->first(fn ($m) => in_array('chunky', Challenges::forMonth($m), true));
    Carbon::setTestNow($month);

    $me = User::factory()->create();
    $book = Book::create(['ol_id' => 'OL-BIG', 'title' => 'A Big One', 'page_count' => 640]);
    $item = $me->shelfItems()->create(['book_id' => $book->id, 'status' => 'reading']);
    $item->update(['status' => 'read', 'read_at' => now()]);

    $key = Challenges::badgeKey($month, 'chunky');
    expect($me->badges()->where('key', $key)->exists())->toBeTrue()
        ->and($me->badges()->where('key', 'books_1')->exists())->toBeTrue()
        ->and($me->acornEvents()->where('key', $key)->value('amount'))->toBe(Challenges::POOL['chunky'][3])
        ->and($me->notifications()->where('data->kind', 'badge')->count())->toBe(2);

    // Checking again gives nothing twice.
    Challenges::check($me->fresh());
    expect($me->badges()->count())->toBe(2);

    Carbon::setTestNow();
});

test('the challenges page and profile badges render', function () {
    $me = User::factory()->create();
    $me->badges()->create(['key' => 'books_1', 'name' => 'First book', 'emoji' => '🌱', 'description' => 'Finished a first book']);

    $this->actingAs($me)->get(route('challenges.index'))->assertOk()->assertSee('This month')->assertSee('Milestones');
    $this->actingAs($me)->get(route('users.show', $me->username))->assertOk()->assertSee('First book');
});

test('the year page offers the story when there are finished books', function () {
    $me = User::factory()->create();
    $book = Book::create(['ol_id' => 'OL-YEAR', 'title' => 'Year Book', 'page_count' => 300]);
    $me->shelfItems()->create(['book_id' => $book->id, 'status' => 'read', 'read_at' => now()]);

    $this->actingAs($me)->get(route('year.show', [$me->username, now()->year]))
        ->assertOk()->assertSee('Play my year story')->assertSee('data-year-story', false)->assertSee('Your reading type');
});
