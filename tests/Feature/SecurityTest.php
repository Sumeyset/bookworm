<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('pages send security headers', function () {
    $this->get('/')->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    expect($this->get('/')->headers->get('Content-Security-Policy'))->toContain("frame-ancestors 'self'")->toContain("object-src 'none'");
});

test('a reader can sign out every other device', function () {
    $user = User::factory()->create(['password' => Hash::make('password')]);
    $this->post('/login', ['email' => $user->email, 'password' => 'password']);

    $this->get(route('profile.edit'))->assertOk()->assertSee('Sign out other devices');
    $this->from(route('profile.edit'))->delete(route('other-devices.destroy'), ['current_password' => 'wrong'])
        ->assertSessionHasErrorsIn('otherDevices', 'current_password');

    $oldHash = $user->fresh()->password;
    $this->from(route('profile.edit'))->delete(route('other-devices.destroy'), ['current_password' => 'password'])
        ->assertRedirect(route('profile.edit'))->assertSessionHas('status', 'other-devices-logged-out');
    expect($user->fresh()->password)->not->toBe($oldHash); // re-hashed, so old sessions no longer match
    $this->get(route('profile.edit'))->assertOk(); // still signed in here
});

test('changing your password keeps you signed in on this device', function () {
    $user = User::factory()->create(['password' => Hash::make('password')]);
    $this->post('/login', ['email' => $user->email, 'password' => 'password']);
    $this->get(route('profile.edit'))->assertOk();
    $this->put(route('password.update'), ['current_password' => 'password', 'password' => 'new-password1', 'password_confirmation' => 'new-password1'])->assertSessionHasNoErrors();
    $this->get(route('profile.edit'))->assertOk();
});
