<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_partners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->decimal('share_percent', 5, 2);
            $table->decimal('committed_capital', 14, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('partner_contributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_partner_id')->constrained('project_partners')->cascadeOnDelete();
            $table->decimal('amount', 14, 2);
            $table->date('due_date');
            $table->date('paid_at')->nullable();
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropColumn('project_id');
        });
        Schema::dropIfExists('partner_contributions');
        Schema::dropIfExists('project_partners');
    }
};
