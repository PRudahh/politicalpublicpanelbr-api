<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Municipality;
use Illuminate\Http\Request;

class BillController extends Controller
{
    public function byMunicipality(Request $request, string $ibgeCode)
    {
        $municipality = Municipality::where('ibge_code', $ibgeCode)->firstOrFail();

        $bills = $municipality->bills()
            ->with('legislator.politician')
            ->when($request->status,       fn ($q) => $q->where('status', $request->status))
            ->when($request->category,     fn ($q) => $q->where('category', $request->category))
            ->when($request->year,         fn ($q) => $q->where('year', $request->year))
            ->when($request->legislator_id, fn ($q) => $q->where('legislator_id', $request->legislator_id))
            ->orderByDesc('submitted_at')
            ->paginate(30);

        return response()->json($bills);
    }

    public function byLegislator(Request $request, string $ibgeCode, int $legislatorId)
    {
        $municipality = Municipality::where('ibge_code', $ibgeCode)->firstOrFail();
        $legislator   = $municipality->legislators()->findOrFail($legislatorId);

        $bills = $legislator->bills()
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->orderByDesc('submitted_at')
            ->paginate(20);

        return response()->json($bills);
    }
}