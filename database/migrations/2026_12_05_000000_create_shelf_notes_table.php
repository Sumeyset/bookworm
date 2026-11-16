<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Sticky notes friends leave on a book on someone's shelf.
        Schema::create('shelf_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_book_shelf_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->string('body', 200);
            $table->string('color', 10)->default('yellow');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shelf_notes');
    }
};
