<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('tree_shelf_names')->nullable()->after('preferred_genres');
        });

        Schema::table('user_book_shelves', function (Blueprint $table) {
            $table->unsignedTinyInteger('shelf_slot')->default(0)->after('status');
            $table->unsignedTinyInteger('shelf_order')->default(0)->after('shelf_slot');
            $table->string('shelf_name', 100)->nullable()->after('shelf_order');

            $table->index(['user_id', 'shelf_slot', 'shelf_order']);
        });
    }

    public function down(): void
    {
        Schema::table('user_book_shelves', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'shelf_slot', 'shelf_order']);
            $table->dropColumn(['shelf_slot', 'shelf_order', 'shelf_name']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('tree_shelf_names');
        });
    }
};
