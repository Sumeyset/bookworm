<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A timed reading session friends join to read "together".
        Schema::create('reading_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('host_id')->constrained('users')->cascadeOnDelete();
            $table->string('title', 80);
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->index();
            $table->timestamps();
        });

        Schema::create('reading_room_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reading_room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->unique(['reading_room_id', 'user_id']);
        });

        Schema::create('reading_room_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reading_room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('body', 280);
            $table->timestamps();

            $table->index(['reading_room_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reading_room_messages');
        Schema::dropIfExists('reading_room_members');
        Schema::dropIfExists('reading_rooms');
    }
};
