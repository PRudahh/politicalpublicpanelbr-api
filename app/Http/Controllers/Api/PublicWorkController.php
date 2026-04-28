<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Municipality;
use Illuminate\Http\Request;

class PublicWorkController extends Controller
{
    public function index(Request $request, string $ibgeCode)
    {
        $municipality = Municipality::where('ibge_code', $ibgeCode)->firstOrFail();

        $works = $municipality->publicWorks()
            ->when($request->status,          fn ($q) => $q->where('status', $request->status))
            ->when($request->category,        fn ($q) => $q->where('category', $request->category))
            ->when($request->funding_source, fn ($q) => $q->where('funding_source', $request->funding_source))
            ->orderByDesc('contracted_value')
            ->paginate(20);

        return response()->json($works);
    }

    public function show(string $ibgeCode, int $workId)
    {
        $municipality = Municipality::where('ibge_code', $ibgeCode)->firstOrFail();
        $work = $municipality->publicWorks()->findOrFail($workId);

        return response()->json(['data' => $work]);
    }
}