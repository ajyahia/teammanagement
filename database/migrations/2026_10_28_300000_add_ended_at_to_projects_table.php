<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The day the client stopped working with us (project closed or subscription stopped)
        Schema::table('projects', function (Blueprint $table) {
            $table->date('ended_at')->nullable()->after('deadline');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('ended_at');
        });
    }
};
