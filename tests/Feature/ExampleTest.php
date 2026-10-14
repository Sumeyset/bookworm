<?php

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

it('shows the welcome page to signed-out visitors without requesting a quote', function () {
    Cache::flush();
    Http::fake();

    $this->get('/')
        ->assertOk()
        ->assertSee('Bookworm')
        ->assertSee('For the love of stories')
        ->assertSee('Find your next favorite book,')
        ->assertSee('and share the stories you love.')
        ->assertSee('PRIDE AND')
        ->assertSee('THE')
        ->assertSee('LITTLE')
        ->assertSee('Log in')
        ->assertSee('Join Bookworm')
        ->assertSee('Join to explore books')
        ->assertSee('rounded-[2rem]');

    Http::assertNothingSent();
});

it('shows the daily ZenQuotes quote and attribution to signed-in readers', function () {
    Cache::flush();
    Http::fake([
        'https://zenquotes.io/api/today' => Http::response([
            ['q' => 'A room without books is like a body without a soul.', 'a' => 'Cicero'],
        ]),
    ]);

    $this->actingAs(User::factory()->create())
        ->get('/')
        ->assertOk()
        ->assertSee('A little thought for today')
        ->assertSee('A room without books is like a body without a soul.')
        ->assertSee('Cicero')
        ->assertSee('Find your next favorite book,')
        ->assertSee('and share the stories you love.')
        ->assertDontSee('A note for today')
        ->assertDontSee('Take a breath. Let the words stay awhile.')
        ->assertSee('Inspirational quotes provided by')
        ->assertSee('https://zenquotes.io/')
        ->assertSee('z-50')
        ->assertSee('max-w-3xl')
        ->assertSee('Welcome, '.$this->app['auth']->user()->name.'!')
        ->assertDontSee('Welcome, reader.')
        ->assertDontSee('Join Bookworm');
});

it('shows a helpful message when ZenQuotes is unavailable', function () {
    Cache::flush();
    Http::fake([
        'https://zenquotes.io/api/today' => Http::response([], 503),
    ]);

    $this->actingAs(User::factory()->create())
        ->get('/')
        ->assertOk()
        ->assertSee('Today’s quote couldn’t be loaded just now.');
});

it('shows the books and profile menu to signed-in readers', function () {
    Cache::flush();
    Http::fake([
        'https://zenquotes.io/api/today' => Http::response([
            ['q' => 'A reader lives a thousand lives.', 'a' => 'George R. R. Martin'],
        ]),
    ]);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/')
        ->assertOk()
        ->assertSee('Books')
        ->assertSee($user->name)
        ->assertSee('Profile settings')
        ->assertSee('Log out')
        ->assertSee('z-[100]')
        ->assertSee('A reader lives a thousand lives.')
        ->assertDontSee('Dashboard')
        ->assertDontSee('Join Bookworm');
});
