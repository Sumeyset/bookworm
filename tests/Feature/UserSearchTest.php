<?php

use App\Models\User;

test('a reader can search other readers by name or username', function () {
    $currentReader = User::factory()->create([
        'name' => 'Current Reader',
        'username' => 'current-reader',
    ]);
    $matchingReader = User::factory()->create([
        'name' => 'Alex Morgan',
        'username' => 'bookish-alex',
    ]);
    $otherReader = User::factory()->create([
        'name' => 'Jordan Lee',
        'username' => 'jordan-reads',
    ]);

    $this->actingAs($currentReader)
        ->get(route('users.index'))
        ->assertOk()
        ->assertSee('Find your reading people')
        ->assertDontSee('Alex Morgan')
        ->assertDontSee('Jordan Lee');

    $this->get(route('users.index', ['q' => 'bookish']))
        ->assertOk()
        ->assertSee('Alex Morgan')
        ->assertSee('@bookish-alex')
        ->assertSee(route('users.show', $matchingReader->username))
        ->assertDontSee('Jordan Lee')
        ->assertViewHas('readers', fn ($readers) => $readers->doesntContain('id', $currentReader->id));

    $this->get(route('users.index', ['q' => 'Jordan']))
        ->assertOk()
        ->assertSee('Jordan Lee')
        ->assertDontSee('Alex Morgan');
});

test('live reader search returns the five best matches, best first', function () {
    $me = User::factory()->create(['name' => 'Me Myself', 'username' => 'me']);
    $friend = User::factory()->create(['name' => 'Sam Mills', 'username' => 'samreads']);
    $me->following()->attach($friend->id);
    User::factory()->create(['name' => 'Alex Sander', 'username' => 'alex']);       // "s" starts a word in the name
    User::factory()->create(['name' => 'Sara Lee', 'username' => 'sara_lee']);     // name and username start with "s"
    User::factory()->create(['name' => 'Bea Toast', 'username' => 'beatoast']);     // only contains "s"
    foreach (range(1, 6) as $i) {
        User::factory()->create(['name' => "Zed $i", 'username' => "s_extra$i"]);
    }

    $results = $this->actingAs($me)->getJson(route('users.suggest', ['q' => 's']))->assertOk()->json();

    expect($results)->toHaveCount(5);
    // The reader you follow comes first among usernames starting with "s".
    expect($results[0]['username'])->toBe('samreads')
        ->and($results[0]['following'])->toBeTrue();
    expect(collect($results)->pluck('username'))->not->toContain('me')->not->toContain('beatoast');

    $exact = $this->getJson(route('users.suggest', ['q' => '@sara_lee']))->json();
    expect($exact[0]['username'])->toBe('sara_lee');

    // "%" and "_" are searched as plain characters.
    expect($this->getJson(route('users.suggest', ['q' => '%']))->json())->toBe([]);

    $this->getJson(route('users.suggest', ['q' => '']))->assertOk()->assertExactJson([]);
});
