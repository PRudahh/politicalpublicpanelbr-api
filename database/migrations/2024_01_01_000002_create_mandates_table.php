<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mandates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('municipality_id')->constrained()->cascadeOnDelete();
            $table->string('level');
            $table->integer('election_year');
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_current')->default(false)->index();
            $table->timestamps();
            $table->index(['municipality_id', 'is_current']);
        });
    }

    public function down(): void { Schema::dropIfExists('mandates'); }
};