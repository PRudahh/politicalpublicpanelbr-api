<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Municipality;
use Illuminate\Support\Facades\Cache;

class ExecutiveController extends Controller
{
    public function index(string $ibgeCode)
    {
        $municipality = Municipality::where('ibge_code', $ibgeCode)->firstOrFail();

        $data = Cache::remember("executive.{$ibgeCode}", 3600, function () use ($municipality) {
            return [
                'mayor'       => $municipality->currentMayor,
                'secretaries' => $municipality->currentSecretaries()->get(),
                'data_source' => 'TSE + Portal Transparência Municipal',
                'last_updated' => now()->toDateString(),
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function mayor(string $ibgeCode)
    {
        $municipality = Municipality::where('ibge_code', $ibgeCode)->firstOrFail();
        $mayor = $municipality->currentMayor()->with('politician', 'mandate')->firstOrFail();

        return response()->json(['data' => $mayor]);
    }

    public function secretaries(string $ibgeCode)
    {
        $municipality = Municipality::where('ibge_code', $ibgeCode)->firstOrFail();
        $secretaries  = $municipality->currentSecretaries()->with('politician')->get();

        return response()->json([
            'data'        => $secretaries,
            'total'       => $secretaries->count(),
            'data_source' => 'Portal de Transparência Municipal',
        ]);
    }
}