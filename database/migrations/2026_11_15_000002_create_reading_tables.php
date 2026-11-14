<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One row per reader, book and day they read: powers streaks and the yearly recap.
        Schema::create('reading_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->nullable()->constrained()->nullOnDelete();
            $table->date('read_on');
            $table->unsignedInteger('pages_read')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'book_id', 'read_on']);
            $table->index(['user_id', 'read_on']);
        });

        Schema::create('buddy_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->foreignId('creator_id')->constrained('users')->cascadeOnDelete();
            $table->string('title', 120)->nullable();
            $table->timestamps();
        });

        Schema::create('buddy_read_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buddy_read_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('joined_at')->nullable(); // null = invited, not joined yet
            $table->timestamps();

            $table->unique(['buddy_read_id', 'user_id']);
        });

        Schema::create('buddy_read_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buddy_read_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('page')->nullable();
            $table->text('body');
            $table->timestamps();

            $table->index(['buddy_read_id', 'created_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            // Lets anyone with the link see the reader's tree and yearly recap.
            $table->boolean('is_public')->default(false)->after('bio');
        });

        Schema::table('books', function (Blueprint $table) {
            $table->string('isbn', 20)->nullable()->after('ol_id');
            $table->unsignedInteger('page_count')->nullable()->after('first_publish_year');
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn(['isbn', 'page_count']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_public');
        });
        Schema::dropIfExists('buddy_read_comments');
        Schema::dropIfExists('buddy_read_user');
        Schema::dropIfExists('buddy_reads');
        Schema::dropIfExists('reading_sessions');
    }
};
