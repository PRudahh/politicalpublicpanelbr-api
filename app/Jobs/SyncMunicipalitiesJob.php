<?php

namespace App\Jobs;

use App\Models\Municipality;
use App\Services\IbgeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncMunicipalitiesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 300;

    public function __construct(public readonly ?string $uf = null) {}

    public function handle(IbgeService $ibge): void
    {
        Log::info("SyncMunicipalitiesJob: iniciando para UF={$this->uf}");

        $municipalities = $ibge->getMunicipalities($this->uf);

        foreach ($municipalities as $m) {
            Municipality::updateOrCreate(
                ['ibge_code' => (string) $m['id']],
                [
                    'name'       => $m['nome'],
                    'uf'         => $m['microrregiao']['mesorregiao']['UF']['sigla'],
                    'state_name' => $m['microrregiao']['mesorregiao']['UF']['nome'],
                    'region'     => $m['microrregiao']['mesorregiao']['UF']['regiao']['nome'],
                ]
            );
        }

        Log::info("SyncMunicipalitiesJob: {$this->uf} — " . count($municipalities) . " municípios sincronizados");
    }
}