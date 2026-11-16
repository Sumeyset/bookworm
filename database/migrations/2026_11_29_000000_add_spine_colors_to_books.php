<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Spine colours taken from the cover, so a book on the shelf looks like its cover.
        Schema::table('books', function (Blueprint $table) {
            $table->string('spine_color', 7)->nullable();
            $table->string('spine_text', 7)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn(['spine_color', 'spine_text']);
        });
    }
};
