<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Photos on a post (Instagram style): up to 10, shown as a swipeable carousel.
        Schema::create('post_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->unsignedTinyInteger('position')->default(0);
            // Where the frame is centred on the photo (0–100 %), and the colour filter.
            $table->unsignedTinyInteger('focus_x')->default(50);
            $table->unsignedTinyInteger('focus_y')->default(50);
            $table->string('filter', 20)->nullable();
            $table->timestamps();

            $table->index(['post_id', 'position']);
        });

        Schema::table('posts', function (Blueprint $table) {
            // Frame shape shared by every photo in the post: square, portrait or landscape.
            $table->string('photo_ratio', 12)->nullable()->after('card_font');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('photo_ratio');
        });
        Schema::dropIfExists('post_photos');
    }
};
