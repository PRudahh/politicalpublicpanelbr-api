<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RankingController extends Controller
{
    public function transparency(Request $request)
    {
        $cacheKey = 'ranking.transparency.' . md5($request->fullUrl());

        $data = Cache::remember($cacheKey, config('cache.ttls.ranking'), function () use ($request) {
            return \App\Models\TransparencyRanking::with('municipality:id,ibge_code,name,uf')
                ->where('year', now()->year)
                ->where('month', now()->month)
                ->when($request->uf, fn ($q) => $q->whereHas('municipality', fn ($q2) => $q2->where('uf', strtoupper($request->uf))))
                ->orderByDesc('total_score')
                ->paginate($request->integer('per_page', 50));
        });

        return response()->json($data);
    }

    public function budgetExecution(Request $request)
    {
        return response()->json(['message' => 'Em implementação']);
    }

    public function publicWorks(Request $request)
    {
        return response()->json(['message' => 'Em implementação']);
    }

    public function legislative(Request $request)
    {
        return response()->json(['message' => 'Em implementação']);
    }
}