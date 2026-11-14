<?php

use App\Models\Post;
use App\Models\PostPhoto;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('a reader can post photos with a frame, focus and filters, and they show in the feed', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('posts.store'), [
        'type' => 'post',
        'body' => 'My reading nook this weekend.',
        'photo_ratio' => 'portrait',
        'photos' => [
            UploadedFile::fake()->image('nook.jpg', 1200, 1500),
            UploadedFile::fake()->image('shelf.png', 1600, 1200),
        ],
        'photo_meta' => [
            ['focus_x' => 30, 'focus_y' => 70, 'filter' => 'warm'],
            ['focus_x' => 50, 'focus_y' => 50, 'filter' => 'normal'],
        ],
    ])->assertSessionHasNoErrors()->assertRedirect(route('feed.index'));

    $post = Post::with('photos')->firstOrFail();
    expect($post->photo_ratio)->toBe('portrait')
        ->and($post->photos)->toHaveCount(2)
        ->and($post->photos[0]->focus_x)->toBe(30)
        ->and($post->photos[0]->filter)->toBe('warm')
        ->and($post->photos[0]->width)->toBe(1200)
        ->and($post->photos[1]->filter)->toBeNull();
    Storage::disk('public')->assertExists($post->photos[0]->path);
    expect($post->photos[0]->path)->toStartWith('post-photos/' . $user->id . '/');

    $this->get(route('feed.index'))
        ->assertOk()
        ->assertSee('My reading nook this weekend.')
        ->assertSee('ig-ratio--portrait', false)
        ->assertSee('pf-warm', false)
        ->assertSee('1/2');

    // Deleting the post removes the files.
    $paths = $post->photos->pluck('path');
    $this->delete(route('posts.destroy', $post))->assertRedirect();
    $paths->each(fn ($path) => Storage::disk('public')->assertMissing($path));
    expect(PostPhoto::count())->toBe(0);
});

test('a photo post can go without a caption, but a post needs words or a photo', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('posts.store'), ['type' => 'post', 'photos' => [UploadedFile::fake()->image('a.jpg')]])
        ->assertSessionHasNoErrors();
    expect(Post::count())->toBe(1);

    $this->post(route('posts.store'), ['type' => 'post'])->assertSessionHasErrors('body');
});

test('photo uploads are checked', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('posts.store'), ['type' => 'post', 'photos' => [UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf')]])
        ->assertSessionHasErrors('photos.0');

    $this->post(route('posts.store'), ['type' => 'post', 'photos' => array_fill(0, 11, UploadedFile::fake()->image('a.jpg'))])
        ->assertSessionHasErrors('photos');

    $this->post(route('posts.store'), ['type' => 'post', 'photos' => [UploadedFile::fake()->image('a.jpg')], 'photo_meta' => [['filter' => 'sparkle']]])
        ->assertSessionHasErrors('photo_meta.0.filter');

    $this->post(route('posts.store'), ['type' => 'quote', 'quote_text' => 'Hi', 'photos' => [UploadedFile::fake()->image('a.jpg')]])
        ->assertSessionHasErrors('photos');

    expect(Post::count())->toBe(0);
});

test('deleting an account removes its post photos', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $this->actingAs($user)->post(route('posts.store'), ['type' => 'post', 'photos' => [UploadedFile::fake()->image('a.jpg')]]);
    $path = PostPhoto::firstOrFail()->path;

    $this->delete(route('profile.destroy'), ['password' => 'password'])->assertRedirect('/');

    Storage::disk('public')->assertMissing($path);
});
