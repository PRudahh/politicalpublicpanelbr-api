<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('municipality_id')->constrained()->cascadeOnDelete();
            $table->foreignId('legislator_id')->nullable()->constrained()->nullOnDelete();
            $table->string('external_id')->nullable();
            $table->string('number');
            $table->integer('year');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->string('status');
            $table->date('submitted_at')->nullable();
            $table->date('voted_at')->nullable();
            $table->integer('votes_for')->nullable();
            $table->integer('votes_against')->nullable();
            $table->integer('abstentions')->nullable();
            $table->string('source_url')->nullable();
            $table->timestamps();
            $table->index(['municipality_id', 'status']);
            $table->index(['municipality_id', 'year']);
        });
    }

    public function down(): void { Schema::dropIfExists('bills'); }
};