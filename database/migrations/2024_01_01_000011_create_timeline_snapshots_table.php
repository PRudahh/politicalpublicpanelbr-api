<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('timeline_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('municipality_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mandate_id')->constrained()->cascadeOnDelete();
            $table->smallInteger('year');
            $table->tinyInteger('month');
            $table->tinyInteger('mandate_month');
            $table->decimal('total_revenue', 16, 2)->nullable();
            $table->decimal('total_expenditure', 16, 2)->nullable();
            $table->decimal('total_transfers', 16, 2)->nullable();
            $table->integer('works_in_progress')->default(0);
            $table->integer('works_completed')->default(0);
            $table->integer('works_delayed')->default(0);
            $table->integer('bills_approved')->default(0);
            $table->integer('bills_submitted')->default(0);
            $table->json('highlights')->nullable();
            $table->timestamps();
            $table->unique(['municipality_id', 'year', 'month']);
        });
    }

    public function down(): void { Schema::dropIfExists('timeline_snapshots'); }
};