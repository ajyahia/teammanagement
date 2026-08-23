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
        Schema::create('project_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            
            $table->unique(['project_id', 'user_id']);
        });

        // Migrate existing data
        \Illuminate\Support\Facades\DB::statement("INSERT INTO project_user (project_id, user_id, created_at, updated_at) SELECT id, employee_id, NOW(), NOW() FROM projects WHERE employee_id IS NOT NULL");

        // Drop employee_id from projects table
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropColumn('employee_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('employee_id')->nullable()->constrained('users')->nullOnDelete();
        });

        // Migrate data back
        \Illuminate\Support\Facades\DB::statement("UPDATE projects p JOIN project_user pu ON p.id = pu.project_id SET p.employee_id = pu.user_id");

        Schema::dropIfExists('project_user');
    }
};
