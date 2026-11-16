<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_private')->default(false);      // followers need approval
            $table->boolean('hide_activity')->default(false);   // no "started/finished" notes to followers
            $table->boolean('is_admin')->default(false);        // can review reports
            $table->timestamp('suspended_at')->nullable();      // suspended by a moderator: can't sign in
        });

        // Follow requests to private accounts, waiting for approval.
        Schema::create('follow_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('target_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['requester_id', 'target_id']);
        });

        Schema::create('user_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blocker_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('blocked_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['blocker_id', 'blocked_id']);
        });

        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reported_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reportable_type', 40);
            $table->unsignedBigInteger('reportable_id');
            $table->string('reason', 20);
            $table->string('details', 1000)->nullable();
            $table->string('excerpt', 500)->nullable();          // what was reported, kept even if it's deleted
            $table->string('status', 12)->default('open');      // open | dismissed | actioned
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['reportable_type', 'reportable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
        Schema::dropIfExists('user_blocks');
        Schema::dropIfExists('follow_requests');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_private', 'hide_activity', 'is_admin', 'suspended_at']);
        });
    }
};
