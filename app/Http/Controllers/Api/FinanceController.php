<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Municipality;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class FinanceController extends Controller
{
    public function overview(Request $request, string $ibgeCode)
    {
        $municipality = Municipality::where('ibge_code', $ibgeCode)->firstOrFail();
        $year  = $request->integer('year', now()->year);
        $month = $request->integer('month', now()->month);

        $data = Cache::remember("finances.overview.{$ibgeCode}.{$year}.{$month}", 3600, function () use ($municipality, $year, $month) {
            $revenues     = $municipality->finances()->where('year', $year)->where('month', $month)->where('type', 'revenue')->sum('paid_value');
            $expenditures = $municipality->finances()->where('year', $year)->where('month', $month)->where('type', 'expenditure')->sum('paid_value');

            return [
                'year'               => $year,
                'month'              => $month,
                'total_revenue'      => $revenues,
                'total_expenditure'  => $expenditures,
                'balance'            => $revenues - $expenditures,
                'budget_execution_pct' => $municipality->annual_budget > 0
                    ? round($expenditures / ($municipality->annual_budget / 12) * 100, 1) : null,
                'data_source'    => 'SICONFI — Tesouro Nacional',
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function revenues(Request $request, string $ibgeCode)
    {
        $municipality = Municipality::where('ibge_code', $ibgeCode)->firstOrFail();
        $year  = $request->integer('year', now()->year);
        $month = $request->integer('month', now()->month);

        $revenues = $municipality->finances()
            ->where('type', 'revenue')
            ->where('year', $year)
            ->where('month', $month)
            ->orderByDesc('paid_value')
            ->get(['category', 'subcategory', 'budgeted_value', 'paid_value']);

        return response()->json([
            'data'        => $revenues,
            'period'      => compact('year', 'month'),
            'data_source' => 'SICONFI',
        ]);
    }

    public function expenditures(Request $request, string $ibgeCode)
    {
        $municipality = Municipality::where('ibge_code', $ibgeCode)->firstOrFail();
        $year  = $request->integer('year', now()->year);
        $month = $request->integer('month', now()->month);

        $expenditures = $municipality->finances()
            ->where('type', 'expenditure')
            ->where('year', $year)
            ->where('month', $month)
            ->orderByDesc('paid_value')
            ->get(['category', 'subcategory', 'budgeted_value', 'committed_value', 'liquidated_value', 'paid_value']);

        return response()->json([
            'data'        => $expenditures,
            'period'      => compact('year', 'month'),
            'data_source' => 'SICONFI',
        ]);
    }

    public function fiscalReport(string $ibgeCode)
    {
        $municipality = Municipality::where('ibge_code', $ibgeCode)->firstOrFail();
        return response()->json([
            'data'        => null,
            'message'     => 'Relatório de Gestão Fiscal (RGF) — em integração',
            'data_source' => 'SICONFI — Tesouro Nacional',
        ]);
    }
}