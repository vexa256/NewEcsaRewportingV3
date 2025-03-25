<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PerformanceAnalyticsController extends Controller
{
    // Status thresholds - centralized for consistency
    private $statusThresholds = [
        'needs_attention' => 25,
        'in_progress'     => 50,
        'on_track'        => 90,
        'met'             => 100,
    ];

    // Color codes for status - centralized for consistency
    private $statusColors = [
        'Needs Attention' => '#dc3545', // Red
        'In Progress'     => '#ffc107', // Yellow
        'On Track'        => '#17a2b8', // Blue
        'Met'             => '#28a745', // Green
        'Over Achieved'   => '#6f42c1', // Purple
    ];

    /**
     * Main entry point for the performance dashboard
     */
    public function index(Request $request)
    {
        try {
            // Verify data integrity before proceeding
            if (! $this->verifyDataIntegrity()) {
                Log::error('Data integrity check failed');
                return view('scrn', [
                    'Page'  => 'Semi-annual.Report',
                    'error' => 'Data integrity check failed. Please contact system administrator.',
                ]);
            }

            // Get available filter options
            $availableYears       = $this->getAvailableYears();
            $availableSemiAnnuals = $this->getAvailableSemiAnnuals();
            $clusters             = $this->getAvailableClusters();
            $strategicObjectives  = $this->getAvailableStrategicObjectives();

            // Get selected filters or use defaults
            $selectedYear       = $request->input('year', count($availableYears) > 0 ? $availableYears[0] : null);
            $selectedSemiAnnual = $request->input('semi_annual', 'All');
            $selectedCluster    = $request->input('cluster', 'All');
            $selectedSO         = $request->input('strategic_objective', 'All');

            // Validate filters
            $filters = $this->validateFilters($request);

            // Prepare comprehensive data for the dashboard
            $dashboardData = $this->prepareComprehensiveData(
                $filters['year'],
                $filters['semi_annual'],
                $filters['cluster'],
                $filters['strategic_objective']
            );

            // Return the view with all data
            return view('scrn', [
                'Page'                 => 'Semi-annual.Report',
                'availableYears'       => $availableYears,
                'availableSemiAnnuals' => $availableSemiAnnuals,
                'clusters'             => $clusters,
                'strategicObjectives'  => $strategicObjectives,
                'selectedYear'         => $selectedYear,
                'selectedSemiAnnual'   => $selectedSemiAnnual,
                'selectedCluster'      => $selectedCluster,
                'selectedSO'           => $selectedSO,
                'dashboardData'        => $dashboardData,
                'statusThresholds'     => $this->statusThresholds,
                'statusColors'         => $this->statusColors,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in performance dashboard: ' . $e->getMessage());
            return view('scrn', [
                'Page'  => 'Semi-annual.Report',
                'error' => 'An error occurred while loading the dashboard. Please try again later.' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Verify data integrity before processing
     */
    private function verifyDataIntegrity()
    {
        try {
            // Check if the view exists
            $viewExists = DB::select("SHOW TABLES LIKE 'vw_semi_annual_performance'");
            if (empty($viewExists)) {
                Log::error('Required view vw_semi_annual_performance does not exist');
                return false;
            }

            // Check if required columns exist
            $columns         = DB::select("SHOW COLUMNS FROM vw_semi_annual_performance");
            $requiredColumns = [
                'cluster_pk', 'cluster_name', 'cluster_code',
                'so_pk', 'so_name', 'so_number',
                'indicator_pk', 'indicator_name', 'indicator_number',
                'timeline_year', 'semi_annual_label',
                'raw_target_value', 'raw_actual_value',
                'achievement_percent', 'status_label', 'comment',
            ];

            $columnNames    = array_column($columns, 'Field');
            $missingColumns = array_diff($requiredColumns, $columnNames);

            if (! empty($missingColumns)) {
                Log::error('Missing required columns in vw_semi_annual_performance: ' . implode(', ', $missingColumns));
                return false;
            }

            // Check if the cluster summary view exists
            $summaryViewExists = DB::select("SHOW TABLES LIKE 'vw_semi_annual_cluster_summary'");
            if (empty($summaryViewExists)) {
                Log::error('Required view vw_semi_annual_cluster_summary does not exist');
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Error verifying data integrity: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Validate and sanitize filter parameters
     */
    private function validateFilters(Request $request)
    {
        $validated = [];

        // Validate year
        $year = $request->input('year');
        if ($year && $year !== 'All') {
            $validated['year'] = is_numeric($year) ? (int) $year : null;
        } else {
            $validated['year'] = 'All';
        }

        // Validate semi_annual
        $semiAnnual = $request->input('semi_annual');
        if ($semiAnnual && in_array($semiAnnual, ['First Semi Annual', 'Second Semi Annual'])) {
            $validated['semi_annual'] = $semiAnnual;
        } else {
            $validated['semi_annual'] = 'All';
        }

        // Validate cluster
        $cluster = $request->input('cluster');
        if ($cluster && $cluster !== 'All') {
            $validated['cluster'] = is_numeric($cluster) ? (int) $cluster : null;
        } else {
            $validated['cluster'] = 'All';
        }

        // Validate strategic_objective
        $so = $request->input('strategic_objective');
        if ($so && $so !== 'All') {
            $validated['strategic_objective'] = is_numeric($so) ? (int) $so : null;
        } else {
            $validated['strategic_objective'] = 'All';
        }

        return $validated;
    }

    /**
     * Get available years from the database
     */
    private function getAvailableYears()
    {
        return DB::table('vw_semi_annual_performance')
            ->select('timeline_year')
            ->distinct()
            ->orderBy('timeline_year', 'desc')
            ->pluck('timeline_year')
            ->toArray();
    }

    /**
     * Get available semi-annual periods
     */
    private function getAvailableSemiAnnuals()
    {
        return DB::table('vw_semi_annual_performance')
            ->select('semi_annual_label')
            ->distinct()
            ->orderBy('semi_annual_label')
            ->pluck('semi_annual_label')
            ->toArray();
    }

    /**
     * Get available clusters from the database
     */
    private function getAvailableClusters()
    {
        return DB::table('vw_semi_annual_performance')
            ->select('cluster_pk', 'cluster_name')
            ->distinct()
            ->orderBy('cluster_name')
            ->get()
            ->toArray();
    }

    /**
     * Get available strategic objectives from the database
     */
    private function getAvailableStrategicObjectives()
    {
        return DB::table('vw_semi_annual_performance')
            ->select('so_pk', 'so_name', 'so_number')
            ->distinct()
            ->orderBy('so_number')
            ->get()
            ->toArray();
    }

    /**
     * Prepare comprehensive data for the dashboard
     */
    private function prepareComprehensiveData($year, $semiAnnual, $cluster, $strategicObjective)
    {
        // Get basic performance data
        $summary              = $this->getPerformanceSummary($year, $semiAnnual, $cluster, $strategicObjective);
        $strategicObjectives  = $this->getStrategicObjectivePerformance($year, $semiAnnual, $cluster, $strategicObjective);
        $clusterPerformance   = $this->getClusterPerformance($year, $semiAnnual, $cluster, $strategicObjective);
        $indicatorPerformance = $this->getIndicatorPerformance($year, $semiAnnual, $cluster, $strategicObjective);

        // Get items needing attention
        $attentionItems = $this->getItemsNeedingAttention($year, $semiAnnual, $cluster, $strategicObjective);

        // Get performance trends
        $trends = $this->getPerformanceTrends($year, $semiAnnual, $cluster, $strategicObjective);

        // Get statistical analysis
        $statistics = $this->calculatePerformanceStatistics($year, $semiAnnual, $cluster, $strategicObjective);

        // Detect anomalies
        $anomalies = $this->detectAnomalies($year, $semiAnnual, $cluster, $strategicObjective);

        // Generate AI insights
        $insights = $this->generateAdvancedInsights(
            $summary,
            $strategicObjectives,
            $clusterPerformance,
            $indicatorPerformance,
            $trends,
            $statistics,
            $anomalies
        );

        // Generate recommendations
        $recommendations = $this->generateRecommendations(
            $summary,
            $strategicObjectives,
            $clusterPerformance,
            $indicatorPerformance,
            $trends,
            $statistics,
            $anomalies
        );

        // Get comparative analysis
        $comparativeAnalysis = $this->performComparativeAnalysis($year, $semiAnnual, $cluster, $strategicObjective);

        // Return comprehensive data
        return [
            'summary'              => $summary,
            'strategicObjectives'  => $strategicObjectives,
            'clusterPerformance'   => $clusterPerformance,
            'indicatorPerformance' => $indicatorPerformance,
            'attentionItems'       => $attentionItems,
            'trends'               => $trends,
            'statistics'           => $statistics,
            'anomalies'            => $anomalies,
            'insights'             => $insights,
            'recommendations'      => $recommendations,
            'comparativeAnalysis'  => $comparativeAnalysis,
        ];
    }

    /**
     * Get overall performance summary with verified calculations
     */
    private function getPerformanceSummary($year, $semiAnnual, $cluster, $strategicObjective)
    {
        $query = DB::table('vw_semi_annual_performance');

        // Apply filters
        $this->applyFilters($query, $year, $semiAnnual, $cluster, $strategicObjective);

        // Calculate overall metrics with proper handling of NULL values
        $data = $query->selectRaw('
            COUNT(DISTINCT cluster_pk) as total_clusters,
            COUNT(DISTINCT so_pk) as total_strategic_objectives,
            COUNT(DISTINCT indicator_pk) as total_indicators,
            AVG(CASE WHEN achievement_percent IS NOT NULL THEN achievement_percent ELSE NULL END) as overall_achievement_percent,
            SUM(CASE WHEN status_label = "Needs Attention" THEN 1 ELSE 0 END) as needs_attention_count,
            SUM(CASE WHEN status_label = "In Progress" THEN 1 ELSE 0 END) as in_progress_count,
            SUM(CASE WHEN status_label = "On Track" THEN 1 ELSE 0 END) as on_track_count,
            SUM(CASE WHEN status_label = "Met" THEN 1 ELSE 0 END) as met_count,
            SUM(CASE WHEN comment = "Over Achieved" THEN 1 ELSE 0 END) as over_achieved_count,
            MIN(timeline_year) as min_year,
            MAX(timeline_year) as max_year
        ')->first();

        // Calculate overall status using centralized thresholds
        if ($data && $data->overall_achievement_percent !== null) {
            $percent                           = $data->overall_achievement_percent;
            $data->overall_status              = $this->determineStatus($percent);
            $data->overall_achievement_percent = round($percent, 2); // Round for consistency
        } else {
            $data->overall_status              = 'Unknown';
            $data->overall_achievement_percent = 0;
        }

        // Verify data consistency
        $this->verifyPerformanceSummary($data);

        return $data;
    }

    /**
     * Verify performance summary data for consistency
     */
    private function verifyPerformanceSummary($data)
    {
        if (! $data) {
            return;
        }

        // Verify that status counts add up to total indicators
        $totalStatusCount = $data->needs_attention_count + $data->in_progress_count +
        $data->on_track_count + $data->met_count;

        if ($totalStatusCount != $data->total_indicators) {
            Log::warning('Performance summary inconsistency: Status counts (' . $totalStatusCount .
                ') do not match total indicators (' . $data->total_indicators . ')');
        }

        // Verify that over-achieved count is not greater than met count
        if ($data->over_achieved_count > $data->met_count) {
            Log::warning('Performance summary inconsistency: Over-achieved count (' . $data->over_achieved_count .
                ') is greater than met count (' . $data->met_count . ')');
        }
    }

    /**
     * Get performance by strategic objective with verified calculations
     */
    private function getStrategicObjectivePerformance($year, $semiAnnual, $cluster, $strategicObjective)
    {
        $query = DB::table('vw_semi_annual_performance');

        // Apply filters
        $this->applyFilters($query, $year, $semiAnnual, $cluster, $strategicObjective);

        // Group by strategic objective with proper NULL handling
        $results = $query->select('so_pk', 'so_number', 'so_name')
            ->selectRaw('
                COUNT(DISTINCT indicator_pk) as indicator_count,
                AVG(CASE WHEN achievement_percent IS NOT NULL THEN achievement_percent ELSE NULL END) as avg_achievement_percent,
                SUM(CASE WHEN status_label = "Needs Attention" THEN 1 ELSE 0 END) as needs_attention_count,
                SUM(CASE WHEN status_label = "In Progress" THEN 1 ELSE 0 END) as in_progress_count,
                SUM(CASE WHEN status_label = "On Track" THEN 1 ELSE 0 END) as on_track_count,
                SUM(CASE WHEN status_label = "Met" THEN 1 ELSE 0 END) as met_count,
                SUM(CASE WHEN comment = "Over Achieved" THEN 1 ELSE 0 END) as over_achieved_count,
                SUM(raw_target_value) as total_target,
                SUM(raw_actual_value) as total_actual
            ')
            ->groupBy('so_pk', 'so_number', 'so_name')
            ->orderBy('so_number')
            ->get();

        // Calculate status for each strategic objective using centralized function
        foreach ($results as $item) {
            // Round for consistency
            $item->avg_achievement_percent = round($item->avg_achievement_percent, 2);

            // Determine status
            $item->status = $this->determineStatus($item->avg_achievement_percent);

            // Verify data consistency
            $this->verifyStrategicObjectiveData($item);
        }

        return $results;
    }

    /**
     * Verify strategic objective data for consistency
     */
    private function verifyStrategicObjectiveData($item)
    {
        // Verify that status counts add up to indicator count
        $totalStatusCount = $item->needs_attention_count + $item->in_progress_count +
        $item->on_track_count + $item->met_count;

        if ($totalStatusCount != $item->indicator_count) {
            Log::warning('Strategic objective data inconsistency for SO ' . $item->so_number .
                ': Status counts (' . $totalStatusCount .
                ') do not match indicator count (' . $item->indicator_count . ')');
        }

        // Verify that over-achieved count is not greater than met count
        if ($item->over_achieved_count > $item->met_count) {
            Log::warning('Strategic objective data inconsistency for SO ' . $item->so_number .
                ': Over-achieved count (' . $item->over_achieved_count .
                ') is greater than met count (' . $item->met_count . ')');
        }
    }

    /**
     * Get performance by cluster with verified calculations
     */
    private function getClusterPerformance($year, $semiAnnual, $cluster, $strategicObjective)
    {
        $query = DB::table('vw_semi_annual_performance');

        // Apply filters
        $this->applyFilters($query, $year, $semiAnnual, $cluster, $strategicObjective);

        // Group by cluster with proper NULL handling
        $results = $query->select('cluster_pk', 'cluster_code', 'cluster_name')
            ->selectRaw('
                COUNT(DISTINCT indicator_pk) as indicator_count,
                COUNT(DISTINCT so_pk) as so_count,
                AVG(CASE WHEN achievement_percent IS NOT NULL THEN achievement_percent ELSE NULL END) as avg_achievement_percent,
                SUM(CASE WHEN status_label = "Needs Attention" THEN 1 ELSE 0 END) as needs_attention_count,
                SUM(CASE WHEN status_label = "In Progress" THEN 1 ELSE 0 END) as in_progress_count,
                SUM(CASE WHEN status_label = "On Track" THEN 1 ELSE 0 END) as on_track_count,
                SUM(CASE WHEN status_label = "Met" THEN 1 ELSE 0 END) as met_count,
                SUM(CASE WHEN comment = "Over Achieved" THEN 1 ELSE 0 END) as over_achieved_count,
                SUM(raw_target_value) as total_target,
                SUM(raw_actual_value) as total_actual
            ')
            ->groupBy('cluster_pk', 'cluster_code', 'cluster_name')
            ->orderBy('avg_achievement_percent', 'desc')
            ->get();

        // Calculate status for each cluster using centralized function
        foreach ($results as $item) {
            // Round for consistency
            $item->avg_achievement_percent = round($item->avg_achievement_percent, 2);

            // Determine status
            $item->status = $this->determineStatus($item->avg_achievement_percent);

            // Verify data consistency
            $this->verifyClusterData($item);
        }

        return $results;
    }

    /**
     * Verify cluster data for consistency
     */
    private function verifyClusterData($item)
    {
        // Verify that status counts add up to indicator count
        $totalStatusCount = $item->needs_attention_count + $item->in_progress_count +
        $item->on_track_count + $item->met_count;

        if ($totalStatusCount != $item->indicator_count) {
            Log::warning('Cluster data inconsistency for ' . $item->cluster_name .
                ': Status counts (' . $totalStatusCount .
                ') do not match indicator count (' . $item->indicator_count . ')');
        }

        // Verify that over-achieved count is not greater than met count
        if ($item->over_achieved_count > $item->met_count) {
            Log::warning('Cluster data inconsistency for ' . $item->cluster_name .
                ': Over-achieved count (' . $item->over_achieved_count .
                ') is greater than met count (' . $item->met_count . ')');
        }
    }

    /**
     * Get performance by indicator with verified calculations
     */
    private function getIndicatorPerformance($year, $semiAnnual, $cluster, $strategicObjective)
    {
        $query = DB::table('vw_semi_annual_performance');

        // Apply filters
        $this->applyFilters($query, $year, $semiAnnual, $cluster, $strategicObjective);

        // Group by indicator with proper NULL handling
        $results = $query->select('indicator_pk', 'indicator_number', 'indicator_name', 'so_number', 'so_name')
            ->selectRaw('
                AVG(CASE WHEN achievement_percent IS NOT NULL THEN achievement_percent ELSE NULL END) as avg_achievement_percent,
                COUNT(DISTINCT cluster_pk) as cluster_count,
                SUM(raw_actual_value) as total_actual_value,
                SUM(raw_target_value) as total_target_value,
                COUNT(CASE WHEN status_label = "Needs Attention" THEN 1 ELSE NULL END) as needs_attention_count,
                COUNT(CASE WHEN status_label = "In Progress" THEN 1 ELSE NULL END) as in_progress_count,
                COUNT(CASE WHEN status_label = "On Track" THEN 1 ELSE NULL END) as on_track_count,
                COUNT(CASE WHEN status_label = "Met" THEN 1 ELSE NULL END) as met_count,
                COUNT(CASE WHEN comment = "Over Achieved" THEN 1 ELSE NULL END) as over_achieved_count
            ')
            ->groupBy('indicator_pk', 'indicator_number', 'indicator_name', 'so_number', 'so_name')
            ->orderBy('so_number')
            ->orderBy('indicator_number')
            ->get();

        // Calculate status and additional metrics for each indicator
        foreach ($results as $item) {
            // Round for consistency
            $item->avg_achievement_percent = round($item->avg_achievement_percent, 2);

            // Determine status
            $item->status = $this->determineStatus($item->avg_achievement_percent);

            // Calculate if over achieved
            $item->over_achieved = false;
            if ($item->total_target_value > 0 && $item->total_actual_value > $item->total_target_value) {
                $item->over_achieved = true;
            }

            // Calculate achievement gap
            if ($item->total_target_value > 0) {
                $item->achievement_gap         = $item->total_actual_value - $item->total_target_value;
                $item->achievement_gap_percent = round(($item->achievement_gap / $item->total_target_value) * 100, 2);
            } else {
                $item->achievement_gap         = 0;
                $item->achievement_gap_percent = 0;
            }

            // Verify data consistency
            $this->verifyIndicatorData($item);
        }

        return $results;
    }

    /**
     * Verify indicator data for consistency
     */
    private function verifyIndicatorData($item)
    {
        // Verify that status counts add up to cluster count
        $totalStatusCount = $item->needs_attention_count + $item->in_progress_count +
        $item->on_track_count + $item->met_count;

        if ($totalStatusCount != $item->cluster_count) {
            Log::warning('Indicator data inconsistency for ' . $item->indicator_name .
                ': Status counts (' . $totalStatusCount .
                ') do not match cluster count (' . $item->cluster_count . ')');
        }

        // Verify that over-achieved count is not greater than met count
        if ($item->over_achieved_count > $item->met_count) {
            Log::warning('Indicator data inconsistency for ' . $item->indicator_name .
                ': Over-achieved count (' . $item->over_achieved_count .
                ') is greater than met count (' . $item->met_count . ')');
        }

        // Verify achievement percent calculation
        if ($item->total_target_value > 0) {
            $calculatedPercent = round(($item->total_actual_value / $item->total_target_value) * 100, 2);
            $difference        = abs($calculatedPercent - $item->avg_achievement_percent);

            // If there's a significant difference, log it
            if ($difference > 5) {
                Log::warning('Indicator achievement calculation inconsistency for ' . $item->indicator_name .
                    ': Calculated percent (' . $calculatedPercent .
                    ') differs from reported percent (' . $item->avg_achievement_percent . ')');
            }
        }
    }

    /**
     * Get items that need attention (low performance) with verified data
     */
    private function getItemsNeedingAttention($year, $semiAnnual, $cluster, $strategicObjective)
    {
                                                                      // Define attention threshold
        $attentionThreshold = $this->statusThresholds['in_progress']; // 50%

        // Get clusters needing attention
        $clustersNeedingAttention = DB::table('vw_semi_annual_performance')
            ->select('cluster_pk', 'cluster_name')
            ->selectRaw('
                AVG(CASE WHEN achievement_percent IS NOT NULL THEN achievement_percent ELSE NULL END) as avg_achievement,
                COUNT(DISTINCT indicator_pk) as indicator_count
            ')
            ->when($year !== 'All', function ($query) use ($year) {
                return $query->where('timeline_year', $year);
            })
            ->when($semiAnnual !== 'All', function ($query) use ($semiAnnual) {
                return $query->where('semi_annual_label', $semiAnnual);
            })
            ->when($strategicObjective !== 'All', function ($query) use ($strategicObjective) {
                return $query->where('so_pk', $strategicObjective);
            })
            ->groupBy('cluster_pk', 'cluster_name')
            ->having('avg_achievement', '<', $attentionThreshold)
            ->having('indicator_count', '>', 0) // Ensure we have data
            ->orderBy('avg_achievement')
            ->limit(5)
            ->get();

        // Get indicators needing attention
        $indicatorsNeedingAttention = DB::table('vw_semi_annual_performance')
            ->select('indicator_pk', 'indicator_name', 'so_number', 'so_name')
            ->selectRaw('
                AVG(CASE WHEN achievement_percent IS NOT NULL THEN achievement_percent ELSE NULL END) as avg_achievement,
                COUNT(DISTINCT cluster_pk) as cluster_count
            ')
            ->when($year !== 'All', function ($query) use ($year) {
                return $query->where('timeline_year', $year);
            })
            ->when($semiAnnual !== 'All', function ($query) use ($semiAnnual) {
                return $query->where('semi_annual_label', $semiAnnual);
            })
            ->when($cluster !== 'All', function ($query) use ($cluster) {
                return $query->where('cluster_pk', $cluster);
            })
            ->groupBy('indicator_pk', 'indicator_name', 'so_number', 'so_name')
            ->having('avg_achievement', '<', $attentionThreshold)
            ->having('cluster_count', '>', 0) // Ensure we have data
            ->orderBy('avg_achievement')
            ->limit(5)
            ->get();

        // Get strategic objectives needing attention
        $strategicObjectivesNeedingAttention = DB::table('vw_semi_annual_performance')
            ->select('so_pk', 'so_number', 'so_name')
            ->selectRaw('
                AVG(CASE WHEN achievement_percent IS NOT NULL THEN achievement_percent ELSE NULL END) as avg_achievement,
                COUNT(DISTINCT indicator_pk) as indicator_count
            ')
            ->when($year !== 'All', function ($query) use ($year) {
                return $query->where('timeline_year', $year);
            })
            ->when($semiAnnual !== 'All', function ($query) use ($semiAnnual) {
                return $query->where('semi_annual_label', $semiAnnual);
            })
            ->when($cluster !== 'All', function ($query) use ($cluster) {
                return $query->where('cluster_pk', $cluster);
            })
            ->groupBy('so_pk', 'so_number', 'so_name')
            ->having('avg_achievement', '<', $attentionThreshold)
            ->having('indicator_count', '>', 0) // Ensure we have data
            ->orderBy('avg_achievement')
            ->limit(5)
            ->get();

        // Round achievement percentages for consistency
        foreach ($clustersNeedingAttention as $item) {
            $item->avg_achievement = round($item->avg_achievement, 2);
        }

        foreach ($indicatorsNeedingAttention as $item) {
            $item->avg_achievement = round($item->avg_achievement, 2);
        }

        foreach ($strategicObjectivesNeedingAttention as $item) {
            $item->avg_achievement = round($item->avg_achievement, 2);
        }

        return [
            'clusters'            => $clustersNeedingAttention,
            'indicators'          => $indicatorsNeedingAttention,
            'strategicObjectives' => $strategicObjectivesNeedingAttention,
        ];
    }

    /**
     * Get performance trends over time with verified data
     */
    private function getPerformanceTrends($year, $semiAnnual, $cluster, $strategicObjective)
    {
        // Get trend data for overall performance
        $trendData = DB::table('vw_semi_annual_performance')
            ->select('timeline_year', 'semi_annual_label')
            ->selectRaw('
                AVG(CASE WHEN achievement_percent IS NOT NULL THEN achievement_percent ELSE NULL END) as avg_achievement,
                COUNT(DISTINCT indicator_pk) as indicator_count
            ')
            ->when($cluster !== 'All', function ($query) use ($cluster) {
                return $query->where('cluster_pk', $cluster);
            })
            ->when($strategicObjective !== 'All', function ($query) use ($strategicObjective) {
                return $query->where('so_pk', $strategicObjective);
            })
            ->groupBy('timeline_year', 'semi_annual_label')
            ->having('indicator_count', '>', 0) // Ensure we have data
            ->orderBy('timeline_year')
            ->orderBy('semi_annual_label')
            ->get();

        // Get trend by strategic objective
        $soTrends = DB::table('vw_semi_annual_performance')
            ->select('timeline_year', 'semi_annual_label', 'so_number', 'so_name')
            ->selectRaw('
                AVG(CASE WHEN achievement_percent IS NOT NULL THEN achievement_percent ELSE NULL END) as avg_achievement,
                COUNT(DISTINCT indicator_pk) as indicator_count
            ')
            ->when($cluster !== 'All', function ($query) use ($cluster) {
                return $query->where('cluster_pk', $cluster);
            })
            ->groupBy('timeline_year', 'semi_annual_label', 'so_number', 'so_name')
            ->having('indicator_count', '>', 0) // Ensure we have data
            ->orderBy('timeline_year')
            ->orderBy('semi_annual_label')
            ->orderBy('so_number')
            ->get();

        // Get trend by cluster (top 5 clusters)
        $topClusters = DB::table('vw_semi_annual_performance')
            ->select('cluster_pk', 'cluster_name')
            ->selectRaw('
                AVG(CASE WHEN achievement_percent IS NOT NULL THEN achievement_percent ELSE NULL END) as avg_achievement,
                COUNT(DISTINCT indicator_pk) as indicator_count
            ')
            ->when($strategicObjective !== 'All', function ($query) use ($strategicObjective) {
                return $query->where('so_pk', $strategicObjective);
            })
            ->groupBy('cluster_pk', 'cluster_name')
            ->having('indicator_count', '>', 0) // Ensure we have data
            ->orderBy('avg_achievement', 'desc')
            ->limit(5)
            ->pluck('cluster_pk');

        $clusterTrends = DB::table('vw_semi_annual_performance')
            ->select('timeline_year', 'semi_annual_label', 'cluster_name')
            ->selectRaw('
                AVG(CASE WHEN achievement_percent IS NOT NULL THEN achievement_percent ELSE NULL END) as avg_achievement,
                COUNT(DISTINCT indicator_pk) as indicator_count
            ')
            ->whereIn('cluster_pk', $topClusters)
            ->when($strategicObjective !== 'All', function ($query) use ($strategicObjective) {
                return $query->where('so_pk', $strategicObjective);
            })
            ->groupBy('timeline_year', 'semi_annual_label', 'cluster_name')
            ->having('indicator_count', '>', 0) // Ensure we have data
            ->orderBy('timeline_year')
            ->orderBy('semi_annual_label')
            ->orderBy('cluster_name')
            ->get();

        // Round achievement percentages for consistency
        foreach ($trendData as $item) {
            $item->avg_achievement = round($item->avg_achievement, 2);
        }

        foreach ($soTrends as $item) {
            $item->avg_achievement = round($item->avg_achievement, 2);
        }

        foreach ($clusterTrends as $item) {
            $item->avg_achievement = round($item->avg_achievement, 2);
        }

        // Calculate growth rates and trends
        $growthRates = $this->calculateGrowthRates($trendData);

        return [
            'overall'              => $trendData,
            'byStrategicObjective' => $soTrends,
            'byCluster'            => $clusterTrends,
            'growthRates'          => $growthRates,
        ];
    }

    /**
     * Calculate growth rates from trend data
     */
    private function calculateGrowthRates($trendData)
    {
        if (count($trendData) < 2) {
            return [
                'overall_growth' => null,
                'period_growth'  => [],
                'cagr'           => null,
            ];
        }

        $growthRates = [];
        $firstValue  = $trendData[0]->avg_achievement;
        $lastValue   = $trendData[count($trendData) - 1]->avg_achievement;

        // Calculate overall growth
        $overallGrowth = $firstValue > 0 ? (($lastValue - $firstValue) / $firstValue) * 100 : 0;

        // Calculate period-to-period growth
        $periodGrowth = [];
        for ($i = 1; $i < count($trendData); $i++) {
            $previous = $trendData[$i - 1]->avg_achievement;
            $current  = $trendData[$i]->avg_achievement;

            $growth = $previous > 0 ? (($current - $previous) / $previous) * 100 : 0;

            $periodGrowth[] = [
                'from_period' => $trendData[$i - 1]->timeline_year . ' ' . $trendData[$i - 1]->semi_annual_label,
                'to_period'   => $trendData[$i]->timeline_year . ' ' . $trendData[$i]->semi_annual_label,
                'growth_rate' => round($growth, 2),
            ];
        }

        // Calculate CAGR (Compound Annual Growth Rate)
        $periods = count($trendData) - 1;
        $cagr    = $periods > 0 && $firstValue > 0 ? (pow(($lastValue / $firstValue), (1 / $periods)) - 1) * 100 : 0;

        return [
            'overall_growth' => round($overallGrowth, 2),
            'period_growth'  => $periodGrowth,
            'cagr'           => round($cagr, 2),
        ];
    }

    /**
     * Calculate statistical measures for performance data
     */
    private function calculatePerformanceStatistics($year, $semiAnnual, $cluster, $strategicObjective)
    {
        $query = DB::table('vw_semi_annual_performance');

        // Apply filters
        $this->applyFilters($query, $year, $semiAnnual, $cluster, $strategicObjective);

        // Get raw achievement percentages
        $achievementValues = $query->whereNotNull('achievement_percent')
            ->pluck('achievement_percent')
            ->toArray();

        // If no data, return empty statistics
        $count = count($achievementValues);
        if ($count === 0) {
            return (object) [
                'count'          => 0,
                'mean'           => null,
                'median'         => null,
                'std_deviation'  => null,
                'min'            => null,
                'max'            => null,
                'lower_quartile' => null,
                'upper_quartile' => null,
                'skewness'       => null,
                'kurtosis'       => null,
            ];
        }

        // Calculate basic statistics
        $mean = array_sum($achievementValues) / $count;

        // Sort for median and percentiles
        sort($achievementValues);

        $median = $count % 2 === 0
        ? ($achievementValues[$count / 2 - 1] + $achievementValues[$count / 2]) / 2
        : $achievementValues[floor($count / 2)];

        // Calculate standard deviation
        $variance = 0;
        foreach ($achievementValues as $value) {
            $variance += pow($value - $mean, 2);
        }
        $stdDeviation = sqrt($variance / $count);

        // Calculate quartiles
        $lowerQuartile = $achievementValues[floor($count * 0.25)];
        $upperQuartile = $achievementValues[floor($count * 0.75)];

        // Calculate skewness (measure of asymmetry)
        $skewness = 0;
        if ($stdDeviation > 0) {
            foreach ($achievementValues as $value) {
                $skewness += pow(($value - $mean) / $stdDeviation, 3);
            }
            $skewness = $skewness / $count;
        }

        // Calculate kurtosis (measure of "tailedness")
        $kurtosis = 0;
        if ($stdDeviation > 0) {
            foreach ($achievementValues as $value) {
                $kurtosis += pow(($value - $mean) / $stdDeviation, 4);
            }
            $kurtosis = $kurtosis / $count - 3; // Excess kurtosis (normal = 0)
        }

        return (object) [
            'count'               => $count,
            'mean'                => round($mean, 2),
            'median'              => round($median, 2),
            'std_deviation'       => round($stdDeviation, 2),
            'min'                 => round(min($achievementValues), 2),
            'max'                 => round(max($achievementValues), 2),
            'lower_quartile'      => round($lowerQuartile, 2),
            'upper_quartile'      => round($upperQuartile, 2),
            'interquartile_range' => round($upperQuartile - $lowerQuartile, 2),
            'skewness'            => round($skewness, 2),
            'kurtosis'            => round($kurtosis, 2),
        ];
    }

    /**
     * Detect anomalies in performance data
     */
    private function detectAnomalies($year, $semiAnnual, $cluster, $strategicObjective)
    {
        // Get statistics
        $stats = $this->calculatePerformanceStatistics($year, $semiAnnual, $cluster, $strategicObjective);

        if (! $stats || $stats->count === 0 || $stats->std_deviation === 0) {
            return [
                'outliers'            => [],
                'inconsistencies'     => [],
                'data_quality_issues' => [],
            ];
        }

        // Define anomaly thresholds (3 standard deviations)
        $upperThreshold = $stats->mean + (3 * $stats->std_deviation);
        $lowerThreshold = $stats->mean - (3 * $stats->std_deviation);

        $query = DB::table('vw_semi_annual_performance');
        $this->applyFilters($query, $year, $semiAnnual, $cluster, $strategicObjective);

        // Find indicators with anomalous values
        $outliers = $query
            ->select('indicator_pk', 'indicator_name', 'so_number', 'cluster_name', 'achievement_percent', 'raw_target_value', 'raw_actual_value')
            ->where(function ($q) use ($upperThreshold, $lowerThreshold) {
                $q->where('achievement_percent', '>', $upperThreshold)
                    ->orWhere('achievement_percent', '<', $lowerThreshold);
            })
            ->get()
            ->map(function ($item) use ($stats) {
                $item->z_score         = ($item->achievement_percent - $stats->mean) / $stats->std_deviation;
                $item->is_high_outlier = $item->achievement_percent > $stats->mean + (3 * $stats->std_deviation);
                $item->is_low_outlier  = $item->achievement_percent < $stats->mean - (3 * $stats->std_deviation);
                return $item;
            });

        // Find data inconsistencies (e.g., achievement percent doesn't match actual/target)
        $inconsistencies = $query
            ->select('indicator_pk', 'indicator_name', 'so_number', 'cluster_name', 'achievement_percent', 'raw_target_value', 'raw_actual_value')
            ->whereNotNull('raw_target_value')
            ->whereNotNull('raw_actual_value')
            ->whereNotNull('achievement_percent')
            ->get()
            ->filter(function ($item) {
                if ($item->raw_target_value == 0) {
                    return false;
                }

                $calculatedPercent = ($item->raw_actual_value / $item->raw_target_value) * 100;
                $difference        = abs($calculatedPercent - $item->achievement_percent);

                // If difference is more than 5%, flag as inconsistency
                return $difference > 5;
            })
            ->map(function ($item) {
                $calculatedPercent = $item->raw_target_value > 0 ?
                ($item->raw_actual_value / $item->raw_target_value) * 100 : 0;

                $item->calculated_percent = round($calculatedPercent, 2);
                $item->difference         = round(abs($calculatedPercent - $item->achievement_percent), 2);

                return $item;
            });

        // Find data quality issues (missing values, etc.)
        $dataQualityIssues = $query
            ->select('indicator_pk', 'indicator_name', 'so_number', 'cluster_name')
            ->selectRaw('
                CASE WHEN raw_target_value IS NULL THEN 1 ELSE 0 END as missing_target,
                CASE WHEN raw_actual_value IS NULL THEN 1 ELSE 0 END as missing_actual,
                CASE WHEN achievement_percent IS NULL THEN 1 ELSE 0 END as missing_achievement
            ')
            ->havingRaw('missing_target = 1 OR missing_actual = 1 OR missing_achievement = 1')
            ->get();

        return [
            'outliers'            => $outliers,
            'inconsistencies'     => $inconsistencies,
            'data_quality_issues' => $dataQualityIssues,
        ];
    }

    /**
     * Generate advanced AI-like insights from the data
     */
    private function generateAdvancedInsights($summary, $strategicObjectives, $clusterPerformance, $indicatorPerformance, $trends, $statistics, $anomalies)
    {
        $insights = [];

        // Overall performance insight
        if ($summary && $summary->overall_achievement_percent !== null) {
            $overallPercent = round($summary->overall_achievement_percent, 1);
            $insights[]     = [
                'type'     => 'overall',
                'message'  => "Overall performance is at {$overallPercent}% with status '{$summary->overall_status}'.",
                'priority' => $this->getInsightPriority($summary->overall_status),
                'category' => 'summary',
            ];
        }

        // Statistical insights
        if ($statistics && $statistics->count > 0) {
            // Variability insight
            if ($statistics->std_deviation !== null) {
                $stdDev           = $statistics->std_deviation;
                $variabilityLevel = $stdDev > 20 ? "high" : ($stdDev > 10 ? "moderate" : "low");

                $insights[] = [
                    'type'     => 'variability',
                    'message'  => "Performance variability is {$stdDev}% (standard deviation), indicating {$variabilityLevel} variability across indicators.",
                    'priority' => $stdDev > 20 ? 'high' : 'medium',
                    'category' => 'statistics',
                ];
            }

            // Distribution insight
            if ($statistics->skewness !== null) {
                $skewType = $statistics->skewness > 0.5 ? "positively skewed (more low performers)" :
                ($statistics->skewness < -0.5 ? "negatively skewed (more high performers)" : "normally distributed");

                $insights[] = [
                    'type'     => 'distribution',
                    'message'  => "Performance distribution is {$skewType} with a skewness of {$statistics->skewness}.",
                    'priority' => 'medium',
                    'category' => 'statistics',
                ];
            }
        }

        // Trend insights
        if (! empty($trends['overall']) && count($trends['overall']) >= 2) {
            $growthRates = $trends['growthRates'];

            if ($growthRates['overall_growth'] !== null) {
                $overallGrowth  = $growthRates['overall_growth'];
                $trendDirection = $overallGrowth > 0 ? 'improving' : ($overallGrowth < 0 ? 'declining' : 'stable');
                $trendPercent   = abs($overallGrowth);

                $insights[] = [
                    'type'     => 'trend',
                    'message'  => "Overall performance is {$trendDirection} by {$trendPercent}% compared to the first period.",
                    'priority' => $trendDirection === 'declining' ? 'high' : 'medium',
                    'category' => 'trends',
                ];
            }

            if ($growthRates['cagr'] !== null) {
                $cagr          = $growthRates['cagr'];
                $cagrDirection = $cagr > 0 ? 'positive' : ($cagr < 0 ? 'negative' : 'neutral');

                $insights[] = [
                    'type'     => 'growth_rate',
                    'message'  => "Compound growth rate is {$cagr}% ({$cagrDirection}), indicating " .
                    ($cagrDirection === 'positive' ? 'consistent improvement' :
                        ($cagrDirection === 'negative' ? 'consistent decline' : 'stability')) . " over time.",
                    'priority' => $cagrDirection === 'negative' ? 'high' : 'medium',
                    'category' => 'trends',
                ];
            }
        }

        // Strategic objective insights
        if (! empty($strategicObjectives)) {
            // Find worst performing strategic objectives
            $worstSOs = collect($strategicObjectives)
                ->sortBy('avg_achievement_percent')
                ->take(3);

            if ($worstSOs->count() > 0) {
                $soNames       = $worstSOs->pluck('so_number')->implode(', ');
                $lowestPercent = $worstSOs->first()->avg_achievement_percent;

                $insights[] = [
                    'type'     => 'strategic_objectives',
                    'message'  => "Strategic objectives requiring urgent attention: {$soNames}. The lowest performing is at {$lowestPercent}%.",
                    'priority' => 'high',
                    'category' => 'performance_gaps',
                ];
            }

            // Find best performing strategic objectives
            $bestSOs = collect($strategicObjectives)
                ->sortByDesc('avg_achievement_percent')
                ->take(3);

            if ($bestSOs->count() > 0) {
                $soNames        = $bestSOs->pluck('so_number')->implode(', ');
                $highestPercent = $bestSOs->first()->avg_achievement_percent;

                $insights[] = [
                    'type'     => 'strategic_objectives',
                    'message'  => "Top performing strategic objectives: {$soNames}. The highest performing is at {$highestPercent}%.",
                    'priority' => 'medium',
                    'category' => 'success_stories',
                ];
            }
        }

        // Cluster insights
        if (! empty($clusterPerformance)) {
            // Find worst performing clusters
            $worstClusters = collect($clusterPerformance)
                ->sortBy('avg_achievement_percent')
                ->take(3);

            if ($worstClusters->count() > 0) {
                $clusterNames = $worstClusters->pluck('cluster_name')->implode(', ');

                $insights[] = [
                    'type'     => 'clusters',
                    'message'  => "Clusters needing attention: {$clusterNames}.",
                    'priority' => 'high',
                    'category' => 'performance_gaps',
                ];
            }

            // Find best performing clusters
            $bestClusters = collect($clusterPerformance)
                ->sortByDesc('avg_achievement_percent')
                ->take(3);

            if ($bestClusters->count() > 0) {
                $clusterNames = $bestClusters->pluck('cluster_name')->implode(', ');

                $insights[] = [
                    'type'     => 'clusters',
                    'message'  => "Top performing clusters: {$clusterNames}.",
                    'priority' => 'medium',
                    'category' => 'success_stories',
                ];
            }
        }

        // Anomaly insights
        if (! empty($anomalies['outliers'])) {
            $outlierCount = count($anomalies['outliers']);

            if ($outlierCount > 0) {
                $insights[] = [
                    'type'     => 'anomalies',
                    'message'  => "Detected {$outlierCount} performance outliers that significantly deviate from the average.",
                    'priority' => 'high',
                    'category' => 'data_quality',
                ];
            }
        }

        if (! empty($anomalies['inconsistencies'])) {
            $inconsistencyCount = count($anomalies['inconsistencies']);

            if ($inconsistencyCount > 0) {
                $insights[] = [
                    'type'     => 'data_quality',
                    'message'  => "Found {$inconsistencyCount} data inconsistencies where calculated achievement percentages don't match reported values.",
                    'priority' => 'high',
                    'category' => 'data_quality',
                ];
            }
        }

        // Over-achievement insights
        $overAchievedCount = $summary ? $summary->over_achieved_count : 0;
        if ($overAchievedCount > 0) {
            $insights[] = [
                'type'     => 'over_achievement',
                'message'  => "{$overAchievedCount} indicators have exceeded their targets, suggesting potential for target recalibration.",
                'priority' => 'medium',
                'category' => 'target_setting',
            ];
        }

        return $insights;
    }

    /**
     * Generate actionable recommendations based on insights
     */
    private function generateRecommendations($summary, $strategicObjectives, $clusterPerformance, $indicatorPerformance, $trends, $statistics, $anomalies)
    {
        $recommendations = [];

        // Overall performance recommendations
        if ($summary && $summary->overall_achievement_percent < $this->statusThresholds['in_progress']) {
            $recommendations[] = [
                'type'     => 'strategic',
                'message'  => "Conduct an urgent strategic review to address the overall low performance of " .
                round($summary->overall_achievement_percent, 1) . "%.",
                'priority' => 'high',
                'category' => 'performance_improvement',
            ];
        }

        // Strategic objective recommendations
        $lowPerformingSOs = collect($strategicObjectives)
            ->filter(function ($so) {
                return $so->avg_achievement_percent < $this->statusThresholds['in_progress'];
            })
            ->sortBy('avg_achievement_percent')
            ->take(3);

        if ($lowPerformingSOs->count() > 0) {
            foreach ($lowPerformingSOs as $so) {
                $recommendations[] = [
                    'type'     => 'strategic_objective',
                    'message'  => "Develop an intervention plan for Strategic Objective {$so->so_number} ({$so->so_name}) " .
                    "which is performing at only " . round($so->avg_achievement_percent, 1) . "%.",
                    'priority' => 'high',
                    'category' => 'performance_improvement',
                ];
            }
        }

        // Cluster recommendations
        $lowPerformingClusters = collect($clusterPerformance)
            ->filter(function ($cluster) {
                return $cluster->avg_achievement_percent < $this->statusThresholds['in_progress'];
            })
            ->sortBy('avg_achievement_percent')
            ->take(3);

        if ($lowPerformingClusters->count() > 0) {
            foreach ($lowPerformingClusters as $cluster) {
                $recommendations[] = [
                    'type'     => 'cluster',
                    'message'  => "Provide targeted support and resources to {$cluster->cluster_name} cluster " .
                    "which is performing at only " . round($cluster->avg_achievement_percent, 1) . "%.",
                    'priority' => 'high',
                    'category' => 'resource_allocation',
                ];
            }
        }

        // Data quality recommendations
        if (! empty($anomalies['inconsistencies']) || ! empty($anomalies['data_quality_issues'])) {
            $recommendations[] = [
                'type'     => 'data_quality',
                'message'  => "Implement data quality checks and validation procedures to address inconsistencies " .
                "and missing data in performance reporting.",
                'priority' => 'medium',
                'category' => 'data_management',
            ];
        }

        // Target setting recommendations
        $overAchievedCount = $summary ? $summary->over_achieved_count : 0;
        if ($overAchievedCount > 0 && $summary && $overAchievedCount > ($summary->total_indicators * 0.2)) {
            $recommendations[] = [
                'type'     => 'target_setting',
                'message'  => "Review target setting methodology as " . round(($overAchievedCount / $summary->total_indicators) * 100, 1) .
                "% of indicators have exceeded their targets, suggesting targets may be too conservative.",
                'priority' => 'medium',
                'category' => 'planning',
            ];
        }

        // Trend-based recommendations
        if (! empty($trends['growthRates']) && $trends['growthRates']['overall_growth'] !== null) {
            $overallGrowth = $trends['growthRates']['overall_growth'];

            if ($overallGrowth < -5) {
                $recommendations[] = [
                    'type'     => 'trend',
                    'message'  => "Address the declining performance trend of " . abs($overallGrowth) .
                    "% by conducting a root cause analysis and developing a recovery plan.",
                    'priority' => 'high',
                    'category' => 'performance_improvement',
                ];
            } elseif ($overallGrowth > 10) {
                $recommendations[] = [
                    'type'     => 'trend',
                    'message'  => "Document and share best practices from the positive growth trend of " .
                    $overallGrowth . "% to replicate success across other areas.",
                    'priority' => 'medium',
                    'category' => 'knowledge_sharing',
                ];
            }
        }

        // Knowledge sharing recommendations
        $highPerformingClusters = collect($clusterPerformance)
            ->filter(function ($cluster) {
                return $cluster->avg_achievement_percent > $this->statusThresholds['on_track'];
            })
            ->sortByDesc('avg_achievement_percent')
            ->take(2);

        if ($highPerformingClusters->count() > 0 && $lowPerformingClusters->count() > 0) {
            $recommendations[] = [
                'type'     => 'knowledge_sharing',
                'message'  => "Facilitate knowledge sharing sessions between high-performing clusters (" .
                $highPerformingClusters->pluck('cluster_name')->implode(', ') .
                ") and those requiring support (" .
                $lowPerformingClusters->pluck('cluster_name')->implode(', ') . ").",
                'priority' => 'medium',
                'category' => 'capacity_building',
            ];
        }

        return $recommendations;
    }

    /**
     * Perform comparative analysis between different dimensions
     */
    private function performComparativeAnalysis($year, $semiAnnual, $cluster, $strategicObjective)
    {
        // Compare current period with previous period
        $currentPeriodData = $this->getPeriodData($year, $semiAnnual, $cluster, $strategicObjective);

        // Determine previous period
        $previousPeriod     = $this->determinePreviousPeriod($year, $semiAnnual);
        $previousPeriodData = $this->getPeriodData($previousPeriod['year'], $previousPeriod['semi_annual'], $cluster, $strategicObjective);

        // Compare clusters
        $clusterComparison = $this->compareClusterPerformance($year, $semiAnnual, $strategicObjective);

        // Compare strategic objectives
        $soComparison = $this->compareStrategicObjectivePerformance($year, $semiAnnual, $cluster);

        return [
            'period_comparison'              => [
                'current'    => $currentPeriodData,
                'previous'   => $previousPeriodData,
                'difference' => $this->calculateDifference($currentPeriodData, $previousPeriodData),
            ],
            'cluster_comparison'             => $clusterComparison,
            'strategic_objective_comparison' => $soComparison,
        ];
    }

    /**
     * Get aggregated data for a specific period
     */
    private function getPeriodData($year, $semiAnnual, $cluster, $strategicObjective)
    {
        if ($year === 'All' || $year === null) {
            return null;
        }

        $query = DB::table('vw_semi_annual_performance')
            ->where('timeline_year', $year);

        if ($semiAnnual !== 'All') {
            $query->where('semi_annual_label', $semiAnnual);
        }

        if ($cluster !== 'All') {
            $query->where('cluster_pk', $cluster);
        }

        if ($strategicObjective !== 'All') {
            $query->where('so_pk', $strategicObjective);
        }

        // Get aggregated data
        $data = $query->selectRaw('
            COUNT(DISTINCT indicator_pk) as total_indicators,
            AVG(CASE WHEN achievement_percent IS NOT NULL THEN achievement_percent ELSE NULL END) as avg_achievement_percent,
            SUM(CASE WHEN status_label = "Needs Attention" THEN 1 ELSE 0 END) as needs_attention_count,
            SUM(CASE WHEN status_label = "In Progress" THEN 1 ELSE 0 END) as in_progress_count,
            SUM(CASE WHEN status_label = "On Track" THEN 1 ELSE 0 END) as on_track_count,
            SUM(CASE WHEN status_label = "Met" THEN 1 ELSE 0 END) as met_count,
            SUM(CASE WHEN comment = "Over Achieved" THEN 1 ELSE 0 END) as over_achieved_count
        ')->first();

        if ($data) {
            $data->avg_achievement_percent = round($data->avg_achievement_percent, 2);
            $data->status                  = $this->determineStatus($data->avg_achievement_percent);
            $data->period_label            = $year . ' ' . ($semiAnnual !== 'All' ? $semiAnnual : 'Full Year');
        }

        return $data;
    }

    /**
     * Determine the previous period based on current period
     */
    private function determinePreviousPeriod($year, $semiAnnual)
    {
        if ($year === 'All' || $year === null) {
            return ['year' => null, 'semi_annual' => null];
        }

        $previousYear       = $year;
        $previousSemiAnnual = $semiAnnual;

        if ($semiAnnual === 'Second Semi Annual') {
            $previousSemiAnnual = 'First Semi Annual';
        } elseif ($semiAnnual === 'First Semi Annual') {
            $previousYear       = $year - 1;
            $previousSemiAnnual = 'Second Semi Annual';
        } else {
            $previousYear = $year - 1;
        }

        // Check if previous period exists in the database
        $exists = DB::table('vw_semi_annual_performance')
            ->where('timeline_year', $previousYear)
            ->when($previousSemiAnnual !== 'All', function ($query) use ($previousSemiAnnual) {
                return $query->where('semi_annual_label', $previousSemiAnnual);
            })
            ->exists();

        if (! $exists) {
            // If previous period doesn't exist, try just the previous year
            $previousYear       = $year - 1;
            $previousSemiAnnual = 'All';

            $exists = DB::table('vw_semi_annual_performance')
                ->where('timeline_year', $previousYear)
                ->exists();

            if (! $exists) {
                return ['year' => null, 'semi_annual' => null];
            }
        }

        return ['year' => $previousYear, 'semi_annual' => $previousSemiAnnual];
    }

    /**
     * Calculate difference between current and previous period
     */
    private function calculateDifference($current, $previous)
    {
        if (! $current || ! $previous) {
            return null;
        }

        $difference = new \stdClass();

        $difference->avg_achievement_percent = round($current->avg_achievement_percent - $previous->avg_achievement_percent, 2);
        $difference->needs_attention_count   = $current->needs_attention_count - $previous->needs_attention_count;
        $difference->in_progress_count       = $current->in_progress_count - $previous->in_progress_count;
        $difference->on_track_count          = $current->on_track_count - $previous->on_track_count;
        $difference->met_count               = $current->met_count - $previous->met_count;
        $difference->over_achieved_count     = $current->over_achieved_count - $previous->over_achieved_count;

        $difference->percent_change = $previous->avg_achievement_percent > 0 ?
        round(($difference->avg_achievement_percent / $previous->avg_achievement_percent) * 100, 2) : 0;

        $difference->trend = $difference->avg_achievement_percent > 0 ? 'improving' :
        ($difference->avg_achievement_percent < 0 ? 'declining' : 'stable');

        return $difference;
    }

    /**
     * Compare performance across clusters
     */
    private function compareClusterPerformance($year, $semiAnnual, $strategicObjective)
    {
        if ($year === 'All' || $year === null) {
            return [];
        }

        $query = DB::table('vw_semi_annual_performance')
            ->where('timeline_year', $year);

        if ($semiAnnual !== 'All') {
            $query->where('semi_annual_label', $semiAnnual);
        }

        if ($strategicObjective !== 'All') {
            $query->where('so_pk', $strategicObjective);
        }

        // Get performance by cluster
        $clusterPerformance = $query
            ->select('cluster_pk', 'cluster_name')
            ->selectRaw('
                COUNT(DISTINCT indicator_pk) as indicator_count,
                AVG(CASE WHEN achievement_percent IS NOT NULL THEN achievement_percent ELSE NULL END) as avg_achievement_percent
            ')
            ->groupBy('cluster_pk', 'cluster_name')
            ->having('indicator_count', '>', 0)
            ->orderBy('avg_achievement_percent', 'desc')
            ->get();

        // Calculate average and standard deviation
        $avgValues = $clusterPerformance->pluck('avg_achievement_percent')->toArray();
        $avgCount  = count($avgValues);

        if ($avgCount === 0) {
            return [];
        }

        $avgMean = array_sum($avgValues) / $avgCount;

        $variance = 0;
        foreach ($avgValues as $value) {
            $variance += pow($value - $avgMean, 2);
        }
        $stdDev = sqrt($variance / $avgCount);

        // Add relative performance indicators
        foreach ($clusterPerformance as $cluster) {
            $cluster->avg_achievement_percent = round($cluster->avg_achievement_percent, 2);
            $cluster->status                  = $this->determineStatus($cluster->avg_achievement_percent);

            // Calculate z-score (standard deviations from mean)
            $cluster->z_score = $stdDev > 0 ? round(($cluster->avg_achievement_percent - $avgMean) / $stdDev, 2) : 0;

            // Determine relative performance
            if ($cluster->z_score > 1) {
                $cluster->relative_performance = 'well above average';
            } elseif ($cluster->z_score > 0.5) {
                $cluster->relative_performance = 'above average';
            } elseif ($cluster->z_score > -0.5) {
                $cluster->relative_performance = 'average';
            } elseif ($cluster->z_score > -1) {
                $cluster->relative_performance = 'below average';
            } else {
                $cluster->relative_performance = 'well below average';
            }
        }

        return [
            'clusters'   => $clusterPerformance,
            'statistics' => [
                'mean'    => round($avgMean, 2),
                'std_dev' => round($stdDev, 2),
                'min'     => round(min($avgValues), 2),
                'max'     => round(max($avgValues), 2),
                'range'   => round(max($avgValues) - min($avgValues), 2),
            ],
        ];
    }

    /**
     * Compare performance across strategic objectives
     */
    private function compareStrategicObjectivePerformance($year, $semiAnnual, $cluster)
    {
        if ($year === 'All' || $year === null) {
            return [];
        }

        $query = DB::table('vw_semi_annual_performance')
            ->where('timeline_year', $year);

        if ($semiAnnual !== 'All') {
            $query->where('semi_annual_label', $semiAnnual);
        }

        if ($cluster !== 'All') {
            $query->where('cluster_pk', $cluster);
        }

        // Get performance by strategic objective
        $soPerformance = $query
            ->select('so_pk', 'so_number', 'so_name')
            ->selectRaw('
                COUNT(DISTINCT indicator_pk) as indicator_count,
                AVG(CASE WHEN achievement_percent IS NOT NULL THEN achievement_percent ELSE NULL END) as avg_achievement_percent
            ')
            ->groupBy('so_pk', 'so_number', 'so_name')
            ->having('indicator_count', '>', 0)
            ->orderBy('avg_achievement_percent', 'desc')
            ->get();

        // Calculate average and standard deviation
        $avgValues = $soPerformance->pluck('avg_achievement_percent')->toArray();
        $avgCount  = count($avgValues);

        if ($avgCount === 0) {
            return [];
        }

        $avgMean = array_sum($avgValues) / $avgCount;

        $variance = 0;
        foreach ($avgValues as $value) {
            $variance += pow($value - $avgMean, 2);
        }
        $stdDev = sqrt($variance / $avgCount);

        // Add relative performance indicators
        foreach ($soPerformance as $so) {
            $so->avg_achievement_percent = round($so->avg_achievement_percent, 2);
            $so->status                  = $this->determineStatus($so->avg_achievement_percent);

            // Calculate z-score (standard deviations from mean)
            $so->z_score = $stdDev > 0 ? round(($so->avg_achievement_percent - $avgMean) / $stdDev, 2) : 0;

            // Determine relative performance
            if ($so->z_score > 1) {
                $so->relative_performance = 'well above average';
            } elseif ($so->z_score > 0.5) {
                $so->relative_performance = 'above average';
            } elseif ($so->z_score > -0.5) {
                $so->relative_performance = 'average';
            } elseif ($so->z_score > -1) {
                $so->relative_performance = 'below average';
            } else {
                $so->relative_performance = 'well below average';
            }
        }

        return [
            'strategic_objectives' => $soPerformance,
            'statistics'           => [
                'mean'    => round($avgMean, 2),
                'std_dev' => round($stdDev, 2),
                'min'     => round(min($avgValues), 2),
                'max'     => round(max($avgValues), 2),
                'range'   => round(max($avgValues) - min($avgValues), 2),
            ],
        ];
    }

    /**
     * Determine status based on achievement percentage
     */
    private function determineStatus($achievementPercent)
    {
        if ($achievementPercent === null) {
            return 'Unknown';
        }

        if ($achievementPercent < $this->statusThresholds['needs_attention']) {
            return 'Needs Attention';
        } elseif ($achievementPercent < $this->statusThresholds['in_progress']) {
            return 'In Progress';
        } elseif ($achievementPercent < $this->statusThresholds['on_track']) {
            return 'On Track';
        } else {
            return 'Met';
        }
    }

    /**
     * Get insight priority based on status
     */
    private function getInsightPriority($status)
    {
        switch ($status) {
            case 'Needs Attention':
                return 'high';
            case 'In Progress':
                return 'medium';
            case 'On Track':
                return 'low';
            case 'Met':
                return 'low';
            default:
                return 'medium';
        }
    }

    /**
     * Apply filters to a query with error handling
     */
    private function applyFilters(&$query, $year, $semiAnnual, $cluster, $strategicObjective)
    {
        try {
            // Apply year filter
            if ($year && $year !== 'All') {
                $query->where('timeline_year', $year);
            }

            // Apply semi-annual filter
            if ($semiAnnual && $semiAnnual !== 'All') {
                $query->where('semi_annual_label', $semiAnnual);
            }

            // Apply cluster filter
            if ($cluster && $cluster !== 'All') {
                $query->where('cluster_pk', $cluster);
            }

            // Apply strategic objective filter
            if ($strategicObjective && $strategicObjective !== 'All') {
                $query->where('so_pk', $strategicObjective);
            }
        } catch (\Exception $e) {
            Log::error('Error applying filters: ' . $e->getMessage());
            // Return a default query if there's an error
            $query = DB::table('vw_semi_annual_performance');
        }
    }

    /**
     * Export performance data to Excel with multiple sheets for AU presidents
     */
    public function exportExcel(Request $request)
    {
        try {
            // Validate filters
            $filters = $this->validateFilters($request);

            // Create new spreadsheet
            $spreadsheet = new Spreadsheet();

            // Set document properties
            $spreadsheet->getProperties()
                ->setCreator('ECSA-HC Performance Monitoring System')
                ->setLastModifiedBy('ECSA-HC Performance Monitoring System')
                ->setTitle('Performance Report')
                ->setSubject('Semi-Annual Performance Report')
                ->setDescription('Comprehensive performance report for AU presidents')
                ->setKeywords('performance, monitoring, ECSA-HC')
                ->setCategory('Report');

            // Get data for the report
            $dashboardData = $this->prepareComprehensiveData(
                $filters['year'],
                $filters['semi_annual'],
                $filters['cluster'],
                $filters['strategic_objective']
            );

            // Create Executive Summary sheet
            $this->createExecutiveSummarySheet($spreadsheet, $dashboardData, $filters);

            // Create Strategic Objectives sheet
            $this->createStrategicObjectivesSheet($spreadsheet, $dashboardData);

            // Create Cluster Performance sheet
            $this->createClusterPerformanceSheet($spreadsheet, $dashboardData);

            // Create Indicators sheet
            $this->createIndicatorsSheet($spreadsheet, $dashboardData);

            // Create Trends sheet
            $this->createTrendsSheet($spreadsheet, $dashboardData);

            // Create Insights & Recommendations sheet
            $this->createInsightsSheet($spreadsheet, $dashboardData);

            // Create writer
            $writer = new Xlsx($spreadsheet);

            // Prepare response
            $fileName = 'ECSA-HC_Performance_Report_' . date('Y-m-d') . '.xlsx';

            // Create response
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $fileName . '"');
            header('Cache-Control: max-age=0');

            // Save to output
            $writer->save('php://output');
            exit;

        } catch (\Exception $e) {
            Log::error('Error exporting Excel: ' . $e->getMessage());
            return back()->with('error', 'An error occurred during export. Please try again.');
        }
    }

    /**
     * Create Executive Summary sheet
     */
    private function createExecutiveSummarySheet($spreadsheet, $dashboardData, $filters)
    {
        // Set active sheet
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Executive Summary');

        // Add title
        $sheet->setCellValue('A1', 'ECSA-HC PERFORMANCE REPORT - EXECUTIVE SUMMARY');
        $sheet->mergeCells('A1:H1');

        // Style the title
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Add report period
        $periodText = 'Report Period: ';
        if ($filters['year'] !== 'All') {
            $periodText .= $filters['year'];
            if ($filters['semi_annual'] !== 'All') {
                $periodText .= ' - ' . $filters['semi_annual'];
            }
        } else {
            $periodText .= 'All Available Data';
        }

        $sheet->setCellValue('A2', $periodText);
        $sheet->mergeCells('A2:H2');
        $sheet->getStyle('A2')->getFont()->setBold(true);

        // Add filters
        $filterText = 'Filters: ';
        if ($filters['cluster'] !== 'All') {
            $clusterName = DB::table('clusters')->where('id', $filters['cluster'])->value('Cluster_Name');
            $filterText .= 'Cluster: ' . ($clusterName ?? 'Unknown');
        }
        if ($filters['strategic_objective'] !== 'All') {
            $soName = DB::table('strategic_objectives')->where('id', $filters['strategic_objective'])->value('SO_Name');
            $filterText .= ($filters['cluster'] !== 'All' ? ', ' : '') . 'Strategic Objective: ' . ($soName ?? 'Unknown');
        }
        if ($filters['cluster'] === 'All' && $filters['strategic_objective'] === 'All') {
            $filterText .= 'All Data';
        }

        $sheet->setCellValue('A3', $filterText);
        $sheet->mergeCells('A3:H3');

        // Add generation date
        $sheet->setCellValue('A4', 'Report Generated: ' . date('Y-m-d H:i:s'));
        $sheet->mergeCells('A4:H4');

        $sheet->getStyle('A1:A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Add overall performance summary
        $sheet->setCellValue('A6', 'OVERALL PERFORMANCE SUMMARY');
        $sheet->mergeCells('A6:H6');
        $sheet->getStyle('A6')->getFont()->setBold(true);
        $sheet->getStyle('A6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DDDDDD');

        // Add summary data
        $summary = $dashboardData['summary'];

        $sheet->setCellValue('A7', 'Overall Achievement:');
        $sheet->setCellValue('B7', round($summary->overall_achievement_percent, 1) . '%');
        $sheet->setCellValue('C7', 'Status:');
        $sheet->setCellValue('D7', $summary->overall_status);

        // Color the status cell based on status
        $statusColor = $this->statusColors[$summary->overall_status] ?? '#FFFFFF';
        $sheet->getStyle('D7')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(substr($statusColor, 1));
        $sheet->getStyle('D7')->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE));

        $sheet->setCellValue('A8', 'Total Clusters:');
        $sheet->setCellValue('B8', $summary->total_clusters);
        $sheet->setCellValue('C8', 'Total Strategic Objectives:');
        $sheet->setCellValue('D8', $summary->total_strategic_objectives);
        $sheet->setCellValue('E8', 'Total Indicators:');
        $sheet->setCellValue('F8', $summary->total_indicators);

        // Add status distribution
        $sheet->setCellValue('A10', 'PERFORMANCE STATUS DISTRIBUTION');
        $sheet->mergeCells('A10:H10');
        $sheet->getStyle('A10')->getFont()->setBold(true);
        $sheet->getStyle('A10')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DDDDDD');

        $sheet->setCellValue('A11', 'Status');
        $sheet->setCellValue('B11', 'Count');
        $sheet->setCellValue('C11', 'Percentage');

        $sheet->getStyle('A11:C11')->getFont()->setBold(true);

        $sheet->setCellValue('A12', 'Needs Attention');
        $sheet->setCellValue('B12', $summary->needs_attention_count);
        $sheet->setCellValue('C12', $summary->total_indicators > 0 ?
            round(($summary->needs_attention_count / $summary->total_indicators) * 100, 1) . '%' : '0%');
        $sheet->getStyle('A12')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(substr($this->statusColors['Needs Attention'], 1));
        $sheet->getStyle('A12')->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE));

        $sheet->setCellValue('A13', 'In Progress');
        $sheet->setCellValue('B13', $summary->in_progress_count);
        $sheet->setCellValue('C13', $summary->total_indicators > 0 ?
            round(($summary->in_progress_count / $summary->total_indicators) * 100, 1) . '%' : '0%');
        $sheet->getStyle('A13')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(substr($this->statusColors['In Progress'], 1));

        $sheet->setCellValue('A14', 'On Track');
        $sheet->setCellValue('B14', $summary->on_track_count);
        $sheet->setCellValue('C14', $summary->total_indicators > 0 ?
            round(($summary->on_track_count / $summary->total_indicators) * 100, 1) . '%' : '0%');
        $sheet->getStyle('A14')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(substr($this->statusColors['On Track'], 1));
        $sheet->getStyle('A14')->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE));

        $sheet->setCellValue('A15', 'Met');
        $sheet->setCellValue('B15', $summary->met_count);
        $sheet->setCellValue('C15', $summary->total_indicators > 0 ?
            round(($summary->met_count / $summary->total_indicators) * 100, 1) . '%' : '0%');
        $sheet->getStyle('A15')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(substr($this->statusColors['Met'], 1));
        $sheet->getStyle('A15')->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE));

        $sheet->setCellValue('A16', 'Over Achieved');
        $sheet->setCellValue('B16', $summary->over_achieved_count);
        $sheet->setCellValue('C16', $summary->total_indicators > 0 ?
            round(($summary->over_achieved_count / $summary->total_indicators) * 100, 1) . '%' : '0%');
        $sheet->getStyle('A16')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(substr($this->statusColors['Over Achieved'], 1));
        $sheet->getStyle('A16')->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE));

        // Add key insights
        $sheet->setCellValue('A18', 'KEY INSIGHTS');
        $sheet->mergeCells('A18:H18');
        $sheet->getStyle('A18')->getFont()->setBold(true);
        $sheet->getStyle('A18')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DDDDDD');

        $insights = $dashboardData['insights'];
        $row      = 19;

        foreach ($insights as $index => $insight) {
            if ($index >= 5) {
                break;
            }
            // Limit to top 5 insights

            $priorityColor = '';
            switch ($insight['priority']) {
                case 'high':
                    $priorityColor = 'FFCCCC'; // Light red
                    break;
                case 'medium':
                    $priorityColor = 'FFFFCC'; // Light yellow
                    break;
                case 'low':
                    $priorityColor = 'CCFFCC'; // Light green
                    break;
            }

            $sheet->setCellValue('A' . $row, ($index + 1) . '. ' . $insight['message']);
            $sheet->mergeCells('A' . $row . ':H' . $row);

            if ($priorityColor) {
                $sheet->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($priorityColor);
            }

            $row++;
        }

        // Add key recommendations
        $sheet->setCellValue('A' . ($row + 1), 'KEY RECOMMENDATIONS');
        $sheet->mergeCells('A' . ($row + 1) . ':H' . ($row + 1));
        $sheet->getStyle('A' . ($row + 1))->getFont()->setBold(true);
        $sheet->getStyle('A' . ($row + 1))->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DDDDDD');

        $recommendations = $dashboardData['recommendations'];
        $row += 2;

        foreach ($recommendations as $index => $recommendation) {
            if ($index >= 5) {
                break;
            }
            // Limit to top 5 recommendations

            $priorityColor = '';
            switch ($recommendation['priority']) {
                case 'high':
                    $priorityColor = 'FFCCCC'; // Light red
                    break;
                case 'medium':
                    $priorityColor = 'FFFFCC'; // Light yellow
                    break;
                case 'low':
                    $priorityColor = 'CCFFCC'; // Light green
                    break;
            }

            $sheet->setCellValue('A' . $row, ($index + 1) . '. ' . $recommendation['message']);
            $sheet->mergeCells('A' . $row . ':H' . $row);

            if ($priorityColor) {
                $sheet->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($priorityColor);
            }

            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Add borders
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A6:H16')->applyFromArray($styleArray);
        $sheet->getStyle('A11:C16')->applyFromArray($styleArray);
    }

    /**
     * Create Strategic Objectives sheet
     */
    private function createStrategicObjectivesSheet($spreadsheet, $dashboardData)
    {
        // Create new sheet
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Strategic Objectives');

        // Add title
        $sheet->setCellValue('A1', 'STRATEGIC OBJECTIVES PERFORMANCE');
        $sheet->mergeCells('A1:J1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Add headers
        $headers = [
            'A' => 'SO Number',
            'B' => 'Strategic Objective',
            'C' => 'Indicators',
            'D' => 'Achievement %',
            'E' => 'Status',
            'F' => 'Needs Attention',
            'G' => 'In Progress',
            'H' => 'On Track',
            'I' => 'Met',
            'J' => 'Over Achieved',
        ];

        $row = 3;
        foreach ($headers as $col => $header) {
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $sheet->getStyle($col . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DDDDDD');
        }

        // Add data
        $strategicObjectives = $dashboardData['strategicObjectives'];
        $row                 = 4;

        foreach ($strategicObjectives as $so) {
            $sheet->setCellValue('A' . $row, $so->so_number);
            $sheet->setCellValue('B' . $row, $so->so_name);
            $sheet->setCellValue('C' . $row, $so->indicator_count);
            $sheet->setCellValue('D' . $row, $so->avg_achievement_percent . '%');
            $sheet->setCellValue('E' . $row, $so->status);
            $sheet->setCellValue('F' . $row, $so->needs_attention_count);
            $sheet->setCellValue('G' . $row, $so->in_progress_count);
            $sheet->setCellValue('H' . $row, $so->on_track_count);
            $sheet->setCellValue('I' . $row, $so->met_count);
            $sheet->setCellValue('J' . $row, $so->over_achieved_count . $row, $so->met_count);
            $sheet->setCellValue('J' . $row, $so->over_achieved_count);

            // Color the status cell based on status
            $statusColor = $this->statusColors[$so->status] ?? '#FFFFFF';
            $sheet->getStyle('E' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(substr($statusColor, 1));
            $sheet->getStyle('E' . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE));

            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Add borders
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A3:J' . $row)->applyFromArray($styleArray);
    }

    /**
     * Create Cluster Performance sheet
     */
    private function createClusterPerformanceSheet($spreadsheet, $dashboardData)
    {
        // Create new sheet
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Cluster Performance');

        // Add title
        $sheet->setCellValue('A1', 'CLUSTER PERFORMANCE');
        $sheet->mergeCells('A1:K1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Add headers
        $headers = [
            'A' => 'Cluster Code',
            'B' => 'Cluster Name',
            'C' => 'Indicators',
            'D' => 'Strategic Objectives',
            'E' => 'Achievement %',
            'F' => 'Status',
            'G' => 'Needs Attention',
            'H' => 'In Progress',
            'I' => 'On Track',
            'J' => 'Met',
            'K' => 'Over Achieved',
        ];

        $row = 3;
        foreach ($headers as $col => $header) {
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $sheet->getStyle($col . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DDDDDD');
        }

        // Add data
        $clusterPerformance = $dashboardData['clusterPerformance'];
        $row                = 4;

        foreach ($clusterPerformance as $cluster) {
            $sheet->setCellValue('A' . $row, $cluster->cluster_code);
            $sheet->setCellValue('B' . $row, $cluster->cluster_name);
            $sheet->setCellValue('C' . $row, $cluster->indicator_count);
            $sheet->setCellValue('D' . $row, $cluster->so_count);
            $sheet->setCellValue('E' . $row, $cluster->avg_achievement_percent . '%');
            $sheet->setCellValue('F' . $row, $cluster->status);
            $sheet->setCellValue('G' . $row, $cluster->needs_attention_count);
            $sheet->setCellValue('H' . $row, $cluster->in_progress_count);
            $sheet->setCellValue('I' . $row, $cluster->on_track_count);
            $sheet->setCellValue('J' . $row, $cluster->met_count);
            $sheet->setCellValue('K' . $row, $cluster->over_achieved_count);

            // Color the status cell based on status
            $statusColor = $this->statusColors[$cluster->status] ?? '#FFFFFF';
            $sheet->getStyle('F' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(substr($statusColor, 1));
            $sheet->getStyle('F' . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE));

            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Add borders
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A3:K' . ($row - 1))->applyFromArray($styleArray);
    }

    /**
     * Create Indicators sheet
     */
    private function createIndicatorsSheet($spreadsheet, $dashboardData)
    {
        // Create new sheet
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Indicators');

        // Add title
        $sheet->setCellValue('A1', 'INDICATOR PERFORMANCE');
        $sheet->mergeCells('A1:I1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Add headers
        $headers = [
            'A' => 'SO Number',
            'B' => 'Indicator Number',
            'C' => 'Indicator Name',
            'D' => 'Clusters',
            'E' => 'Total Target',
            'F' => 'Total Actual',
            'G' => 'Achievement %',
            'H' => 'Status',
            'I' => 'Over Achieved',
        ];

        $row = 3;
        foreach ($headers as $col => $header) {
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $sheet->getStyle($col . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DDDDDD');
        }

        // Add data
        $indicatorPerformance = $dashboardData['indicatorPerformance'];
        $row                  = 4;

        foreach ($indicatorPerformance as $indicator) {
            $sheet->setCellValue('A' . $row, $indicator->so_number);
            $sheet->setCellValue('B' . $row, $indicator->indicator_number);
            $sheet->setCellValue('C' . $row, $indicator->indicator_name);
            $sheet->setCellValue('D' . $row, $indicator->cluster_count);
            $sheet->setCellValue('E' . $row, $indicator->total_target_value);
            $sheet->setCellValue('F' . $row, $indicator->total_actual_value);
            $sheet->setCellValue('G' . $row, $indicator->avg_achievement_percent . '%');
            $sheet->setCellValue('H' . $row, $indicator->status);
            $sheet->setCellValue('I' . $row, $indicator->over_achieved ? 'Yes' : 'No');

            // Color the status cell based on status
            $statusColor = $this->statusColors[$indicator->status] ?? '#FFFFFF';
            $sheet->getStyle('H' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(substr($statusColor, 1));
            $sheet->getStyle('H' . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE));

            // Highlight over-achieved indicators
            if ($indicator->over_achieved) {
                $sheet->getStyle('I' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(substr($this->statusColors['Over Achieved'], 1));
                $sheet->getStyle('I' . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE));
            }

            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Add borders
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A3:I' . ($row - 1))->applyFromArray($styleArray);
    }

    /**
     * Create Trends sheet
     */
    private function createTrendsSheet($spreadsheet, $dashboardData)
    {
        // Create new sheet
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Performance Trends');

        // Add title
        $sheet->setCellValue('A1', 'PERFORMANCE TRENDS OVER TIME');
        $sheet->mergeCells('A1:D1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Add overall trend section
        $sheet->setCellValue('A3', 'OVERALL PERFORMANCE TREND');
        $sheet->mergeCells('A3:D3');
        $sheet->getStyle('A3')->getFont()->setBold(true);
        $sheet->getStyle('A3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DDDDDD');

        // Add headers
        $sheet->setCellValue('A4', 'Year');
        $sheet->setCellValue('B4', 'Period');
        $sheet->setCellValue('C4', 'Achievement %');
        $sheet->setCellValue('D4', 'Indicators');

        $sheet->getStyle('A4:D4')->getFont()->setBold(true);

        // Add overall trend data
        $trendData = $dashboardData['trends']['overall'];
        $row       = 5;

        foreach ($trendData as $trend) {
            $sheet->setCellValue('A' . $row, $trend->timeline_year);
            $sheet->setCellValue('B' . $row, $trend->semi_annual_label);
            $sheet->setCellValue('C' . $row, $trend->avg_achievement . '%');
            $sheet->setCellValue('D' . $row, $trend->indicator_count);
            $row++;
        }

        // Add growth rates section
        $row += 2;
        $sheet->setCellValue('A' . $row, 'GROWTH RATES');
        $sheet->mergeCells('A' . $row . ':D' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DDDDDD');
        $row++;

        $growthRates = $dashboardData['trends']['growthRates'];

        $sheet->setCellValue('A' . $row, 'Overall Growth:');
        $sheet->setCellValue('B' . $row, ($growthRates['overall_growth'] !== null ? $growthRates['overall_growth'] . '%' : 'N/A'));
        $row++;

        $sheet->setCellValue('A' . $row, 'Compound Annual Growth Rate (CAGR):');
        $sheet->setCellValue('B' . $row, ($growthRates['cagr'] !== null ? $growthRates['cagr'] . '%' : 'N/A'));
        $row++;

        // Add period-to-period growth rates
        $row += 1;
        $sheet->setCellValue('A' . $row, 'PERIOD-TO-PERIOD GROWTH RATES');
        $sheet->mergeCells('A' . $row . ':D' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DDDDDD');
        $row++;

        $sheet->setCellValue('A' . $row, 'From Period');
        $sheet->setCellValue('B' . $row, 'To Period');
        $sheet->setCellValue('C' . $row, 'Growth Rate');
        $sheet->getStyle('A' . $row . ':C' . $row)->getFont()->setBold(true);
        $row++;

        foreach ($growthRates['period_growth'] as $growth) {
            $sheet->setCellValue('A' . $row, $growth['from_period']);
            $sheet->setCellValue('B' . $row, $growth['to_period']);
            $sheet->setCellValue('C' . $row, $growth['growth_rate'] . '%');

            // Color negative growth rates in red
            if ($growth['growth_rate'] < 0) {
                $sheet->getStyle('C' . $row)->getFont()->getColor()->setRGB('FF0000');
            }

            $row++;
        }

        // Add strategic objective trends section
        $row += 2;
        $sheet->setCellValue('A' . $row, 'STRATEGIC OBJECTIVE TRENDS');
        $sheet->mergeCells('A' . $row . ':E' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DDDDDD');
        $row++;

        $sheet->setCellValue('A' . $row, 'Year');
        $sheet->setCellValue('B' . $row, 'Period');
        $sheet->setCellValue('C' . $row, 'SO Number');
        $sheet->setCellValue('D' . $row, 'SO Name');
        $sheet->setCellValue('E' . $row, 'Achievement %');
        $sheet->getStyle('A' . $row . ':E' . $row)->getFont()->setBold(true);
        $row++;

        $soTrends = $dashboardData['trends']['byStrategicObjective'];

        foreach ($soTrends as $trend) {
            $sheet->setCellValue('A' . $row, $trend->timeline_year);
            $sheet->setCellValue('B' . $row, $trend->semi_annual_label);
            $sheet->setCellValue('C' . $row, $trend->so_number);
            $sheet->setCellValue('D' . $row, $trend->so_name);
            $sheet->setCellValue('E' . $row, $trend->avg_achievement . '%');
            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Add borders
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A4:D' . ($row - 1))->applyFromArray($styleArray);
    }

    /**
     * Create Insights & Recommendations sheet
     */
    private function createInsightsSheet($spreadsheet, $dashboardData)
    {
        // Create new sheet
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Insights & Recommendations');

        // Add title
        $sheet->setCellValue('A1', 'INSIGHTS & RECOMMENDATIONS');
        $sheet->mergeCells('A1:D1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Add insights section
        $sheet->setCellValue('A3', 'KEY INSIGHTS');
        $sheet->mergeCells('A3:D3');
        $sheet->getStyle('A3')->getFont()->setBold(true);
        $sheet->getStyle('A3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DDDDDD');

        // Add headers
        $sheet->setCellValue('A4', 'Category');
        $sheet->setCellValue('B4', 'Type');
        $sheet->setCellValue('C4', 'Priority');
        $sheet->setCellValue('D4', 'Message');
        $sheet->getStyle('A4:D4')->getFont()->setBold(true);

        // Add insights data
        $insights = $dashboardData['insights'];
        $row      = 5;

        foreach ($insights as $insight) {
            $sheet->setCellValue('A' . $row, ucfirst($insight['category'] ?? 'General'));
            $sheet->setCellValue('B' . $row, ucfirst($insight['type']));
            $sheet->setCellValue('C' . $row, ucfirst($insight['priority']));
            $sheet->setCellValue('D' . $row, $insight['message']);

            // Color based on priority
            $priorityColor = '';
            switch ($insight['priority']) {
                case 'high':
                    $priorityColor = 'FFCCCC'; // Light red
                    break;
                case 'medium':
                    $priorityColor = 'FFFFCC'; // Light yellow
                    break;
                case 'low':
                    $priorityColor = 'CCFFCC'; // Light green
                    break;
            }

            if ($priorityColor) {
                $sheet->getStyle('C' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($priorityColor);
            }

            $row++;
        }

        // Add recommendations section
        $row += 2;
        $sheet->setCellValue('A' . $row, 'RECOMMENDATIONS');
        $sheet->mergeCells('A' . $row . ':D' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DDDDDD');
        $row++;

        // Add headers
        $sheet->setCellValue('A' . $row, 'Category');
        $sheet->setCellValue('B' . $row, 'Type');
        $sheet->setCellValue('C' . $row, 'Priority');
        $sheet->setCellValue('D' . $row, 'Recommendation');
        $sheet->getStyle('A' . $row . ':D' . $row)->getFont()->setBold(true);
        $row++;

        // Add recommendations data
        $recommendations = $dashboardData['recommendations'];

        foreach ($recommendations as $recommendation) {
            $sheet->setCellValue('A' . $row, ucfirst($recommendation['category'] ?? 'General'));
            $sheet->setCellValue('B' . $row, ucfirst($recommendation['type']));
            $sheet->setCellValue('C' . $row, ucfirst($recommendation['priority']));
            $sheet->setCellValue('D' . $row, $recommendation['message']);

            // Color based on priority
            $priorityColor = '';
            switch ($recommendation['priority']) {
                case 'high':
                    $priorityColor = 'FFCCCC'; // Light red
                    break;
                case 'medium':
                    $priorityColor = 'FFFFCC'; // Light yellow
                    break;
                case 'low':
                    $priorityColor = 'CCFFCC'; // Light green
                    break;
            }

            if ($priorityColor) {
                $sheet->getStyle('C' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($priorityColor);
            }

            $row++;
        }

        // Add anomalies section if there are any
        if (! empty($dashboardData['anomalies']['outliers']) || ! empty($dashboardData['anomalies']['inconsistencies'])) {
            $row += 2;
            $sheet->setCellValue('A' . $row, 'DATA ANOMALIES & QUALITY ISSUES');
            $sheet->mergeCells('A' . $row . ':D' . $row);
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $sheet->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DDDDDD');
            $row++;

            // Add outliers if any
            if (! empty($dashboardData['anomalies']['outliers'])) {
                $sheet->setCellValue('A' . $row, 'PERFORMANCE OUTLIERS');
                $sheet->mergeCells('A' . $row . ':D' . $row);
                $sheet->getStyle('A' . $row)->getFont()->setBold(true);
                $row++;

                $sheet->setCellValue('A' . $row, 'Indicator');
                $sheet->setCellValue('B' . $row, 'Cluster');
                $sheet->setCellValue('C' . $row, 'Achievement %');
                $sheet->setCellValue('D' . $row, 'Z-Score');
                $sheet->getStyle('A' . $row . ':D' . $row)->getFont()->setBold(true);
                $row++;

                foreach ($dashboardData['anomalies']['outliers'] as $outlier) {
                    $sheet->setCellValue('A' . $row, $outlier->indicator_name);
                    $sheet->setCellValue('B' . $row, $outlier->cluster_name);
                    $sheet->setCellValue('C' . $row, $outlier->achievement_percent . '%');
                    $sheet->setCellValue('D' . $row, $outlier->z_score);

                    // Color based on outlier type
                    if ($outlier->is_high_outlier) {
                        $sheet->getStyle('C' . $row . ':D' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('CCFFCC'); // Light green
                    } else {
                        $sheet->getStyle('C' . $row . ':D' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFCCCC'); // Light red
                    }

                    $row++;
                }
            }

            // Add data inconsistencies if any
            if (! empty($dashboardData['anomalies']['inconsistencies'])) {
                $row += 1;
                $sheet->setCellValue('A' . $row, 'DATA INCONSISTENCIES');
                $sheet->mergeCells('A' . $row . ':E' . $row);
                $sheet->getStyle('A' . $row)->getFont()->setBold(true);
                $row++;

                $sheet->setCellValue('A' . $row, 'Indicator');
                $sheet->setCellValue('B' . $row, 'Cluster');
                $sheet->setCellValue('C' . $row, 'Reported %');
                $sheet->setCellValue('D' . $row, 'Calculated %');
                $sheet->setCellValue('E' . $row, 'Difference');
                $sheet->getStyle('A' . $row . ':E' . $row)->getFont()->setBold(true);
                $row++;

                foreach ($dashboardData['anomalies']['inconsistencies'] as $inconsistency) {
                    $sheet->setCellValue('A' . $row, $inconsistency->indicator_name);
                    $sheet->setCellValue('B' . $row, $inconsistency->cluster_name);
                    $sheet->setCellValue('C' . $row, $inconsistency->achievement_percent . '%');
                    $sheet->setCellValue('D' . $row, $inconsistency->calculated_percent . '%');
                    $sheet->setCellValue('E' . $row, $inconsistency->difference . '%');

                    // Color based on difference magnitude
                    if ($inconsistency->difference > 10) {
                        $sheet->getStyle('E' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFCCCC'); // Light red
                    } else {
                        $sheet->getStyle('E' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFFFCC'); // Light yellow
                    }

                    $row++;
                }
            }
        }

        // Auto-size columns
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Add borders
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A4:D' . ($row - 1))->applyFromArray($styleArray);
    }

    /**
     * Export performance data to CSV
     */
    public function exportCsv(Request $request)
    {
        try {
            // Validate filters
            $filters    = $this->validateFilters($request);
            $exportType = $request->input('export_type', 'summary');

            // Get data based on export type
            $data     = [];
            $filename = 'performance_report_' . date('Y-m-d') . '.csv';
            $headers  = [];

            switch ($exportType) {
                case 'summary':
                    $data     = $this->getExportSummaryData($filters['year'], $filters['semi_annual'], $filters['cluster'], $filters['strategic_objective']);
                    $headers  = ['Strategic Objective', 'Cluster', 'Indicator', 'Target', 'Actual', 'Achievement %', 'Status'];
                    $filename = 'performance_summary_' . date('Y-m-d') . '.csv';
                    break;

                case 'cluster':
                    $data     = $this->getExportClusterData($filters['year'], $filters['semi_annual'], $filters['cluster'], $filters['strategic_objective']);
                    $headers  = ['Cluster', 'Indicator Count', 'Average Achievement %', 'Status'];
                    $filename = 'cluster_performance_' . date('Y-m-d') . '.csv';
                    break;

                case 'strategic_objective':
                    $data     = $this->getExportSOData($filters['year'], $filters['semi_annual'], $filters['cluster'], $filters['strategic_objective']);
                    $headers  = ['Strategic Objective', 'Indicator Count', 'Average Achievement %', 'Status'];
                    $filename = 'strategic_objective_performance_' . date('Y-m-d') . '.csv';
                    break;

                case 'indicator':
                    $data     = $this->getExportIndicatorData($filters['year'], $filters['semi_annual'], $filters['cluster'], $filters['strategic_objective']);
                    $headers  = ['Strategic Objective', 'Indicator', 'Cluster Count', 'Average Achievement %', 'Status'];
                    $filename = 'indicator_performance_' . date('Y-m-d') . '.csv';
                    break;
            }

            // Create CSV file
            $callback = function () use ($data, $headers) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $headers);

                foreach ($data as $row) {
                    fputcsv($file, $row);
                }

                fclose($file);
            };

            // Return CSV response
            return response()->stream($callback, 200, [
                'Content-Type'        => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);

        } catch (\Exception $e) {
            Log::error('Error exporting CSV: ' . $e->getMessage());
            return back()->with('error', 'An error occurred during export. Please try again.');
        }
    }

    /**
     * Get summary data for CSV export
     */
    private function getExportSummaryData($year, $semiAnnual, $cluster, $strategicObjective)
    {
        $query = DB::table('vw_semi_annual_performance');

        // Apply filters
        $this->applyFilters($query, $year, $semiAnnual, $cluster, $strategicObjective);

        // Get data
        $results = $query->select(
            'so_number',
            'cluster_name',
            'indicator_name',
            'raw_target_value',
            'raw_actual_value',
            'achievement_percent',
            'status_label'
        )
            ->orderBy('so_number')
            ->orderBy('cluster_name')
            ->orderBy('indicator_name')
            ->get();

        // Format data for CSV
        $data = [];
        foreach ($results as $row) {
            $data[] = [
                $row->so_number,
                $row->cluster_name,
                $row->indicator_name,
                $row->raw_target_value,
                $row->raw_actual_value,
                round($row->achievement_percent, 2) . '%',
                $row->status_label,
            ];
        }

        return $data;
    }

    /**
     * Get cluster data for CSV export
     */
    private function getExportClusterData($year, $semiAnnual, $cluster, $strategicObjective)
    {
        $clusterPerformance = $this->getClusterPerformance($year, $semiAnnual, $cluster, $strategicObjective);

        // Format data for CSV
        $data = [];
        foreach ($clusterPerformance as $row) {
            $data[] = [
                $row->cluster_name,
                $row->indicator_count,
                round($row->avg_achievement_percent, 2) . '%',
                $row->status,
            ];
        }

        return $data;
    }

    /**
     * Get strategic objective data for CSV export
     */
    private function getExportSOData($year, $semiAnnual, $cluster, $strategicObjective)
    {
        $soPerformance = $this->getStrategicObjectivePerformance($year, $semiAnnual, $cluster, $strategicObjective);

        // Format data for CSV
        $data = [];
        foreach ($soPerformance as $row) {
            $data[] = [
                $row->so_number . ' - ' . $row->so_name,
                $row->indicator_count,
                round($row->avg_achievement_percent, 2) . '%',
                $row->status,
            ];
        }

        return $data;
    }

    /**
     * Get indicator data for CSV export
     */
    private function getExportIndicatorData($year, $semiAnnual, $cluster, $strategicObjective)
    {
        $indicatorPerformance = $this->getIndicatorPerformance($year, $semiAnnual, $cluster, $strategicObjective);

        // Format data for CSV
        $data = [];
        foreach ($indicatorPerformance as $row) {
            $data[] = [
                $row->so_number,
                $row->indicator_number . ' - ' . $row->indicator_name,
                $row->cluster_count,
                round($row->avg_achievement_percent, 2) . '%',
                $row->status,
            ];
        }

        return $data;
    }

// EXPORT CODE
// EXPORT CODE
// EXPORT CODE
// EXPORT CODE
// EXPORT CODE
// EXPORT CODE
// EXPORT CODE
// EXPORT CODE
// EXPORT CODE
// EXPORT CODE
// EXPORT CODE
// EXPORT CODE
// EXPORT CODE
// EXPORT CODE
// EXPORT CODE
// EXPORT CODE
// EXPORT CODE
// EXPORT CODE
// EXPORT CODE
// EXPORT CODE

/**
 * Export detailed cluster performance data to Excel
 * Primarily using vw_semi_annual_performance view data
 *
 * @param Request $request
 * @return Response
 */
    /**
     * Export detailed cluster performance data to Excel
     * Primarily using vw_semi_annual_performance view data
     *
     * @param Request $request
     * @return Response
     */
    public function exportDetailedClusterPerformance(Request $request)
    {
        try {
            // Validate filters using existing controller method
            $filters = $this->validateFilters($request);

            // Create new spreadsheet
            $spreadsheet = new Spreadsheet();

            // Set document properties
            $spreadsheet->getProperties()
                ->setCreator('ECSA-HC Performance Monitoring System')
                ->setLastModifiedBy('ECSA-HC Performance Monitoring System')
                ->setTitle('Detailed Cluster Performance Report')
                ->setSubject('Cluster Performance by Strategic Objective and Indicator')
                ->setDescription('Comprehensive analysis of cluster performance per strategic objective and indicator')
                ->setKeywords('performance, cluster, strategic objective, indicator, ECSA-HC')
                ->setCategory('Report');

            // Create sheets for different report sections
            $this->createClusterPerformanceBySOSheet($spreadsheet, $filters);
            $this->createClusterPerformanceByIndicatorSheet($spreadsheet, $filters);
            $this->createSOPerformanceAggregateSheet($spreadsheet, $filters);
            $this->createIndicatorPerformanceAggregateSheet($spreadsheet, $filters);

            // Create writer
            $writer = new Xlsx($spreadsheet);

            // Prepare filename
            $fileName = 'ECSA-HC_Detailed_Cluster_Performance_' . date('Y-m-d') . '.xlsx';

            // Create a temporary file
            $tempFile = tempnam(sys_get_temp_dir(), 'excel');
            $writer->save($tempFile);

            // Return the file as a download response
            return response()->download($tempFile, $fileName, [
                'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ])->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            Log::error('Error exporting detailed cluster performance: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            echo $e->getMessage();
            die();
            // dd($e->getMessage());
            return back()->with('error', 'An error occurred during export: ' . $e->getMessage());
        }
    }

/**
 * Create sheet showing cluster performance by strategic objective
 *
 * @param Spreadsheet $spreadsheet
 * @param array $filters
 */
    private function createClusterPerformanceBySOSheet($spreadsheet, $filters)
    {
        // Set active sheet
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Cluster Performance by SO');

        // Add title
        $sheet->setCellValue('A1', 'CLUSTER PERFORMANCE BY STRATEGIC OBJECTIVE');
        $sheet->mergeCells('A1:I1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Add report period
        $periodText = 'Report Period: ';
        if ($filters['year'] !== 'All') {
            $periodText .= $filters['year'];
            if ($filters['semi_annual'] !== 'All') {
                $periodText .= ' - ' . $filters['semi_annual'];
            }
        } else {
            $periodText .= 'All Available Data';
        }

        $sheet->setCellValue('A2', $periodText);
        $sheet->mergeCells('A2:I2');
        $sheet->getStyle('A2')->getFont()->setBold(true);

        // Add headers
        $headers = [
            'A' => 'Cluster',
            'B' => 'Strategic Objective',
            'C' => 'SO Number',
            'D' => 'Indicators Count',
            'E' => 'Achievement %',
            'F' => 'Status',
            'G' => 'Needs Attention',
            'H' => 'In Progress',
            'I' => 'On Track',
            'J' => 'Met',
            'K' => 'Over Achieved',
        ];

        $row = 4;
        foreach ($headers as $col => $header) {
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $sheet->getStyle($col . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DDDDDD');
        }

        // Get data
        $data = $this->getClusterPerformanceBySOData($filters);

        // Add data
        $row = 5;
        foreach ($data as $item) {
            $sheet->setCellValue('A' . $row, $item->cluster_name);
            $sheet->setCellValue('B' . $row, $item->so_name);
            $sheet->setCellValue('C' . $row, $item->so_number);
            $sheet->setCellValue('D' . $row, $item->indicator_count);
            $sheet->setCellValue('E' . $row, round($item->avg_achievement_percent, 2) . '%');
            $sheet->setCellValue('F' . $row, $item->status);
            $sheet->setCellValue('G' . $row, $item->needs_attention_count);
            $sheet->setCellValue('H' . $row, $item->in_progress_count);
            $sheet->setCellValue('I' . $row, $item->on_track_count);
            $sheet->setCellValue('J' . $row, $item->met_count);
            $sheet->setCellValue('K' . $row, $item->over_achieved_count);

            // Color the status cell based on status
            $statusColor = $this->statusColors[$item->status] ?? '#FFFFFF';
            $sheet->getStyle('F' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(substr($statusColor, 1));
            $sheet->getStyle('F' . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE));

            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Add borders
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A4:K' . ($row - 1))->applyFromArray($styleArray);
    }

/**
 * Create sheet showing cluster performance by indicator
 *
 * @param Spreadsheet $spreadsheet
 * @param array $filters
 */
    private function createClusterPerformanceByIndicatorSheet($spreadsheet, $filters)
    {
        // Create new sheet
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Cluster Sats by Indicator');

        // Add title
        $sheet->setCellValue('A1', 'CLUSTER PERFORMANCE BY INDICATOR');
        $sheet->mergeCells('A1:I1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Add report period
        $periodText = 'Report Period: ';
        if ($filters['year'] !== 'All') {
            $periodText .= $filters['year'];
            if ($filters['semi_annual'] !== 'All') {
                $periodText .= ' - ' . $filters['semi_annual'];
            }
        } else {
            $periodText .= 'All Available Data';
        }

        $sheet->setCellValue('A2', $periodText);
        $sheet->mergeCells('A2:I2');
        $sheet->getStyle('A2')->getFont()->setBold(true);

        // Add headers
        $headers = [
            'A' => 'Cluster',
            'B' => 'Strategic Objective',
            'C' => 'Indicator Number',
            'D' => 'Indicator Name',
            'E' => 'Target Value',
            'F' => 'Actual Value',
            'G' => 'Achievement %',
            'H' => 'Status',
            'I' => 'Comment',
        ];

        $row = 4;
        foreach ($headers as $col => $header) {
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $sheet->getStyle($col . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DDDDDD');
        }

        // Get data
        $data = $this->getClusterPerformanceByIndicatorData($filters);

        // Add data
        $row = 5;
        foreach ($data as $item) {
            $sheet->setCellValue('A' . $row, $item->cluster_name);
            $sheet->setCellValue('B' . $row, $item->so_number);
            $sheet->setCellValue('C' . $row, $item->indicator_number);
            $sheet->setCellValue('D' . $row, $item->indicator_name);
            $sheet->setCellValue('E' . $row, $item->target_value);
            $sheet->setCellValue('F' . $row, $item->actual_value);
            $sheet->setCellValue('G' . $row, round($item->achievement_percent, 2) . '%');
            $sheet->setCellValue('H' . $row, $item->status);
            $sheet->setCellValue('I' . $row, $item->comment);

            // Color the status cell based on status
            $statusColor = $this->statusColors[$item->status] ?? '#FFFFFF';
            $sheet->getStyle('H' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(substr($statusColor, 1));
            $sheet->getStyle('H' . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE));

            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Add borders
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A4:I' . ($row - 1))->applyFromArray($styleArray);
    }

/**
 * Create sheet showing aggregate strategic objective performance
 *
 * @param Spreadsheet $spreadsheet
 * @param array $filters
 */
    private function createSOPerformanceAggregateSheet($spreadsheet, $filters)
    {
        // Create new sheet
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('SO Performance Aggregate');

        // Add title
        $sheet->setCellValue('A1', 'STRATEGIC OBJECTIVE PERFORMANCE AGGREGATE');
        $sheet->mergeCells('A1:I1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Add report period
        $periodText = 'Report Period: ';
        if ($filters['year'] !== 'All') {
            $periodText .= $filters['year'];
            if ($filters['semi_annual'] !== 'All') {
                $periodText .= ' - ' . $filters['semi_annual'];
            }
        } else {
            $periodText .= 'All Available Data';
        }

        $sheet->setCellValue('A2', $periodText);
        $sheet->mergeCells('A2:I2');
        $sheet->getStyle('A2')->getFont()->setBold(true);

        // Add headers
        $headers = [
            'A' => 'Strategic Objective',
            'B' => 'SO Number',
            'C' => 'Total Indicators',
            'D' => 'Total Clusters',
            'E' => 'Overall Achievement %',
            'F' => 'Status',
            'G' => 'Needs Attention',
            'H' => 'In Progress',
            'I' => 'On Track',
            'J' => 'Met',
            'K' => 'Over Achieved',
        ];

        $row = 4;
        foreach ($headers as $col => $header) {
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $sheet->getStyle($col . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DDDDDD');
        }

        // Get data
        $data = $this->getSOPerformanceAggregateData($filters);

        // Add data
        $row = 5;
        foreach ($data as $item) {
            $sheet->setCellValue('A' . $row, $item->so_name);
            $sheet->setCellValue('B' . $row, $item->so_number);
            $sheet->setCellValue('C' . $row, $item->indicator_count);
            $sheet->setCellValue('D' . $row, $item->cluster_count);
            $sheet->setCellValue('E' . $row, round($item->avg_achievement_percent, 2) . '%');
            $sheet->setCellValue('F' . $row, $item->status);
            $sheet->setCellValue('G' . $row, $item->needs_attention_count);
            $sheet->setCellValue('H' . $row, $item->in_progress_count);
            $sheet->setCellValue('I' . $row, $item->on_track_count);
            $sheet->setCellValue('J' . $row, $item->met_count);
            $sheet->setCellValue('K' . $row, $item->over_achieved_count);

            // Color the status cell based on status
            $statusColor = $this->statusColors[$item->status] ?? '#FFFFFF';
            $sheet->getStyle('F' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(substr($statusColor, 1));
            $sheet->getStyle('F' . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE));

            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Add borders
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A4:K' . ($row - 1))->applyFromArray($styleArray);
    }

/**
 * Create sheet showing aggregate indicator performance
 *
 * @param Spreadsheet $spreadsheet
 * @param array $filters
 */
    private function createIndicatorPerformanceAggregateSheet($spreadsheet, $filters)
    {
        // Create new sheet
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Indicator Performance Aggregate');

        // Add title
        $sheet->setCellValue('A1', 'INDICATOR PERFORMANCE AGGREGATE');
        $sheet->mergeCells('A1:I1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Add report period
        $periodText = 'Report Period: ';
        if ($filters['year'] !== 'All') {
            $periodText .= $filters['year'];
            if ($filters['semi_annual'] !== 'All') {
                $periodText .= ' - ' . $filters['semi_annual'];
            }
        } else {
            $periodText .= 'All Available Data';
        }

        $sheet->setCellValue('A2', $periodText);
        $sheet->mergeCells('A2:I2');
        $sheet->getStyle('A2')->getFont()->setBold(true);

        // Add headers
        $headers = [
            'A' => 'Strategic Objective',
            'B' => 'Indicator Number',
            'C' => 'Indicator Name',
            'D' => 'Total Clusters',
            'E' => 'Total Target',
            'F' => 'Total Actual',
            'G' => 'Achievement %',
            'H' => 'Status',
            'I' => 'Over Achieved',
        ];

        $row = 4;
        foreach ($headers as $col => $header) {
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $sheet->getStyle($col . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DDDDDD');
        }

        // Get data
        $data = $this->getIndicatorPerformanceAggregateData($filters);

        // Add data
        $row = 5;
        foreach ($data as $item) {
            $sheet->setCellValue('A' . $row, $item->so_number);
            $sheet->setCellValue('B' . $row, $item->indicator_number);
            $sheet->setCellValue('C' . $row, $item->indicator_name);
            $sheet->setCellValue('D' . $row, $item->cluster_count);
            $sheet->setCellValue('E' . $row, $item->total_target_value);
            $sheet->setCellValue('F' . $row, $item->total_actual_value);
            $sheet->setCellValue('G' . $row, round($item->avg_achievement_percent, 2) . '%');
            $sheet->setCellValue('H' . $row, $item->status);
            $sheet->setCellValue('I' . $row, $item->over_achieved ? 'Yes' : 'No');

            // Color the status cell based on status
            $statusColor = $this->statusColors[$item->status] ?? '#FFFFFF';
            $sheet->getStyle('H' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(substr($statusColor, 1));
            $sheet->getStyle('H' . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE));

            // Highlight over-achieved indicators
            if ($item->over_achieved) {
                $sheet->getStyle('I' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(substr($this->statusColors['Over Achieved'], 1));
                $sheet->getStyle('I' . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE));
            }

            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Add borders
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A4:I' . ($row - 1))->applyFromArray($styleArray);
    }

/**
 * Get data for cluster performance by strategic objective
 *
 * @param array $filters
 * @return \Illuminate\Support\Collection
 */
    private function getClusterPerformanceBySOData($filters)
    {
        $query = DB::table('vw_semi_annual_performance')
            ->select(
                'cluster_pk',
                'cluster_name',
                'so_pk',
                'so_number',
                'so_name'
            )
            ->selectRaw('
            COUNT(DISTINCT indicator_pk) as indicator_count,
            AVG(achievement_percent) as avg_achievement_percent,
            SUM(CASE WHEN status_label = "Needs Attention" THEN 1 ELSE 0 END) as needs_attention_count,
            SUM(CASE WHEN status_label = "In Progress" THEN 1 ELSE 0 END) as in_progress_count,
            SUM(CASE WHEN status_label = "On Track" THEN 1 ELSE 0 END) as on_track_count,
            SUM(CASE WHEN status_label = "Met" THEN 1 ELSE 0 END) as met_count,
            SUM(CASE WHEN comment = "Over Achieved" THEN 1 ELSE 0 END) as over_achieved_count
        ');

        // Apply filters
        if ($filters['year'] !== 'All') {
            $query->where('timeline_year', $filters['year']);
        }

        if ($filters['semi_annual'] !== 'All') {
            $query->where('semi_annual_label', $filters['semi_annual']);
        }

        if ($filters['cluster'] !== 'All') {
            $query->where('cluster_pk', $filters['cluster']);
        }

        if ($filters['strategic_objective'] !== 'All') {
            $query->where('so_pk', $filters['strategic_objective']);
        }

        $results = $query->groupBy('cluster_pk', 'cluster_name', 'so_pk', 'so_number', 'so_name')
            ->orderBy('cluster_name')
            ->orderBy('so_number')
            ->get();

        // Calculate status for each group
        foreach ($results as $item) {
            $item->status = $this->determineStatus($item->avg_achievement_percent);
        }

        return $results;
    }

/**
 * Get data for cluster performance by indicator
 *
 * @param array $filters
 * @return \Illuminate\Support\Collection
 */
    private function getClusterPerformanceByIndicatorData($filters)
    {
        $query = DB::table('vw_semi_annual_performance');

        // Apply filters
        if ($filters['year'] !== 'All') {
            $query->where('timeline_year', $filters['year']);
        }

        if ($filters['semi_annual'] !== 'All') {
            $query->where('semi_annual_label', $filters['semi_annual']);
        }

        if ($filters['cluster'] !== 'All') {
            $query->where('cluster_pk', $filters['cluster']);
        }

        if ($filters['strategic_objective'] !== 'All') {
            $query->where('so_pk', $filters['strategic_objective']);
        }

        $results = $query->select(
            'cluster_name',
            'so_number',
            'indicator_number',
            'indicator_name',
            'raw_target_value as target_value',
            'raw_actual_value as actual_value',
            'achievement_percent',
            'status_label as status',
            'comment'
        )
            ->orderBy('cluster_name')
            ->orderBy('so_number')
            ->orderBy('indicator_number')
            ->get();

        return $results;
    }

/**
 * Get data for strategic objective performance aggregate
 *
 * @param array $filters
 * @return \Illuminate\Support\Collection
 */
    private function getSOPerformanceAggregateData($filters)
    {
        $query = DB::table('vw_semi_annual_performance')
            ->select(
                'so_pk',
                'so_number',
                'so_name'
            )
            ->selectRaw('
            COUNT(DISTINCT indicator_pk) as indicator_count,
            COUNT(DISTINCT cluster_pk) as cluster_count,
            AVG(achievement_percent) as avg_achievement_percent,
            SUM(CASE WHEN status_label = "Needs Attention" THEN 1 ELSE 0 END) as needs_attention_count,
            SUM(CASE WHEN status_label = "In Progress" THEN 1 ELSE 0 END) as in_progress_count,
            SUM(CASE WHEN status_label = "On Track" THEN 1 ELSE 0 END) as on_track_count,
            SUM(CASE WHEN status_label = "Met" THEN 1 ELSE 0 END) as met_count,
            SUM(CASE WHEN comment = "Over Achieved" THEN 1 ELSE 0 END) as over_achieved_count
        ');

        // Apply filters
        if ($filters['year'] !== 'All') {
            $query->where('timeline_year', $filters['year']);
        }

        if ($filters['semi_annual'] !== 'All') {
            $query->where('semi_annual_label', $filters['semi_annual']);
        }

        if ($filters['cluster'] !== 'All') {
            $query->where('cluster_pk', $filters['cluster']);
        }

        if ($filters['strategic_objective'] !== 'All') {
            $query->where('so_pk', $filters['strategic_objective']);
        }

        $results = $query->groupBy('so_pk', 'so_number', 'so_name')
            ->orderBy('so_number')
            ->get();

        // Calculate status for each group
        foreach ($results as $item) {
            $item->status = $this->determineStatus($item->avg_achievement_percent);
        }

        return $results;
    }

/**
 * Get data for indicator performance aggregate
 *
 * @param array $filters
 * @return \Illuminate\Support\Collection
 */
    private function getIndicatorPerformanceAggregateData($filters)
    {
        $query = DB::table('vw_semi_annual_performance')
            ->select(
                'indicator_pk',
                'indicator_number',
                'indicator_name',
                'so_number'
            )
            ->selectRaw('
            COUNT(DISTINCT cluster_pk) as cluster_count,
            SUM(raw_target_value) as total_target_value,
            SUM(raw_actual_value) as total_actual_value,
            AVG(achievement_percent) as avg_achievement_percent
        ');

        // Apply filters
        if ($filters['year'] !== 'All') {
            $query->where('timeline_year', $filters['year']);
        }

        if ($filters['semi_annual'] !== 'All') {
            $query->where('semi_annual_label', $filters['semi_annual']);
        }

        if ($filters['cluster'] !== 'All') {
            $query->where('cluster_pk', $filters['cluster']);
        }

        if ($filters['strategic_objective'] !== 'All') {
            $query->where('so_pk', $filters['strategic_objective']);
        }

        $results = $query->groupBy('indicator_pk', 'indicator_number', 'indicator_name', 'so_number')
            ->orderBy('so_number')
            ->orderBy('indicator_number')
            ->get();

        // Calculate additional metrics for each indicator
        foreach ($results as $item) {
            // Determine status
            $item->status = $this->determineStatus($item->avg_achievement_percent);

            // Calculate if over achieved
            $item->over_achieved = false;
            if ($item->total_target_value > 0 && $item->total_actual_value > $item->total_target_value) {
                $item->over_achieved = true;
            }
        }

        return $results;
    }

/**
 * Helper method to determine status based on achievement percentage
 *
 * @param float $achievementPercent
 * @return string
 */
// private function determineStatus($achievementPercent)
// {
//     if ($achievementPercent === null) {
//         return 'Unknown';
//     }

//     if ($achievementPercent < 10) {
//         return 'Needs Attention';
//     } elseif ($achievementPercent < 50) {
//         return 'In Progress';
//     } elseif ($achievementPercent < 90) {
//         return 'On Track';
//     } else {
//         return 'Met';
//     }
// }

}