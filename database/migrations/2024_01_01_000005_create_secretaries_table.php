<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('secretaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('politician_id')->constrained()->cascadeOnDelete();
            $table->foreignId('municipality_id')->constrained()->cascadeOnDelete();
            $table->string('secretariat_name');
            $table->string('secretariat_acronym')->nullable();
            $table->date('appointment_date')->nullable();
            $table->string('employment_type')->nullable();
            $table->decimal('gross_salary', 10, 2)->nullable();
            $table->boolean('is_current')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('secretaries'); }
};