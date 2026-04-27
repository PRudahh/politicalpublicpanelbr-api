<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transparency_rankings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('municipality_id')->constrained()->cascadeOnDelete();
            $table->smallInteger('year');
            $table->tinyInteger('month');
            $table->integer('national_rank')->nullable();
            $table->integer('state_rank')->nullable();
            $table->integer('total_score')->default(0);
            $table->integer('electoral_data_score')->default(0);
            $table->integer('finance_data_score')->default(0);
            $table->integer('works_data_score')->default(0);
            $table->integer('legislative_data_score')->default(0);
            $table->integer('executive_data_score')->default(0);
            $table->integer('portal_score')->default(0);
            $table->timestamps();
            $table->unique(['municipality_id', 'year', 'month']);
            $table->index(['year', 'month', 'total_score']);
        });
    }

    public function down(): void { Schema::dropIfExists('transparency_rankings'); }
};