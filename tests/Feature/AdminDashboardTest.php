<?php

use App\Models\User;

test('only admins see the dashboard, and they can find and suspend readers', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $reader = User::factory()->create(['name' => 'Rita Reader']);

    $this->actingAs($reader)->get(route('admin.index'))->assertForbidden();
    $this->actingAs($reader)->post(route('admin.users.suspend', $admin))->assertForbidden();

    $this->actingAs($admin)->get(route('admin.index'))->assertOk()
        ->assertSee('Server health')->assertSee('Last 14 days')->assertSee('Rita Reader');
    $this->actingAs($admin)->get(route('admin.index', ['q' => 'Rita']))->assertOk()->assertSee('Readers matching');

    $this->actingAs($admin)->post(route('admin.users.suspend', $reader))->assertRedirect();
    expect($reader->fresh()->suspended_at)->not->toBeNull();
    $this->actingAs($admin)->post(route('admin.users.suspend', $reader));
    expect($reader->fresh()->suspended_at)->toBeNull();

    // Admins can't be suspended from here.
    $other = User::factory()->create(['is_admin' => true]);
    $this->actingAs($admin)->post(route('admin.users.suspend', $other))->assertStatus(422);
});
