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
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount_limit', 14, 2);
            $table->string('period', 20)->default('monthly');
            $table->string('status', 20)->default('active');
            $table->string('note', 255)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['category_id', 'period']);
            $table->index(['period', 'status']);
            $table->unique(['user_id', 'category_id', 'period']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
