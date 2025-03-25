<?php
use App\Http\Controllers\Clusters;
use App\Http\Controllers\ClusterTargetController;
use App\Http\Controllers\CrudController;
use App\Http\Controllers\EcsahcTimelines;
use App\Http\Controllers\EcsaReportingController;
use App\Http\Controllers\IndicatorsController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {

    Route::post('/mark-indicators-not-applicable', [EcsaReportingController::class, 'MarkIndicatorsNotApplicable'])->name('MarkIndicatorsNotApplicable');

    Route::get('/get-reporting-summary', [EcsaReportingController::class, 'GetReportingSummary'])->name('GetReportingSummary');

    Route::prefix('ecsa')->group(function () {

        // GET route for selecting a user.
        Route::get('select-user', [EcsaReportingController::class, 'SelectUser'])
            ->name('Ecsa_SelectUser');

        // Route::get('/', [EcsaReportingController::class, 'SelectUser']);

        // POST route for selecting a cluster.
        Route::any('select-cluster', [EcsaReportingController::class, 'SelectCluster'])
            ->name('Ecsa_SelectCluster');

        // POST route for selecting a timeline.
        Route::any('select-timeline', [EcsaReportingController::class, 'SelectTimeline'])
            ->name('Ecsa_SelectTimeline');

        // POST route for selecting a strategic objective.
        Route::any('select-strategic-objective', [EcsaReportingController::class, 'SelectStrategicObjective'])
            ->name('Ecsa_SelectStrategicObjective');

        // POST route for reporting performance indicators.
        Route::any('report-performance-indicators', [EcsaReportingController::class, 'ReportPerformanceIndicators'])
            ->name('Ecsa_ReportPerformanceIndicators');

        // POST route for saving the performance report.
        Route::any('save-performance-report', [EcsaReportingController::class, 'SavePerformanceReport'])
            ->name('Ecsa_SavePerformanceReport');

        // POST route for getting the reporting summary.
        Route::any('get-reporting-summary', [EcsaReportingController::class, 'GetReportingSummary'])
            ->name('Ecsa_GetReportingSummary');
    });

    Route::prefix('targets')->name('targets.')->group(function () {
        // Display the cluster selection view for target management.
        Route::get('/', [ClusterTargetController::class, 'index'])->name('index');

        // Display the target management form for a selected cluster.
        // (This route expects a query parameter or form submission with ClusterID.)
        Route::get('/setup', [ClusterTargetController::class, 'showTargetForm'])->name('setup');

        // Store a new target.
        Route::post('/', [ClusterTargetController::class, 'saveTarget'])->name('store');

        // Update an existing target.
        Route::put('/{target}', [ClusterTargetController::class, 'updateTarget'])->name('update');

        // Delete an existing target.
        Route::delete('/{target}', [ClusterTargetController::class, 'delete'])->name('destroy');
    });

    Route::get('/MgtEcsaUsers', [UsersController::class, 'MgtEcsaUsers'])->name('MgtEcsaUsers');

    Route::post('/MassInsert', [CrudController::class, 'MassInsert'])->name('MassInsert');

// Mass Update Route
    Route::put('/MassUpdate', [CrudController::class, 'MassUpdate'])->name('MassUpdate');

// Mass Delete Route
    Route::delete('/MassDelete', [CrudController::class, 'MassDelete'])->name('MassDelete');

    Route::get('/MgtClusters', [Clusters::class, 'MgtClusters'])->name('MgtClusters');

    Route::get('/MgtEcsaTimelinesStatus', [EcsahcTimelines::class, 'MgtEcsaTimelinesStatus'])
        ->name('MgtEcsaTimelinesStatus');

    Route::get('/MgtEcsaTimelines', [EcsahcTimelines::class, 'MgtEcsaTimelines'])->name('MgtEcsaTimelines');

    Route::any('/UpdateEcsahcIndicators', [IndicatorsController::class, 'UpdateEcsahcIndicators'])->name('UpdateEcsahcIndicators');

    Route::any('/DeleteEcsahcIndicators', [IndicatorsController::class, 'DeleteEcsahcIndicators'])->name('DeleteEcsahcIndicators');

    Route::post('/AddEcsahcIndicators', [IndicatorsController::class, 'AddEcsahcIndicators'])->name('AddEcsahcIndicators');

    // Mass Insert Route
    Route::any('/MgtEcsaIndicators', [IndicatorsController::class, 'MgtEcsaIndicators'])
        ->name('MgtEcsaIndicators');

    Route::get('/SelectSo', [IndicatorsController::class, 'SelectSo'])->name('SelectSo');

    Route::get('/MgtSO', [IndicatorsController::class, 'MgtSO'])->name('MgtSO');
});