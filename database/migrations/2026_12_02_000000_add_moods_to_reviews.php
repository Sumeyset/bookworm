<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // How the book felt to the reader: a few moods and its pace.
        Schema::table('reviews', function (Blueprint $table) {
            $table->json('moods')->nullable();
            $table->string('pace', 10)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('reviews', fn (Blueprint $table) => $table->dropColumn(['moods', 'pace']));
    }
};
