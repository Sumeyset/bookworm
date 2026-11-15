<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Acorns: earned by reading, spent on tree decorations.
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('acorns')->default(0)->after('shelf_style');
        });

        // Every acorn earned or spent, with the reason. "key" makes each reward one-time.
        Schema::create('acorn_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('amount');
            $table->string('reason', 30);
            $table->string('key', 100)->nullable();
            $table->string('note')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'key']);
            $table->index(['user_id', 'reason', 'created_at']);
        });

        // Decorations a reader owns, and whether they're on the tree.
        Schema::create('user_decorations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('item', 30);
            $table->boolean('enabled')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'item']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_decorations');
        Schema::dropIfExists('acorn_events');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('acorns');
        });
    }
};
