<?php

use App\Http\Controllers\ClusterCompletenessReportController;
use App\Http\Controllers\PerformanceAnalyticsController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('scrn');
// });

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

// Route::get('/home', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('home');

Route::middleware('auth')->group(function () {

    Route::get('/logout', function () {
        // Store the previous URL before logout
        $previousUrl = url()->previous();

        // Logout the authenticated user
        auth()->logout();

        // Check if the previous URL is the same as the logout URL to avoid redirect loops
        if ($previousUrl == url('/logout')) {
            return redirect()->route('login');
        }

        // Redirect to the previous URL
        return redirect($previousUrl);
    })->name('logout');

    Route::prefix('reports/completeness')->group(function () {
        // Show the filter page - unchanged
        Route::get('/filter', [ClusterCompletenessReportController::class, 'showFilterPage'])
            ->name('completeness.filter');

        // Generate the report based on filters - now uses the consolidated method
        Route::get('/generate', [ClusterCompletenessReportController::class, 'generateCompletenessReport'])
            ->name('completeness.report');

        // Export the report - now uses the consolidated method with export parameters
        Route::get('/export', [ClusterCompletenessReportController::class, 'exportCompletenessReport'])
            ->name('completeness.export');

        // Detail view - now uses the consolidated method with detail view mode
        Route::get('/detail/{clusterPk}/{year}', function ($clusterPk, $year) {
            return app()->make(ClusterCompletenessReportController::class)
                ->generateCompletenessReport(request()->merge([
                    'cluster_pk' => [$clusterPk],
                    'year'       => $year,
                    'view_mode'  => 'detail',
                ]));
        })->name('completeness.detail');

        // Compare view - now uses the consolidated method with compare view mode
        Route::get('/compare', function () {
            return app()->make(ClusterCompletenessReportController::class)
                ->generateCompletenessReport(request()->merge([
                    'view_mode' => 'compare',
                ]));
        })->name('completeness.compare');

        // Dashboard view - now uses the consolidated method with dashboard view mode
        Route::get('/dashboard', function () {
            return app()->make(ClusterCompletenessReportController::class)
                ->generateCompletenessReport(request()->merge([
                    'view_mode' => 'dashboard',
                    'year'      => request('year', date('Y')),
                ]));
        })->name('completeness.dashboard');

        // API endpoints for modal content - unchanged
        Route::get('/api/cluster-detail', [ClusterCompletenessReportController::class, 'getClusterDetail'])
            ->name('api.completeness.cluster.detail');

        Route::get('/api/comparison-data', [ClusterCompletenessReportController::class, 'getComparisonData'])
            ->name('api.completeness.comparison');

        Route::get('/api/dashboard-data', [ClusterCompletenessReportController::class, 'getDashboardData'])
            ->name('api.completeness.dashboard');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->group(function () {

    Route::get('/', [App\Http\Controllers\PerformanceAnalyticsController::class, 'index'])
        ->name('dashboard');

    Route::get('/home', [App\Http\Controllers\PerformanceAnalyticsController::class, 'index'])
        ->name('home');
// routes/web.php

// Add this to your routes/web.php file
    Route::get('/performance-analytics/export-detailed', [PerformanceAnalyticsController::class, 'exportDetailedClusterPerformance'])->name('performance-analytics.export-detailed');

// Performance Analytics Routes
    Route::prefix('performance-analytics')->name('performance-analytics.')->group(function () {
        // Main dashboard view
        Route::get('/dashboard', [App\Http\Controllers\PerformanceAnalyticsController::class, 'index'])
            ->name('dashboard');

        // Excel export
        Route::get('/export-excel', [App\Http\Controllers\PerformanceAnalyticsController::class, 'exportExcel'])
            ->name('export-excel');

        // CSV export
        Route::get('/export-csv', [App\Http\Controllers\PerformanceAnalyticsController::class, 'exportCsv'])
            ->name('export-csv');
    });

    Route::prefix('performance-quarterly-reports')->group(function () {
        // Show filter form
        Route::get('/filter', [
            'as'   => 'ecsahc.performance.quarterly.filter',
            'uses' => 'App\Http\Controllers\PerformanceQuarterlyReportController@showFilterForm',
        ]);

        // Process filters and show results (POST for form submission)
        Route::post('/results', [
            'as'   => 'ecsahc.performance.quarterly.results',
            'uses' => 'App\Http\Controllers\PerformanceQuarterlyReportController@showResults',
        ]);

        // Alternative GET route for bookmarking results or direct access with query parameters
        Route::get('/results', [
            'as'   => 'ecsahc.performance.quarterly.results.get',
            'uses' => 'App\Http\Controllers\PerformanceQuarterlyReportController@showResults',
        ]);

        // Print-friendly version of results
        Route::get('/print', [
            'as'   => 'ecsahc.performance.quarterly.print',
            'uses' => 'App\Http\Controllers\PerformanceQuarterlyReportController@printResults',
        ]);

        // Export to Excel/CSV
        Route::get('/export', [
            'as'   => 'ecsahc.performance.quarterly.export',
            'uses' => 'App\Http\Controllers\PerformanceQuarterlyReportController@exportResults',
        ]);
    });

    // Redirect root path to filter form
    Route::redirect('/performance-quarterly-reports', '/performance-quarterly-reports/filter', 301);
});

// Redirect root path to filter form
Route::redirect('/performance-quarterly-reports', '/performance-quarterly-reports/filter', 301);

// Optional: Add middleware for authentication if needed
// Route::middleware(['auth', 'verified'])->group(function () {
//     // Place the above routes here if authentication is required
// });
require __DIR__ . '/auth.php';
require __DIR__ . '/v2.php';