<?php

namespace App\Jobs;

use App\Models\Municipality;
use App\Models\TransparencyRanking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ComputeTransparencyRankingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;

    public function handle(): void
    {
        $year  = now()->year;
        $month = now()->month;

        Municipality::chunk(200, function ($municipalities) use ($year, $month) {
            foreach ($municipalities as $municipality) {
                $this->computeScore($municipality, $year, $month);
            }
        });

        // Calcular rankings nacional e por estado
        $this->computeNationalRanks($year, $month);
        $this->computeStateRanks($year, $month);

        // Atualizar pontuação na tabela municipalities
        TransparencyRanking::where('year', $year)->where('month', $month)
            ->chunk(100, function ($rankings) {
                foreach ($rankings as $r) {
                    $r->municipality()->update(['transparency_score' => $r->total_score]);
                }
            });

        Log::info("ComputeTransparencyRankingJob: rankings atualizados {$year}/{$month}");
    }

    private function computeScore(Municipality $m, int $year, int $month): void
    {
        $scores = [
            'electoral_data_score'  => $this->scoreElectoralData($m),   // max 20
            'finance_data_score'    => $this->scoreFinanceData($m, $year, $month), // max 25
            'works_data_score'      => $this->scoreWorksData($m),        // max 15
            'legislative_data_score'=> $this->scoreLegislativeData($m),  // max 20
            'executive_data_score'  => $this->scoreExecutiveData($m),    // max 10
            'portal_score'          => $m->has_open_data_portal ? 10 : 0, // max 10
        ];

        $totalScore = array_sum($scores);

        TransparencyRanking::updateOrCreate(
            ['municipality_id' => $m->id, 'year' => $year, 'month' => $month],
            array_merge($scores, ['total_score' => $totalScore])
        );
    }

    private function scoreElectoralData(Municipality $m): int
    {
        $hasMayor      = $m->mayors()->exists() ? 10 : 0;
        $hasLegislators= $m->legislators()->exists() ? 10 : 0;
        return $hasMayor + $hasLegislators;
    }

    private function scoreFinanceData(Municipality $m, int $year, int $month): int
    {
        $hasFinances = $m->finances()->where('year', $year)->where('month', $month)->exists();
        return $hasFinances ? 25 : 0;
    }

    private function scoreWorksData(Municipality $m): int
    {
        return $m->publicWorks()->exists() ? 15 : 0;
    }

    private function scoreLegislativeData(Municipality $m): int
    {
        return $m->bills()->exists() ? 20 : 0;
    }

    private function scoreExecutiveData(Municipality $m): int
    {
        return $m->secretaries()->exists() ? 10 : 0;
    }

    private function computeNationalRanks(int $year, int $month): void
    {
        $rankings = TransparencyRanking::where('year', $year)->where('month', $month)
            ->orderByDesc('total_score')->get();

        foreach ($rankings as $i => $ranking) {
            $ranking->update(['national_rank' => $i + 1]);
        }
    }

    private function computeStateRanks(int $year, int $month): void
    {
        $ufs = Municipality::distinct()->pluck('uf');
        foreach ($ufs as $uf) {
            $rankings = TransparencyRanking::where('year', $year)->where('month', $month)
                ->whereHas('municipality', fn ($q) => $q->where('uf', $uf))
                ->orderByDesc('total_score')->get();

            foreach ($rankings as $i => $ranking) {
                $ranking->update(['state_rank' => $i + 1]);
            }
        }
    }
}