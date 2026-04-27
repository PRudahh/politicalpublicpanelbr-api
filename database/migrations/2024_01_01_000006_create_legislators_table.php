<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('legislators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('politician_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mandate_id')->constrained()->cascadeOnDelete();
            $table->foreignId('municipality_id')->constrained()->cascadeOnDelete();
            $table->decimal('votes_received', 10, 0)->nullable();
            $table->decimal('votes_percentage', 5, 2)->nullable();
            $table->decimal('gross_salary', 10, 2)->nullable();
            $table->decimal('office_budget', 10, 2)->nullable();
            $table->integer('number_of_aides')->default(0);
            $table->integer('sessions_present')->default(0);
            $table->integer('sessions_absent')->default(0);
            $table->integer('sessions_justified_absent')->default(0);
            $table->integer('bills_submitted')->default(0);
            $table->integer('bills_approved')->default(0);
            $table->integer('bills_rejected')->default(0);
            $table->integer('bills_pending')->default(0);
            $table->decimal('executive_alignment_pct', 5, 2)->nullable();
            $table->decimal('productivity_index', 5, 2)->nullable();
            $table->boolean('is_current')->default(true)->index();
            $table->timestamps();
            $table->index(['municipality_id', 'is_current']);
        });
    }

    public function down(): void { Schema::dropIfExists('legislators'); }
};