<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Municipality;
use Illuminate\Support\Facades\Cache;

class TimelineController extends Controller
{
    public function index(string $ibgeCode)
    {
        $municipality = Municipality::where('ibge_code', $ibgeCode)->firstOrFail();

        $snapshots = Cache::remember("timeline.{$ibgeCode}", 3600, function () use ($municipality) {
            return $municipality->timelineSnapshots()->with('mandate')->get();
        });

        return response()->json([
            'data'          => $snapshots,
            'mandate_months'=> $snapshots->count(),
            'data_source'   => 'Consolidado interno (SICONFI + Portal Transparência)',
        ]);
    }

    public function snapshot(string $ibgeCode, int $year, int $month)
    {
        $municipality = Municipality::where('ibge_code', $ibgeCode)->firstOrFail();

        $snapshot = $municipality->timelineSnapshots()
            ->where('year', $year)
            ->where('month', $month)
            ->firstOrFail();

        return response()->json(['data' => $snapshot]);
    }
}