<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // How the reader's shelf looks: the tree (plain or seasonal) or another bookcase.
        Schema::table('users', function (Blueprint $table) {
            $table->string('shelf_style', 20)->nullable()->after('is_public');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('shelf_style');
        });
    }
};
