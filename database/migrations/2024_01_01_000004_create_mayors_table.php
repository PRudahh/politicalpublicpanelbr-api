<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mayors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('politician_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mandate_id')->constrained()->cascadeOnDelete();
            $table->foreignId('municipality_id')->constrained()->cascadeOnDelete();
            $table->decimal('votes_received', 10, 0)->nullable();
            $table->decimal('votes_percentage', 5, 2)->nullable();
            $table->string('coalition')->nullable();
            $table->decimal('gross_salary', 10, 2)->nullable();
            $table->integer('number_of_aides')->default(0);
            $table->boolean('is_current')->default(false)->index();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('mayors'); }
};