<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('finances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('municipality_id')->constrained()->cascadeOnDelete();
            $table->smallInteger('year');
            $table->tinyInteger('month');
            $table->string('type');
            $table->string('category');
            $table->string('subcategory')->nullable();
            $table->decimal('budgeted_value', 16, 2)->nullable();
            $table->decimal('committed_value', 16, 2)->nullable();
            $table->decimal('liquidated_value', 16, 2)->nullable();
            $table->decimal('paid_value', 16, 2)->nullable();
            $table->string('source')->default('siconfi');
            $table->timestamps();
            $table->unique(['municipality_id', 'year', 'month', 'type', 'category'], 'finances_unique');
            $table->index(['municipality_id', 'year', 'month']);
        });
    }

    public function down(): void { Schema::dropIfExists('finances'); }
};