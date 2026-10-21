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
        ->assertDontSee(route('users.show', $currentReader->username));

    $this->get(route('users.index', ['q' => 'Jordan']))
        ->assertOk()
        ->assertSee('Jordan Lee')
        ->assertDontSee('Alex Morgan');
});
