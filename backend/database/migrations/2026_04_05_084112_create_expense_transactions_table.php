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
        Schema::create('expense_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wallet_id')->constrained('user_wallets')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 20);
            $table->decimal('amount', 14, 2);
            $table->date('transacted_at');
            $table->string('status', 20)->default('posted');
            $table->string('note', 255)->nullable();
            $table->json('labels')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'transacted_at']);
            $table->index(['wallet_id', 'type']);
            $table->index(['status', 'transacted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_transactions');
    }
};
