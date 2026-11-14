<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_book_shelves', function (Blueprint $table) {
            $table->text('note')->nullable()->after('shelf_name');
            $table->timestamp('started_at')->nullable()->after('total_pages');
        });

        Schema::table('reviews', function (Blueprint $table) {
            // Feed + book pages query reviews by book and by author, newest first.
            $table->index(['book_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });

        Schema::table('user_follows', function (Blueprint $table) {
            $table->index('followed_id');
        });

        // Every book used to default to shelf slot 0, so books piled up on a
        // hidden branch and never showed on the tree. Spread them out: at most
        // three books per branch, in the order they were added.
        DB::table('user_book_shelves')
            ->select('user_id')
            ->distinct()
            ->orderBy('user_id')
            ->pluck('user_id')
            ->each(function ($userId): void {
                $counts = [];
                DB::table('user_book_shelves')
                    ->where('user_id', $userId)
                    ->orderBy('shelf_slot')
                    ->orderBy('shelf_order')
                    ->orderBy('id')
                    ->get(['id', 'shelf_slot'])
                    ->each(function ($item) use (&$counts): void {
                        $slot = (int) $item->shelf_slot;
                        if (($counts[$slot] ?? 0) >= 3) {
                            $slot = 0;
                            while (($counts[$slot] ?? 0) >= 3) {
                                $slot++;
                            }
                        }
                        DB::table('user_book_shelves')->where('id', $item->id)->update([
                            'shelf_slot' => $slot,
                            'shelf_order' => $counts[$slot] ?? 0,
                        ]);
                        $counts[$slot] = ($counts[$slot] ?? 0) + 1;
                    });
            });
    }

    public function down(): void
    {
        Schema::table('user_follows', function (Blueprint $table) {
            $table->dropIndex(['followed_id']);
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex(['book_id', 'created_at']);
            $table->dropIndex(['user_id', 'created_at']);
        });

        Schema::table('user_book_shelves', function (Blueprint $table) {
            $table->dropColumn(['note', 'started_at']);
        });
    }
};
