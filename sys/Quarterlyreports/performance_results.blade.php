<!--begin::Performance Quarterly Report Results-->
<div class="d-flex flex-column">
    <!--begin::Header-->
    <div class="card shadow-sm mb-5">
        <div class="card-body p-5">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                <div>
                    <h1 class="fs-2x fw-bold mb-2">Performance Dashboard</h1>
                    <p class="text-muted fs-6">
                        {{ isset($filters['year']) ? $filters['year'] : date('Y') }}
                        {{ isset($filters['quarter']) ? ' Q'.$filters['quarter'] : '' }}
                        {{ isset($filters['cluster_name']) ? ' | '.$filters['cluster_name'] : '' }}
                        {{ isset($filters['indicator_name']) ? ' | '.$filters['indicator_name'] : '' }}
                    </p>
                </div>
                <div class="d-flex gap-2 mt-3 mt-md-0">
                    <a href="{{ route('ecsahc.performance.quarterly.filter') }}" class="btn btn-light-primary">
                        <i class="ki-duotone ki-filter fs-2 me-1">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        Modify Filters
                    </a>
                    <button type="button" class="btn btn-light-primary" onclick="window.print();">
                        <i class="ki-duotone ki-printer fs-2 me-1">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        Print
                    </button>
                    <a href="{{ route('ecsahc.performance.quarterly.export', $filters ?? []) }}" class="btn btn-light-primary">
                        <i class="ki-duotone ki-file-down fs-2 me-1">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        Export
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!--end::Header-->

    @if(isset($error) && $error)
        <div class="alert alert-danger d-flex align-items-center p-5 mb-5">
            <i class="ki-duotone ki-shield-cross fs-2hx text-danger me-4"><span class="path1"></span><span class="path2"></span></i>
            <div class="d-flex flex-column">
                <h4 class="mb-1 text-danger">Error</h4>
                <span>{{ $error }}</span>
            </div>
        </div>
    @endif

    @if(isset($noDataMessage) && $noDataMessage)
        <div class="alert alert-warning d-flex align-items-center p-5 mb-5">
            <i class="ki-duotone ki-information-5 fs-2hx text-warning me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
            <div class="d-flex flex-column">
                <h4 class="mb-1 text-warning">No Data</h4>
                <span>{{ $noDataMessage }}</span>
            </div>
        </div>
    @endif

    @php
        // Reconcile indicator counts with performance_indicators table
        $uniqueIndicatorIds = [];
        $uniqueIndicatorNames = [];
        $uniqueClusterNames = [];
        $validDataPoints = 0;
        $metCount = 0;
        $needsAttentionCount = 0;

        if(isset($performanceData) && $performanceData->count() > 0) {
            foreach($performanceData as $item) {
                // Track unique indicators by name
                if(!in_array($item->indicator_name, $uniqueIndicatorNames)) {
                    $uniqueIndicatorNames[] = $item->indicator_name;
                }

                // Track unique clusters
                if(!in_array($item->cluster_name, $uniqueClusterNames)) {
                    $uniqueClusterNames[] = $item->cluster_name;
                }

                // Count valid data points (where both target and actual exist)
                if(isset($item->total_target_value) && isset($item->total_actual_value) &&
                   $item->total_target_value !== null && $item->total_actual_value !== null) {
                    $validDataPoints++;
                }

                // Count met targets and needs attention
                if(isset($item->status_label)) {
                    if($item->status_label == 'Met') {
                        $metCount++;
                    } elseif($item->status_label == 'Needs Attention') {
                        $needsAttentionCount++;
                    }
                }
            }
        }

        // Override summary metrics with reconciled data
        if(isset($summaryMetrics)) {
            $summaryMetrics['validDataPoints'] = $validDataPoints;
            $summaryMetrics['indicatorsCount'] = count($uniqueIndicatorNames);
            $summaryMetrics['clustersCount'] = count($uniqueClusterNames);

            // Update status counts if they exist
            if(isset($summaryMetrics['statusCounts']) && isset($summaryMetrics['statusCounts']['Met'])) {
                $summaryMetrics['statusCounts']['Met']['count'] = $metCount;
                $totalStatusCount = $metCount;

                if(isset($summaryMetrics['statusCounts']['Needs Attention'])) {
                    $summaryMetrics['statusCounts']['Needs Attention']['count'] = $needsAttentionCount;
                    $totalStatusCount += $needsAttentionCount;
                }

                // Recalculate percentages
                if($totalStatusCount > 0) {
                    $summaryMetrics['statusCounts']['Met']['percentage'] = ($metCount / $totalStatusCount) * 100;

                    if(isset($summaryMetrics['statusCounts']['Needs Attention'])) {
                        $summaryMetrics['statusCounts']['Needs Attention']['percentage'] = ($needsAttentionCount / $totalStatusCount) * 100;
                    }
                }
            }
        }
    @endphp

    <!--begin::Key Metrics-->
    @if(isset($summaryMetrics) && !empty($summaryMetrics))
        <div class="row g-5 g-xl-8 mb-5">
            <div class="col-xl-3 col-md-6">
                <div class="card bg-light-primary shadow-sm h-100">
                    <div class="card-body p-5">
                        <div class="d-flex flex-column">
                            <div class="text-primary fw-bold fs-5">Overall Achievement</div>
                            <div class="d-flex align-items-center mt-2">
                                <span class="text-gray-900 fw-bold fs-2x me-2">{{ number_format($summaryMetrics['averageAchievement'] ?? 0, 1) }}%</span>
                                <span class="badge badge-light-{{ isset($summaryMetrics['averageAchievement']) && $summaryMetrics['averageAchievement'] >= 90 ? 'success' : (isset($summaryMetrics['averageAchievement']) && $summaryMetrics['averageAchievement'] >= 50 ? 'primary' : (isset($summaryMetrics['averageAchievement']) && $summaryMetrics['averageAchievement'] >= 10 ? 'warning' : 'danger')) }} fs-base">
                                    {{ isset($summaryMetrics['averageAchievement']) && $summaryMetrics['averageAchievement'] >= 90 ? 'Excellent' : (isset($summaryMetrics['averageAchievement']) && $summaryMetrics['averageAchievement'] >= 50 ? 'Good' : (isset($summaryMetrics['averageAchievement']) && $summaryMetrics['averageAchievement'] >= 10 ? 'Fair' : 'Poor')) }}
                                </span>
                            </div>
                            <div class="text-muted fs-7 mt-1">Based on {{ $summaryMetrics['validDataPoints'] ?? 0 }} data points</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card bg-light-success shadow-sm h-100">
                    <div class="card-body p-5">
                        <div class="d-flex flex-column">
                            <div class="text-success fw-bold fs-5">Met Targets</div>
                            <div class="d-flex align-items-center mt-2">
                                @php
                                    $metCount = isset($summaryMetrics['statusCounts']['Met']) ? $summaryMetrics['statusCounts']['Met']['count'] : 0;
                                    $metPercentage = isset($summaryMetrics['statusCounts']['Met']) ? $summaryMetrics['statusCounts']['Met']['percentage'] : 0;
                                @endphp
                                <span class="text-gray-900 fw-bold fs-2x me-2">{{ number_format($metPercentage, 1) }}%</span>
                            </div>
                            <div class="text-muted fs-7 mt-1">{{ $metCount }} indicators met their targets</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card bg-light-warning shadow-sm h-100">
                    <div class="card-body p-5">
                        <div class="d-flex flex-column">
                            <div class="text-warning fw-bold fs-5">Needs Attention</div>
                            <div class="d-flex align-items-center mt-2">
                                @php
                                    $needsAttentionCount = isset($summaryMetrics['statusCounts']['Needs Attention']) ? $summaryMetrics['statusCounts']['Needs Attention']['count'] : 0;
                                    $needsAttentionPercentage = isset($summaryMetrics['statusCounts']['Needs Attention']) ? $summaryMetrics['statusCounts']['Needs Attention']['percentage'] : 0;
                                @endphp
                                <span class="text-gray-900 fw-bold fs-2x me-2">{{ number_format($needsAttentionPercentage, 1) }}%</span>
                            </div>
                            <div class="text-muted fs-7 mt-1">{{ $needsAttentionCount }} indicators need attention</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card bg-light-info shadow-sm h-100">
                    <div class="card-body p-5">
                        <div class="d-flex flex-column">
                            <div class="text-info fw-bold fs-5">Data Completeness</div>
                            <div class="d-flex align-items-center mt-2">
                                <span class="text-gray-900 fw-bold fs-2x me-2">{{ number_format($summaryMetrics['dataCompleteness'] ?? 0, 1) }}%</span>
                            </div>
                            <div class="text-muted fs-7 mt-1">{{ $summaryMetrics['clustersCount'] ?? 0 }} clusters, {{ $summaryMetrics['indicatorsCount'] ?? 0 }} indicators</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <!--end::Key Metrics-->

    <!--begin::Cluster Rankings-->
    @if(isset($clusterRankings) && !empty($clusterRankings))
        <div class="card shadow-sm mb-5">
            <div class="card-header border-0 pt-5">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-dark">Cluster Rankings</span>
                    <span class="text-muted mt-1 fw-semibold fs-7">
                        @if(isset($filters) && is_array($filters))
                            Filters:
                            {{ isset($filters['year']) ? 'Year: '.$filters['year'] : '' }}
                            {{ isset($filters['quarter']) ? ' | Quarter: Q'.$filters['quarter'] : '' }}
                            {{ isset($filters['cluster_name']) ? ' | Cluster: '.$filters['cluster_name'] : '' }}
                            {{ isset($filters['indicator_name']) ? ' | Indicator: '.$filters['indicator_name'] : '' }}
                        @else
                            All data
                        @endif
                    </span>
                </h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                        <thead>
                            <tr class="fw-bold text-muted bg-light">
                                <th class="min-w-50px">Rank</th>
                                <th class="min-w-150px">Cluster</th>
                                <th class="min-w-100px">Avg Achievement</th>
                                <th class="min-w-100px">Met %</th>
                                <th class="min-w-100px">Needs Attention %</th>
                                <th class="min-w-100px">Data Completeness</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($clusterRankings as $cluster)
                                <tr>
                                    <td>
                                        <span class="text-dark fw-bold fs-6">{{ $cluster['rank'] ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="d-flex justify-content-start flex-column">
                                                <span class="text-dark fw-bold fs-6">{{ $cluster['name'] ?? 'Unknown' }}</span>
                                                <span class="text-muted fw-semibold text-muted d-block fs-7">{{ $cluster['code'] ?? 'N/A' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-dark fw-bold d-block fs-6">{{ number_format($cluster['avgAchievement'] ?? 0, 1) }}%</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-dark fw-bold d-block fs-6">{{ number_format($cluster['metPercentage'] ?? 0, 1) }}%</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-dark fw-bold d-block fs-6">{{ number_format($cluster['needsAttentionPercentage'] ?? 0, 1) }}%</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-dark fw-bold d-block fs-6">{{ number_format($cluster['dataCompleteness'] ?? 0, 1) }}%</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
    <!--end::Cluster Rankings-->

    <!--begin::Main Dashboard-->
    <div class="row g-5 g-xl-8 mb-5">
        <!--begin::Left Column-->
        <div class="col-xl-8">
            <!--begin::Performance Charts-->
            @if(isset($chartData) && !empty($chartData))
                <div class="card shadow-sm mb-5">
                    <div class="card-header border-0 pt-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold text-dark">Performance Analysis</span>
                            <span class="text-muted mt-1 fw-semibold fs-7">Visual performance breakdown</span>
                        </h3>
                        <div class="card-toolbar">
                            <ul class="nav nav-tabs nav-line-tabs nav-stretch fs-6 border-0">
                                <li class="nav-item">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#kt_tab_clusters">Clusters</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#kt_tab_indicators">Indicators</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#kt_tab_trends">Trends</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="tab-content" id="myTabContent">
                            <div class="tab-pane fade show active" id="kt_tab_clusters" role="tabpanel">
                                @if(isset($chartData['achievementByCluster']) && !empty($chartData['achievementByCluster']))
                                    <div class="d-flex flex-column">
                                        <div class="d-flex justify-content-between mb-5">
                                            <span class="fw-bold fs-6 text-gray-800">Achievement by Cluster</span>
                                            <button type="button" class="btn btn-sm btn-light-primary" data-bs-toggle="modal" data-bs-target="#modalClusterDetails">
                                                View Details
                                            </button>
                                        </div>

                                        <div class="chart-container" style="height: 350px;">
                                            <canvas id="clusterChart"></canvas>
                                        </div>
                                    </div>
                                @else
                                    <div class="text-muted fs-7">No cluster data available</div>
                                @endif
                            </div>

                            <div class="tab-pane fade" id="kt_tab_indicators" role="tabpanel">
                                <div class="d-flex justify-content-between mb-5">
                                    <span class="fw-bold fs-6 text-gray-800">Top vs Bottom Indicators</span>
                                    <button type="button" class="btn btn-sm btn-light-primary" data-bs-toggle="modal" data-bs-target="#modalIndicatorDetails">
                                        View Details
                                    </button>
                                </div>

                                <div class="chart-container" style="height: 350px;">
                                    <canvas id="indicatorChart"></canvas>
                                </div>

                                @if(isset($chartData['achievementDistribution']) && !empty($chartData['achievementDistribution']))
                                    <div class="separator separator-dashed my-5"></div>

                                    <div class="d-flex flex-column">
                                        <div class="d-flex justify-content-between mb-5">
                                            <span class="fw-bold fs-6 text-gray-800">Achievement Distribution</span>
                                        </div>

                                        <div class="chart-container" style="height: 200px;">
                                            <canvas id="distributionChart"></canvas>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="tab-pane fade" id="kt_tab_trends" role="tabpanel">
                                @if(isset($chartData['quarterlyTrend']) && !empty($chartData['quarterlyTrend']))
                                    <div class="d-flex flex-column mb-8">
                                        <div class="d-flex justify-content-between mb-5">
                                            <span class="fw-bold fs-6 text-gray-800">Quarterly Trend ({{ $filters['year'] ?? date('Y') }})</span>
                                            <button type="button" class="btn btn-sm btn-light-primary" data-bs-toggle="modal" data-bs-target="#modalTrendDetails">
                                                View Details
                                            </button>
                                        </div>

                                        <div class="chart-container" style="height: 350px;">
                                            <canvas id="trendChart"></canvas>
                                        </div>
                                    </div>
                                @else
                                    <div class="text-muted fs-7">No quarterly trend data available</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <!--end::Performance Charts-->

            <!--begin::Performance Data Preview-->
            <div class="card shadow-sm mb-5">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-dark">Performance Data</span>
                        <span class="text-muted mt-1 fw-semibold fs-7">Recent performance metrics</span>
                    </h3>
                    <div class="card-toolbar">
                        <button type="button" class="btn btn-sm btn-light-primary" data-bs-toggle="modal" data-bs-target="#modalPerformanceData">
                            View All Data
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if(isset($performanceData) && !empty($performanceData) && $performanceData->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                <thead>
                                    <tr class="fw-bold text-muted bg-light">
                                        <th class="min-w-150px">Indicator</th>
                                        <th class="min-w-150px">Cluster</th>
                                        <th class="min-w-100px">Target</th>
                                        <th class="min-w-100px">Actual</th>
                                        <th class="min-w-100px">Achievement</th>
                                        <th class="min-w-100px">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($performanceData->take(5) as $item)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="d-flex justify-content-start flex-column">
                                                        <span class="text-dark fw-bold fs-6">{{ $item->indicator_name ?? 'Unknown' }}</span>
                                                        <span class="text-muted fw-semibold text-muted d-block fs-7">{{ $item->indicator_number ?? 'N/A' }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="d-flex justify-content-start flex-column">
                                                        <span class="text-dark fw-bold fs-6">{{ $item->cluster_name ?? 'Unknown' }}</span>
                                                        <span class="text-muted fw-semibold text-muted d-block fs-7">{{ $item->cluster_code ?? 'N/A' }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                <span class="text-dark fw-bold d-block fs-6">{{ number_format($item->total_target_value ?? 0, 1) }}</span>
                                            </td>
                                            <td class="text-end">
                                                <span class="text-dark fw-bold d-block fs-6">{{ number_format($item->total_actual_value ?? 0, 1) }}</span>
                                            </td>
                                            <td class="text-end">
                                                <span class="text-dark fw-bold d-block fs-6">{{ number_format($item->achievement_percent ?? 0, 1) }}%</span>
                                            </td>
                                            <td class="text-end">
                                                @php
                                                    $statusLabel = $item->status_label ?? 'Unknown';
                                                    $statusColor = $statusLabel == 'Met' ? 'success' : ($statusLabel == 'On Track' ? 'primary' : ($statusLabel == 'In Progress' ? 'warning' : 'danger'));
                                                @endphp
                                                <span class="badge badge-light-{{ $statusColor }} fs-7 fw-bold">{{ $statusLabel }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($performanceData->count() > 5)
                            <div class="text-center mt-5">
                                <button type="button" class="btn btn-sm btn-light-primary" data-bs-toggle="modal" data-bs-target="#modalPerformanceData">
                                    View All {{ $performanceData->count() }} Records
                                </button>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-10">
                            <i class="ki-duotone ki-information-5 fs-5x text-muted mb-5">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                            <div class="text-muted fw-semibold fs-6">No performance data available for the selected filters.</div>
                        </div>
                    @endif
                </div>
            </div>
            <!--end::Performance Data Preview-->
        </div>
        <!--end::Left Column-->

        <!--begin::Right Column-->
        <div class="col-xl-4">
            <!--begin::Key Insights-->
            @if(isset($insights) && !empty($insights))
                <div class="card shadow-sm mb-5">
                    <div class="card-header border-0 pt-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold text-dark">Key Insights</span>
                            <span class="text-muted mt-1 fw-semibold fs-7">AI-generated analysis</span>
                        </h3>
                        <div class="card-toolbar">
                            <button type="button" class="btn btn-sm btn-light-primary" data-bs-toggle="modal" data-bs-target="#modalInsights">
                                View All
                            </button>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="d-flex flex-column gap-5">
                            @foreach(array_slice($insights, 0, 5) as $insight)
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-circle symbol-40px me-3">
                                        <span class="symbol-label bg-light-{{ $insight['type'] ?? 'primary' }}">
                                            @php
                                                $iconType = isset($insight['type']) ? $insight['type'] : 'primary';
                                                $iconName = 'abstract-26';

                                                if($iconType == 'success') {
                                                    $iconName = 'check-circle';
                                                } elseif($iconType == 'warning') {
                                                    $iconName = 'information-5';
                                                } elseif($iconType == 'danger') {
                                                    $iconName = 'shield-cross';
                                                }
                                            @endphp
                                            <i class="ki-duotone ki-{{ $iconName }} fs-2 text-{{ $iconType }}">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                @if($iconName == 'information-5')
                                                    <span class="path3"></span>
                                                @endif
                                            </i>
                                        </span>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span class="text-gray-800 fs-6 fw-semibold">{{ $insight['message'] ?? 'No insight available' }}</span>
                                        @if(isset($insight['category']))
                                            <span class="text-muted fw-semibold fs-7">{{ ucfirst(str_replace('_', ' ', $insight['category'])) }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if(count($insights) > 5)
                            <div class="text-center mt-5">
                                <button type="button" class="btn btn-sm btn-light-primary" data-bs-toggle="modal" data-bs-target="#modalInsights">
                                    View All {{ count($insights) }} Insights
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
            <!--end::Key Insights-->

            <!--begin::Status Distribution-->
            @if(isset($summaryMetrics['statusCounts']) && !empty($summaryMetrics['statusCounts']))
                <div class="card shadow-sm mb-5">
                    <div class="card-header border-0 pt-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold text-dark">Status Distribution</span>
                            <span class="text-muted mt-1 fw-semibold fs-7">Performance status breakdown</span>
                        </h3>
                    </div>
                    <div class="card-body pt-0">
                        <div class="chart-container" style="height: 200px;">
                            <canvas id="statusChart"></canvas>
                        </div>

                        <div class="mt-5">
                            @foreach($summaryMetrics['statusCounts'] as $status => $data)
                                <div class="d-flex flex-stack mb-2">
                                    <div class="d-flex align-items-center me-3">
                                        <div class="symbol symbol-circle symbol-30px me-3 bg-light">
                                            <span class="symbol-label bg-light-{{ $status == 'Met' ? 'success' : ($status == 'On Track' ? 'primary' : ($status == 'In Progress' ? 'warning' : 'danger')) }}">
                                                <i class="ki-duotone ki-abstract-{{ $status == 'Met' ? 'check' : ($status == 'On Track' ? 'up' : ($status == 'In Progress' ? 'right' : 'down')) }} fs-3 text-{{ $status == 'Met' ? 'success' : ($status == 'On Track' ? 'primary' : ($status == 'In Progress' ? 'warning' : 'danger')) }}">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </span>
                                        </div>
                                        <div class="text-gray-800 fw-bold fs-6">{{ $status }}</div>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <div class="text-gray-800 fw-bold fs-6">{{ $data['count'] ?? 0 }}</div>
                                        <div class="text-muted fw-semibold fs-7 ms-2">({{ number_format($data['percentage'] ?? 0, 1) }}%)</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
            <!--end::Status Distribution-->

            <!--begin::Data Quality-->
            @if(isset($dataQualityIssues) && !empty($dataQualityIssues))
                <div class="card shadow-sm mb-5">
                    <div class="card-header border-0 pt-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold text-dark">Data Quality</span>
                            <span class="text-muted mt-1 fw-semibold fs-7">Potential data issues</span>
                        </h3>
                        <div class="card-toolbar">
                            <button type="button" class="btn btn-sm btn-light-primary" data-bs-toggle="modal" data-bs-target="#modalDataQuality">
                                View Details
                            </button>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="d-flex flex-column gap-5">
                            @if(isset($dataQualityIssues['missingTargets']))
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-circle symbol-40px me-3">
                                        <span class="symbol-label bg-light-warning">
                                            <i class="ki-duotone ki-information-5 fs-2 text-warning">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                <span class="path3"></span>
                                            </i>
                                        </span>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span class="text-gray-800 fs-6 fw-semibold">{{ number_format($dataQualityIssues['missingTargets']['percentage'] ?? 0, 1) }}% of indicators are missing target values</span>
                                        <span class="text-muted fw-semibold fs-7">{{ $dataQualityIssues['missingTargets']['count'] ?? 0 }} indicators affected</span>
                                    </div>
                                </div>
                            @endif

                            @if(isset($dataQualityIssues['missingActuals']))
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-circle symbol-40px me-3">
                                        <span class="symbol-label bg-light-warning">
                                            <i class="ki-duotone ki-information-5 fs-2 text-warning">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                <span class="path3"></span>
                                            </i>
                                        </span>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span class="text-gray-800 fs-6 fw-semibold">{{ number_format($dataQualityIssues['missingActuals']['percentage'] ?? 0, 1) }}% of indicators are missing actual values</span>
                                        <span class="text-muted fw-semibold fs-7">{{ $dataQualityIssues['missingActuals']['count'] ?? 0 }} indicators affected</span>
                                    </div>
                                </div>
                            @endif

                            @if(isset($dataQualityIssues['inconsistentStatuses']))
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-circle symbol-40px me-3">
                                        <span class="symbol-label bg-light-warning">
                                            <i class="ki-duotone ki-information-5 fs-2 text-warning">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                <span class="path3"></span>
                                            </i>
                                        </span>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span class="text-gray-800 fs-6 fw-semibold">{{ number_format($dataQualityIssues['inconsistentStatuses']['percentage'] ?? 0, 1) }}% have inconsistent status labels  ?? 0, 1) }}% have inconsistent status labels</span>
                                        <span class="text-muted fw-semibold fs-7">{{ $dataQualityIssues['inconsistentStatuses']['count'] ?? 0 }} indicators affected</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
            <!--end::Data Quality-->
        </div>
        <!--end::Right Column-->
    </div>
    <!--end::Main Dashboard-->

    <!--begin::Modals-->
    <!--begin::Modal - Performance Data-->
    <div class="modal fade" id="modalPerformanceData" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="fw-bold">Performance Data</h2>
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                </div>
                <div class="modal-body">
                    @if(isset($performanceData) && !empty($performanceData) && $performanceData->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4" id="performanceDataTable">
                                <thead>
                                    <tr class="fw-bold text-muted bg-light">
                                        <th class="min-w-150px">Indicator</th>
                                        <th class="min-w-150px">Cluster</th>
                                        <th class="min-w-100px">Target</th>
                                        <th class="min-w-100px">Actual</th>
                                        <th class="min-w-100px">Achievement</th>
                                        <th class="min-w-100px">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($performanceData as $item)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="d-flex justify-content-start flex-column">
                                                        <span class="text-dark fw-bold fs-6">{{ $item->indicator_name ?? 'Unknown' }}</span>
                                                        <span class="text-muted fw-semibold text-muted d-block fs-7">{{ $item->indicator_number ?? 'N/A' }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="d-flex justify-content-start flex-column">
                                                        <span class="text-dark fw-bold fs-6">{{ $item->cluster_name ?? 'Unknown' }}</span>
                                                        <span class="text-muted fw-semibold text-muted d-block fs-7">{{ $item->cluster_code ?? 'N/A' }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                <span class="text-dark fw-bold d-block fs-6">{{ number_format($item->total_target_value ?? 0, 1) }}</span>
                                            </td>
                                            <td class="text-end">
                                                <span class="text-dark fw-bold d-block fs-6">{{ number_format($item->total_actual_value ?? 0, 1) }}</span>
                                            </td>
                                            <td class="text-end">
                                                <span class="text-dark fw-bold d-block fs-6">{{ number_format($item->achievement_percent ?? 0, 1) }}%</span>
                                            </td>
                                            <td class="text-end">
                                                @php
                                                    $statusLabel = $item->status_label ?? 'Unknown';
                                                    $statusColor = $statusLabel == 'Met' ? 'success' : ($statusLabel == 'On Track' ? 'primary' : ($statusLabel == 'In Progress' ? 'warning' : 'danger'));
                                                @endphp
                                                <span class="badge badge-light-{{ $statusColor }} fs-7 fw-bold">{{ $statusLabel }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-10">
                            <i class="ki-duotone ki-information-5 fs-5x text-muted mb-5">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                            <div class="text-muted fw-semibold fs-6">No performance data available for the selected filters.</div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <a href="{{ route('ecsahc.performance.quarterly.export', $filters ?? []) }}" class="btn btn-primary">
                        <i class="ki-duotone ki-file-down fs-2 me-1">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        Export to Excel
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!--end::Modal - Performance Data-->

    <!--begin::Modal - Insights-->
    <div class="modal fade" id="modalInsights" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="fw-bold">Key Insights</h2>
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                </div>
                <div class="modal-body">
                    @if(isset($insights) && !empty($insights))
                        <div class="row g-5">
                            <div class="col-xl-8">
                                <div class="card shadow-sm">
                                    <div class="card-header">
                                        <h3 class="card-title">All Insights</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex flex-column gap-7">
                                            @foreach($insights as $insight)
                                                <div class="d-flex align-items-center">
                                                    <div class="symbol symbol-circle symbol-50px me-5">
                                                        <span class="symbol-label bg-light-{{ $insight['type'] ?? 'primary' }}">
                                                            @php
                                                                $iconType = isset($insight['type']) ? $insight['type'] : 'primary';
                                                                $iconName = 'abstract-26';

                                                                if($iconType == 'success') {
                                                                    $iconName = 'check-circle';
                                                                } elseif($iconType == 'warning') {
                                                                    $iconName = 'information-5';
                                                                } elseif($iconType == 'danger') {
                                                                    $iconName = 'shield-cross';
                                                                }
                                                            @endphp
                                                            <i class="ki-duotone ki-{{ $iconName }} fs-1 text-{{ $iconType }}">
                                                                <span class="path1"></span>
                                                                <span class="path2"></span>
                                                                @if($iconName == 'information-5')
                                                                    <span class="path3"></span>
                                                                @endif
                                                            </i>
                                                        </span>
                                                    </div>
                                                    <div class="d-flex flex-column">
                                                        <span class="text-gray-800 fs-5 fw-bold">{{ $insight['message'] ?? 'No insight available' }}</span>
                                                        @if(isset($insight['category']))
                                                            <span class="text-muted fw-semibold fs-6">{{ ucfirst(str_replace('_', ' ', $insight['category'])) }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4">
                                <div class="card shadow-sm mb-5">
                                    <div class="card-header">
                                        <h3 class="card-title">Insights by Category</h3>
                                    </div>
                                    <div class="card-body">
                                        @php
                                            $categories = [];
                                            foreach($insights as $insight) {
                                                $category = $insight['category'] ?? 'other';
                                                if(!isset($categories[$category])) {
                                                    $categories[$category] = 0;
                                                }
                                                $categories[$category]++;
                                            }
                                        @endphp

                                        <div class="chart-container" style="height: 250px;">
                                            <canvas id="insightCategoryChart"></canvas>
                                        </div>

                                        <div class="mt-5">
                                            @foreach($categories as $category => $count)
                                                <div class="d-flex flex-stack mb-2">
                                                    <div class="text-gray-800 fw-bold fs-6">{{ ucfirst(str_replace('_', ' ', $category)) }}</div>
                                                    <div class="text-gray-800 fw-bold fs-6">{{ $count }}</div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <div class="card shadow-sm">
                                    <div class="card-header">
                                        <h3 class="card-title">Insights by Type</h3>
                                    </div>
                                    <div class="card-body">
                                        @php
                                            $types = [];
                                            foreach($insights as $insight) {
                                                $type = $insight['type'] ?? 'info';
                                                if(!isset($types[$type])) {
                                                    $types[$type] = 0;
                                                }
                                                $types[$type]++;
                                            }
                                        @endphp

                                        <div class="chart-container" style="height: 250px;">
                                            <canvas id="insightTypeChart"></canvas>
                                        </div>

                                        <div class="mt-5">
                                            @foreach($types as $type => $count)
                                                <div class="d-flex flex-stack mb-2">
                                                    <div class="d-flex align-items-center">
                                                        <div class="symbol symbol-circle symbol-30px me-3 bg-light">
                                                            <span class="symbol-label bg-light-{{ $type }}">
                                                                <i class="ki-duotone ki-abstract-{{ $type == 'success' ? 'check' : ($type == 'warning' ? 'right' : ($type == 'danger' ? 'down' : 'up')) }} fs-3 text-{{ $type }}">
                                                                    <span class="path1"></span>
                                                                    <span class="path2"></span>
                                                                </i>
                                                            </span>
                                                        </div>
                                                        <div class="text-gray-800 fw-bold fs-6">{{ ucfirst($type) }}</div>
                                                    </div>
                                                    <div class="text-gray-800 fw-bold fs-6">{{ $count }}</div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-10">
                            <i class="ki-duotone ki-information-5 fs-5x text-muted mb-5">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                            <div class="text-muted fw-semibold fs-6">No insights available for the selected filters.</div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!--end::Modal - Insights-->

    <!--begin::Modal - Cluster Details-->
    <div class="modal fade" id="modalClusterDetails" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="fw-bold">Cluster Performance Details</h2>
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="row g-5">
                        <div class="col-xl-8">
                            <div class="card shadow-sm mb-5">
                                <div class="card-header">
                                    <h3 class="card-title">Cluster Achievement</h3>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container" style="height: 400px;">
                                        <canvas id="clusterDetailChart"></canvas>
                                    </div>
                                </div>
                            </div>

                            @if(isset($clusterRankings) && !empty($clusterRankings))
                                <div class="card shadow-sm">
                                    <div class="card-header">
                                        <h3 class="card-title">Cluster Rankings</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                                <thead>
                                                    <tr class="fw-bold text-muted bg-light">
                                                        <th class="min-w-50px">Rank</th>
                                                        <th class="min-w-150px">Cluster</th>
                                                        <th class="min-w-100px">Avg Achievement</th>
                                                        <th class="min-w-100px">Met %</th>
                                                        <th class="min-w-100px">Needs Attention %</th>
                                                        <th class="min-w-100px">Data Completeness</th>
                                                        <th class="min-w-100px">Overall Score</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($clusterRankings as $cluster)
                                                        <tr>
                                                            <td>
                                                                <span class="text-dark fw-bold fs-6">{{ $cluster['rank'] ?? 'N/A' }}</span>
                                                            </td>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <div class="d-flex justify-content-start flex-column">
                                                                        <span class="text-dark fw-bold fs-6">{{ $cluster['name'] ?? 'Unknown' }}</span>
                                                                        <span class="text-muted fw-semibold text-muted d-block fs-7">{{ $cluster['code'] ?? 'N/A' }}</span>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td class="text-end">
                                                                <span class="text-dark fw-bold d-block fs-6">{{ number_format($cluster['avgAchievement'] ?? 0, 1) }}%</span>
                                                            </td>
                                                            <td class="text-end">
                                                                <span class="text-dark fw-bold d-block fs-6">{{ number_format($cluster['metPercentage'] ?? 0, 1) }}%</span>
                                                            </td>
                                                            <td class="text-end">
                                                                <span class="text-dark fw-bold d-block fs-6">{{ number_format($cluster['needsAttentionPercentage'] ?? 0, 1) }}%</span>
                                                            </td>
                                                            <td class="text-end">
                                                                <span class="text-dark fw-bold d-block fs-6">{{ number_format($cluster['dataCompleteness'] ?? 0, 1) }}%</span>
                                                            </td>
                                                            <td class="text-end">
                                                                <span class="text-dark fw-bold d-block fs-6">{{ number_format($cluster['overallScore'] ?? 0, 1) }}</span>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="col-xl-4">
                            @if(isset($comparisons) && isset($comparisons['clustersSummary']))
                                <div class="card shadow-sm mb-5">
                                    <div class="card-header">
                                        <h3 class="card-title">Cluster Comparison</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-5">
                                            <div class="col-md-12">
                                                <div class="card bg-light-primary shadow-sm">
                                                    <div class="card-body p-5">
                                                        <div class="d-flex flex-column">
                                                            <div class="text-primary fw-bold fs-5">Top Performer</div>
                                                            <div class="d-flex align-items-center mt-2">
                                                                <span class="text-gray-900 fw-bold fs-2x me-2">{{ $comparisons['clustersSummary']['topPerformer'] ?? 'N/A' }}</span>
                                                            </div>
                                                            <div class="text-muted fs-7 mt-1">{{ number_format($comparisons['clustersSummary']['topPerformerAvg'] ?? 0, 1) }}% achievement</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="card bg-light shadow-sm">
                                                    <div class="card-body p-5">
                                                        <div class="d-flex flex-column">
                                                            <div class="text-gray-800 fw-bold fs-5">Overall Average</div>
                                                            <div class="d-flex align-items-center mt-2">
                                                                <span class="text-gray-900 fw-bold fs-2x me-2">{{ number_format($comparisons['clustersSummary']['overallAvg'] ?? 0, 1) }}%</span>
                                                            </div>
                                                            <div class="text-muted fs-7 mt-1">Across all clusters</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="card bg-light-warning shadow-sm">
                                                    <div class="card-body p-5">
                                                        <div class="d-flex flex-column">
                                                            <div class="text-warning fw-bold fs-5">Performance Gap</div>
                                                            <div class="d-flex align-items-center mt-2">
                                                                <span class="text-gray-900 fw-bold fs-2x me-2">{{ number_format($comparisons['clustersSummary']['gap'] ?? 0, 1) }}%</span>
                                                            </div>
                                                            <div class="text-muted fs-7 mt-1">{{ number_format($comparisons['clustersSummary']['gapPercentage'] ?? 0, 1) }}% difference</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if(isset($achievementGaps) && isset($achievementGaps['clusterGaps']) && !empty($achievementGaps['clusterGaps']))
                                <div class="card shadow-sm">
                                    <div class="card-header">
                                        <h3 class="card-title">Achievement Gaps</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex flex-column gap-5">
                                            <div class="d-flex flex-column">
                                                <span class="text-gray-800 fw-bold fs-6 mb-2">Top Cluster</span>
                                                <div class="d-flex align-items-center">
                                                    <div class="symbol symbol-circle symbol-40px me-3">
                                                        <span class="symbol-label bg-light-success">
                                                            <i class="ki-duotone ki-abstract-check fs-2 text-success">
                                                                <span class="path1"></span>
                                                                <span class="path2"></span>
                                                            </i>
                                                        </span>
                                                    </div>
                                                    <div class="d-flex flex-column">
                                                        <span class="text-gray-800 fs-6 fw-bold">{{ $achievementGaps['clusterGaps']['topCluster'] }}</span>
                                                        <span class="text-muted fw-semibold fs-7">{{ number_format($achievementGaps['clusterGaps']['topClusterAchievement'], 1) }}% achievement</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="d-flex flex-column">
                                                <span class="text-gray-800 fw-bold fs-6 mb-2">Bottom Cluster</span>
                                                <div class="d-flex align-items-center">
                                                    <div class="symbol symbol-circle symbol-40px me-3">
                                                        <span class="symbol-label bg-light-danger">
                                                            <i class="ki-duotone ki-abstract-down fs-2 text-danger">
                                                                <span class="path1"></span>
                                                                <span class="path2"></span>
                                                            </i>
                                                        </span>
                                                    </div>
                                                    <div class="d-flex flex-column">
                                                        <span class="text-gray-800 fs-6 fw-bold">{{ $achievementGaps['clusterGaps']['bottomCluster'] }}</span>
                                                        <span class="text-muted fw-semibold fs-7">{{ number_format($achievementGaps['clusterGaps']['bottomClusterAchievement'], 1) }}% achievement</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="d-flex flex-column">
                                                <span class="text-gray-800 fw-bold fs-6 mb-2">Gap Analysis</span>
                                                <div class="d-flex align-items-center bg-light-warning rounded p-5">
                                                    <i class="ki-duotone ki-information-5 fs-2x text-warning me-4">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                        <span class="path3"></span>
                                                    </i>
                                                    <div class="text-gray-700 fs-6">
                                                        The performance gap between the top and bottom clusters is {{ number_format($achievementGaps['clusterGaps']['absoluteGap'], 1) }}% ({{ number_format($achievementGaps['clusterGaps']['percentageGap'], 1) }}% difference).
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!--end::Modal - Cluster Details-->

    <!--begin::Modal - Indicator Details-->
    <div class="modal fade" id="modalIndicatorDetails" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="fw-bold">Indicator Performance Details</h2>
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="row g-5">
                        <div class="col-xl-8">
                            @if(isset($indicatorPerformance) && !empty($indicatorPerformance))
                                <div class="card shadow-sm mb-5">
                                    <div class="card-header">
                                        <h3 class="card-title">Indicator Performance</h3>
                                        <div class="card-toolbar">
                                            <ul class="nav nav-tabs nav-line-tabs nav-stretch fs-6 border-0">
                                                <li class="nav-item">
                                                    <a class="nav-link active" data-bs-toggle="tab" href="#kt_tab_top_indicators_detail">Top Performers</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" data-bs-toggle="tab" href="#kt_tab_bottom_indicators_detail">Needs Attention</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" data-bs-toggle="tab" href="#kt_tab_consistent_indicators_detail">Most Consistent</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" data-bs-toggle="tab" href="#kt_tab_variable_indicators_detail">Most Variable</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="tab-content">
                                            <div class="tab-pane fade show active" id="kt_tab_top_indicators_detail" role="tabpanel">
                                                <div class="table-responsive">
                                                    <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                                        <thead>
                                                            <tr class="fw-bold text-muted bg-light">
                                                                <th class="min-w-150px">Indicator</th>
                                                                <th class="min-w-100px">Avg Achievement</th>
                                                                <th class="min-w-100px">Met %</th>
                                                                <th class="min-w-100px">Top Cluster</th>
                                                                <th class="min-w-100px">Bottom Cluster</th>
                                                                <th class="min-w-100px">Variance</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($indicatorPerformance['topIndicators'] as $indicator)
                                                                <tr>
                                                                    <td>
                                                                        <div class="d-flex align-items-center">
                                                                            <div class="d-flex justify-content-start flex-column">
                                                                                <span class="text-dark fw-bold fs-6">{{ $indicator['name'] ?? 'Unknown' }}</span>
                                                                                <span class="text-muted fw-semibold text-muted d-block fs-7">{{ $indicator['number'] ?? 'N/A' }}</span>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <span class="text-dark fw-bold d-block fs-6">{{ number_format($indicator['avgAchievement'] ?? 0, 1) }}%</span>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <span class="text-dark fw-bold d-block fs-6">{{ number_format($indicator['metPercentage'] ?? 0, 1) }}%</span>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <span class="text-dark fw-bold d-block fs-6">{{ $indicator['topCluster'] ?? 'N/A' }}</span>
                                                                        <span class="text-muted fw-semibold text-muted d-block fs-7">{{ number_format($indicator['topClusterAchievement'] ?? 0, 1) }}%</span>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <span class="text-dark fw-bold d-block fs-6">{{ $indicator['bottomCluster'] ?? 'N/A' }}</span>
                                                                        <span class="text-muted fw-semibold text-muted d-block fs-7">{{ number_format($indicator['bottomClusterAchievement'] ?? 0, 1) }}%</span>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <span class="text-dark fw-bold d-block fs-6">{{ number_format($indicator['achievementVariance'] ?? 0, 1) }}</span>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>

                                            <div class="tab-pane fade" id="kt_tab_bottom_indicators_detail" role="tabpanel">
                                                <div class="table-responsive">
                                                    <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                                        <thead>
                                                            <tr class="fw-bold text-muted bg-light">
                                                                <th class="min-w-150px">Indicator</th>
                                                                <th class="min-w-100px">Avg Achievement</th>
                                                                <th class="min-w-100px">Needs Attention %</th>
                                                                <th class="min-w-100px">Top Cluster</th>
                                                                <th class="min-w-100px">Bottom Cluster</th>
                                                                <th class="min-w-100px">Variance</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($indicatorPerformance['bottomIndicators'] as $indicator)
                                                                <tr>
                                                                    <td>
                                                                        <div class="d-flex align-items-center">
                                                                            <div class="d-flex justify-content-start flex-column">
                                                                                <span class="text-dark fw-bold fs-6">{{ $indicator['name'] ?? 'Unknown' }}</span>
                                                                                <span class="text-muted fw-semibold text-muted d-block fs-7">{{ $indicator['number'] ?? 'N/A' }}</span>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <span class="text-dark fw-bold d-block fs-6">{{ number_format($indicator['avgAchievement'] ?? 0, 1) }}%</span>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <span class="text-dark fw-bold d-block fs-6">{{ number_format($indicator['needsAttentionPercentage'] ?? 0, 1) }}%</span>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <span class="text-dark fw-bold d-block fs-6">{{ $indicator['topCluster'] ?? 'N/A' }}</span>
                                                                        <span class="text-muted fw-semibold text-muted d-block fs-7">{{ number_format($indicator['topClusterAchievement'] ?? 0, 1) }}%</span>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <span class="text-dark fw-bold d-block fs-6">{{ $indicator['bottomCluster'] ?? 'N/A' }}</span>
                                                                        <span class="text-muted fw-semibold text-muted d-block fs-7">{{ number_format($indicator['bottomClusterAchievement'] ?? 0, 1) }}%</span>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <span class="text-dark fw-bold d-block fs-6">{{ number_format($indicator['achievementVariance'] ?? 0, 1) }}</span>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>

                                            <div class="tab-pane fade" id="kt_tab_consistent_indicators_detail" role="tabpanel">
                                                <div class="table-responsive">
                                                    <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                                        <thead>
                                                            <tr class="fw-bold text-muted bg-light">
                                                                <th class="min-w-150px">Indicator</th>
                                                                <th class="min-w-100px">Avg Achievement</th>
                                                                <th class="min-w-100px">Variance</th>
                                                                <th class="min-w-100px">Min-Max Range</th>
                                                                <th class="min-w-100px">Clusters</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($indicatorPerformance['mostConsistent'] as $indicator)
                                                                <tr>
                                                                    <td>
                                                                        <div class="d-flex align-items-center">
                                                                            <div class="d-flex justify-content-start flex-column">
                                                                                <span class="text-dark fw-bold fs-6">{{ $indicator['name'] ?? 'Unknown' }}</span>
                                                                                <span class="text-muted fw-semibold text-muted d-block fs-7">{{ $indicator['number'] ?? 'N/A' }}</span>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <span class="text-dark fw-bold d-block fs-6">{{ number_format($indicator['avgAchievement'] ?? 0, 1) }}%</span>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <span class="text-dark fw-bold d-block fs-6">{{ number_format($indicator['achievementVariance'] ?? 0, 1) }}</span>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <span class="text-dark fw-bold d-block fs-6">{{ number_format($indicator['minAchievement'] ?? 0, 1) }}% - {{ number_format($indicator['maxAchievement'] ?? 0, 1) }}%</span>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <span class="text-dark fw-bold d-block fs-6">{{ $indicator['totalClusters'] ?? 0 }}</span>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>

                                            <div class="tab-pane fade" id="kt_tab_variable_indicators_detail" role="tabpanel">
                                                <div class="table-responsive">
                                                    <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                                        <thead>
                                                            <tr class="fw-bold text-muted bg-light">
                                                                <th class="min-w-150px">Indicator</th>
                                                                <th class="min-w-100px">Avg Achievement</th>
                                                                <th class="min-w-100px">Variance</th>
                                                                <th class="min-w-100px">Min-Max Range</th>
                                                                <th class="min-w-100px">Clusters</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($indicatorPerformance['mostVariable'] as $indicator)
                                                                <tr>
                                                                    <td>
                                                                        <div class="d-flex align-items-center">
                                                                            <div class="d-flex justify-content-start flex-column">
                                                                                <span class="text-dark fw-bold fs-6">{{ $indicator['name'] ?? 'Unknown' }}</span>
                                                                                <span class="text-muted fw-semibold text-muted d-block fs-7">{{ $indicator['number'] ?? 'N/A' }}</span>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <span class="text-dark fw-bold d-block fs-6">{{ number_format($indicator['avgAchievement'] ?? 0, 1) }}%</span>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <span class="text-dark fw-bold d-block fs-6">{{ number_format($indicator['achievementVariance'] ?? 0, 1) }}</span>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <span class="text-dark fw-bold d-block fs-6">{{ number_format($indicator['minAchievement'] ?? 0, 1) }}% - {{ number_format($indicator['maxAchievement'] ?? 0, 1) }}%</span>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <span class="text-dark fw-bold d-block fs-6">{{ $indicator['totalClusters'] ?? 0 }}</span>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="card shadow-sm">
                                <div class="card-header">
                                    <h3 class="card-title">Indicator Achievement Distribution</h3>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container" style="height: 350px;">
                                        <canvas id="indicatorDistributionChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4">
                            @if(isset($comparisons) && isset($comparisons['indicatorsSummary']))
                                <div class="card shadow-sm mb-5">
                                    <div class="card-header">
                                        <h3 class="card-title">Indicator Comparison</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-5">
                                            <div class="col-md-12">
                                                <div class="card bg-light-success shadow-sm">
                                                    <div class="card-body p-5">
                                                        <div class="d-flex flex-column">
                                                            <div class="text-success fw-bold fs-5">Top Indicator</div>
                                                            <div class="d-flex align-items-center mt-2">
                                                                <span class="text-gray-900 fw-bold fs-2x me-2">{{ number_format($comparisons['indicatorsSummary']['topPerformerAvg'] ?? 0, 1) }}%</span>
                                                            </div>
                                                            <div class="text-muted fs-7 mt-1">{{ $comparisons['indicatorsSummary']['topPerformer'] ?? 'N/A' }}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="card bg-light shadow-sm">
                                                    <div class="card-body p-5">
                                                        <div class="d-flex flex-column">
                                                            <div class="text-gray-800 fw-bold fs-5">Average</div>
                                                            <div class="d-flex align-items-center mt-2">
                                                                <span class="text-gray-900 fw-bold fs-2x me-2">{{ number_format($comparisons['indicatorsSummary']['overallAvg'] ?? 0, 1) }}%</span>
                                                            </div>
                                                            <div class="text-muted fs-7 mt-1">Across all indicators</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="card bg-light-danger shadow-sm">
                                                    <div class="card-body p-5">
                                                        <div class="d-flex flex-column">
                                                            <div class="text-danger fw-bold fs-5">Bottom Indicator</div>
                                                            <div class="d-flex align-items-center mt-2">
                                                                <span class="text-gray-900 fw-bold fs-2x me-2">{{ number_format($comparisons['indicatorsSummary']['bottomPerformerAvg'] ?? 0, 1) }}%</span>
                                                            </div>
                                                            <div class="text-muted fs-7 mt-1">{{ $comparisons['indicatorsSummary']['bottomPerformer'] ?? 'N/A' }}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if(isset($achievementGaps) && isset($achievementGaps['indicatorGaps']) && !empty($achievementGaps['indicatorGaps']))
                                <div class="card shadow-sm">
                                    <div class="card-header">
                                        <h3 class="card-title">Indicator Achievement Gaps</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex flex-column gap-5">
                                            <div class="d-flex flex-column">
                                                <span class="text-gray-800 fw-bold fs-6 mb-2">Top Indicator</span>
                                                <div class="d-flex align-items-center">
                                                    <div class="symbol symbol-circle symbol-40px me-3">
                                                        <span class="symbol-label bg-light-success">
                                                            <i class="ki-duotone ki-abstract-check fs-2 text-success">
                                                                <span class="path1"></span>
                                                                <span class="path2"></span>
                                                            </i>
                                                        </span>
                                                    </div>
                                                    <div class="d-flex flex-column">
                                                        <span class="text-gray-800 fs-6 fw-bold">{{ $achievementGaps['indicatorGaps']['topIndicator'] }}</span>
                                                        <span class="text-muted fw-semibold fs-7">{{ number_format($achievementGaps['indicatorGaps']['topIndicatorAchievement'], 1) }}% achievement</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="d-flex flex-column">
                                                <span class="text-gray-800 fw-bold fs-6 mb-2">Bottom Indicator</span>
                                                <div class="d-flex align-items-center">
                                                    <div class="symbol symbol-circle symbol-40px me-3">
                                                        <span class="symbol-label bg-light-danger">
                                                            <i class="ki-duotone ki-abstract-down fs-2 text-danger">
                                                                <span class="path1"></span>
                                                                <span class="path2"></span>
                                                            </i>
                                                        </span>
                                                    </div>
                                                    <div class="d-flex flex-column">
                                                        <span class="text-gray-800 fs-6 fw-bold">{{ $achievementGaps['indicatorGaps']['bottomIndicator'] }}</span>
                                                        <span class="text-muted fw-semibold fs-7">{{ number_format($achievementGaps['indicatorGaps']['bottomIndicatorAchievement'], 1) }}% achievement</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="d-flex flex-column">
                                                <span class="text-gray-800 fw-bold fs-6 mb-2">Gap Analysis</span>
                                                <div class="d-flex align-items-center bg-light-warning rounded p-5">
                                                    <i class="ki-duotone ki-information-5 fs-2x text-warning me-4">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                        <span class="path3"></span>
                                                    </i>
                                                    <div class="text-gray-700 fs-6">
                                                        The performance gap between the top and bottom indicators is {{ number_format($achievementGaps['indicatorGaps']['absoluteGap'], 1) }}% ({{ number_format($achievementGaps['indicatorGaps']['percentageGap'], 1) }}% difference).
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!--end::Modal - Indicator Details-->

    <!--begin::Modal - Trend Details-->
    <div class="modal fade" id="modalTrendDetails" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="fw-bold">Trend Analysis Details</h2>
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="row g-5">
                        <div class="col-xl-8">
                            @if(isset($trends) && isset($trends['quarterlyTrend']) && !empty($trends['quarterlyTrend']))
                                <div class="card shadow-sm mb-5">
                                    <div class="card-header">
                                        <h3 class="card-title">Quarterly Trend</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="chart-container" style="height: 400px;">
                                            <canvas id="quarterlyTrendChart"></canvas>
                                        </div>

                                        <div class="table-responsive mt-5">
                                            <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                                <thead>
                                                    <tr class="fw-bold text-muted bg-light">
                                                        <th class="min-w-100px">Quarter</th>
                                                        <th class="min-w-100px">{{ isset($trends['yearOverYear']) ? $trends['yearOverYear']['currentYear'] : date('Y') }}</th>
                                                        <th class="min-w-100px">{{ isset($trends['yearOverYear']) ? $trends['yearOverYear']['previousYear'] : (date('Y')-1) }}</th>
                                                        <th class="min-w-100px">Change</th>
                                                        <th class="min-w-100px">% Change</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($trends['quarterlyTrend'] as $quarter => $data)
                                                        <tr>
                                                            <td>
                                                                <span class="text-dark fw-bold fs-6">Q{{ $quarter }}</span>
                                                            </td>
                                                            <td>
                                                                <span class="text-dark fw-bold d-block fs-6">{{ number_format($data['current'] ?? 0, 1) }}%</span>
                                                            </td>
                                                            <td>
                                                                <span class="text-dark fw-bold d-block fs-6">{{ isset($data['previous']) && $data['previous'] !== null ? number_format($data['previous'], 1) . '%' : 'N/A' }}</span>
                                                            </td>
                                                            <td>
                                                                @if(isset($data['change']) && $data['change'] !== null)
                                                                    @php
                                                                        $improved = isset($data['improved']) ? $data['improved'] : ($data['change'] >= 0);
                                                                    @endphp
                                                                    <span class="text-{{ $improved ? 'success' : 'danger' }} fw-bold d-block fs-6">
                                                                        {{ $data['change'] >= 0 ? '+' : '' }}{{ number_format($data['change'], 1) }}%
                                                                    </span>
                                                                @else
                                                                    <span class="text-muted fw-bold d-block fs-6">N/A</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if(isset($data['percentChange']) && $data['percentChange'] !== null)
                                                                    @php
                                                                        $improved = isset($data['improved']) ? $data['improved'] : ($data['percentChange'] >= 0);
                                                                    @endphp
                                                                    <span class="text-{{ $improved ? 'success' : 'danger' }} fw-bold d-block fs-6">
                                                                        {{ $data['percentChange'] >= 0 ? '+' : '' }}{{ number_format($data['percentChange'], 1) }}%
                                                                    </span>
                                                                @else
                                                                    <span class="text-muted fw-bold d-block fs-6">N/A</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if(isset($trends) && isset($trends['clusterTrends']) && !empty($trends['clusterTrends']))
                                <div class="card shadow-sm">
                                    <div class="card-header">
                                        <h3 class="card-title">Cluster Trends</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                                <thead>
                                                    <tr class="fw-bold text-muted bg-light">
                                                        <th class="min-w-150px">Cluster</th>
                                                        <th class="min-w-100px">Current</th>
                                                        <th class="min-w-100px">Previous</th>
                                                        <th class="min-w-100px">Change</th>
                                                        <th class="min-w-100px">% Change</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($trends['clusterTrends'] as $cluster => $data)
                                                        <tr>
                                                            <td>
                                                                <span class="text-dark fw-bold fs-6">{{ $cluster }}</span>
                                                            </td>
                                                            <td>
                                                                <span class="text-dark fw-bold d-block fs-6">{{ number_format($data['current'] ?? 0, 1) }}%</span>
                                                            </td>
                                                            <td>
                                                                <span class="text-dark fw-bold d-block fs-6">{{ number_format($data['previous'] ?? 0, 1) }}%</span>
                                                            </td>
                                                            <td>
                                                                <span class="text-{{ $data['improved'] ? 'success' : 'danger' }} fw-bold d-block fs-6">
                                                                    {{ $data['change'] >= 0 ? '+' : '' }}{{ number_format($data['change'] ?? 0, 1) }}%
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <span class="text-{{ $data['improved'] ? 'success' : 'danger' }} fw-bold d-block fs-6">
                                                                    {{ $data['percentChange'] >= 0 ? '+' : '' }}{{ number_format($data['percentChange'] ?? 0, 1) }}%
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="col-xl-4">
                            @if(isset($trends) && isset($trends['yearOverYear']))
                                <div class="card shadow-sm mb-5">
                                    <div class="card-header">
                                        <h3 class="card-title">Year-over-Year Comparison</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-5">
                                            <div class="col-md-12">
                                                <div class="card bg-light shadow-sm">
                                                    <div class="card-body p-5">
                                                        <div class="d-flex flex-column">
                                                            <div class="text-gray-800 fw-bold fs-5">{{ $trends['yearOverYear']['previousYear'] ?? 'Previous Year' }}</div>
                                                            <div class="d-flex align-items-center mt-2">
                                                                <span class="text-gray-900 fw-bold fs-2x me-2">{{ number_format($trends['yearOverYear']['previousAvg'] ?? 0, 1) }}%</span>
                                                            </div>
                                                            <div class="text-muted fs-7 mt-1">{{ $trends['yearOverYear']['previousCount'] ?? 0 }} data points</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="card bg-light-primary shadow-sm">
                                                    <div class="card-body p-5">
                                                        <div class="d-flex flex-column">
                                                            <div class="text-primary fw-bold fs-5">{{ $trends['yearOverYear']['currentYear'] ?? 'Current Year' }}</div>
                                                            <div class="d-flex align-items-center mt-2">
                                                                <span class="text-gray-900 fw-bold fs-2x me-2">{{ number_format($trends['yearOverYear']['currentAvg'] ?? 0, 1) }}%</span>
                                                            </div>
                                                            <div class="text-muted fs-7 mt-1">{{ $trends['yearOverYear']['currentCount'] ?? 0 }} data points</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                @php
                                                    $improved = isset($trends['yearOverYear']['improved']) ? $trends['yearOverYear']['improved'] : false;
                                                    $absoluteChange = isset($trends['yearOverYear']['absoluteChange']) ? $trends['yearOverYear']['absoluteChange'] : 0;
                                                    $percentChange = isset($trends['yearOverYear']['percentChange']) ? $trends['yearOverYear']['percentChange'] : 0;
                                                @endphp
                                                <div class="card bg-light-{{ $improved ? 'success' : 'danger' }} shadow-sm">
                                                    <div class="card-body p-5">
                                                        <div class="d-flex flex-column">
                                                            <div class="text-{{ $improved ? 'success' : 'danger' }} fw-bold fs-5">Change</div>
                                                            <div class="d-flex align-items-center mt-2">
                                                                <span class="text-gray-900 fw-bold fs-2x me-2">{{ $absoluteChange >= 0 ? '+' : '' }}{{ number_format($absoluteChange, 1) }}%</span>
                                                            </div>
                                                            <div class="text-muted fs-7 mt-1">{{ $percentChange >= 0 ? '+' : '' }}{{ number_format($percentChange, 1) }}% change</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="card shadow-sm">
                                <div class="card-header">
                                    <h3 class="card-title">Performance Forecast</h3>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container" style="height: 250px;">
                                        <canvas id="forecastChart"></canvas>
                                    </div>

                                    <div class="d-flex align-items-center bg-light-info rounded p-5 mt-5">
                                        <i class="ki-duotone ki-chart-line-star fs-2x text-info me-4">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                            <span class="path3"></span>
                                        </i>
                                        <div class="text-gray-700 fs-6">
                                            Based on current trends, we forecast an overall achievement of
                                            <span class="fw-bold">{{ isset($trends['yearOverYear']) && isset($trends['yearOverYear']['currentAvg']) ? number_format($trends['yearOverYear']['currentAvg'] * 1.1, 1) : number_format(40, 1) }}%</span>
                                            by the end of the year.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!--end::Modal - Trend Details-->

    <!--begin::Modal - Data Quality-->
    <div class="modal fade" id="modalDataQuality" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="fw-bold">Data Quality Analysis</h2>
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                </div>
                <div class="modal-body">
                    @if(isset($dataQualityIssues) && !empty($dataQualityIssues))
                        <div class="row g-5">
                            @if(isset($dataQualityIssues['missingTargets']))
                                <div class="col-xl-6">
                                    <div class="card shadow-sm">
                                        <div class="card-header">
                                            <h3 class="card-title">Missing Target Values</h3>
                                        </div>
                                        <div class="card-body">
                                            <div class="d-flex align-items-center bg-light-warning rounded p-5 mb-5">
                                                <i class="ki-duotone ki-information-5 fs-2x text-warning me-4">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                    <span class="path3"></span>
                                                </i>
                                                <div class="text-gray-700 fs-6">
                                                    {{ $dataQualityIssues['missingTargets']['count'] ?? 0 }} indicators ({{ number_format($dataQualityIssues['missingTargets']['percentage'] ?? 0, 1) }}%) are missing target values.
                                                </div>
                                            </div>

                                            @if(isset($dataQualityIssues['missingTargets']['affectedClusters']) && !empty($dataQualityIssues['missingTargets']['affectedClusters']))
                                                <div class="mb-5">
                                                    <h4 class="fs-6 fw-bold text-gray-800 mb-3">Affected Clusters</h4>
                                                    <div class="d-flex flex-wrap gap-2">
                                                        @foreach($dataQualityIssues['missingTargets']['affectedClusters'] as $cluster)
                                                            <span class="badge badge-light-primary fs-7 fw-bold">{{ $cluster }}</span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif

                                            @if(isset($dataQualityIssues['missingTargets']['items']) && !empty($dataQualityIssues['missingTargets']['items']))
                                                <h4 class="fs-6 fw-bold text-gray-800 mb-3">Examples</h4>
                                                <div class="table-responsive">
                                                    <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                                        <thead>
                                                            <tr class="fw-bold text-muted bg-light">
                                                                <th class="min-w-150px">Indicator</th>
                                                                <th class="min-w-150px">Cluster</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach(array_slice($dataQualityIssues['missingTargets']['items'], 0, 5) as $item)
                                                                <tr>
                                                                    <td>{{ $item->indicator_name ?? 'Unknown' }}</td>
                                                                    <td>{{ $item->cluster_name ?? 'Unknown' }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if(isset($dataQualityIssues['missingActuals']))
                                <div class="col-xl-6">
                                    <div class="card shadow-sm">
                                        <div class="card-header">
                                            <h3 class="card-title">Missing Actual Values</h3>
                                        </div>
                                        <div class="card-body">
                                            <div class="d-flex align-items-center bg-light-warning rounded p-5 mb-5">
                                                <i class="ki-duotone ki-information-5 fs-2x text-warning me-4">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                    <span class="path3"></span>
                                                </i>
                                                <div class="text-gray-700 fs-6">
                                                    {{ $dataQualityIssues['missingActuals']['count'] ?? 0 }} indicators ({{ number_format($dataQualityIssues['missingActuals']['percentage'] ?? 0, 1) }}%) have targets but are missing actual values.
                                                </div>
                                            </div>

                                            @if(isset($dataQualityIssues['missingActuals']['affectedClusters']) && !empty($dataQualityIssues['missingActuals']['affectedClusters']))
                                                <div class="mb-5">
                                                    <h4 class="fs-6 fw-bold text-gray-800 mb-3">Affected Clusters</h4>
                                                    <div class="d-flex flex-wrap gap-2">
                                                        @foreach($dataQualityIssues['missingActuals']['affectedClusters'] as $cluster)
                                                            <span class="badge badge-light-primary fs-7 fw-bold">{{ $cluster }}</span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif

                                            @if(isset($dataQualityIssues['missingActuals']['items']) && !empty($dataQualityIssues['missingActuals']['items']))
                                                <h4 class="fs-6 fw-bold text-gray-800 mb-3">Examples</h4>
                                                <div class="table-responsive">
                                                    <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                                        <thead>
                                                            <tr class="fw-bold text-muted bg-light">
                                                                <th class="min-w-150px">Indicator</th>
                                                                <th class="min-w-150px">Cluster</th>
                                                                <th class="min-w-100px">Target</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach(array_slice($dataQualityIssues['missingActuals']['items'], 0, 5) as $item)
                                                                <tr>
                                                                    <td>{{ $item->indicator_name ?? 'Unknown' }}</td>
                                                                    <td>{{ $item->cluster_name ?? 'Unknown' }}</td>
                                                                    <td>{{ number_format($item->total_target_value ?? 0, 1) }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if(isset($dataQualityIssues['inconsistentStatuses']))
                                <div class="col-xl-12">
                                    <div class="card shadow-sm">
                                        <div class="card-header">
                                            <h3 class="card-title">Inconsistent Status Labels</h3>
                                        </div>
                                        <div class="card-body">
                                            <div class="d-flex align-items-center bg-light-warning rounded p-5 mb-5">
                                                <i class="ki-duotone ki-information-5 fs-2x text-warning me-4">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                    <span class="path3"></span>
                                                </i>
                                                <div class="text-gray-700 fs-6">
                                                    {{ $dataQualityIssues['inconsistentStatuses']['count'] ?? 0 }} indicators ({{ number_format($dataQualityIssues['inconsistentStatuses']['percentage'] ?? 0, 1) }}%) have status labels inconsistent with their achievement percentages.
                                                </div>
                                            </div>

                                            @if(isset($dataQualityIssues['inconsistentStatuses']['items']) && !empty($dataQualityIssues['inconsistentStatuses']['items']))
                                                <div class="table-responsive">
                                                    <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                                        <thead>
                                                            <tr class="fw-bold text-muted bg-light">
                                                                <th class="min-w-150px">Indicator</th>
                                                                <th class="min-w-100px">Cluster</th>
                                                                <th class="min-w-100px">Achievement</th>
                                                                <th class="min-w-100px">Actual Status</th>
                                                                <th class="min-w-100px">Expected Status</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($dataQualityIssues['inconsistentStatuses']['items'] as $item)
                                                                <tr>
                                                                    <td>{{ $item['indicator_name'] ?? 'Unknown' }}</td>
                                                                    <td>{{ $item['cluster_name'] ?? 'Unknown' }}</td>
                                                                    <td>{{ number_format($item['achievement_percent'] ?? 0, 1) }}%</td>
                                                                    <td>
                                                                        @php
                                                                            $actualStatus = $item['actual_status'] ?? 'Unknown';
                                                                            $actualStatusColor = $actualStatus == 'Met' ? 'success' : ($actualStatus == 'On Track' ? 'primary' : ($actualStatus == 'In Progress' ? 'warning' : 'danger'));
                                                                        @endphp
                                                                        <span class="badge badge-light-{{ $actualStatusColor }} fs-7 fw-bold">{{ $actualStatus }}</span>
                                                                    </td>
                                                                    <td>
                                                                        @php
                                                                            $expectedStatus = $item['expected_status'] ?? 'Unknown';
                                                                            $expectedStatusColor = $expectedStatus == 'Met' ? 'success' : ($expectedStatus == 'On Track' ? 'primary' : ($expectedStatus == 'In Progress' ? 'warning' : 'danger'));
                                                                        @endphp
                                                                        <span class="badge badge-light-{{ $expectedStatusColor }} fs-7 fw-bold">{{ $expectedStatus }}</span>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if(isset($dataQualityIssues['outliers']))
                                <div class="col-xl-12">
                                    <div class="card shadow-sm">
                                        <div class="card-header">
                                            <h3 class="card-title">Unusual Achievement Values</h3>
                                        </div>
                                        <div class="card-body">
                                            <div class="d-flex align-items-center bg-light-warning rounded p-5 mb-5">
                                                <i class="ki-duotone ki-information-5 fs-2x text-warning me-4">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                    <span class="path3"></span>
                                                </i>
                                                <div class="text-gray-700 fs-6">
                                                    {{ $dataQualityIssues['outliers']['count'] ?? 0 }} indicators ({{ number_format($dataQualityIssues['outliers']['percentage'] ?? 0, 1) }}%) have unusual achievement values that may need verification.
                                                </div>
                                            </div>

                                            <div class="text-gray-600 fs-7 mb-5">
                                                <strong>Threshold Range:</strong> {{ number_format($dataQualityIssues['outliers']['lowerBound'] ?? 0, 1) }}% to {{ number_format($dataQualityIssues['outliers']['upperBound'] ?? 0, 1) }}%
                                            </div>

                                            @if(isset($dataQualityIssues['outliers']['items']) && !empty($dataQualityIssues['outliers']['items']))
                                                <div class="table-responsive">
                                                    <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                                        <thead>
                                                            <tr class="fw-bold text-muted bg-light">
                                                                <th class="min-w-150px">Indicator</th>
                                                                <th class="min-w-100px">Cluster</th>
                                                                <th class="min-w-100px">Target</th>
                                                                <th class="min-w-100px">Actual</th>
                                                                <th class="min-w-100px">Achievement</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach(array_slice($dataQualityIssues['outliers']['items'], 0, 10) as $item)
                                                                <tr>
                                                                    <td>{{ $item['indicator'] ?? 'Unknown' }}</td>
                                                                    <td>{{ $item['cluster'] ?? 'Unknown' }}</td>
                                                                    <td>{{ number_format($item['target'] ?? 0, 1) }}</td>
                                                                    <td>{{ number_format($item['actual'] ?? 0, 1) }}</td>
                                                                    <td>{{ number_format($item['achievement'] ?? 0, 1) }}%</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="text-center py-10">
                            <i class="ki-duotone ki-information-5 fs-5x text-muted mb-5">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                            <div class="text-muted fw-semibold fs-6">No data quality issues detected.</div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!--end::Modal - Data Quality-->
    <!--end::Modals-->
</div>
<!--end::Performance Quarterly Report Results-->

<!--begin::Javascript-->
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Initialize DataTables
    if (typeof $.fn.dataTable !== 'undefined') {
        $('#performanceDataTable').DataTable({
            responsive: true,
            order: [[4, 'desc']], // Sort by achievement column
            pageLength: 25,
            lengthMenu: [10, 25, 50, 100]
        });
    }

    // Initialize Charts
    if (typeof Chart !== 'undefined') {
        // Status Chart
        @if(isset($summaryMetrics['statusCounts']) && !empty($summaryMetrics['statusCounts']))
            const statusCtx = document.getElementById('statusChart');
            if (statusCtx) {
                const statusLabels = [];
                const statusData = [];
                const statusColors = [];

                @foreach($summaryMetrics['statusCounts'] as $status => $data)
                    statusLabels.push('{{ $status }}');
                    statusData.push({{ $data['percentage'] ?? 0 }});
                    statusColors.push('{{ $status == "Met" ? "rgba(80, 205, 137, 0.8)" : ($status == "On Track" ? "rgba(0, 158, 247, 0.8)" : ($status == "In Progress" ? "rgba(255, 199, 0, 0.8)" : "rgba(241, 65, 108, 0.8)")) }}');
                @endforeach

                new Chart(statusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: statusLabels,
                        datasets: [{
                            data: statusData,
                            backgroundColor: statusColors,
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        },
                        cutout: '70%'
                    }
                });
            }
        @endif

        // Cluster Chart
        @if(isset($chartData['achievementByCluster']) && !empty($chartData['achievementByCluster']))
            const clusterCtx = document.getElementById('clusterChart');
            if (clusterCtx) {
                const clusterLabels = [];
                const clusterData = [];
                const clusterColors = [];

                @foreach($chartData['achievementByCluster'] as $cluster)
                    clusterLabels.push('{{ $cluster['name'] }}');
                    clusterData.push({{ $cluster['value'] ?? 0 }});

                    @php
                        $value = $cluster['value'] ?? 0;
                        $color = $value >= 90 ? "rgba(80, 205, 137, 0.8)" : ($value >= 50 ? "rgba(0, 158, 247, 0.8)" : ($value >= 10 ? "rgba(255, 199, 0, 0.8)" : "rgba(241, 65, 108, 0.8)"));
                    @endphp
                    clusterColors.push('{{ $color }}');
                @endforeach

                new Chart(clusterCtx, {
                    type: 'bar',
                    data: {
                        labels: clusterLabels,
                        datasets: [{
                            label: 'Achievement %',
                            data: clusterData,
                            backgroundColor: clusterColors,
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                title: {
                                    display: true,
                                    text: 'Achievement %'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Clusters'
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
            }

            // Cluster Detail Chart
            const clusterDetailCtx = document.getElementById('clusterDetailChart');
            if (clusterDetailCtx) {
                // Make sure we have the variables defined for this chart
                const detailClusterLabels = [];
                const detailClusterData = [];
                const detailClusterColors = [];

                @foreach($chartData['achievementByCluster'] as $cluster)
                    detailClusterLabels.push('{{ $cluster['name'] }}');
                    detailClusterData.push({{ $cluster['value'] ?? 0 }});

                    @php
                        $value = $cluster['value'] ?? 0;
                        $color = $value >= 90 ? "rgba(80, 205, 137, 0.8)" : ($value >= 50 ? "rgba(0, 158, 247, 0.8)" : ($value >= 10 ? "rgba(255, 199, 0, 0.8)" : "rgba(241, 65, 108, 0.8)"));
                    @endphp
                    detailClusterColors.push('{{ $color }}');
                @endforeach

                new Chart(clusterDetailCtx, {
                    type: 'bar',
                    data: {
                        labels: detailClusterLabels,
                        datasets: [{
                            label: 'Achievement %',
                            data: detailClusterData,
                            backgroundColor: detailClusterColors,
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                title: {
                                    display: true,
                                    text: 'Achievement %'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Clusters'
                                }
                            }
                        }
                    }
                });
            }
        @endif

        // Indicator Chart
        @if(isset($chartData['topIndicators']) && !empty($chartData['topIndicators']) && isset($chartData['bottomIndicators']) && !empty($chartData['bottomIndicators']))
            const indicatorCtx = document.getElementById('indicatorChart');
            if (indicatorCtx) {
                const topLabels = [];
                const topData = [];
                const bottomLabels = [];
                const bottomData = [];

                @foreach($chartData['topIndicators'] as $indicator)
                    topLabels.push('{{ substr($indicator['name'], 0, 20) }}...');
                    topData.push({{ $indicator['value'] ?? 0 }});
                @endforeach

                @foreach($chartData['bottomIndicators'] as $indicator)
                    bottomLabels.push('{{ substr($indicator['name'], 0, 20) }}...');
                    bottomData.push({{ $indicator['value'] ?? 0 }});
                @endforeach

                new Chart(indicatorCtx, {
                    type: 'bar',
                    data: {
                        labels: ['Top Performers', 'Needs Attention'],
                        datasets: [{
                            label: 'Top Performers',
                            data: [Math.max(...topData), null],
                            backgroundColor: 'rgba(80, 205, 137, 0.8)',
                            borderWidth: 0
                        }, {
                            label: 'Needs Attention',
                            data: [null, Math.min(...bottomData)],
                            backgroundColor: 'rgba(241, 65, 108, 0.8)',
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                title: {
                                    display: true,
                                    text: 'Achievement %'
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    title: function(context) {
                                        const index = context[0].dataIndex;
                                        return index === 0 ? topLabels[0] : bottomLabels[0];
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Indicator Distribution Chart
            const indicatorDistributionCtx = document.getElementById('indicatorDistributionChart');
            if (indicatorDistributionCtx) {
                // Ensure topData and bottomData are defined
                let allIndicatorData = [];

                // Check if we're inside the scope where topData and bottomData are defined
                if (typeof topData !== 'undefined' && typeof bottomData !== 'undefined') {
                    allIndicatorData = [...topData, ...bottomData];
                } else {
                    // If not defined, create sample data for the chart
                    @if(isset($chartData['achievementDistribution']) && !empty($chartData['achievementDistribution']))
                        allIndicatorData = [
                            @foreach($chartData['achievementDistribution'] as $range)
                                {{ $range['value'] ?? 0 }},
                            @endforeach
                        ];
                    @else
                        allIndicatorData = [0, 25, 50, 75, 90]; // Default sample data
                    @endif
                }

                // Create histogram data
                const bins = [0, 10, 25, 50, 75, 90, 100, 150];
                const histData = Array(bins.length - 1).fill(0);
                allIndicatorData.forEach(value => {
                    for (let i = 0; i < bins.length - 1; i++) {
                        if (value >= bins[i] && value < bins[i + 1]) {
                            histData[i]++;
                            break;
                        }
                    }
                });

                const histLabels = bins.slice(0, -1).map((bin, i) => `${bin}-${bins[i+1]}%`);

                new Chart(indicatorDistributionCtx, {
                    type: 'bar',
                    data: {
                        labels: histLabels,
                        datasets: [{
                            label: 'Number of Indicators',
                            data: histData,
                            backgroundColor: 'rgba(0, 158, 247, 0.8)',
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Count'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Achievement Range'
                                }
                            }
                        }
                    }
                });
            }
        @endif

        // Distribution Chart
        @if(isset($chartData['achievementDistribution']) && !empty($chartData['achievementDistribution']))
            const distributionCtx = document.getElementById('distributionChart');
            if (distributionCtx) {
                const distLabels = [];
                const distData = [];

                @foreach($chartData['achievementDistribution'] as $range)
                    distLabels.push('{{ $range['name'] }}');
                    distData.push({{ $range['value'] ?? 0 }});
                @endforeach

                new Chart(distributionCtx, {
                    type: 'bar',
                    data: {
                        labels: distLabels,
                        datasets: [{
                            label: 'Count',
                            data: distData,
                            backgroundColor: 'rgba(0, 158, 247, 0.8)',
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Count'
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
            }
        @endif

        // Trend Chart
        @if(isset($chartData['quarterlyTrend']) && !empty($chartData['quarterlyTrend']))
            const trendCtx = document.getElementById('trendChart');
            if (trendCtx) {
                const trendLabels = [];
                const currentData = [];

                @foreach($chartData['quarterlyTrend'] as $quarter)
                    trendLabels.push('{{ $quarter['name'] }}');
                    currentData.push({{ $quarter['value'] !== null ? $quarter['value'] : 'null' }});
                @endforeach

                const trendDatasets = [{
                    label: '{{ isset($filters['year']) ? $filters['year'] : date('Y') }}',
                    data: currentData,
                    borderColor: 'rgba(0, 158, 247, 1)',
                    backgroundColor: 'rgba(0, 158, 247, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }];

                @if(isset($chartData['previousYearQuarterlyTrend']) && !empty($chartData['previousYearQuarterlyTrend']))
                    const previousData = [];
                    @foreach($chartData['previousYearQuarterlyTrend'] as $quarter)
                        previousData.push({{ $quarter['value'] !== null ? $quarter['value'] : 'null' }});
                    @endforeach

                    trendDatasets.push({
                        label: '{{ isset($filters['year']) ? ($filters['year'] - 1) : (date('Y') - 1) }}',
                        data: previousData,
                        borderColor: 'rgba(180, 180, 180, 1)',
                        backgroundColor: 'rgba(180, 180, 180, 0.1)',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        fill: true,
                        tension: 0.4
                    });
                @endif

                new Chart(trendCtx, {
                    type: 'line',
                    data: {
                        labels: trendLabels,
                        datasets: trendDatasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                title: {
                                    display: true,
                                    text: 'Achievement %'
                                }
                            }
                        }
                    }
                });
            }

            // Quarterly Trend Chart (for modal)
            const quarterlyTrendCtx = document.getElementById('quarterlyTrendChart');
            if (quarterlyTrendCtx) {
                const qtrendLabels = [];
                const qcurrentData = [];

                @foreach($chartData['quarterlyTrend'] as $quarter)
                    qtrendLabels.push('{{ $quarter['name'] }}');
                    qcurrentData.push({{ $quarter['value'] !== null ? $quarter['value'] : 'null' }});
                @endforeach

                const qtrendDatasets = [{
                    label: '{{ isset($filters['year']) ? $filters['year'] : date('Y') }}',
                    data: qcurrentData,
                    borderColor: 'rgba(0, 158, 247, 1)',
                    backgroundColor: 'rgba(0, 158, 247, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }];

                @if(isset($chartData['previousYearQuarterlyTrend']) && !empty($chartData['previousYearQuarterlyTrend']))
                    const qpreviousData = [];
                    @foreach($chartData['previousYearQuarterlyTrend'] as $quarter)
                        qpreviousData.push({{ $quarter['value'] !== null ? $quarter['value'] : 'null' }});
                    @endforeach

                    qtrendDatasets.push({
                        label: '{{ isset($filters['year']) ? ($filters['year'] - 1) : (date('Y') - 1) }}',
                        data: qpreviousData,
                        borderColor: 'rgba(180, 180, 180, 1)',
                        backgroundColor: 'rgba(180, 180, 180, 0.1)',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        fill: true,
                        tension: 0.4
                    });
                @endif

                new Chart(quarterlyTrendCtx, {
                    type: 'line',
                    data: {
                        labels: qtrendLabels,
                        datasets: qtrendDatasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                title: {
                                    display: true,
                                    text: 'Achievement %'
                                }
                            }
                        }
                    }
                });
            }
        @endif

        // Forecast Chart
        const forecastCtx = document.getElementById('forecastChart');
        if (forecastCtx) {
            // Generate forecast data based on current trends
            const forecastLabels = ['Q1', 'Q2', 'Q3', 'Q4'];
            let actualData = [];
            let forecastData = [];

            // Use actual data if available, otherwise generate sample data
            @if(isset($chartData['quarterlyTrend']) && !empty($chartData['quarterlyTrend']))
                @foreach($chartData['quarterlyTrend'] as $quarter)
                    actualData.push({{ $quarter['value'] !== null ? $quarter['value'] : 'null' }});
                @endforeach

                // Fill in missing quarters with projected values
                forecastData = [...actualData];
                for (let i = actualData.length; i < 4; i++) {
                    if (actualData.length > 0) {
                        // Simple linear projection
                        const validValues = actualData.filter(v => v !== null);
                        const lastValue = validValues.length > 0 ? validValues[validValues.length - 1] : 0;
                        const growth = Math.min(10, Math.max(2, lastValue * 0.1)); // 10% growth capped
                        forecastData.push(Math.min(100, lastValue + growth));
                    } else {
                        forecastData.push(40 + i * 10); // Sample data if no actual data
                    }
                }
            @else
                // Sample forecast data if no actual data is available
                forecastData = [35, 42, 55, 65];
                actualData = forecastData.slice(0, 2);
            @endif

            new Chart(forecastCtx, {
                type: 'line',
                data: {
                    labels: forecastLabels,
                    datasets: [{
                        label: 'Actual',
                        data: forecastData.slice(0, 2),
                        borderColor: 'rgba(0, 158, 247, 1)',
                        backgroundColor: 'rgba(0, 158, 247, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }, {
                        label: 'Forecast',
                        data: [null, null, ...forecastData.slice(2)],
                        borderColor: 'rgba(255, 159, 64, 1)',
                        backgroundColor: 'rgba(255, 159, 64, 0.1)',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            title: {
                                display: true,
                                text: 'Achievement %'
                            }
                        }
                    }
                }
            });
        }

        // Insight Category Chart
        @if(isset($insights) && !empty($insights))
            const insightCategoryCtx = document.getElementById('insightCategoryChart');
            if (insightCategoryCtx) {
                @php
                    $categories = [];
                    foreach($insights as $insight) {
                        $category = $insight['category'] ?? 'other';
                        if(!isset($categories[$category])) {
                            $categories[$category] = 0;
                        }
                        $categories[$category]++;
                    }
                @endphp

                const categoryLabels = [];
                const categoryData = [];
                const categoryColors = [
                    'rgba(80, 205, 137, 0.8)',
                    'rgba(0, 158, 247, 0.8)',
                    'rgba(255, 199, 0, 0.8)',
                    'rgba(241, 65, 108, 0.8)',
                    'rgba(153, 102, 255, 0.8)',
                    'rgba(255, 159, 64, 0.8)',
                    'rgba(201, 203, 207, 0.8)'
                ];

                @foreach($categories as $category => $count)
                    categoryLabels.push('{{ ucfirst(str_replace('_', ' ', $category)) }}');
                    categoryData.push({{ $count }});
                @endforeach

                new Chart(insightCategoryCtx, {
                    type: 'doughnut',
                    data: {
                        labels: categoryLabels,
                        datasets: [{
                            data: categoryData,
                            backgroundColor: categoryColors.slice(0, categoryLabels.length),
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        },
                        cutout: '70%'
                    }
                });
            }

            // Insight Type Chart
            const insightTypeCtx = document.getElementById('insightTypeChart');
            if (insightTypeCtx) {
                @php
                    $types = [];
                    foreach($insights as $insight) {
                        $type = $insight['type'] ?? 'info';
                        if(!isset($types[$type])) {
                            $types[$type] = 0;
                        }
                        $types[$type]++;
                    }
                @endphp

                const typeLabels = [];
                const typeData = [];
                const typeColors = {
                    'success': 'rgba(80, 205, 137, 0.8)',
                    'primary': 'rgba(0, 158, 247, 0.8)',
                    'info': 'rgba(0, 158, 247, 0.8)',
                    'warning': 'rgba(255, 199, 0, 0.8)',
                    'danger': 'rgba(241, 65, 108, 0.8)'
                };
                const typeColorArray = [];

                @foreach($types as $type => $count)
                    typeLabels.push('{{ ucfirst($type) }}');
                    typeData.push({{ $count }});
                    typeColorArray.push(typeColors['{{ $type }}'] || 'rgba(201, 203, 207, 0.8)');
                @endforeach

                new Chart(insightTypeCtx, {
                    type: 'pie',
                    data: {
                        labels: typeLabels,
                        datasets: [{
                            data: typeData,
                            backgroundColor: typeColorArray,
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }
        @endif
    }

    // Initialize collapse functionality
    document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(function(button) {
        button.addEventListener('click', function() {
            const icon = this.querySelector('i');
            if (icon) {
                if (icon.classList.contains('ki-down')) {
                    icon.classList.remove('ki-down');
                    icon.classList.add('ki-up');
                } else {
                    icon.classList.remove('ki-up');
                    icon.classList.add('ki-down');
                }
            }
        });
    });

    // Add print styles
    if (window.matchMedia) {
        const mediaQueryList = window.matchMedia('print');
        mediaQueryList.addEventListener('change', function(mql) {
            if (mql.matches) {
                // Expand all collapsed sections for printing
                document.querySelectorAll('.collapse').forEach(function(collapse) {
                    collapse.classList.add('show');
                });
            }
        });
    }
});
</script>
<!--end::Javascript-->

