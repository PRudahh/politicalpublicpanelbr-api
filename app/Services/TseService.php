<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TseService
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.tse.base_url', 'https://dadosabertos.tse.jus.br');
    }

    /**
     * Busca candidatos eleitos para um município em um ano eleitoral.
     * Retorna prefeito e vereadores eleitos.
     */
    public function getElectedCandidates(string $ibgeCode, int $electionYear): array
    {
        try {
            $uf = $this->getUfFromIbge($ibgeCode);

            $response = Http::timeout(config('services.tse.timeout', 30))
                ->get("{$this->baseUrl}/candidatos/{$electionYear}/candidatos_{$electionYear}_{$uf}.csv");

            if ($response->failed()) {
                Log::warning("TSE: falha ao buscar candidatos {$ibgeCode} {$electionYear}");
                return [];
            }

            return $this->parseCsvResponse($response->body(), $ibgeCode);
        } catch (\Exception $e) {
            Log::error("TseService::getElectedCandidates: {$e->getMessage()}");
            return [];
        }
    }

    /**
     * Busca resultado das eleições municipais para um município.
     */
    public function getElectionResults(string $ibgeCode, int $electionYear): array
    {
        try {
            $uf = $this->getUfFromIbge($ibgeCode);
            $response = Http::timeout(30)
                ->get("{$this->baseUrl}/resultados/{$electionYear}/votacao_candidato_munzona_{$electionYear}_{$uf}.csv");

            if ($response->failed()) return [];

            return $this->parseCsvResponse($response->body(), $ibgeCode, 'results');
        } catch (\Exception $e) {
            Log::error("TseService::getElectionResults: {$e->getMessage()}");
            return [];
        }
    }

    private function getUfFromIbge(string $ibgeCode): string
    {
        $stateCode = substr($ibgeCode, 0, 2);
        $map = [
            '11' => 'RO', '12' => 'AC', '13' => 'AM', '14' => 'RR', '15' => 'PA',
            '16' => 'AP', '17' => 'TO', '21' => 'MA', '22' => 'PI', '23' => 'CE',
            '24' => 'RN', '25' => 'PB', '26' => 'PE', '27' => 'AL', '28' => 'SE',
            '29' => 'BA', '31' => 'MG', '32' => 'ES', '33' => 'RJ', '35' => 'SP',
            '41' => 'PR', '42' => 'SC', '43' => 'RS', '50' => 'MS', '51' => 'MT',
            '52' => 'GO', '53' => 'DF',
        ];
        return $map[$stateCode] ?? 'BR';
    }

    private function parseCsvResponse(string $csv, string $ibgeCode, string $type = 'candidates'): array
    {
        // Implementação real: usar league/csv para parsear o CSV do TSE
        return [];
    }
}