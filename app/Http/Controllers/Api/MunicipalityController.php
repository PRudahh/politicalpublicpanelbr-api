<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MunicipalityResource;
use App\Http\Resources\MunicipalitySummaryResource;
use App\Models\Municipality;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MunicipalityController extends Controller
{
    public function index(Request $request)
    {
        $cacheKey = 'municipalities.index.' . md5($request->fullUrl());

        $data = Cache::remember($cacheKey, config('cache.ttls.municipality'), function () use ($request) {
            $query = Municipality::query();

            if ($request->uf) {
                $query->byUf($request->uf);
            }
            if ($request->region) {
                $query->where('region', $request->region);
            }
            if ($request->boolean('has_open_data')) {
                $query->where('has_open_data_portal', true);
            }

            $sortBy = in_array($request->sort_by, ['name', 'population', 'transparency_score'])
                ? $request->sort_by : 'name';

            return $query->orderBy($sortBy)
                         ->paginate($request->integer('per_page', 50));
        });

        return MunicipalityResource::collection($data);
    }

    public function search(Request $request)
    {
        $request->validate(['q' => 'required|string|min:2']);

        $results = Municipality::search($request->q)
            ->select(['id', 'ibge_code', 'name', 'uf', 'transparency_level'])
            ->limit(20)
            ->get();

        return MunicipalityResource::collection($results);
    }

    public function show(string $ibgeCode)
    {
        $municipality = Cache::remember(
            "municipality.{$ibgeCode}",
            config('cache.ttls.municipality'),
            fn () => Municipality::with('currentMandate')->where('ibge_code', $ibgeCode)->firstOrFail()
        );

        return new MunicipalityResource($municipality);
    }

    public function summary(string $ibgeCode)
    {
        $municipality = Cache::remember(
            "municipality.summary.{$ibgeCode}",
            config('cache.ttls.municipality'),
            fn () => Municipality::with([
                'currentMandate',
                'currentMayor.politician',
                'transparencyRankings' => fn ($q) => $q->latest()->limit(1),
            ])->where('ibge_code', $ibgeCode)->firstOrFail()
        );

        return new MunicipalitySummaryResource($municipality);
    }
}