<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The bell's unread count runs on every page.
        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['notifiable_type', 'notifiable_id', 'read_at'], 'notifications_unread_index');
        });

        // The "Everyone" feed reads the newest posts first.
        Schema::table('posts', function (Blueprint $table) {
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', fn (Blueprint $table) => $table->dropIndex('notifications_unread_index'));
        Schema::table('posts', fn (Blueprint $table) => $table->dropIndex(['created_at']));
    }
};
