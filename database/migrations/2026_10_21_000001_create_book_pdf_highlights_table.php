<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_pdf_highlights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_pdf_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('page_number');
            $table->text('text');
            $table->json('rectangles');
            $table->timestamps();

            $table->index(['book_pdf_id', 'user_id', 'page_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_pdf_highlights');
    }
};
