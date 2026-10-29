<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('preferred_genres')->nullable();
            $table->unsignedSmallInteger('yearly_reading_goal')->default(12);
            $table->timestamp('onboarding_completed_at')->nullable();
        });

        Schema::create('user_book_shelves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20)->default('want_to_read');
            $table->unsignedInteger('current_page')->nullable();
            $table->unsignedInteger('total_pages')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'book_id']);
            $table->index(['user_id', 'status', 'updated_at']);
        });

        DB::table('reviews')->orderBy('id')->chunk(500, function ($reviews): void {
            DB::table('user_book_shelves')->insert(
                $reviews->map(fn ($review) => [
                    'user_id' => $review->user_id,
                    'book_id' => $review->book_id,
                    'status' => 'read',
                    'read_at' => $review->updated_at,
                    'created_at' => $review->created_at,
                    'updated_at' => $review->updated_at,
                ])->all(),
            );
        });

        Schema::create('quote_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_pdf_highlight_id')->unique()->constrained()->cascadeOnDelete();
            $table->uuid('share_token')->unique();
            $table->timestamp('shared_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_cards');
        Schema::dropIfExists('user_book_shelves');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['preferred_genres', 'yearly_reading_goal', 'onboarding_completed_at']);
        });
    }
};
