<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MunicipalityController;
use App\Http\Controllers\Api\ExecutiveController;
use App\Http\Controllers\Api\LegislatureController;
use App\Http\Controllers\Api\BillController;
use App\Http\Controllers\Api\PublicWorkController;
use App\Http\Controllers\Api\FinanceController;
use App\Http\Controllers\Api\TransferController;
use App\Http\Controllers\Api\TimelineController;
use App\Http\Controllers\Api\RankingController;
use App\Http\Controllers\Api\ExportController;

/*
|--------------------------------------------------------------------------
| API Routes — Painel Político Nacional (v1)
|--------------------------------------------------------------------------
|
| Todos os endpoints de leitura são públicos.
| Endpoints de exportação e comparação avançada requerem token Sanctum
| (Modo Jornalista).
|
*/

Route::prefix('v1')->name('api.v1.')->group(function () {

    // ── Autenticação (Modo Jornalista) ────────────────────────────────
    Route::prefix('auth/journalist')->name('auth.journalist.')->group(function () {
        Route::post('register', [AuthController::class, 'register'])->name('register');
        Route::post('login',    [AuthController::class, 'login'])->name('login');
        Route::post('logout',   [AuthController::class, 'logout'])
            ->middleware('auth:sanctum')->name('logout');
    });

    // ── Municípios ────────────────────────────────────────────────────
    Route::prefix('municipalities')->name('municipalities.')->group(function () {
        Route::get('/',          [MunicipalityController::class, 'index'])->name('index');
        Route::get('search',     [MunicipalityController::class, 'search'])->name('search');

        Route::prefix('{ibgeCode}')->group(function () {
            Route::get('/',        [MunicipalityController::class, 'show'])->name('show');
            Route::get('summary',  [MunicipalityController::class, 'summary'])->name('summary');

            // Executivo
            Route::prefix('executive')->name('executive.')->group(function () {
                Route::get('/',            [ExecutiveController::class, 'index'])->name('index');
                Route::get('mayor',        [ExecutiveController::class, 'mayor'])->name('mayor');
                Route::get('secretaries',  [ExecutiveController::class, 'secretaries'])->name('secretaries');
            });

            // Legislativo (câmara de vereadores)
            Route::prefix('legislature')->name('legislature.')->group(function () {
                Route::get('/',              [LegislatureController::class, 'index'])->name('index');
                Route::get('{legislatorId}', [LegislatureController::class, 'show'])->name('show');
                Route::get('{legislatorId}/bills', [BillController::class, 'byLegislator'])->name('bills');
            });

            // Projetos de lei da câmara
            Route::get('bills', [BillController::class, 'byMunicipality'])->name('bills.index');

            // Obras públicas
            Route::prefix('public-works')->name('public-works.')->group(function () {
                Route::get('/',       [PublicWorkController::class, 'index'])->name('index');
                Route::get('{workId}', [PublicWorkController::class, 'show'])->name('show');
            });

            // Finanças
            Route::prefix('finances')->name('finances.')->group(function () {
                Route::get('/',             [FinanceController::class, 'overview'])->name('overview');
                Route::get('revenues',      [FinanceController::class, 'revenues'])->name('revenues');
                Route::get('expenditures',  [FinanceController::class, 'expenditures'])->name('expenditures');
                Route::get('fiscal-report', [FinanceController::class, 'fiscalReport'])->name('fiscal-report');
            });

            // Repasses
            Route::get('transfers', [TransferController::class, 'index'])->name('transfers.index');

            // Linha do tempo do mandato
            Route::prefix('timeline')->name('timeline.')->group(function () {
                Route::get('/',               [TimelineController::class, 'index'])->name('index');
                Route::get('{year}/{month}',  [TimelineController::class, 'snapshot'])->name('snapshot');
            });
        });
    });

    // ── Rankings ──────────────────────────────────────────────────────
    Route::prefix('rankings')->name('rankings.')->group(function () {
        Route::get('transparency',    [RankingController::class, 'transparency'])->name('transparency');
        Route::get('budget-execution',[RankingController::class, 'budgetExecution'])->name('budget-execution');
        Route::get('public-works',    [RankingController::class, 'publicWorks'])->name('public-works');
        Route::get('legislative',     [RankingController::class, 'legislative'])->name('legislative');
    });

    // ── Exportação e Modo Jornalista (requer token) ───────────────────
    Route::prefix('export')->name('export.')->middleware('auth:sanctum')->group(function () {
        Route::get('{ibgeCode}/full',         [ExportController::class, 'full'])->name('full');
        Route::get('{ibgeCode}/legislators',  [ExportController::class, 'legislators'])->name('legislators');
        Route::get('{ibgeCode}/finances',     [ExportController::class, 'finances'])->name('finances');
        Route::get('{ibgeCode}/public-works', [ExportController::class, 'publicWorks'])->name('public-works');
        Route::get('compare',                 [ExportController::class, 'compare'])->name('compare');
    });

    // ── Health check ─────────────────────────────────────────────────
    Route::get('health', fn () => response()->json([
        'status'    => 'ok',
        'version'   => '1.0.0',
        'timestamp' => now()->toIso8601String(),
    ]))->name('health');
});
