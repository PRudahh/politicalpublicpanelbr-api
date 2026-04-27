<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('municipalities', function (Blueprint $table) {
            $table->id();
            $table->string('ibge_code', 7)->unique()->index();
            $table->string('name');
            $table->string('uf', 2)->index();
            $table->string('state_name');
            $table->string('region');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->unsignedBigInteger('population')->nullable();
            $table->decimal('gdp_per_capita', 12, 2)->nullable();
            $table->decimal('idh', 5, 4)->nullable();
            $table->decimal('annual_budget', 16, 2)->nullable();
            $table->string('fiscal_health_status')->nullable();
            $table->integer('transparency_score')->default(0);
            $table->string('transparency_level')->default('low');
            $table->boolean('has_open_data_portal')->default(false);
            $table->string('transparency_portal_url')->nullable();
            $table->json('available_modules')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('municipalities'); }
};