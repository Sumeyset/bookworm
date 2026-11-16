<?php

use App\Http\Controllers\BookClubController;
use App\Models\Book;
use App\Models\BookClub;
use App\Models\User;
use Illuminate\Support\Carbon;

function clubBook(): Book
{
    return Book::firstOrCreate(['ol_id' => 'OL-CLUB'], ['title' => 'The Club Book', 'page_count' => 300]);
}

test('the schedule splits a book into even parts with due dates', function () {
    $parts = BookClubController::schedule(300, 4, 7, Carbon::parse('2026-12-01'));

    expect($parts)->toHaveCount(4)
        ->and($parts[0])->toMatchArray(['from_page' => 1, 'to_page' => 75, 'due_on' => '2026-12-08'])
        ->and($parts[3])->toMatchArray(['from_page' => 226, 'to_page' => 300, 'due_on' => '2026-12-29']);
});

test('a reader starts a club, picks a book, friends join and talk without spoilers', function () {
    $owner = User::factory()->create();
    $friend = User::factory()->create();
    $book = clubBook();
    $owner->shelfItems()->create(['book_id' => $book->id, 'status' => 'want_to_read']);

    $this->actingAs($owner)->post(route('clubs.store'), ['name' => 'Night Owls', 'emoji' => '🌙'])->assertRedirect();
    $club = BookClub::firstWhere('name', 'Night Owls');
    expect($club->isOwner($owner))->toBeTrue();

    $this->actingAs($owner)->post(route('clubs.rounds.store', $club), [
        'book_id' => $book->id, 'pages' => 300, 'sections' => 3, 'every_days' => 7, 'starts_on' => today()->toDateString(),
    ])->assertRedirect();
    $round = $club->fresh()->currentRound;
    expect($round->sections)->toHaveCount(3);

    // Open club: anyone can join; the book lands on their shelf.
    $this->actingAs($friend)->post(route('clubs.join', $club))->assertRedirect(route('clubs.show', $club));
    expect($club->hasMember($friend))->toBeTrue()
        ->and($friend->shelfItems()->where('book_id', $book->id)->value('status'))->toBe('reading')
        ->and($owner->notifications()->where('data->kind', 'club')->count())->toBe(1);

    // The owner has read part 1 and talks about it.
    $owner->shelfItems()->where('book_id', $book->id)->update(['current_page' => 120, 'status' => 'reading']);
    $section = $round->sections->first();
    $this->actingAs($owner)->post(route('clubs.posts.store', [$club, $section]), ['body' => 'What a twist at the lighthouse!'])->assertRedirect();

    // The friend hasn't reached the end of part 1: it is locked for them, open for the owner.
    $this->actingAs($friend)->get(route('clubs.show', $club))->assertOk()->assertSee('Read to page 100 to open this part');
    $this->actingAs($owner)->get(route('clubs.show', $club))->assertOk()->assertSee('What a twist at the lighthouse!');
});

test('private clubs are hidden from readers who were not invited', function () {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    $club = BookClub::create(['owner_id' => $owner->id, 'name' => 'Secret Shelf', 'is_private' => true]);
    $club->members()->attach($owner->id, ['role' => 'owner', 'joined_at' => now()]);

    $this->actingAs($stranger)->get(route('clubs.show', $club))->assertNotFound();
    $this->actingAs($stranger)->post(route('clubs.join', $club))->assertNotFound();
    $this->actingAs($stranger)->get(route('clubs.index'))->assertOk()->assertDontSee('Secret Shelf');
});

test('when the owner leaves, the club passes to the next member', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $club = BookClub::create(['owner_id' => $owner->id, 'name' => 'Passing On']);
    $club->members()->attach($owner->id, ['role' => 'owner', 'joined_at' => now()->subDay()]);
    $club->members()->attach($member->id, ['role' => 'member', 'joined_at' => now()]);

    $this->actingAs($owner)->delete(route('clubs.leave', $club))->assertRedirect(route('clubs.index'));
    expect($club->fresh()->owner_id)->toBe($member->id);
});

test('joining a club counts for the reading together challenge', function () {
    $owner = User::factory()->create();
    $reader = User::factory()->create();
    $club = BookClub::create(['owner_id' => $owner->id, 'name' => 'Challenge Club']);
    $club->members()->attach($owner->id, ['role' => 'owner', 'joined_at' => now()]);
    $this->actingAs($reader)->post(route('clubs.join', $club));

    $together = (new ReflectionMethod(\App\Support\Challenges::class, 'joinedTogether'));
    expect($together->invoke(null, $reader, now()->startOfMonth(), now()->endOfMonth()))->toBe(1);
});
