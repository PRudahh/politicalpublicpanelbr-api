<?php

namespace App\Jobs;

use App\Models\Municipality;
use App\Models\Finance;
use App\Models\Transfer;
use App\Models\PublicWork;
use App\Models\Bill;
use App\Models\TimelineSnapshot;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateTimelineSnapshotJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly string $ibgeCode,
        public readonly int    $year,
        public readonly int    $month,
    ) {}

    public function handle(): void
    {
        $municipality = Municipality::with('currentMandate')
            ->where('ibge_code', $this->ibgeCode)->first();

        if (!$municipality?->currentMandate) return;

        $mandate = $municipality->currentMandate;

        $totalRevenue    = Finance::where('municipality_id', $municipality->id)
            ->where('year', $this->year)->where('month', $this->month)->where('type', 'revenue')
            ->sum('paid_value');

        $totalExpenditure = Finance::where('municipality_id', $municipality->id)
            ->where('year', $this->year)->where('month', $this->month)->where('type', 'expenditure')
            ->sum('paid_value');

        $totalTransfers = Transfer::where('municipality_id', $municipality->id)
            ->where('year', $this->year)->where('month', $this->month)
            ->sum('transferred_value');

        $worksInProgress = PublicWork::where('municipality_id', $municipality->id)
            ->where('status', 'em_execucao')->count();
        $worksCompleted  = PublicWork::where('municipality_id', $municipality->id)
            ->where('status', 'concluida')->count();
        $worksDelayed    = PublicWork::where('municipality_id', $municipality->id)
            ->where('status', 'atrasada')->count();

        $billsApproved  = Bill::where('municipality_id', $municipality->id)
            ->where('year', $this->year)
            ->where(fn ($q) => $q->whereMonth('voted_at', $this->month))
            ->where('status', 'aprovado')->count();

        $mandateMonth = $mandate->getMandateMonth(
            Carbon::create($this->year, $this->month, 1)
        );

        TimelineSnapshot::updateOrCreate(
            ['municipality_id' => $municipality->id, 'year' => $this->year, 'month' => $this->month],
            [
                'mandate_id'        => $mandate->id,
                'mandate_month'     => $mandateMonth,
                'total_revenue'     => $totalRevenue,
                'total_expenditure' => $totalExpenditure,
                'total_transfers'   => $totalTransfers,
                'works_in_progress' => $worksInProgress,
                'works_completed'   => $worksCompleted,
                'works_delayed'     => $worksDelayed,
                'bills_approved'    => $billsApproved,
            ]
        );

        Log::info("GenerateTimelineSnapshotJob: {$this->ibgeCode} {$this->year}/{$this->month} — mês {$mandateMonth}/48");
    }
}