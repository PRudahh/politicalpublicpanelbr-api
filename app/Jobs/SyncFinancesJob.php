<?php

namespace App\Jobs;

use App\Models\Municipality;
use App\Models\Finance;
use App\Services\SiconfiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncFinancesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 120;

    public function __construct(
        public readonly string $ibgeCode,
        public readonly int    $year,
        public readonly int    $month,
    ) {}

    public function handle(SiconfiService $siconfi): void
    {
        $finances = $siconfi->getMonthlyFinances($this->ibgeCode, $this->year, $this->month);

        $municipality = Municipality::where('ibge_code', $this->ibgeCode)->first();
        if (!$municipality || empty($finances)) return;

        foreach ($finances as $record) {
            Finance::updateOrCreate(
                [
                    'municipality_id' => $municipality->id,
                    'year'    => $record['year'],
                    'month'   => $record['month'],
                    'type'    => $record['type'] ?? 'expenditure',
                    'category'=> $record['category'],
                ],
                [
                    'budgeted_value'   => $record['budgeted_value'] ?? 0,
                    'committed_value'  => $record['committed_value'] ?? 0,
                    'liquidated_value' => $record['liquidated_value'] ?? 0,
                    'paid_value'       => $record['paid_value'] ?? 0,
                    'source'           => 'siconfi',
                ]
            );
        }

        Log::info("SyncFinancesJob: {$this->ibgeCode} {$this->year}/{$this->month} — {$municipality->name}");
    }
}