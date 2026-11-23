<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Preserve legacy task-shaped user rows without treating them as accounts.
            $table->string('task_title')->nullable()->change();
            $table->string('name')->nullable();
            $table->string('email')->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->string('role')->default('member')->index();
        });

        Schema::table('members', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
        });

        Schema::table('membership_applications', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('membership_level_id')->nullable()->constrained()->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('membership_applications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('membership_level_id');
            $table->dropConstrainedForeignId('user_id');
        });
        Schema::table('members', fn (Blueprint $table) => $table->dropConstrainedForeignId('user_id'));
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->dropIndex(['role']);
            $table->dropColumn(['name', 'email', 'email_verified_at', 'password', 'remember_token', 'role']);
        });
    }
};
