<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Municipality;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function full(Request $request, string $ibgeCode)
    {
        $format = $request->input('format', 'json');
        $municipality = Municipality::with([
            'currentMayor.politician',
            'currentSecretaries.politician',
            'currentLegislators.politician',
            'bills',
            'publicWorks',
        ])->where('ibge_code', $ibgeCode)->firstOrFail();

        if ($format === 'csv') {
            return $this->exportCsv($municipality);
        }

        return response()->json(['data' => $municipality]);
    }

    public function legislators(Request $request, string $ibgeCode)
    {
        $municipality = Municipality::where('ibge_code', $ibgeCode)->firstOrFail();
        $legislators  = $municipality->currentLegislators()->with('politician')->get();

        if ($request->input('format') === 'csv') {
            return $this->streamCsv($legislators, "vereadores_{$ibgeCode}.csv");
        }

        return response()->json(['data' => $legislators, 'total' => $legislators->count()]);
    }

    public function finances(Request $request, string $ibgeCode)
    {
        $municipality = Municipality::where('ibge_code', $ibgeCode)->firstOrFail();
        $year = $request->integer('year', now()->year);

        $finances = $municipality->finances()->where('year', $year)->get();

        if ($request->input('format') === 'csv') {
            return $this->streamCsv($finances, "financas_{$ibgeCode}_{$year}.csv");
        }

        return response()->json(['data' => $finances]);
    }

    public function publicWorks(Request $request, string $ibgeCode)
    {
        $municipality = Municipality::where('ibge_code', $ibgeCode)->firstOrFail();
        $works = $municipality->publicWorks()->get();

        if ($request->input('format') === 'csv') {
            return $this->streamCsv($works, "obras_{$ibgeCode}.csv");
        }

        return response()->json(['data' => $works]);
    }

    public function compare(Request $request)
    {
        $request->validate(['codes' => 'required|string']);

        $codes         = explode(',', $request->codes);
        $module        = $request->input('module', 'summary');
        $municipalities = Municipality::whereIn('ibge_code', $codes)->with('currentMandate')->get();

        return response()->json([
            'data'    => $municipalities,
            'module'  => $module,
            'total'   => $municipalities->count(),
        ]);
    }

    private function streamCsv($collection, string $filename): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return response()->streamDownload(function () use ($collection) {
            $handle = fopen('php://output', 'w');
            if ($collection->isNotEmpty()) {
                fputcsv($handle, array_keys($collection->first()->toArray()));
                foreach ($collection as $row) {
                    fputcsv($handle, $row->toArray());
                }
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    private function exportCsv(Municipality $municipality): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return $this->streamCsv(collect([$municipality]), "municipio_{$municipality->ibge_code}.csv");
    }
}