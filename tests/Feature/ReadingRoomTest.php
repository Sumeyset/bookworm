<?php

use App\Models\ReadingRoom;
use App\Models\User;

test('a reader opens a room, a friend joins, they chat, and it ends on time', function () {
    $host = User::factory()->create();
    $friend = User::factory()->create();
    $friend->following()->attach($host->id);

    $this->actingAs($host)->post(route('rooms.store'), ['minutes' => 25, 'title' => 'Sunday pages'])->assertRedirect();
    $room = ReadingRoom::firstOrFail();
    expect($friend->notifications()->where('data->kind', 'room')->count())->toBe(1);

    $this->actingAs($friend)->get(route('rooms.index'))->assertOk()->assertSee('Sunday pages');
    $this->actingAs($friend)->post(route('rooms.join', $room))->assertRedirect(route('rooms.show', $room));
    $this->actingAs($friend)->postJson(route('rooms.message', $room), ['body' => 'Chapter 3 is so good'])->assertOk()
        ->assertJsonPath('messages.0.body', 'Chapter 3 is so good')
        ->assertJsonCount(2, 'members');

    $this->actingAs($host)->getJson(route('rooms.state', $room))->assertOk()->assertJsonPath('live', true);
    $this->actingAs($host)->get(route('rooms.show', $room))->assertOk()->assertSee('data-room-clock', false);

    $this->travel(26)->minutes();
    $this->actingAs($friend)->postJson(route('rooms.message', $room), ['body' => 'late'])->assertForbidden();
    $this->actingAs($host)->getJson(route('rooms.state', $room))->assertJsonPath('live', false);
});
