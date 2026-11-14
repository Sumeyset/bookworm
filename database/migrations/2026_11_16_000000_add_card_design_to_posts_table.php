<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // How a post's card looks in the feed: background design and typeface.
        Schema::table('posts', function (Blueprint $table) {
            $table->string('card_style', 20)->nullable()->after('quote_card_id');
            $table->string('card_font', 20)->nullable()->after('card_style');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['card_style', 'card_font']);
        });
    }
};
