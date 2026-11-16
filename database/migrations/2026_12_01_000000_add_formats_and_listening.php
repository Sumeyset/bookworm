<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // How the reader has the book: paper, e-book or audiobook. Audiobooks
        // keep their place in minutes (position / length).
        Schema::table('user_book_shelves', function (Blueprint $table) {
            $table->string('format', 10)->nullable();
            $table->unsignedInteger('audio_position')->nullable();
            $table->unsignedInteger('audio_length')->nullable();
        });

        // Minutes of listening on a day (pages_read keeps the pages).
        Schema::table('reading_sessions', function (Blueprint $table) {
            $table->unsignedInteger('minutes')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('user_book_shelves', fn (Blueprint $table) => $table->dropColumn(['format', 'audio_position', 'audio_length']));
        Schema::table('reading_sessions', fn (Blueprint $table) => $table->dropColumn('minutes'));
    }
};
