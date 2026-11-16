<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A decoration a friend paid for (null = bought it yourself).
        Schema::table('user_decorations', function (Blueprint $table) {
            $table->foreignId('gifted_by')->nullable()->after('enabled')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('user_decorations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('gifted_by');
        });
    }
};
