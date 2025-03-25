<?php
namespace App\Http\Controllers;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PerformanceQuarterlyReportController extends Controller
{
    /**
     * Display the filter selection view for quarterly reports
     */
    public function showFilterForm()
    {
        $error = null;

        try {
            // Try to use the view for all data
            $viewData = DB::table('vw_performance_over_time_by_quarter');

            // Get available years from the view
            $years = $viewData->select('timeline_year')
                ->distinct()
                ->orderBy('timeline_year', 'desc')
                ->pluck('timeline_year');

            // Get available quarters from the view
            $quarters = $viewData->select('timeline_quarter')
                ->distinct()
                ->orderBy('timeline_quarter')
                ->pluck('timeline_quarter')
                ->mapWithKeys(function ($quarter) {
                    return [$quarter => "Q{$quarter}"];
                });

            // Get clusters from the view
            $clusters = $viewData->select('cluster_pk as id', 'cluster_code as ClusterID', 'cluster_name as Cluster_Name')
                ->distinct()
                ->orderBy('cluster_name')
                ->get();

            // Get indicators from the view
            $indicators = $viewData->select('indicator_pk as id', 'indicator_number as Indicator_Number', 'indicator_name as Indicator_Name')
                ->distinct()
                ->orderBy('indicator_number')
                ->get();

            // Get response types from the view
            $responseTypes = $viewData->select('indicator_response_type')
                ->distinct()
                ->pluck('indicator_response_type');

            // Get statuses from the view
            $statuses = $viewData->select('status_label')
                ->distinct()
                ->pluck('status_label');

            // For strategic objectives, we still need to use the strategic_objectives table
            $strategicObjectives = DB::table('strategic_objectives')
                ->select('id', 'SO_Number', 'SO_Name')
                ->orderBy('SO_Number')
                ->get();

        } catch (\Exception $e) {
            Log::error('Error loading data from performance view: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            // Set error message
            $error = '';

            // Initialize with empty collections
            $years               = collect();
            $quarters            = collect();
            $clusters            = collect();
            $indicators          = collect();
            $responseTypes       = collect();
            $statuses            = collect();
            $strategicObjectives = collect();

            // Try to get years from ecsahc_timelines as fallback
            try {
                $years = DB::table('ecsahc_timelines')
                    ->select('Year')
                    ->distinct()
                    ->orderBy('Year', 'desc')
                    ->pluck('Year');
            } catch (\Exception $e2) {
                Log::warning('Could not load years from ecsahc_timelines: ' . $e2->getMessage());
                $years = collect([date('Y'), date('Y') - 1, date('Y') - 2]);
            }

            // Set default quarters
            $quarters = collect([1, 2, 3, 4])->mapWithKeys(fn($q) => [$q => "Q{$q}"]);

            // Try to get clusters from clusters table
            try {
                $clusters = DB::table('clusters')
                    ->select('id', 'ClusterID', 'Cluster_Name')
                    ->orderBy('Cluster_Name')
                    ->get();

                if ($clusters->isEmpty()) {
                    // Try alternative column names
                    $clusters = DB::table('clusters')
                        ->select('id', 'cluster_code as ClusterID', 'cluster_name as Cluster_Name')
                        ->orderBy('cluster_name')
                        ->get();
                }
            } catch (\Exception $e2) {
                Log::warning('Could not load clusters: ' . $e2->getMessage());
            }

            // Try to get indicators from performance_indicators table
            try {
                $indicators = DB::table('performance_indicators')
                    ->select('id', 'Indicator_Number', 'Indicator_Name')
                    ->orderBy('Indicator_Number')
                    ->get();

                if ($indicators->isEmpty()) {
                    // Try alternative column names
                    $indicators = DB::table('performance_indicators')
                        ->select('id', 'indicator_number as Indicator_Number', 'indicator_name as Indicator_Name')
                        ->orderBy('indicator_number')
                        ->get();
                }
            } catch (\Exception $e2) {
                Log::warning('Could not load indicators: ' . $e2->getMessage());
            }

            // Try to get response types
            try {
                $responseTypes = DB::table('performance_indicators')
                    ->select('Response_Type')
                    ->distinct()
                    ->pluck('Response_Type');

                if ($responseTypes->isEmpty()) {
                    // Try alternative column name
                    $responseTypes = DB::table('performance_indicators')
                        ->select('indicator_response_type')
                        ->distinct()
                        ->pluck('indicator_response_type');
                }
            } catch (\Exception $e2) {
                Log::warning('Could not load response types: ' . $e2->getMessage());
                $responseTypes = collect(['Text', 'Number', 'Boolean', 'Yes/No']);
            }

            // Set default statuses
            $statuses = collect(['Needs Attention', 'In Progress', 'On Track', 'Met']);

            // Try to get strategic objectives
            try {
                $strategicObjectives = DB::table('strategic_objectives')
                    ->select('id', 'SO_Number', 'SO_Name')
                    ->orderBy('SO_Number')
                    ->get();
            } catch (\Exception $e2) {
                Log::warning('Could not load strategic objectives: ' . $e2->getMessage());
            }
        }

        // Default to current year if available
        $defaultYear = date('Y');
        if (! $years->contains($defaultYear) && $years->isNotEmpty()) {
            $defaultYear = $years->first();
        }

        return view('scrn', [
            'Page'                => 'Quarterlyreports.performance_filter',
            'years'               => $years,
            'quarters'            => $quarters,
            'clusters'            => $clusters,
            'indicators'          => $indicators,
            'strategicObjectives' => $strategicObjectives,
            'responseTypes'       => $responseTypes,
            'statuses'            => $statuses,
            'defaultYear'         => $defaultYear,
            'error'               => $error,
        ]);
    }

    /**
     * Process filter inputs and display results
     */
    public function showResults(Request $request)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'year'                   => 'required|integer|min:2000|max:2100',
                'quarter'                => 'nullable|integer|min:1|max:4',
                'cluster_id'             => 'nullable|integer',
                'indicator_id'           => 'nullable|integer',
                'strategic_objective_id' => 'nullable|integer',
                'status'                 => 'nullable|string',
                'response_type'          => 'nullable|string',
                'min_achievement'        => 'nullable|numeric|min:0|max:100',
                'max_achievement'        => 'nullable|numeric|min:0|max:100',
                'sort_by'                => 'nullable|string',
                'sort_direction'         => 'nullable|in:asc,desc',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $filters = $validator->validated();

            // Base query from the performance view
            $query = DB::table('vw_performance_over_time_by_quarter');

            // Apply filters
            $this->applyFilters($query, $filters);

            // Apply sorting
            $this->applySorting($query, $filters);

            // Get the filtered data - ensure we're getting distinct records to avoid duplicates
            $performanceData = $query->get();

            // If no data found, return with message
            if ($performanceData->isEmpty()) {
                return view('scrn', [
                    'Page'                 => 'Quarterlyreports.performance_results',
                    'performanceData'      => collect(),
                    'filters'              => $filters,
                    'noDataMessage'        => 'No data found for the selected filters.',
                    'rawData'              => null,
                    'summaryMetrics'       => null,
                    'insights'             => [['message' => 'No data available for the selected filters.', 'type' => 'warning']],
                    'dataQualityIssues'    => null,
                    'trends'               => null,
                    'comparisons'          => null,
                    'statusDistribution'   => null,
                    'achievementGaps'      => null,
                    'anomalies'            => null,
                    'clusterRankings'      => null,
                    'indicatorPerformance' => null,
                    'error'                => null,
                ]);
            }

            // Check for data quality issues
            $dataQualityIssues = $this->analyzeDataQuality($performanceData);

            // Generate insights
            $insights = $this->generateInsights($performanceData, $filters);

            // Prepare summary metrics
            $summaryMetrics = $this->calculateSummaryMetrics($performanceData);

            // Prepare trend analysis
            $trends = $this->analyzeTrends($performanceData, $filters);

            // Prepare comparative analysis
            $comparisons = $this->prepareComparisons($performanceData, $filters);

            // Prepare status distribution
            $statusDistribution = $this->analyzeStatusDistribution($performanceData);

            // Prepare achievement gap analysis
            $achievementGaps = $this->analyzeAchievementGaps($performanceData);

            // Detect anomalies
            $anomalies = $this->detectAnomalies($performanceData);

            // Prepare cluster rankings
            $clusterRankings = $this->rankClusters($performanceData);

            // Prepare indicator performance
            $indicatorPerformance = $this->analyzeIndicatorPerformance($performanceData);

            // Prepare raw data for export or detailed view
            $rawData = $this->prepareRawData($performanceData);

            // Prepare data for charts
            $chartData = $this->prepareChartData($performanceData, $filters);

            return view('scrn', [
                'Page'                 => 'Quarterlyreports.performance_results',
                'performanceData'      => $performanceData,
                'filters'              => $filters,
                'rawData'              => $rawData,
                'chartData'            => $chartData,
                'dataQualityIssues'    => $dataQualityIssues,
                'insights'             => $insights,
                'summaryMetrics'       => $summaryMetrics,
                'trends'               => $trends,
                'comparisons'          => $comparisons,
                'statusDistribution'   => $statusDistribution,
                'achievementGaps'      => $achievementGaps,
                'anomalies'            => $anomalies,
                'clusterRankings'      => $clusterRankings,
                'indicatorPerformance' => $indicatorPerformance,
                'error'                => null,
                'noDataMessage'        => null,
            ]);
        } catch (\Exception $e) {
            Log::error('Error processing performance data: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return view('scrn', [
                'Page'                 => 'Quarterlyreports.performance_results',
                'performanceData'      => collect(),
                'filters'              => $request->all(),
                'rawData'              => null,
                'chartData'            => null,
                'dataQualityIssues'    => null,
                'insights'             => null,
                'summaryMetrics'       => null,
                'trends'               => null,
                'comparisons'          => null,
                'statusDistribution'   => null,
                'achievementGaps'      => null,
                'anomalies'            => null,
                'clusterRankings'      => null,
                'indicatorPerformance' => null,
                'error'                => 'Error processing performance data: ' . $e->getMessage(),
                'noDataMessage'        => null,
            ]);
        }
    }

    /**
     * Apply filters to the query
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        // Apply year filter (mandatory)
        $query->where('timeline_year', $filters['year']);

        // Apply quarter filter
        if (! empty($filters['quarter'])) {
            $query->where('timeline_quarter', $filters['quarter']);
        }

        // Apply cluster filter
        if (! empty($filters['cluster_id'])) {
            $query->where('cluster_pk', $filters['cluster_id']);
        }

        // Apply indicator filter
        if (! empty($filters['indicator_id'])) {
            $query->where('indicator_pk', $filters['indicator_id']);
        }

        // Apply strategic objective filter - this might require a subquery approach
        if (! empty($filters['strategic_objective_id'])) {
            // Get indicators associated with this strategic objective
            $indicatorIds = DB::table('performance_indicators')
                ->where('SO_ID', $filters['strategic_objective_id'])
                ->pluck('id');

            // Filter the view by these indicator IDs
            $query->whereIn('indicator_pk', $indicatorIds);
        }

        // Apply status filter
        if (! empty($filters['status'])) {
            $query->where('status_label', $filters['status']);
        }

        // Apply response type filter
        if (! empty($filters['response_type'])) {
            $query->where('indicator_response_type', $filters['response_type']);
        }

        // Apply achievement threshold filter
        if (! empty($filters['min_achievement'])) {
            $query->where('achievement_percent', '>=', $filters['min_achievement']);
        }

        if (! empty($filters['max_achievement'])) {
            $query->where('achievement_percent', '<=', $filters['max_achievement']);
        }
    }

    /**
     * Apply sorting to the query
     */
    private function applySorting(Builder $query, array $filters): void
    {
        $sortBy        = $filters['sort_by'] ?? 'achievement_percent';
        $sortDirection = $filters['sort_direction'] ?? 'desc';

        // Map sort fields to actual column names if needed
        $sortFieldMap = [
            'achievement' => 'achievement_percent',
            'cluster'     => 'cluster_name',
            'indicator'   => 'indicator_name',
            'status'      => 'status_label',
            'target'      => 'total_target_value',
            'actual'      => 'total_actual_value',
        ];

        $sortField = $sortFieldMap[$sortBy] ?? $sortBy;

        $query->orderBy($sortField, $sortDirection);

        // Add secondary sort for consistency
        if ($sortField !== 'cluster_name') {
            $query->orderBy('cluster_name', 'asc');
        }

        if ($sortField !== 'indicator_name') {
            $query->orderBy('indicator_name', 'asc');
        }
    }

    /**
     * Prepare raw data for export or detailed view
     */
    private function prepareRawData(Collection $data): array
    {
        return $data->map(function ($item) {
            return [
                'year'                => $item->timeline_year,
                'quarter'             => $item->timeline_quarter,
                'cluster_name'        => $item->cluster_name,
                'cluster_code'        => $item->cluster_code,
                'indicator_number'    => $item->indicator_number,
                'indicator_name'      => $item->indicator_name,
                'response_type'       => $item->indicator_response_type,
                'target_value'        => $item->total_target_value,
                'actual_value'        => $item->total_actual_value,
                'achievement_percent' => $item->achievement_percent,
                'status'              => $item->status_label,
                'comment'             => $item->comment,
            ];
        })->toArray();
    }

    /**
     * Prepare data for charts
     */
    private function prepareChartData(Collection $data, array $filters): array
    {
        $chartData = [];

        // Achievement by cluster chart
        $chartData['achievementByCluster'] = $data->groupBy('cluster_name')
            ->map(fn($group) => [
                'name'  => $group->first()->cluster_name,
                'value' => round($group->avg('achievement_percent'), 2),
                'count' => $group->count(),
            ])
            ->sortByDesc('value')
            ->values()
            ->toArray();

        // Status distribution chart
        $chartData['statusDistribution'] = $data->groupBy('status_label')
            ->map(fn($group) => [
                'name'       => $group->first()->status_label,
                'value'      => $group->count(),
                'percentage' => round(($group->count() / $data->count()) * 100, 2),
            ])
            ->sortByDesc('value')
            ->values()
            ->toArray();

        // Achievement distribution chart (bucketed)
        $achievementBuckets = [
            '0-10%'   => 0,
            '11-25%'  => 0,
            '26-50%'  => 0,
            '51-75%'  => 0,
            '76-90%'  => 0,
            '91-100%' => 0,
            '>100%'   => 0,
        ];

        foreach ($data as $item) {
            $achievement = $item->achievement_percent;

            if ($achievement <= 10) {
                $achievementBuckets['0-10%']++;
            } elseif ($achievement <= 25) {
                $achievementBuckets['11-25%']++;
            } elseif ($achievement <= 50) {
                $achievementBuckets['26-50%']++;
            } elseif ($achievement <= 75) {
                $achievementBuckets['51-75%']++;
            } elseif ($achievement <= 90) {
                $achievementBuckets['76-90%']++;
            } elseif ($achievement <= 100) {
                $achievementBuckets['91-100%']++;
            } else {
                $achievementBuckets['>100%']++;
            }
        }

        $chartData['achievementDistribution'] = collect($achievementBuckets)
            ->map(fn($count, $range) => [
                'name'       => $range,
                'value'      => $count,
                'percentage' => $data->count() > 0 ? round(($count / $data->count()) * 100, 2) : 0,
            ])
            ->values()
            ->toArray();

        // Quarterly trend chart
        if (! empty($filters['year'])) {
            $year = $filters['year'];

            // Use the view for quarterly data with proper grouping to avoid duplicates
            $quarterlyQuery = DB::table('vw_performance_over_time_by_quarter')
                ->where('timeline_year', $year);

            // Apply other relevant filters
            if (! empty($filters['cluster_id'])) {
                $quarterlyQuery->where('cluster_pk', $filters['cluster_id']);
            }

            if (! empty($filters['indicator_id'])) {
                $quarterlyQuery->where('indicator_pk', $filters['indicator_id']);
            }

            // For strategic objective filter
            if (! empty($filters['strategic_objective_id'])) {
                $indicatorIds = DB::table('performance_indicators')
                    ->where('SO_ID', $filters['strategic_objective_id'])
                    ->pluck('id');
                $quarterlyQuery->whereIn('indicator_pk', $indicatorIds);
            }

            $quarterlyData = $quarterlyQuery
                ->select('timeline_quarter', DB::raw('AVG(achievement_percent) as avg_achievement'))
                ->groupBy('timeline_quarter')
                ->orderBy('timeline_quarter')
                ->get();

            $chartData['quarterlyTrend'] = [];

            foreach (range(1, 4) as $quarter) {
                $quarterData = $quarterlyData->firstWhere('timeline_quarter', $quarter);

                $chartData['quarterlyTrend'][] = [
                    'name'  => "Q{$quarter}",
                    'value' => $quarterData ? round($quarterData->avg_achievement, 2) : null,
                ];
            }

            // Add previous year data for comparison if available
            $previousYear      = $year - 1;
            $previousYearQuery = DB::table('vw_performance_over_time_by_quarter')
                ->where('timeline_year', $previousYear);

            // Apply the same filters as current year
            if (! empty($filters['cluster_id'])) {
                $previousYearQuery->where('cluster_pk', $filters['cluster_id']);
            }

            if (! empty($filters['indicator_id'])) {
                $previousYearQuery->where('indicator_pk', $filters['indicator_id']);
            }

            if (! empty($filters['strategic_objective_id'])) {
                $previousYearQuery->whereIn('indicator_pk', $indicatorIds);
            }

            $previousYearData = $previousYearQuery
                ->select('timeline_quarter', DB::raw('AVG(achievement_percent) as avg_achievement'))
                ->groupBy('timeline_quarter')
                ->orderBy('timeline_quarter')
                ->get();

            if ($previousYearData->isNotEmpty()) {
                $chartData['previousYearQuarterlyTrend'] = [];

                foreach (range(1, 4) as $quarter) {
                    $quarterData = $previousYearData->firstWhere('timeline_quarter', $quarter);

                    $chartData['previousYearQuarterlyTrend'][] = [
                        'name'  => "Q{$quarter}",
                        'value' => $quarterData ? round($quarterData->avg_achievement, 2) : null,
                    ];
                }
            }
        }

        // Top and bottom indicators
        $chartData['topIndicators'] = $data->groupBy('indicator_name')
            ->map(fn($group) => [
                'name'  => $group->first()->indicator_name,
                'value' => round($group->avg('achievement_percent'), 2),
                'count' => $group->count(),
            ])
            ->sortByDesc('value')
            ->take(5)
            ->values()
            ->toArray();

        $chartData['bottomIndicators'] = $data->groupBy('indicator_name')
            ->map(fn($group) => [
                'name'  => $group->first()->indicator_name,
                'value' => round($group->avg('achievement_percent'), 2),
                'count' => $group->count(),
            ])
            ->sortBy('value')
            ->take(5)
            ->values()
            ->toArray();

        return $chartData;
    }

    /**
     * Analyze data quality issues
     */
    private function analyzeDataQuality(Collection $data): array
    {
        $issues = [];

        // Check for missing targets
        $missingTargets = $data->filter(fn($item) =>
            $item->total_target_value == 0 || (isset($item->target_value_raw) && ($item->target_value_raw == '0' || empty($item->target_value_raw)))
        );

        if ($missingTargets->isNotEmpty()) {
            $issues['missingTargets'] = [
                'count'              => $missingTargets->count(),
                'percentage'         => round(($missingTargets->count() / $data->count()) * 100, 2),
                'items'              => $missingTargets->take(10)->values()->toArray(), // Limit to 10 examples
                'affectedClusters'   => $missingTargets->pluck('cluster_name')->unique()->values()->toArray(),
                'affectedIndicators' => $missingTargets->pluck('indicator_name')->unique()->values()->toArray(),
            ];
        }

        // Check for incomplete data (missing actual values)
        $missingActuals = $data->filter(fn($item) =>
            $item->total_actual_value == 0 && $item->total_target_value > 0
        );

        if ($missingActuals->isNotEmpty()) {
            $issues['missingActuals'] = [
                'count'              => $missingActuals->count(),
                'percentage'         => round(($missingActuals->count() / $data->count()) * 100, 2),
                'items'              => $missingActuals->take(10)->values()->toArray(),
                'affectedClusters'   => $missingActuals->pluck('cluster_name')->unique()->values()->toArray(),
                'affectedIndicators' => $missingActuals->pluck('indicator_name')->unique()->values()->toArray(),
            ];
        }

        // Check for extreme outliers in achievement percentage
        $validAchievements = $data->filter(fn($item) =>
            $item->total_target_value > 0 && is_numeric($item->achievement_percent)
        )->pluck('achievement_percent')->toArray();

        if (count($validAchievements) >= 5) {
            $median     = $this->calculateMedian($validAchievements);
            $q1         = $this->calculatePercentile($validAchievements, 25);
            $q3         = $this->calculatePercentile($validAchievements, 75);
            $iqr        = $q3 - $q1;
            $lowerBound = $q1 - (1.5 * $iqr);
            $upperBound = $q3 + (1.5 * $iqr);

            $outliers = $data->filter(fn($item) =>
                is_numeric($item->achievement_percent) &&
                ($item->achievement_percent < $lowerBound || $item->achievement_percent > $upperBound) &&
                $item->total_target_value > 0
            );

            if ($outliers->isNotEmpty()) {
                $issues['outliers'] = [
                    'count'      => $outliers->count(),
                    'percentage' => round(($outliers->count() / $data->count()) * 100, 2),
                    'items'      => $outliers->take(10)->values()->toArray(),
                    'lowerBound' => round($lowerBound, 2),
                    'upperBound' => round($upperBound, 2),
                    'median'     => round($median, 2),
                    'q1'         => round($q1, 2),
                    'q3'         => round($q3, 2),
                ];
            }
        }

        // Check for inconsistent status labels
        $inconsistentStatuses = $data->filter(function ($item) {
            if (! is_numeric($item->achievement_percent)) {
                return false;
            }

            $expectedStatus = $this->calculateExpectedStatus($item->achievement_percent);
            return $item->status_label !== $expectedStatus;
        });

        if ($inconsistentStatuses->isNotEmpty()) {
            $issues['inconsistentStatuses'] = [
                'count'      => $inconsistentStatuses->count(),
                'percentage' => round(($inconsistentStatuses->count() / $data->count()) * 100, 2),
                'items'      => $inconsistentStatuses->take(10)->map(function ($item) {
                    return [
                        'cluster_name'        => $item->cluster_name,
                        'indicator_name'      => $item->indicator_name,
                        'achievement_percent' => $item->achievement_percent,
                        'actual_status'       => $item->status_label,
                        'expected_status'     => $this->calculateExpectedStatus($item->achievement_percent),
                    ];
                })->values()->toArray(),
            ];
        }

        return $issues;
    }

    /**
     * Calculate median of an array
     */
    private function calculateMedian(array $values): float
    {
        if (empty($values)) {
            return 0;
        }

        sort($values);
        $count  = count($values);
        $middle = floor($count / 2);

        if ($count % 2 === 0) {
            return ($values[$middle - 1] + $values[$middle]) / 2;
        } else {
            return $values[$middle];
        }
    }

    /**
     * Calculate percentile of an array
     */
    private function calculatePercentile(array $values, int $percentile): float
    {
        if (empty($values)) {
            return 0;
        }

        sort($values);
        $count = count($values);
        $index = ceil($percentile / 100 * $count) - 1;
        return $values[max(0, min($count - 1, $index))];
    }

    /**
     * Calculate expected status based on achievement percentage
     */
    private function calculateExpectedStatus(float $achievement): string
    {
        return match (true) {
            $achievement < 10 => 'Needs Attention',
            $achievement < 50 => 'In Progress',
            $achievement < 90 => 'On Track',
            default => 'Met',
        };
    }

    /**
     * Generate insights based on the data
     */
    private function generateInsights(Collection $data, array $filters): array
    {
        $insights = [];

        // Skip insights generation if data is empty
        if ($data->isEmpty()) {
            return [['message' => 'No data available for insight generation.', 'type' => 'warning']];
        }

        // Calculate overall achievement
        $validAchievements = $data->filter(fn($item) =>
            is_numeric($item->achievement_percent) && $item->total_target_value > 0
        );

        if ($validAchievements->isNotEmpty()) {
            $overallAchievement = $validAchievements->avg('achievement_percent');
            $insights[]         = [
                'message'  => "Overall achievement is " . number_format($overallAchievement, 2) . "%.",
                'type'     => $this->getInsightType($overallAchievement),
                'category' => 'overall',
                'priority' => 1,
            ];

            // Add context about the data
            $insights[] = [
                'message'  => "Analysis based on {$data->count()} data points across " .
                $data->pluck('cluster_name')->unique()->count() . " clusters and " .
                $data->pluck('indicator_name')->unique()->count() . " indicators.",
                'type'     => 'info',
                'category' => 'metadata',
                'priority' => 10,
            ];
        }

        // Identify top performing clusters
        $clusterPerformance = $data->groupBy('cluster_name')
            ->map(fn($group) => [
                'name'            => $group->first()->cluster_name,
                'avg_achievement' => $group->filter(fn($item) =>
                    is_numeric($item->achievement_percent) && $item->total_target_value > 0
                )->avg('achievement_percent'),
                'count'           => $group->count(),
            ])
            ->filter(fn($cluster) => $cluster['count'] >= 3 && is_numeric($cluster['avg_achievement']))
            ->sortByDesc('avg_achievement')
            ->values();

        if ($clusterPerformance->isNotEmpty()) {
            $topCluster = $clusterPerformance->first();
            $insights[] = [
                'message'  => "Top performing cluster is {$topCluster['name']} with " .
                number_format($topCluster['avg_achievement'], 2) . "% achievement.",
                'type'     => 'success',
                'category' => 'cluster',
                'priority' => 2,
            ];

            // Identify underperforming clusters
            $underperformingClusters = $clusterPerformance->filter(fn($cluster) =>
                $cluster['avg_achievement'] < 50 && $cluster['count'] >= 3
            );

            if ($underperformingClusters->isNotEmpty()) {
                $worstCluster = $underperformingClusters->sortBy('avg_achievement')->first();
                $insights[]   = [
                    'message'  => "Cluster {$worstCluster['name']} is underperforming with " .
                    number_format($worstCluster['avg_achievement'], 2) . "% achievement.",
                    'type'     => 'danger',
                    'category' => 'cluster',
                    'priority' => 2,
                ];
            }
        }

        // Analyze status distribution
        $statusCounts = $data->groupBy('status_label')
            ->map(fn($group) => $group->count())
            ->sortDesc();

        $totalCount = $data->count();
        foreach ($statusCounts as $status => $count) {
            $percentage = ($count / $totalCount) * 100;
            if ($percentage > 25) {
                $insights[] = [
                    'message'  => number_format($percentage, 2) . "% of indicators are in '{$status}' status.",
                    'type'     => $this->getStatusInsightType($status),
                    'category' => 'status',
                    'priority' => 3,
                ];
            }
        }

        // Identify over-achievements
        $overAchieved = $data->filter(fn($item) =>
            is_numeric($item->achievement_percent) && $item->achievement_percent > 100
        );

        if ($overAchieved->count() > 0) {
            $overAchievedPercentage = ($overAchieved->count() / $totalCount) * 100;
            $insights[]             = [
                'message'  => number_format($overAchievedPercentage, 2) . "% of indicators have exceeded their targets.",
                'type'     => 'success',
                'category' => 'achievement',
                'priority' => 4,
            ];

            // Identify extreme over-achievements (>200%)
            $extremeOverAchieved = $data->filter(fn($item) =>
                is_numeric($item->achievement_percent) && $item->achievement_percent > 200 && $item->total_target_value > 0
            );

            if ($extremeOverAchieved->count() > 0 && $extremeOverAchieved->count() / $totalCount > 0.05) {
                $insights[] = [
                    'message'  => number_format(($extremeOverAchieved->count() / $totalCount) * 100, 2) .
                    "% of indicators have exceeded 200% of their targets. Consider reviewing target setting methodology.",
                    'type'     => 'warning',
                    'category' => 'achievement',
                    'priority' => 4,
                ];
            }
        }

        // Identify indicators needing attention
        $needsAttention = $data->filter(fn($item) => $item->status_label == 'Needs Attention');
        if ($needsAttention->count() > 0) {
            $needsAttentionPercentage = ($needsAttention->count() / $totalCount) * 100;
            $insights[]               = [
                'message'  => number_format($needsAttentionPercentage, 2) . "% of indicators need attention (below 10% achievement).",
                'type'     => 'danger',
                'category' => 'status',
                'priority' => 3,
            ];

            // List top 3 indicators needing most attention
            $criticalIndicators = $needsAttention
                ->sortBy('achievement_percent')
                ->take(3)
                ->map(fn($item) => "{$item->indicator_name} ({$item->cluster_name})");

            if ($criticalIndicators->isNotEmpty()) {
                $insights[] = [
                    'message'  => "Critical indicators needing immediate attention: " . $criticalIndicators->implode(', '),
                    'type'     => 'danger',
                    'category' => 'indicator',
                    'priority' => 3,
                ];
            }
        }

        // Analyze trends if we have year data
        if (isset($filters['year'])) {
            $currentYear  = (int) $filters['year'];
            $previousYear = $currentYear - 1;

            // Use the view directly for previous year data with proper filtering
            $previousYearQuery = DB::table('vw_performance_over_time_by_quarter')
                ->where('timeline_year', $previousYear);

            // Apply other filters except year
            if (! empty($filters['quarter'])) {
                $previousYearQuery->where('timeline_quarter', $filters['quarter']);
            }

            if (! empty($filters['cluster_id'])) {
                $previousYearQuery->where('cluster_pk', $filters['cluster_id']);
            }

            if (! empty($filters['indicator_id'])) {
                $previousYearQuery->where('indicator_pk', $filters['indicator_id']);
            }

            // For strategic objective filter
            if (! empty($filters['strategic_objective_id'])) {
                $indicatorIds = DB::table('performance_indicators')
                    ->where('SO_ID', $filters['strategic_objective_id'])
                    ->pluck('id');
                $previousYearQuery->whereIn('indicator_pk', $indicatorIds);
            }

            $previousYearData = $previousYearQuery->get();

            if ($previousYearData->isNotEmpty()) {
                $currentAvg  = $validAchievements->avg('achievement_percent');
                $previousAvg = $previousYearData->filter(fn($item) =>
                    is_numeric($item->achievement_percent) && $item->total_target_value > 0
                )->avg('achievement_percent');

                if (is_numeric($currentAvg) && is_numeric($previousAvg) && $previousAvg > 0) {
                    $percentChange   = (($currentAvg - $previousAvg) / $previousAvg) * 100;
                    $changeDirection = $percentChange >= 0 ? 'increased' : 'decreased';
                    $insights[]      = [
                        'message'  => "Overall performance has {$changeDirection} by " .
                        abs(number_format($percentChange, 2)) . "% compared to {$previousYear}.",
                        'type'     => $percentChange >= 0 ? 'success' : 'warning',
                        'category' => 'trend',
                        'priority' => 2,
                    ];
                }
            }
        }

        // Analyze data quality issues
        $dataQualityIssues = $this->analyzeDataQuality($data);
        if (! empty($dataQualityIssues)) {
            foreach ($dataQualityIssues as $issueType => $issue) {
                switch ($issueType) {
                    case 'missingTargets':
                        if ($issue['percentage'] > 5) {
                            $insights[] = [
                                'message'  => number_format($issue['percentage'], 2) . "% of indicators are missing target values, affecting data quality.",
                                'type'     => 'warning',
                                'category' => 'data_quality',
                                'priority' => 5,
                            ];
                        }
                        break;
                    case 'missingActuals':
                        if ($issue['percentage'] > 5) {
                            $insights[] = [
                                'message'  => number_format($issue['percentage'], 2) . "% of indicators are missing actual values despite having targets.",
                                'type'     => 'warning',
                                'category' => 'data_quality',
                                'priority' => 5,
                            ];
                        }
                        break;
                    case 'outliers':
                        if ($issue['percentage'] > 5) {
                            $insights[] = [
                                'message'  => number_format($issue['percentage'], 2) . "% of indicators show unusual achievement values that may need verification.",
                                'type'     => 'warning',
                                'category' => 'data_quality',
                                'priority' => 6,
                            ];
                        }
                        break;
                    case 'inconsistentStatuses':
                        if ($issue['percentage'] > 5) {
                            $insights[] = [
                                'message'  => number_format($issue['percentage'], 2) . "% of indicators have status labels inconsistent with their achievement percentages.",
                                'type'     => 'warning',
                                'category' => 'data_quality',
                                'priority' => 6,
                            ];
                        }
                        break;
                }
            }
        }

        // Sort insights by priority
        usort($insights, fn($a, $b) => $a['priority'] <=> $b['priority']);

        return $insights;
    }

    /**
     * Determine insight type based on achievement percentage
     */
    private function getInsightType(float $achievement): string
    {
        return match (true) {
            $achievement < 10 => 'danger',
            $achievement < 50 => 'warning',
            $achievement < 90 => 'info',
            default => 'success',
        };
    }

    /**
     * Determine insight type based on status label
     */
    private function getStatusInsightType(string $status): string
    {
        return match ($status) {
            'Needs Attention' => 'danger',
            'In Progress' => 'warning',
            'On Track' => 'info',
            'Met' => 'success',
            default => 'secondary',
        };
    }

    /**
     * Calculate summary metrics
     */
    private function calculateSummaryMetrics(Collection $data): array
    {
        if ($data->isEmpty()) {
            return [];
        }

        // Filter for valid achievement percentages
        $validAchievements = $data->filter(fn($item) =>
            is_numeric($item->achievement_percent) && $item->total_target_value > 0
        )->pluck('achievement_percent')->toArray();

        return [
            'totalIndicators'        => $data->count(),
            'validDataPoints'        => count($validAchievements),
            'averageAchievement'     => ! empty($validAchievements) ? round(array_sum($validAchievements) / count($validAchievements), 2) : null,
            'medianAchievement'      => ! empty($validAchievements) ? round($this->calculateMedian($validAchievements), 2) : null,
            'minAchievement'         => ! empty($validAchievements) ? round(min($validAchievements), 2) : null,
            'maxAchievement'         => ! empty($validAchievements) ? round(max($validAchievements), 2) : null,
            'standardDeviation'      => ! empty($validAchievements) ? round($this->calculateStandardDeviation($validAchievements), 2) : null,
            'statusCounts'           => $data->groupBy('status_label')
                ->map(fn($group) => [
                    'count'      => $group->count(),
                    'percentage' => round(($group->count() / $data->count()) * 100, 2),
                ])
                ->toArray(),
            'responseTypeCounts'     => $data->groupBy('indicator_response_type')
                ->map(fn($group) => [
                    'count'      => $group->count(),
                    'percentage' => round(($group->count() / $data->count()) * 100, 2),
                ])
                ->toArray(),
            'overAchievedCount'      => $data->filter(fn($item) =>
                is_numeric($item->achievement_percent) && $item->achievement_percent > 100
            )->count(),
            'overAchievedPercentage' => round(($data->filter(fn($item) =>
                is_numeric($item->achievement_percent) && $item->achievement_percent > 100
            )->count() / $data->count()) * 100, 2),
            'clustersCount'          => $data->pluck('cluster_name')->unique()->count(),
            'indicatorsCount'        => $data->pluck('indicator_name')->unique()->count(),
            'dataCompleteness'       => round(($data->filter(fn($item) =>
                $item->total_target_value > 0 && $item->total_actual_value > 0
            )->count() / $data->count()) * 100, 2),
        ];
    }

    /**
     * Calculate standard deviation
     */
    private function calculateStandardDeviation(array $values): float
    {
        $count = count($values);
        if ($count <= 1) {
            return 0;
        }

        $mean     = array_sum($values) / $count;
        $variance = 0;

        foreach ($values as $value) {
            $variance += pow($value - $mean, 2);
        }

        return sqrt($variance / ($count - 1));
    }

    /**
     * Analyze trends in the data
     */
    private function analyzeTrends(Collection $data, array $filters): array
    {
        // Skip trend analysis if insufficient data
        if ($data->isEmpty() || ! isset($filters['year'])) {
            return [];
        }

        // Get previous year's data for comparison if current year is selected
        $currentYear  = (int) $filters['year'];
        $previousYear = $currentYear - 1;

        // Use the view directly for previous year data with proper filtering
        $previousYearQuery = DB::table('vw_performance_over_time_by_quarter')
            ->where('timeline_year', $previousYear);

        // Apply other filters except year
        if (! empty($filters['quarter'])) {
            $previousYearQuery->where('timeline_quarter', $filters['quarter']);
        }

        if (! empty($filters['cluster_id'])) {
            $previousYearQuery->where('cluster_pk', $filters['cluster_id']);
        }

        if (! empty($filters['indicator_id'])) {
            $previousYearQuery->where('indicator_pk', $filters['indicator_id']);
        }

        // For strategic objective filter, use a subquery approach
        if (! empty($filters['strategic_objective_id'])) {
            // Get indicators associated with this strategic objective
            $indicatorIds = DB::table('performance_indicators')
                ->where('SO_ID', $filters['strategic_objective_id'])
                ->pluck('id');

            // Filter the view by these indicator IDs
            $previousYearQuery->whereIn('indicator_pk', $indicatorIds);
        }

        $previousYearData = $previousYearQuery->get();

        // Calculate year-over-year changes
        if ($previousYearData->isNotEmpty()) {
            $currentValidData = $data->filter(fn($item) =>
                is_numeric($item->achievement_percent) && $item->total_target_value > 0
            );

            $previousValidData = $previousYearData->filter(fn($item) =>
                is_numeric($item->achievement_percent) && $item->total_target_value > 0
            );

            if ($currentValidData->isNotEmpty() && $previousValidData->isNotEmpty()) {
                $currentAvg    = $currentValidData->avg('achievement_percent');
                $previousAvg   = $previousValidData->avg('achievement_percent');
                $percentChange = $previousAvg > 0 ? (($currentAvg - $previousAvg) / $previousAvg) * 100 : 0;

                return [
                    'yearOverYear'    => [
                        'currentYear'    => $currentYear,
                        'previousYear'   => $previousYear,
                        'currentAvg'     => round($currentAvg, 2),
                        'previousAvg'    => round($previousAvg, 2),
                        'absoluteChange' => round($currentAvg - $previousAvg, 2),
                        'percentChange'  => round($percentChange, 2),
                        'improved'       => $currentAvg > $previousAvg,
                        'currentCount'   => $currentValidData->count(),
                        'previousCount'  => $previousValidData->count(),
                    ],
                    'quarterlyTrend'  => $this->calculateQuarterlyTrend($currentValidData, $previousValidData),
                    'clusterTrends'   => $this->calculateClusterTrends($currentValidData, $previousValidData),
                    'indicatorTrends' => $this->calculateIndicatorTrends($currentValidData, $previousValidData),
                ];
            }
        }

        // If no previous year data, analyze quarterly trends within current year
        return [
            'quarterlyTrend'         => $this->calculateCurrentYearQuarterlyTrend($currentYear, $filters),
            'clusterQuarterlyTrends' => $this->calculateClusterQuarterlyTrends($currentYear, $filters),
        ];
    }

    /**
     * Calculate quarterly trend comparing current and previous year
     */
    private function calculateQuarterlyTrend(Collection $currentData, Collection $previousData): array
    {
        $currentByQuarter = $currentData->groupBy('timeline_quarter')
            ->map(fn($group) => $group->avg('achievement_percent'))
            ->toArray();

        $previousByQuarter = $previousData->groupBy('timeline_quarter')
            ->map(fn($group) => $group->avg('achievement_percent'))
            ->toArray();

        $trend = [];
        foreach (range(1, 4) as $quarter) {
            $current  = $currentByQuarter[$quarter] ?? null;
            $previous = $previousByQuarter[$quarter] ?? null;

            if ($current !== null && $previous !== null) {
                $change        = $current - $previous;
                $percentChange = $previous > 0 ? ($change / $previous) * 100 : 0;

                $trend[$quarter] = [
                    'current'       => round($current, 2),
                    'previous'      => round($previous, 2),
                    'change'        => round($change, 2),
                    'percentChange' => round($percentChange, 2),
                    'improved'      => $current > $previous,
                ];
            } elseif ($current !== null) {
                $trend[$quarter] = [
                    'current'       => round($current, 2),
                    'previous'      => null,
                    'change'        => null,
                    'percentChange' => null,
                    'improved'      => null,
                ];
            }
        }

        return $trend;
    }

    /**
     * Calculate cluster trends comparing current and previous year
     */
    private function calculateClusterTrends(Collection $currentData, Collection $previousData): array
    {
        $currentByClusters = $currentData->groupBy('cluster_name')
            ->map(fn($group) => [
                'avg'   => $group->avg('achievement_percent'),
                'count' => $group->count(),
            ])
            ->toArray();

        $previousByClusters = $previousData->groupBy('cluster_name')
            ->map(fn($group) => [
                'avg'   => $group->avg('achievement_percent'),
                'count' => $group->count(),
            ])
            ->toArray();

        $trends = [];
        foreach ($currentByClusters as $cluster => $current) {
            $previous = $previousByClusters[$cluster] ?? null;

            if ($previous) {
                $change        = $current['avg'] - $previous['avg'];
                $percentChange = $previous['avg'] > 0 ? ($change / $previous['avg']) * 100 : 0;

                $trends[$cluster] = [
                    'current'       => round($current['avg'], 2),
                    'previous'      => round($previous['avg'], 2),
                    'change'        => round($change, 2),
                    'percentChange' => round($percentChange, 2),
                    'improved'      => $current['avg'] > $previous['avg'],
                    'currentCount'  => $current['count'],
                    'previousCount' => $previous['count'],
                ];
            }
        }

        // Sort by percent change (most improved first)
        uasort($trends, fn($a, $b) => $b['percentChange'] <=> $a['percentChange']);

        return $trends;
    }

    /**
     * Calculate indicator trends comparing current and previous year
     */
    private function calculateIndicatorTrends(Collection $currentData, Collection $previousData): array
    {
        $currentByIndicators = $currentData->groupBy('indicator_name')
            ->map(fn($group) => [
                'avg'              => $group->avg('achievement_percent'),
                'count'            => $group->count(),
                'indicator_number' => $group->first()->indicator_number,
            ])
            ->toArray();

        $previousByIndicators = $previousData->groupBy('indicator_name')
            ->map(fn($group) => [
                'avg'   => $group->avg('achievement_percent'),
                'count' => $group->count(),
            ])
            ->toArray();

        $trends = [];
        foreach ($currentByIndicators as $indicator => $current) {
            $previous = $previousByIndicators[$indicator] ?? null;

            if ($previous) {
                $change        = $current['avg'] - $previous['avg'];
                $percentChange = $previous['avg'] > 0 ? ($change / $previous['avg']) * 100 : 0;

                $trends[$indicator] = [
                    'indicator_number' => $current['indicator_number'],
                    'current'          => round($current['avg'], 2),
                    'previous'         => round($previous['avg'], 2),
                    'change'           => round($change, 2),
                    'percentChange'    => round($percentChange, 2),
                    'improved'         => $current['avg'] > $previous['avg'],
                    'currentCount'     => $current['count'],
                    'previousCount'    => $previous['count'],
                ];
            }
        }

        // Sort by percent change (most improved first)
        uasort($trends, fn($a, $b) => $b['percentChange'] <=> $a['percentChange']);

        return $trends;
    }

    /**
     * Calculate quarterly trend within the current year
     */
    private function calculateCurrentYearQuarterlyTrend(int $year, array $filters): array
    {
        // Use the view directly for quarterly data with proper filtering
        $query = DB::table('vw_performance_over_time_by_quarter')
            ->where('timeline_year', $year);

        // Apply other filters except year and quarter
        if (! empty($filters['cluster_id'])) {
            $query->where('cluster_pk', $filters['cluster_id']);
        }

        if (! empty($filters['indicator_id'])) {
            $query->where('indicator_pk', $filters['indicator_id']);
        }

        // For strategic objective filter, use a subquery approach
        if (! empty($filters['strategic_objective_id'])) {
            // Get indicators associated with this strategic objective
            $indicatorIds = DB::table('performance_indicators')
                ->where('SO_ID', $filters['strategic_objective_id'])
                ->pluck('id');

            // Filter the view by these indicator IDs
            $query->whereIn('indicator_pk', $indicatorIds);
        }

        $quarterlyData = $query->select('timeline_quarter', DB::raw('AVG(achievement_percent) as avg_achievement'))
            ->groupBy('timeline_quarter')
            ->orderBy('timeline_quarter')
            ->get()
            ->keyBy('timeline_quarter')
            ->map(fn($item) => $item->avg_achievement)
            ->toArray();

        $trend                = [];
        $previousQuarterValue = null;
        $previousQuarter      = null;

        foreach (range(1, 4) as $quarter) {
            if (isset($quarterlyData[$quarter])) {
                $currentValue = $quarterlyData[$quarter];

                if ($previousQuarterValue !== null) {
                    $change        = $currentValue - $previousQuarterValue;
                    $percentChange = $previousQuarterValue > 0 ? ($change / $previousQuarterValue) * 100 : 0;

                    $trend[$quarter] = [
                        'quarter'         => "Q{$quarter}",
                        'previousQuarter' => "Q{$previousQuarter}",
                        'value'           => round($currentValue, 2),
                        'previousValue'   => round($previousQuarterValue, 2),
                        'change'          => round($change, 2),
                        'percentChange'   => round($percentChange, 2),
                        'improved'        => $currentValue > $previousQuarterValue,
                    ];
                } else {
                    $trend[$quarter] = [
                        'quarter'         => "Q{$quarter}",
                        'previousQuarter' => null,
                        'value'           => round($currentValue, 2),
                        'previousValue'   => null,
                        'change'          => null,
                        'percentChange'   => null,
                        'improved'        => null,
                    ];
                }

                $previousQuarterValue = $currentValue;
                $previousQuarter      = $quarter;
            }
        }

        return $trend;
    }

    /**
     * Calculate cluster quarterly trends within the current year
     */
    private function calculateClusterQuarterlyTrends(int $year, array $filters): array
    {
        // Use the view directly for cluster quarterly data with proper filtering
        $query = DB::table('vw_performance_over_time_by_quarter')
            ->where('timeline_year', $year);

        // Apply other filters except year and quarter
        if (! empty($filters['cluster_id'])) {
            $query->where('cluster_pk', $filters['cluster_id']);
        }

        if (! empty($filters['indicator_id'])) {
            $query->where('indicator_pk', $filters['indicator_id']);
        }

        // For strategic objective filter, use a subquery approach
        if (! empty($filters['strategic_objective_id'])) {
            // Get indicators associated with this strategic objective
            $indicatorIds = DB::table('performance_indicators')
                ->where('SO_ID', $filters['strategic_objective_id'])
                ->pluck('id');

            // Filter the view by these indicator IDs
            $query->whereIn('indicator_pk', $indicatorIds);
        }

        $clusterQuarterlyData = $query->select('cluster_name', 'timeline_quarter', DB::raw('AVG(achievement_percent) as avg_achievement'))
            ->groupBy('cluster_name', 'timeline_quarter')
            ->orderBy('cluster_name')
            ->orderBy('timeline_quarter')
            ->get();

        $trends = [];

        foreach ($clusterQuarterlyData->groupBy('cluster_name') as $clusterName => $clusterData) {
            $clusterTrend         = [];
            $previousQuarterValue = null;
            $previousQuarter      = null;

            foreach ($clusterData->sortBy('timeline_quarter') as $item) {
                $quarter      = $item->timeline_quarter;
                $currentValue = $item->avg_achievement;

                if ($previousQuarterValue !== null) {
                    $change        = $currentValue - $previousQuarterValue;
                    $percentChange = $previousQuarterValue > 0 ? ($change / $previousQuarterValue) * 100 : 0;

                    $clusterTrend[$quarter] = [
                        'quarter'         => "Q{$quarter}",
                        'previousQuarter' => "Q{$previousQuarter}",
                        'value'           => round($currentValue, 2),
                        'previousValue'   => round($previousQuarterValue, 2),
                        'change'          => round($change, 2),
                        'percentChange'   => round($percentChange, 2),
                        'improved'        => $currentValue > $previousQuarterValue,
                    ];
                } else {
                    $clusterTrend[$quarter] = [
                        'quarter'         => "Q{$quarter}",
                        'previousQuarter' => null,
                        'value'           => round($currentValue, 2),
                        'previousValue'   => null,
                        'change'          => null,
                        'percentChange'   => null,
                        'improved'        => null,
                    ];
                }

                $previousQuarterValue = $currentValue;
                $previousQuarter      = $quarter;
            }

            if (! empty($clusterTrend)) {
                $trends[$clusterName] = $clusterTrend;
            }
        }

        return $trends;
    }

    /**
     * Prepare comparative analysis
     */
    private function prepareComparisons(Collection $data, array $filters): array
    {
        if ($data->isEmpty()) {
            return [];
        }

        $comparisons = [];

        // Compare clusters
        $clusterComparisons = $data->groupBy('cluster_name')
            ->map(fn($group) => [
                'name'                       => $group->first()->cluster_name,
                'avg_achievement'            => $group->filter(fn($item) =>
                    is_numeric($item->achievement_percent) && $item->total_target_value > 0
                )->avg('achievement_percent'),
                'count'                      => $group->count(),
                'met_count'                  => $group->filter(fn($item) => $item->status_label == 'Met')->count(),
                'met_percentage'             => $group->count() > 0
                ? ($group->filter(fn($item) => $item->status_label == 'Met')->count() / $group->count()) * 100
                : 0,
                'needs_attention_count'      => $group->filter(fn($item) => $item->status_label == 'Needs Attention')->count(),
                'needs_attention_percentage' => $group->count() > 0
                ? ($group->filter(fn($item) => $item->status_label == 'Needs Attention')->count() / $group->count()) * 100
                : 0,
            ])
            ->filter(fn($cluster) => $cluster['count'] >= 3 && is_numeric($cluster['avg_achievement']))
            ->sortByDesc('avg_achievement')
            ->values()
            ->toArray();

        if (! empty($clusterComparisons)) {
            $comparisons['clusters'] = $clusterComparisons;

            // Calculate overall average for comparison
            $overallAvg = array_reduce($clusterComparisons, function ($carry, $item) {
                return $carry + ($item['avg_achievement'] * $item['count']);
            }, 0) / array_reduce($clusterComparisons, fn($carry, $item) => $carry + $item['count'], 0);

            $comparisons['clustersSummary'] = [
                'overallAvg'         => round($overallAvg, 2),
                'topPerformer'       => $clusterComparisons[0]['name'],
                'topPerformerAvg'    => round($clusterComparisons[0]['avg_achievement'], 2),
                'bottomPerformer'    => end($clusterComparisons)['name'],
                'bottomPerformerAvg' => round(end($clusterComparisons)['avg_achievement'], 2),
                'gap'                => round($clusterComparisons[0]['avg_achievement'] - end($clusterComparisons)['avg_achievement'], 2),
                'gapPercentage'      => end($clusterComparisons)['avg_achievement'] > 0
                ? round((($clusterComparisons[0]['avg_achievement'] - end($clusterComparisons)['avg_achievement']) / end($clusterComparisons)['avg_achievement']) * 100, 2)
                : 0,
            ];
        }

        // Compare indicators
        $indicatorComparisons = $data->groupBy('indicator_name')
            ->map(fn($group) => [
                'name'                       => $group->first()->indicator_name,
                'number'                     => $group->first()->indicator_number,
                'avg_achievement'            => $group->filter(fn($item) =>
                    is_numeric($item->achievement_percent) && $item->total_target_value > 0
                )->avg('achievement_percent'),
                'count'                      => $group->count(),
                'met_count'                  => $group->filter(fn($item) => $item->status_label == 'Met')->count(),
                'met_percentage'             => $group->count() > 0
                ? ($group->filter(fn($item) => $item->status_label == 'Met')->count() / $group->count()) * 100
                : 0,
                'needs_attention_count'      => $group->filter(fn($item) => $item->status_label == 'Needs Attention')->count(),
                'needs_attention_percentage' => $group->count() > 0
                ? ($group->filter(fn($item) => $item->status_label == 'Needs Attention')->count() / $group->count()) * 100
                : 0,
            ])
            ->filter(fn($indicator) => $indicator['count'] >= 3 && is_numeric($indicator['avg_achievement']))
            ->sortByDesc('avg_achievement')
            ->values()
            ->toArray();

        if (! empty($indicatorComparisons)) {
            $comparisons['indicators'] = $indicatorComparisons;

            // Calculate overall average for comparison
            $overallAvg = array_reduce($indicatorComparisons, function ($carry, $item) {
                return $carry + ($item['avg_achievement'] * $item['count']);
            }, 0) / array_reduce($indicatorComparisons, fn($carry, $item) => $carry + $item['count'], 0);

            $comparisons['indicatorsSummary'] = [
                'overallAvg'         => round($overallAvg, 2),
                'topPerformer'       => $indicatorComparisons[0]['name'],
                'topPerformerAvg'    => round($indicatorComparisons[0]['avg_achievement'], 2),
                'bottomPerformer'    => end($indicatorComparisons)['name'],
                'bottomPerformerAvg' => round(end($indicatorComparisons)['avg_achievement'], 2),
                'gap'                => round($indicatorComparisons[0]['avg_achievement'] - end($indicatorComparisons)['avg_achievement'], 2),
                'gapPercentage'      => end($indicatorComparisons)['avg_achievement'] > 0
                ? round((($indicatorComparisons[0]['avg_achievement'] - end($indicatorComparisons)['avg_achievement']) / end($indicatorComparisons)['avg_achievement']) * 100, 2)
                : 0,
            ];
        }

        // Compare response types
        $responseTypeComparisons = $data->groupBy('indicator_response_type')
            ->map(fn($group) => [
                'name'            => $group->first()->indicator_response_type,
                'avg_achievement' => $group->filter(fn($item) =>
                    is_numeric($item->achievement_percent) && $item->total_target_value > 0
                )->avg('achievement_percent'),
                'count'           => $group->count(),
                'met_count'       => $group->filter(fn($item) => $item->status_label == 'Met')->count(),
                'met_percentage'  => $group->count() > 0
                ? ($group->filter(fn($item) => $item->status_label == 'Met')->count() / $group->count()) * 100
                : 0,
            ])
            ->filter(fn($type) => $type['count'] >= 3 && is_numeric($type['avg_achievement']))
            ->sortByDesc('avg_achievement')
            ->values()
            ->toArray();

        if (! empty($responseTypeComparisons)) {
            $comparisons['responseTypes'] = $responseTypeComparisons;
        }

        return $comparisons;
    }

    /**
     * Analyze status distribution
     */
    private function analyzeStatusDistribution(Collection $data): array
    {
        if ($data->isEmpty()) {
            return [];
        }

        // Overall status distribution
        $overallDistribution = $data->groupBy('status_label')
            ->map(fn($group) => [
                'count'      => $group->count(),
                'percentage' => round(($group->count() / $data->count()) * 100, 2),
            ])
            ->toArray();

        // Status distribution by cluster
        $clusterDistribution = $data->groupBy('cluster_name')
            ->map(function ($clusterGroup) {
                $total = $clusterGroup->count();

                return [
                    'total'    => $total,
                    'statuses' => $clusterGroup->groupBy('status_label')
                        ->map(fn($statusGroup) => [
                            'count'      => $statusGroup->count(),
                            'percentage' => round(($statusGroup->count() / $total) * 100, 2),
                        ])
                        ->toArray(),
                ];
            })
            ->toArray();

        // Status distribution by indicator (for indicators with multiple entries)
        $indicatorDistribution = $data->groupBy('indicator_name')
            ->filter(fn($group) => $group->count() > 1)
            ->map(function ($indicatorGroup) {
                $total = $indicatorGroup->count();

                return [
                    'total'    => $total,
                    'statuses' => $indicatorGroup->groupBy('status_label')
                        ->map(fn($statusGroup) => [
                            'count'      => $statusGroup->count(),
                            'percentage' => round(($statusGroup->count() / $total) * 100, 2),
                        ])
                        ->toArray(),
                ];
            })
            ->toArray();

        return [
            'overall'     => $overallDistribution,
            'byCluster'   => $clusterDistribution,
            'byIndicator' => $indicatorDistribution,
        ];
    }

    /**
     * Analyze achievement gaps
     */
    private function analyzeAchievementGaps(Collection $data): array
    {
        if ($data->isEmpty()) {
            return [];
        }

        // Cluster achievement gaps
        $clusterAchievements = $data->groupBy('cluster_name')
            ->map(fn($group) => [
                'name'            => $group->first()->cluster_name,
                'avg_achievement' => $group->filter(fn($item) =>
                    is_numeric($item->achievement_percent) && $item->total_target_value > 0
                )->avg('achievement_percent'),
                'count'           => $group->count(),
            ])
            ->filter(fn($cluster) => $cluster['count'] >= 3 && is_numeric($cluster['avg_achievement']))
            ->sortByDesc('avg_achievement')
            ->values()
            ->toArray();

        $clusterGaps = [];
        if (count($clusterAchievements) >= 2) {
            $topCluster    = $clusterAchievements[0];
            $bottomCluster = end($clusterAchievements);

            $clusterGaps = [
                'topCluster'               => $topCluster['name'],
                'topClusterAchievement'    => round($topCluster['avg_achievement'], 2),
                'bottomCluster'            => $bottomCluster['name'],
                'bottomClusterAchievement' => round($bottomCluster['avg_achievement'], 2),
                'absoluteGap'              => round($topCluster['avg_achievement'] - $bottomCluster['avg_achievement'], 2),
                'percentageGap'            => $bottomCluster['avg_achievement'] > 0
                ? round((($topCluster['avg_achievement'] - $bottomCluster['avg_achievement']) / $bottomCluster['avg_achievement']) * 100, 2)
                : 0,
            ];
        }

        // Indicator achievement gaps
        $indicatorAchievements = $data->groupBy('indicator_name')
            ->map(fn($group) => [
                'name'            => $group->first()->indicator_name,
                'number'          => $group->first()->indicator_number,
                'avg_achievement' => $group->filter(fn($item) =>
                    is_numeric($item->achievement_percent) && $item->total_target_value > 0
                )->avg('achievement_percent'),
                'count'           => $group->count(),
            ])
            ->filter(fn($indicator) => $indicator['count'] >= 3 && is_numeric($indicator['avg_achievement']))
            ->sortByDesc('avg_achievement')
            ->values()
            ->toArray();

        $indicatorGaps = [];
        if (count($indicatorAchievements) >= 2) {
            $topIndicator    = $indicatorAchievements[0];
            $bottomIndicator = end($indicatorAchievements);

            $indicatorGaps = [
                'topIndicator'               => $topIndicator['name'],
                'topIndicatorNumber'         => $topIndicator['number'],
                'topIndicatorAchievement'    => round($topIndicator['avg_achievement'], 2),
                'bottomIndicator'            => $bottomIndicator['name'],
                'bottomIndicatorNumber'      => $bottomIndicator['number'],
                'bottomIndicatorAchievement' => round($bottomIndicator['avg_achievement'], 2),
                'absoluteGap'                => round($topIndicator['avg_achievement'] - $bottomIndicator['avg_achievement'], 2),
                'percentageGap'              => $bottomIndicator['avg_achievement'] > 0
                ? round((($topIndicator['avg_achievement'] - $bottomIndicator['avg_achievement']) / $bottomIndicator['avg_achievement']) * 100, 2)
                : 0,
            ];
        }

        // Analyze within-cluster indicator gaps
        $withinClusterGaps = [];
        foreach ($data->groupBy('cluster_name') as $clusterName => $clusterData) {
            $indicatorAchievements = $clusterData->groupBy('indicator_name')
                ->map(fn($group) => [
                    'name'        => $group->first()->indicator_name,
                    'number'      => $group->first()->indicator_number,
                    'achievement' => $group->filter(fn($item) =>
                        is_numeric($item->achievement_percent) && $item->total_target_value > 0
                    )->avg('achievement_percent'),
                ])
                ->filter(fn($indicator) => is_numeric($indicator['achievement']))
                ->sortByDesc('achievement')
                ->values()
                ->toArray();

            if (count($indicatorAchievements) >= 2) {
                $topIndicator    = $indicatorAchievements[0];
                $bottomIndicator = end($indicatorAchievements);

                $withinClusterGaps[$clusterName] = [
                    'topIndicator'               => $topIndicator['name'],
                    'topIndicatorAchievement'    => round($topIndicator['achievement'], 2),
                    'bottomIndicator'            => $bottomIndicator['name'],
                    'bottomIndicatorAchievement' => round($bottomIndicator['achievement'], 2),
                    'absoluteGap'                => round($topIndicator['achievement'] - $bottomIndicator['achievement'], 2),
                    'percentageGap'              => $bottomIndicator['achievement'] > 0
                    ? round((($topIndicator['achievement'] - $bottomIndicator['achievement']) / $bottomIndicator['achievement']) * 100, 2)
                    : 0,
                ];
            }
        }

        return [
            'clusterGaps'       => $clusterGaps,
            'indicatorGaps'     => $indicatorGaps,
            'withinClusterGaps' => $withinClusterGaps,
        ];
    }

    /**
     * Detect anomalies in the data
     */
    private function detectAnomalies(Collection $data): array
    {
        if ($data->isEmpty()) {
            return [];
        }

        $anomalies = [];

        // Filter for valid achievement percentages
        $validData = $data->filter(fn($item) =>
            is_numeric($item->achievement_percent) && $item->total_target_value > 0
        );

        if ($validData->isEmpty()) {
            return [];
        }

        // Calculate statistics for anomaly detection
        $achievements = $validData->pluck('achievement_percent')->toArray();
        $mean         = array_sum($achievements) / count($achievements);
        $stdDev       = $this->calculateStandardDeviation($achievements);

        // Define thresholds for anomalies
        $highThreshold = $mean + (2 * $stdDev);
        $lowThreshold  = max(0, $mean - (2 * $stdDev));

        // Detect high outliers
        $highOutliers = $validData->filter(fn($item) => $item->achievement_percent > $highThreshold)
            ->sortByDesc('achievement_percent')
            ->values()
            ->toArray();

        if (! empty($highOutliers)) {
            $anomalies['highOutliers'] = [
                'count'     => count($highOutliers),
                'threshold' => round($highThreshold, 2),
                'items'     => array_map(function ($item) {
                    return [
                        'cluster'     => $item->cluster_name,
                        'indicator'   => $item->indicator_name,
                        'achievement' => round($item->achievement_percent, 2),
                        'target'      => $item->total_target_value,
                        'actual'      => $item->total_actual_value,
                    ];
                }, array_slice($highOutliers, 0, 10)), // Limit to top 10
            ];
        }

        // Detect low outliers
        $lowOutliers = $validData->filter(fn($item) => $item->achievement_percent < $lowThreshold)
            ->sortBy('achievement_percent')
            ->values()
            ->toArray();

        if (! empty($lowOutliers)) {
            $anomalies['lowOutliers'] = [
                'count'     => count($lowOutliers),
                'threshold' => round($lowThreshold, 2),
                'items'     => array_map(function ($item) {
                    return [
                        'cluster'     => $item->cluster_name,
                        'indicator'   => $item->indicator_name,
                        'achievement' => round($item->achievement_percent, 2),
                        'target'      => $item->total_target_value,
                        'actual'      => $item->total_actual_value,
                    ];
                }, array_slice($lowOutliers, 0, 10)), // Limit to top 10
            ];
        }

        // Detect zero achievements with non-zero targets
        $zeroAchievements = $data->filter(fn($item) =>
            $item->total_target_value > 0 && $item->total_actual_value == 0
        )->values()->toArray();

        if (! empty($zeroAchievements)) {
            $anomalies['zeroAchievements'] = [
                'count' => count($zeroAchievements),
                'items' => array_map(function ($item) {
                    return [
                        'cluster'   => $item->cluster_name,
                        'indicator' => $item->indicator_name,
                        'target'    => $item->total_target_value,
                    ];
                }, array_slice($zeroAchievements, 0, 10)), // Limit to top 10
            ];
        }

        // Detect over-achievements (>200%)
        $extremeOverAchievements = $data->filter(fn($item) =>
            is_numeric($item->achievement_percent) && $item->achievement_percent > 200 && $item->total_target_value > 0
        )->sortByDesc('achievement_percent')->values()->toArray();

        if (! empty($extremeOverAchievements)) {
            $anomalies['extremeOverAchievements'] = [
                'count' => count($extremeOverAchievements),
                'items' => array_map(function ($item) {
                    return [
                        'cluster'     => $item->cluster_name,
                        'indicator'   => $item->indicator_name,
                        'achievement' => round($item->achievement_percent, 2),
                        'target'      => $item->total_target_value,
                        'actual'      => $item->total_actual_value,
                    ];
                }, array_slice($extremeOverAchievements, 0, 10)), // Limit to top 10
            ];
        }

        return $anomalies;
    }

    /**
     * Rank clusters based on performance
     */
    private function rankClusters(Collection $data): array
    {
        if ($data->isEmpty()) {
            return [];
        }

        // Group by cluster and calculate metrics
        $clusterMetrics = $data->groupBy('cluster_name')
            ->map(function ($clusterData) {
                $validData = $clusterData->filter(fn($item) =>
                    is_numeric($item->achievement_percent) && $item->total_target_value > 0
                );

                $totalIndicators = $clusterData->count();
                $validIndicators = $validData->count();

                if ($validIndicators == 0) {
                    return [
                        'name'                     => $clusterData->first()->cluster_name,
                        'code'                     => $clusterData->first()->cluster_code,
                        'totalIndicators'          => $totalIndicators,
                        'validIndicators'          => 0,
                        'avgAchievement'           => 0,
                        'medianAchievement'        => 0,
                        'metCount'                 => 0,
                        'metPercentage'            => 0,
                        'needsAttentionCount'      => 0,
                        'needsAttentionPercentage' => 0,
                        'dataCompleteness'         => 0,
                        'overallScore'             => 0,
                    ];
                }

                $achievements = $validData->pluck('achievement_percent')->toArray();

                return [
                    'name'                     => $clusterData->first()->cluster_name,
                    'code'                     => $clusterData->first()->cluster_code,
                    'totalIndicators'          => $totalIndicators,
                    'validIndicators'          => $validIndicators,
                    'avgAchievement'           => array_sum($achievements) / $validIndicators,
                    'medianAchievement'        => $this->calculateMedian($achievements),
                    'metCount'                 => $clusterData->filter(fn($item) => $item->status_label == 'Met')->count(),
                    'metPercentage'            => ($clusterData->filter(fn($item) => $item->status_label == 'Met')->count() / $totalIndicators) * 100,
                    'needsAttentionCount'      => $clusterData->filter(fn($item) => $item->status_label == 'Needs Attention')->count(),
                    'needsAttentionPercentage' => ($clusterData->filter(fn($item) => $item->status_label == 'Needs Attention')->count() / $totalIndicators) * 100,
                    'dataCompleteness'         => ($validIndicators / $totalIndicators) * 100,
                    // Calculate an overall score (weighted average of metrics)
                    'overallScore'             => (
                        (array_sum($achievements) / $validIndicators) * 0.5 +                                                         // 50% weight to average achievement
                        (($clusterData->filter(fn($item) => $item->status_label == 'Met')->count() / $totalIndicators) * 100) * 0.3 + // 30% weight to met percentage
                        (($validIndicators / $totalIndicators) * 100) * 0.2                                                           // 20% weight to data completeness
                    ),
                ];
            })
            ->filter(fn($metrics) => $metrics['totalIndicators'] >= 3) // Only include clusters with at least 3 indicators
            ->map(function ($metrics) {
                // Round numeric values for display
                return array_map(function ($value) {
                    return is_numeric($value) ? round($value, 2) : $value;
                }, $metrics);
            })
            ->sortByDesc('overallScore')
            ->values()
            ->toArray();

        // Add rank to each cluster
        $rankedClusters = [];
        foreach ($clusterMetrics as $index => $metrics) {
            $metrics['rank']  = $index + 1;
            $rankedClusters[] = $metrics;
        }

        return $rankedClusters;
    }

    /**
     * Analyze indicator performance
     */
    private function analyzeIndicatorPerformance(Collection $data): array
    {
        if ($data->isEmpty()) {
            return [];
        }

        // Group by indicator and calculate metrics
        $indicatorMetrics = $data->groupBy('indicator_name')
            ->map(function ($indicatorData) {
                $validData = $indicatorData->filter(fn($item) =>
                    is_numeric($item->achievement_percent) && $item->total_target_value > 0
                );

                $totalClusters = $indicatorData->count();
                $validClusters = $validData->count();

                if ($validClusters == 0) {
                    return [
                        'name'                     => $indicatorData->first()->indicator_name,
                        'number'                   => $indicatorData->first()->indicator_number,
                        'responseType'             => $indicatorData->first()->indicator_response_type,
                        'totalClusters'            => $totalClusters,
                        'validClusters'            => 0,
                        'avgAchievement'           => 0,
                        'medianAchievement'        => 0,
                        'minAchievement'           => 0,
                        'maxAchievement'           => 0,
                        'metCount'                 => 0,
                        'metPercentage'            => 0,
                        'needsAttentionCount'      => 0,
                        'needsAttentionPercentage' => 0,
                        'dataCompleteness'         => 0,
                        'achievementVariance'      => 0,
                    ];
                }

                $achievements = $validData->pluck('achievement_percent')->toArray();

                return [
                    'name'                     => $indicatorData->first()->indicator_name,
                    'number'                   => $indicatorData->first()->indicator_number,
                    'responseType'             => $indicatorData->first()->indicator_response_type,
                    'totalClusters'            => $totalClusters,
                    'validClusters'            => $validClusters,
                    'avgAchievement'           => array_sum($achievements) / $validClusters,
                    'medianAchievement'        => $this->calculateMedian($achievements),
                    'minAchievement'           => min($achievements),
                    'maxAchievement'           => max($achievements),
                    'metCount'                 => $indicatorData->filter(fn($item) => $item->status_label == 'Met')->count(),
                    'metPercentage'            => ($indicatorData->filter(fn($item) => $item->status_label == 'Met')->count() / $totalClusters) * 100,
                    'needsAttentionCount'      => $indicatorData->filter(fn($item) => $item->status_label == 'Needs Attention')->count(),
                    'needsAttentionPercentage' => ($indicatorData->filter(fn($item) => $item->status_label == 'Needs Attention')->count() / $totalClusters) * 100,
                    'dataCompleteness'         => ($validClusters / $totalClusters) * 100,
                    'achievementVariance'      => $this->calculateStandardDeviation($achievements),
                    // Top and bottom clusters for this indicator
                    'topCluster'               => $validData->sortByDesc('achievement_percent')->first() ? $validData->sortByDesc('achievement_percent')->first()->cluster_name : null,
                    'topClusterAchievement'    => $validData->sortByDesc('achievement_percent')->first() ? $validData->sortByDesc('achievement_percent')->first()->achievement_percent : null,
                    'bottomCluster'            => $validData->sortBy('achievement_percent')->first() ? $validData->sortBy('achievement_percent')->first()->cluster_name : null,
                    'bottomClusterAchievement' => $validData->sortBy('achievement_percent')->first() ? $validData->sortBy('achievement_percent')->first()->achievement_percent : null,
                ];
            })
            ->filter(fn($metrics) => $metrics['totalClusters'] >= 3) // Only include indicators reported by at least 3 clusters
            ->map(function ($metrics) {
                // Round numeric values for display
                return array_map(function ($value) {
                    return is_numeric($value) ? round($value, 2) : $value;
                }, $metrics);
            });

        // Sort indicators by average achievement
        $topIndicators    = $indicatorMetrics->sortByDesc('avgAchievement')->take(10)->values()->toArray();
        $bottomIndicators = $indicatorMetrics->sortBy('avgAchievement')->take(10)->values()->toArray();

        // Sort indicators by variance (most consistent and most variable)
        $mostConsistent = $indicatorMetrics->sortBy('achievementVariance')->take(10)->values()->toArray();
        $mostVariable   = $indicatorMetrics->sortByDesc('achievementVariance')->take(10)->values()->toArray();

        return [
            'topIndicators'    => $topIndicators,
            'bottomIndicators' => $bottomIndicators,
            'mostConsistent'   => $mostConsistent,
            'mostVariable'     => $mostVariable,
            'allIndicators'    => $indicatorMetrics->sortByDesc('avgAchievement')->values()->toArray(),
        ];
    }
}