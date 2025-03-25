<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClusterCompletenessReportController extends Controller
{
    /**
     * Display the filter form for cluster completeness report
     *
     * @return \Illuminate\View\View
     */
    public function showFilterPage()
    {
        try {
            // Get all years from the view
            $years = DB::table('vw_cluster_completeness_summary')
                ->select('timeline_year')
                ->distinct()
                ->orderBy('timeline_year', 'desc')
                ->pluck('timeline_year')
                ->toArray();

            // If no years found, provide a specific error
            if (empty($years)) {
                Log::warning('No reporting periods found in the database.');
                return view('scrn', [
                    'Page'     => 'Completness.filter',
                    'error'    => 'No reporting periods found in the database. Please check the reporting periods.',
                    'years'    => [],
                    'clusters' => [],
                ]);
            }

            // Get all clusters from the view
            $clusters = DB::table('vw_cluster_completeness_summary')
                ->select('cluster_pk', 'cluster_text_identifier', 'cluster_name')
                ->distinct()
                ->orderBy('cluster_name')
                ->get();

            return view('scrn', [
                'Page'     => 'Completness.filter',
                'years'    => $years,
                'clusters' => $clusters,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in completeness filter: ' . $e->getMessage());
            return view('scrn', [
                'Page'     => 'Completness.filter',
                'error'    => 'An error occurred while loading filter data: ' . $e->getMessage(),
                'years'    => [],
                'clusters' => [],
            ]);
        }
    }

    public function generateCompletenessReport(Request $request)
    {
        return $this->generateReport($request);
    }
    /**
     * Generate and display the cluster completeness report
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function generateReport($request)
    {
        try {
            $year            = $request->input('year');
            $clusterPks      = $request->input('cluster_pk', []);
            $minCompleteness = $request->input('min_completeness');
            $maxCompleteness = $request->input('max_completeness');

            // Validate year input
            if (empty($year)) {
                return redirect()->route('completeness.filter')
                    ->with('error', 'Please select a reporting year.');
            }

            // Build the query for the completeness report
            $query = DB::table('vw_cluster_completeness_summary')
                ->where('timeline_year', $year);

            // Apply cluster filter if provided
            if (! empty($clusterPks)) {
                $query->whereIn('cluster_pk', $clusterPks);
            }

            // Apply completeness range filters if provided
            if (! empty($minCompleteness)) {
                $query->where('completeness_percentage', '>=', $minCompleteness);
            }

            if (! empty($maxCompleteness)) {
                $query->where('completeness_percentage', '<=', $maxCompleteness);
            }

            // Get the report data
            $reportData = $query->orderBy('completeness_percentage', 'desc')
                ->get();

            // If no data found, return with a message
            if ($reportData->isEmpty()) {
                return view('scrn', [
                    'Page'       => 'Completness.report',
                    'error'      => 'No data found for the selected filters.',
                    'year'       => $year,
                    'reportData' => [],
                    'summary'    => null,
                ]);
            }

            // Calculate summary statistics
            $summary = [
                'totalClusters'       => $reportData->unique('cluster_pk')->count(),
                'averageCompleteness' => round($reportData->avg('completeness_percentage'), 2),
                'fullCompleteness'    => $reportData->where('completeness_percentage', 100)->count(),
                'lowCompleteness'     => $reportData->where('completeness_percentage', '<', 50)->count(),
            ];

            return view('scrn', [
                'Page'       => 'Completness.report',
                'year'       => $year,
                'reportData' => $reportData,
                'summary'    => $summary,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in completeness report: ' . $e->getMessage());
            return view('scrn', [
                'Page'       => 'Completness.report',
                'error'      => 'An error occurred while generating the report: ' . $e->getMessage(),
                'year'       => $year ?? null,
                'reportData' => [],
                'summary'    => null,
            ]);
        }
    }

    /**
     * Display detailed report for a specific cluster
     *
     * @param Request $request
     * @param int $clusterPk
     * @param int $year
     * @return \Illuminate\View\View
     */
    public function showDetailPage(Request $request, $clusterPk, $year)
    {
        try {
            // Get cluster information
            $cluster = DB::table('vw_cluster_completeness_summary')
                ->select('cluster_pk', 'cluster_text_identifier', 'cluster_name', 'cluster_description')
                ->where('cluster_pk', $clusterPk)
                ->first();

            if (! $cluster) {
                return redirect()->route('completeness.filter')
                    ->with('error', 'Cluster not found.');
            }

            // Build the query for the completeness detail
            $query = DB::table('vw_cluster_completeness_summary')
                ->where('cluster_pk', $clusterPk)
                ->where('timeline_year', $year);

            // Get the detail data
            $detailData = $query->orderBy('timeline_quarter')
                ->get();

            // If no data found, return with a message
            if ($detailData->isEmpty()) {
                return view('scrn', [
                    'Page'       => 'Completness.detail',
                    'error'      => 'No data found for the selected cluster and year.',
                    'cluster'    => $cluster,
                    'year'       => $year,
                    'detailData' => [],
                ]);
            }

            return view('scrn', [
                'Page'       => 'Completness.detail',
                'cluster'    => $cluster,
                'year'       => $year,
                'detailData' => $detailData,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in completeness detail: ' . $e->getMessage());
            return view('scrn', [
                'Page'       => 'Completness.detail',
                'error'      => 'An error occurred while loading detail data: ' . $e->getMessage(),
                'cluster'    => null,
                'year'       => $year,
                'detailData' => [],
            ]);
        }
    }

    /**
     * Compare completeness between clusters
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function compareReports(Request $request)
    {
        try {
            $year       = $request->input('year');
            $clusterPks = $request->input('cluster_pk', []);

            // Validate inputs
            if (empty($year)) {
                return redirect()->route('completeness.filter')
                    ->with('error', 'Please select a reporting year.');
            }

            if (count($clusterPks) < 2) {
                return redirect()->route('completeness.filter')
                    ->with('error', 'Please select at least two clusters to compare.');
            }

            // Build the query for the comparison
            $query = DB::table('vw_cluster_completeness_summary')
                ->where('timeline_year', $year)
                ->whereIn('cluster_pk', $clusterPks);

            // Get the comparison data
            $comparisonData = $query->orderBy('cluster_name')
                ->orderBy('timeline_quarter')
                ->get();

            // If no data found, return with a message
            if ($comparisonData->isEmpty()) {
                return view('scrn', [
                    'Page'           => 'Completness.compare',
                    'error'          => 'No data found for the selected clusters and year.',
                    'year'           => $year,
                    'comparisonData' => [],
                    'clusters'       => [],
                ]);
            }

            // Get cluster information
            $clusters = DB::table('vw_cluster_completeness_summary')
                ->select('cluster_pk', 'cluster_name')
                ->whereIn('cluster_pk', $clusterPks)
                ->distinct()
                ->get();

            return view('scrn', [
                'Page'           => 'Completness.compare',
                'year'           => $year,
                'comparisonData' => $comparisonData,
                'clusters'       => $clusters,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in completeness compare: ' . $e->getMessage());
            return view('scrn', [
                'Page'           => 'Completness.compare',
                'error'          => 'An error occurred while generating the comparison: ' . $e->getMessage(),
                'year'           => $year ?? null,
                'comparisonData' => [],
                'clusters'       => [],
            ]);
        }
    }

    /**
     * Display dashboard with summary statistics
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function showDashboard(Request $request)
    {
        try {
            $year = $request->input('year', date('Y'));

            // Get all years for the filter
            $years = DB::table('vw_cluster_completeness_summary')
                ->select('timeline_year')
                ->distinct()
                ->orderBy('timeline_year', 'desc')
                ->pluck('timeline_year')
                ->toArray();

            // Get overall completeness by quarter
            $quarterlyData = DB::table('vw_cluster_completeness_summary')
                ->select(
                    'timeline_year',
                    'timeline_quarter',
                    DB::raw('COUNT(DISTINCT cluster_pk) as total_clusters'),
                    DB::raw('SUM(total_indicators) as total_indicators'),
                    DB::raw('SUM(reported_indicators) as reported_indicators'),
                    DB::raw('SUM(not_reported_indicators) as not_reported_indicators'),
                    DB::raw('ROUND(SUM(reported_indicators) * 100.0 / SUM(total_indicators), 2) as overall_completeness')
                )
                ->where('timeline_year', $year)
                ->groupBy('timeline_year', 'timeline_quarter')
                ->orderBy('timeline_quarter')
                ->get();

            // Get completeness by cluster
            $clusterData = DB::table('vw_cluster_completeness_summary')
                ->select(
                    'cluster_pk',
                    'cluster_name',
                    DB::raw('SUM(total_indicators) as total_indicators'),
                    DB::raw('SUM(reported_indicators) as reported_indicators'),
                    DB::raw('SUM(not_reported_indicators) as not_reported_indicators'),
                    DB::raw('ROUND(SUM(reported_indicators) * 100.0 / SUM(total_indicators), 2) as overall_completeness')
                )
                ->where('timeline_year', $year)
                ->groupBy('cluster_pk', 'cluster_name')
                ->orderBy('overall_completeness', 'desc')
                ->get();

            return view('scrn', [
                'Page'          => 'Completness.dashboard',
                'year'          => $year,
                'years'         => $years,
                'quarterlyData' => $quarterlyData,
                'clusterData'   => $clusterData,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in completeness dashboard: ' . $e->getMessage());
            return view('scrn', [
                'Page'          => 'Completness.dashboard',
                'error'         => 'An error occurred while loading dashboard data: ' . $e->getMessage(),
                'year'          => $year ?? date('Y'),
                'years'         => [],
                'quarterlyData' => [],
                'clusterData'   => [],
            ]);
        }
    }

    public function exportCompletenessReport(Request $request)
    {
        return $this->exportToCsv($request);
        return $this->exportToPdf($request);
    }

    /**
     * Export report data to CSV
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportToCsv($request)
    {
        try {
            $year       = $request->input('year');
            $clusterPks = $request->input('cluster_pk', []);

            // Validate year input
            if (empty($year)) {
                return redirect()->route('completeness.filter')
                    ->with('error', 'Please select a reporting year.');
            }

            // Build the query for the report data
            $query = DB::table('vw_cluster_completeness_summary')
                ->where('timeline_year', $year);

            // Apply cluster filter if provided
            if (! empty($clusterPks)) {
                $query->whereIn('cluster_pk', $clusterPks);
            }

            // Get the report data
            $reportData = $query->orderBy('cluster_name')
                ->orderBy('timeline_quarter')
                ->get();

            // If no data found, return with a message
            if ($reportData->isEmpty()) {
                return redirect()->route('completeness.filter')
                    ->with('error', 'No data found for the selected filters.');
            }

            $filename = 'cluster_completeness_report_' . $year . '_' . date('Ymd_His') . '.csv';
            $headers  = [
                'Content-Type'        => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function () use ($reportData) {
                $file = fopen('php://output', 'w');

                // Add header row
                fputcsv($file, [
                    'Cluster',
                    'Timeline',
                    'Year',
                    'Quarter',
                    'Total Indicators',
                    'Reported Indicators',
                    'Not Reported Indicators',
                    'Completeness %',
                ]);

                // Add data rows
                foreach ($reportData as $row) {
                    fputcsv($file, [
                        $row->cluster_name,
                        $row->timeline_name,
                        $row->timeline_year,
                        $row->timeline_quarter,
                        $row->total_indicators,
                        $row->reported_indicators,
                        $row->not_reported_indicators,
                        $row->completeness_percentage,
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            Log::error('Error exporting to CSV: ' . $e->getMessage());
            return redirect()->route('completeness.filter')
                ->with('error', 'An error occurred while exporting to CSV: ' . $e->getMessage());
        }
    }

    /**
     * Export report data to PDF
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function exportToPdf($request)
    {
        try {
            $year       = $request->input('year');
            $clusterPks = $request->input('cluster_pk', []);

            // Validate year input
            if (empty($year)) {
                return redirect()->route('completeness.filter')
                    ->with('error', 'Please select a reporting year.');
            }

            // Build the query for the report data
            $query = DB::table('vw_cluster_completeness_summary')
                ->where('timeline_year', $year);

            // Apply cluster filter if provided
            if (! empty($clusterPks)) {
                $query->whereIn('cluster_pk', $clusterPks);
            }

            // Get the report data
            $reportData = $query->orderBy('cluster_name')
                ->orderBy('timeline_quarter')
                ->get();

            // If no data found, return with a message
            if ($reportData->isEmpty()) {
                return redirect()->route('completeness.filter')
                    ->with('error', 'No data found for the selected filters.');
            }

            // Calculate summary statistics
            $summary = [
                'totalClusters'       => $reportData->unique('cluster_pk')->count(),
                'averageCompleteness' => round($reportData->avg('completeness_percentage'), 2),
                'fullCompleteness'    => $reportData->where('completeness_percentage', 100)->count(),
                'lowCompleteness'     => $reportData->where('completeness_percentage', '<', 50)->count(),
            ];

            // Return view for PDF generation
            // Note: In a real application, you would use a PDF library to generate the PDF
            return view('scrn', [
                'Page'        => 'Completness.pdf',
                'reportData'  => $reportData,
                'summary'     => $summary,
                'year'        => $year,
                'generatedAt' => now()->format('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            Log::error('Error exporting to PDF: ' . $e->getMessage());
            return redirect()->route('completeness.filter')
                ->with('error', 'An error occurred while exporting to PDF: ' . $e->getMessage());
        }
    }
}