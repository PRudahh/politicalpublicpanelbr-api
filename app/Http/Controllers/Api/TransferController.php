<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Municipality;
use Illuminate\Http\Request;

class TransferController extends Controller
{
    public function index(Request $request, string $ibgeCode)
    {
        $municipality = Municipality::where('ibge_code', $ibgeCode)->firstOrFail();

        $transfers = $municipality->transfers()
            ->when($request->source, fn ($q) => $q->where('source_sphere', $request->source))
            ->when($request->year,   fn ($q) => $q->where('year', $request->year))
            ->when($request->month,  fn ($q) => $q->where('month', $request->month))
            ->when($request->type,   fn ($q) => $q->where('transfer_type', $request->type))
            ->orderByDesc('transferred_value')
            ->paginate(30);

        return response()->json($transfers);
    }
}