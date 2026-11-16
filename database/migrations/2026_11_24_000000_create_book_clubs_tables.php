<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A club: a group of readers reading one book at a time on a schedule.
        Schema::create('book_clubs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 80);
            $table->string('emoji', 16)->default('📚');
            $table->string('description', 500)->nullable();
            $table->boolean('is_private')->default(false);   // private: invite only, hidden from Discover
            $table->timestamps();
        });

        // Members; joined_at null = invited, not joined yet.
        Schema::create('book_club_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_club_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role', 12)->default('member');   // owner | member
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            $table->unique(['book_club_id', 'user_id']);
        });

        // One book the club reads (current, or finished in the past).
        Schema::create('book_club_rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_club_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->date('starts_on');
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });

        // The schedule: read up to a page by a date, then talk about it.
        Schema::create('book_club_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_club_round_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('number');
            $table->string('title', 80);
            $table->unsignedInteger('from_page');
            $table->unsignedInteger('to_page');
            $table->date('due_on');
            $table->timestamps();
        });

        Schema::create('book_club_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_club_section_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();

            $table->index(['book_club_section_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_club_posts');
        Schema::dropIfExists('book_club_sections');
        Schema::dropIfExists('book_club_rounds');
        Schema::dropIfExists('book_club_members');
        Schema::dropIfExists('book_clubs');
    }
};
