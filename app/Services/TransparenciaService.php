<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TransparenciaService
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.transparencia.base_url');
        $this->apiKey  = config('services.transparencia.api_key');
    }

    /**
     * Busca convênios/transferências recebidos por um município.
     */
    public function getTransfers(string $ibgeCode, int $year, int $month): array
    {
        try {
            $response = Http::withHeaders(['chave-api-dados' => $this->apiKey])
                ->timeout(30)
                ->get("{$this->baseUrl}/transferencias-voluntarias", [
                    'codigoIbge'  => $ibgeCode,
                    'ano'           => $year,
                    'mes'           => str_pad($month, 2, '0', STR_PAD_LEFT),
                    'pagina'      => 1,
                ]);

            if ($response->failed()) {
                Log::warning("Transparência: falha ao buscar transferências {$ibgeCode}");
                return [];
            }

            return $this->parseTransfers($response->json(), $ibgeCode, $year, $month);
        } catch (\Exception $e) {
            Log::error("TransparenciaService::getTransfers: {$e->getMessage()}");
            return [];
        }
    }

    /**
     * Busca contratos (obras) firmados por um município via recursos federais.
     */
    public function getContracts(string $ibgeCode, int $year): array
    {
        try {
            $response = Http::withHeaders(['chave-api-dados' => $this->apiKey])
                ->timeout(30)
                ->get("{$this->baseUrl}/contratos", [
                    'municipioDoContratoId' => $ibgeCode,
                    'dataInicioVigencia'    => "{$year}-01-01",
                ]);

            return $response->successful() ? ($response->json() ?? []) : [];
        } catch (\Exception $e) {
            Log::error("TransparenciaService::getContracts: {$e->getMessage()}");
            return [];
        }
    }

    private function parseTransfers(array $data, string $ibgeCode, int $year, int $month): array
    {
        return collect($data)->map(function ($item) use ($ibgeCode, $year, $month) {
            return [
                'ibge_code'          => $ibgeCode,
                'source_sphere'      => 'federal',
                'source_entity'      => $item['orgao']['nome'] ?? '',
                'transfer_type'      => $item['modalidade'] ?? 'outros',
                'program_name'       => $item['acao']['nome'] ?? null,
                'agreement_number'   => $item['numero'] ?? null,
                'transferred_value'  => $item['valor'] ?? 0,
                'year'               => $year,
                'month'              => $month,
            ];
        })->toArray();
    }
}