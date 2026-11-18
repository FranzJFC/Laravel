<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('membership_categories', function (Blueprint $table) {
            $table->string('level')->nullable();
            $table->decimal('fee', 10, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('membership_categories', fn (Blueprint $table) => $table->dropColumn(['level', 'fee']));
    }
};
