<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('billing_type')->default('one_time')->after('cost'); // one_time, monthly
            $table->string('subscription_status')->nullable()->after('billing_type'); // active, stopped
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['billing_type', 'subscription_status']);
        });
    }
};
