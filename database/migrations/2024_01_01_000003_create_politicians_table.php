<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('politicians', function (Blueprint $table) {
            $table->id();
            $table->string('tse_candidate_id')->nullable()->index();
            $table->string('cpf_hash')->nullable()->unique();
            $table->string('name');
            $table->string('social_name')->nullable();
            $table->string('photo_url')->nullable();
            $table->date('birthdate')->nullable();
            $table->string('education_level')->nullable();
            $table->text('education_description')->nullable();
            $table->text('biography')->nullable();
            $table->string('party')->nullable()->index();
            $table->string('party_abbreviation')->nullable();
            $table->json('social_media')->nullable();
            $table->string('institutional_email')->nullable();
            $table->string('institutional_phone')->nullable();
            $table->string('website')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('politicians'); }
};