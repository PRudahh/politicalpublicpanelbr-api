<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IbgeService
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.ibge.base_url', 'https://servicodados.ibge.gov.br/api/v1');
    }

    /**
     * Busca todos os municípios de uma UF (ou todos do Brasil).
     */
    public function getMunicipalities(string $uf = null): array
    {
        $endpoint = $uf
            ? "{$this->baseUrl}/localidades/estados/{$uf}/municipios"
            : "{$this->baseUrl}/localidades/municipios";

        try {
            $response = Http::timeout(30)->get($endpoint);
            return $response->successful() ? $response->json() : [];
        } catch (\Exception $e) {
            Log::error("IbgeService::getMunicipalities: {$e->getMessage()}");
            return [];
        }
    }

    /**
     * Busca dados demográficos de um município (Censo).
     */
    public function getMunicipalityData(string $ibgeCode): array
    {
        try {
            $response = Http::timeout(15)->get(
                "{$this->baseUrl}/localidades/municipios/{$ibgeCode}"
            );
            return $response->successful() ? $response->json() : [];
        } catch (\Exception $e) {
            Log::error("IbgeService::getMunicipalityData: {$e->getMessage()}");
            return [];
        }
    }
}