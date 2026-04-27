<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('public_works', function (Blueprint $table) {
            $table->id();
            $table->foreignId('municipality_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category');
            $table->string('neighborhood')->nullable();
            $table->string('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('contracted_value', 16, 2)->nullable();
            $table->decimal('executed_value', 16, 2)->nullable();
            $table->decimal('execution_percentage', 5, 2)->nullable();
            $table->string('contractor_name')->nullable();
            $table->string('contractor_cnpj', 14)->nullable();
            $table->string('contract_number')->nullable();
            $table->string('bidding_number')->nullable();
            $table->date('start_date')->nullable();
            $table->date('expected_end_date')->nullable();
            $table->date('actual_end_date')->nullable();
            $table->string('status');
            $table->string('funding_source');
            $table->string('source_url')->nullable();
            $table->timestamps();
            $table->index(['municipality_id', 'status']);
            $table->index('contractor_cnpj');
        });
    }

    public function down(): void { Schema::dropIfExists('public_works'); }
};