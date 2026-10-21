<?php

use App\Models\User;

test('a reader can follow and unfollow another reader', function () {
    $reader = User::factory()->create();
    $profile = User::factory()->create();

    $this->actingAs($reader)
        ->get(route('users.show', $profile->username))
        ->assertOk()
        ->assertSee('Follow reader');

    $this->post(route('users.follow', $profile->username))
        ->assertRedirect()
        ->assertSessionHas('status', 'You are now following @' . $profile->username . '.');

    expect($reader->following()->whereKey($profile->id)->exists())->toBeTrue();

    $this->get(route('users.show', $profile->username))
        ->assertOk()
        ->assertSee('Following · Unfollow');

    $this->delete(route('users.unfollow', $profile->username))
        ->assertRedirect()
        ->assertSessionHas('status', 'You unfollowed @' . $profile->username . '.');

    expect($reader->following()->whereKey($profile->id)->exists())->toBeFalse();
});
