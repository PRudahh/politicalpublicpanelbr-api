<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SiconfiService
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.siconfi.base_url', 'https://apidatalake.tesouro.gov.br/ords/siconfi');
    }

    /**
     * Busca receitas e despesas mensais de um município.
     */
    public function getMonthlyFinances(string $ibgeCode, int $year, int $month): array
    {
        try {
            $response = Http::timeout(30)->get("{$this->baseUrl}/api/rreo", [
                'an_exercicio'        => $year,
                'in_periodicidade'   => 'M',
                'nr_periodo'          => $month,
                'co_tipo_demonstrativo' => 'RREO',
                'id_ente'             => $ibgeCode . '00', // SICONFI usa 8 dígitos
            ]);

            if ($response->failed()) {
                Log::warning("SICONFI: falha ao buscar finanças {$ibgeCode} {$year}/{$month}");
                return [];
            }

            return $this->parseFinancesResponse($response->json(), $ibgeCode, $year, $month);
        } catch (\Exception $e) {
            Log::error("SiconfiService::getMonthlyFinances: {$e->getMessage()}");
            return [];
        }
    }

    /**
     * Busca o Relatório de Gestão Fiscal (RGF) de um município.
     */
    public function getFiscalReport(string $ibgeCode, int $year, int $period): array
    {
        try {
            $response = Http::timeout(30)->get("{$this->baseUrl}/api/rgf", [
                'an_exercicio'    => $year,
                'nr_periodo'      => $period,
                'co_tipo_poder'   => 'E', // Executivo
                'id_ente'         => $ibgeCode . '00',
            ]);

            return $response->successful() ? $response->json('items', []) : [];
        } catch (\Exception $e) {
            Log::error("SiconfiService::getFiscalReport: {$e->getMessage()}");
            return [];
        }
    }

    private function parseFinancesResponse(array $data, string $ibgeCode, int $year, int $month): array
    {
        return collect($data['items'] ?? [])->map(function ($item) use ($ibgeCode, $year, $month) {
            return [
                'ibge_code'        => $ibgeCode,
                'year'             => $year,
                'month'            => $month,
                'category'         => $item['no_conta'] ?? '',
                'budgeted_value'   => $item['vl_orcado_atualizado'] ?? 0,
                'committed_value'  => $item['vl_empenhado'] ?? 0,
                'liquidated_value' => $item['vl_liquidado'] ?? 0,
                'paid_value'       => $item['vl_pago'] ?? 0,
            ];
        })->toArray();
    }
}