<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('member_number')->unique();
            $table->foreignId('membership_category_id')->constrained()->restrictOnDelete();
            $table->foreignId('membership_level_id')->constrained()->restrictOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable()->unique();
            $table->string('phone', 30)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('organization')->nullable();
            $table->text('address')->nullable();
            $table->date('joined_at');
            $table->date('expires_at')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index(['status', 'last_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
