<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // A gentle nudge when a reading streak is about to end (off unless turned on).
            $table->boolean('streak_reminder')->default(false);
            $table->unsignedTinyInteger('reminder_hour')->default(20);
            $table->string('timezone', 64)->nullable();
            $table->date('last_reminded_on')->nullable();
            // Streak freezes bought with acorns, waiting to be used.
            $table->unsignedTinyInteger('streak_freezes')->default(0);
        });

        // Days a freeze kept a streak alive; they count as reading days for streaks.
        Schema::create('streak_freezes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('day');
            $table->timestamps();

            $table->unique(['user_id', 'day']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('streak_freezes');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['streak_reminder', 'reminder_hour', 'timezone', 'last_reminded_on', 'streak_freezes']);
        });
    }
};
