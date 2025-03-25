<?php

use App\Http\Controllers\ClusterCompletenessReportController;
use App\Http\Controllers\PerformanceQuarterlyReportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
// API endpoints for modal content - unchanged

Route::prefix('reports/completeness')->group(function () {

    Route::get('/api/cluster-detail', [ClusterCompletenessReportController::class, 'getClusterDetail'])
        ->name('api.completeness.cluster.detail');

    Route::get('/api/comparison-data', [ClusterCompletenessReportController::class, 'getComparisonData'])
        ->name('api.completeness.comparison');

    Route::get('/api/dashboard-data', [ClusterCompletenessReportController::class, 'getDashboardData'])
        ->name('api.completeness.dashboard');

    // API routes for chart data
    Route::get('/api/chart-data', [PerformanceQuarterlyReportController::class, 'getChartData'])->name('api.chart_data');

});