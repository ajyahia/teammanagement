<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // External workers the company hands a client's job to (subcontractors)
        Schema::create('project_vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->decimal('agreed_cost', 14, 2);
            // Subscription projects: the cost repeats with every billing cycle
            $table->boolean('is_recurring')->default(false);
            $table->timestamps();
        });

        Schema::create('vendor_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_vendor_id')->constrained('project_vendors')->cascadeOnDelete();
            $table->foreignId('cycle_id')->nullable()->constrained('project_cycles')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->decimal('amount', 14, 2);
            $table->date('due_date');
            $table->date('paid_at')->nullable();
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_payments');
        Schema::dropIfExists('project_vendors');
    }
};
