<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Drop foreign key, then unique index
        Schema::table('salary_adjustments', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropUnique(['user_id', 'month', 'year']);
        });

        // Step 2: Re-add foreign key and add regular index
        Schema::table('salary_adjustments', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('user_id', 'salary_adjustments_user_id_index');
        });

        // Step 3: Add 'type' and 'amount' columns for single-record adjustments
        Schema::table('salary_adjustments', function (Blueprint $table) {
            $table->enum('type', ['bonus', 'deduction'])->default('bonus')->after('year');
            $table->decimal('amount', 12, 2)->default(0.00)->after('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salary_adjustments', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex('salary_adjustments_user_id_index');
            $table->dropColumn(['type', 'amount']);
            $table->unique(['user_id', 'month', 'year']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
