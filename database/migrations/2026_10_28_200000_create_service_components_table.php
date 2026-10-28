<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A composite service (e.g. Marketing) is made of other services (Video Editing, Design...)
        Schema::create('service_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('component_service_id')->constrained('services')->cascadeOnDelete();
            $table->unique(['service_id', 'component_service_id']);
        });

        // Which role (service) an assigned employee covers on a project
        Schema::table('project_user', function (Blueprint $table) {
            $table->foreignId('service_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('project_user', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
            $table->dropColumn('service_id');
        });
        Schema::dropIfExists('service_components');
    }
};
