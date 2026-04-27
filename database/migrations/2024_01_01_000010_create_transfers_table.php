<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('municipality_id')->constrained()->cascadeOnDelete();
            $table->string('source_sphere');
            $table->string('source_entity');
            $table->string('transfer_type');
            $Program_name = $table->string('program_name')->nullable();
            $table->string('agreement_number')->nullable();
            $table->string('object')->nullable();
            $table->decimal('authorized_value', 16, 2)->nullable();
            $table->decimal('transferred_value', 16, 2);
            $table->decimal('execution_percentage', 5, 2)->nullable();
            $table->smallInteger('year');
            $table->tinyInteger('month');
            $table->string('source_url')->nullable();
            $table->timestamps();
            $table->index(['municipality_id', 'year', 'month']);
            $table->index(['municipality_id', 'source_sphere']);
        });
    }

    public function down(): void { Schema::dropIfExists('transfers'); }
};