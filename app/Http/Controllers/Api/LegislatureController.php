<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Municipality;
use Illuminate\Http\Request;

class LegislatureController extends Controller
{
    public function index(Request $request, string $ibgeCode)
    {
        $municipality = Municipality::where('ibge_code', $ibgeCode)->firstOrFail();

        $query = $municipality->currentLegislators()
            ->with('politician')
            ->when($request->party, fn ($q) => $q->whereHas('politician', fn ($q2) => $q2->where('party_abbreviation', $request->party)));

        $sortBy = match ($request->sort_by) {
            'bills_approved' => 'bills_approved',
            'attendance'     => 'sessions_present',
            default          => 'votes_received',
        };

        $legislators = $query->orderByDesc($sortBy)->get();

        return response()->json([
            'data'        => $legislators,
            'total'       => $legislators->count(),
            'data_source' => 'TSE + Câmara Municipal',
        ]);
    }

    public function show(string $ibgeCode, int $legislatorId)
    {
        $municipality = Municipality::where('ibge_code', $ibgeCode)->firstOrFail();

        $legislator = $municipality->legislators()
            ->with(['politician', 'bills' => fn ($q) => $q->latest()->limit(10)])
            ->findOrFail($legislatorId);

        return response()->json(['data' => $legislator]);
    }
}