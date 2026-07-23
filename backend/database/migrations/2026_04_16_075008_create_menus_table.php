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
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('title', 120);
            $table->string('url')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('menus')->nullOnDelete();
            $table->string('canonical', 80)->default('home.header');
            $table->unsignedInteger('sort_order')->default(0);
            $table->enum('target', ['_self', '_blank'])->default('_self');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->index(['canonical', 'status']);
            $table->index(['parent_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
