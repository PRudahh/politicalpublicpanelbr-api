<?php

namespace App\Jobs;

use App\Models\Municipality;
use App\Models\Transfer;
use App\Services\TransparenciaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncTransfersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 120;

    public function __construct(
        public readonly string $ibgeCode,
        public readonly int    $year,
        public readonly int    $month,
    ) {}

    public function handle(TransparenciaService $transparencia): void
    {
        $transfers    = $transparencia->getTransfers($this->ibgeCode, $this->year, $this->month);
        $municipality = Municipality::where('ibge_code', $this->ibgeCode)->first();
        if (!$municipality || empty($transfers)) return;

        foreach ($transfers as $t) {
            Transfer::updateOrCreate(
                [
                    'municipality_id' => $municipality->id,
                    'agreement_number'=> $t['agreement_number'] ?? null,
                    'year'  => $t['year'],
                    'month' => $t['month'],
                ],
                [
                    'source_sphere'     => $t['source_sphere'],
                    'source_entity'     => $t['source_entity'],
                    'transfer_type'     => $t['transfer_type'],
                    'program_name'      => $t['program_name'] ?? null,
                    'transferred_value' => $t['transferred_value'],
                ]
            );
        }
    }
}