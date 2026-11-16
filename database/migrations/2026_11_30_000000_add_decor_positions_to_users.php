<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Where the reader moved their decorations, per shelf style:
        // {"window": {"owl": [44.1, 82.5], ...}, "tree": {...}}
        Schema::table('users', function (Blueprint $table) {
            $table->json('decor_positions')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('decor_positions');
        });
    }
};
