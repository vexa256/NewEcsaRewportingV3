
<!--begin::Cluster Completeness Report-->
<div class="card mb-5 mb-xl-8">
    <!--begin::Header-->
    <div class="card-header border-0 pt-5 shadow-lg">
        <h3 class="card-title align-items-start flex-column">
            <span class="card-label fw-bold fs-3 mb-1">Cluster Completeness Report</span>
            <span class="text-muted mt-1 fw-semibold fs-7">Reporting Year: {{ $year }}</span>
        </h3>
        <div class="card-toolbar">
            <!--begin::Menu-->
            <button type="button" class="btn btn-sm btn-icon btn-color-primary btn-active-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                <i class="ki-duotone ki-category fs-6">
                    <span class="path1"></span>
                    <span class="path2"></span>
                    <span class="path3"></span>
                    <span class="path4"></span>
                </i>
            </button>
            <!--begin::Menu 3-->
            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-semibold w-200px py-3" data-kt-menu="true">
                <!--begin::Heading-->
                <div class="menu-item px-3">
                    <div class="menu-content text-muted pb-2 px-3 fs-7 text-uppercase">Export Options</div>
                </div>
                <!--end::Heading-->
                <!--begin::Menu item-->
                <div class="menu-item px-3">
                    <a href="{{ route('completeness.export', ['year' => $year, 'format' => 'csv']) }}" class="menu-link px-3">
                        <i class="ki-duotone ki-file-down fs-5 me-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>Export to CSV
                    </a>
                </div>
                <!--end::Menu item-->
                <!--begin::Menu item-->
                <div class="menu-item px-3">
                    <a href="{{ route('completeness.export', ['year' => $year, 'format' => 'pdf']) }}" class="menu-link px-3">
                        <i class="ki-duotone ki-document fs-5 me-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>Export to PDF
                    </a>
                </div>
                <!--end::Menu item-->
                <!--begin::Menu item-->
                <div class="menu-item px-3">
                    <a href="{{ route('completeness.filter') }}" class="menu-link px-3">
                        <i class="ki-duotone ki-filter fs-5 me-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>Change Filters
                    </a>
                </div>
                <!--end::Menu item-->
            </div>
            <!--end::Menu 3-->
            <!--end::Menu-->
        </div>
    </div>
    <!--end::Header-->

    <!--begin::Body-->
    <div class="card-body py-3">
        @if(isset($error))
        <!--begin::Alert-->
        <div class="alert alert-danger d-flex align-items-center p-5 mb-10">
            <i class="ki-duotone ki-shield-cross fs-2hx text-danger me-4">
                <span class="path1"></span>
                <span class="path2"></span>
            </i>
            <div class="d-flex flex-column">
                <h4 class="mb-1 text-danger">Error</h4>
                <span>{{ $error }}</span>
            </div>
        </div>
        <!--end::Alert-->
        @else

        <!--begin::Tabs-->
        <ul class="nav nav-tabs nav-line-tabs mb-5 fs-6">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#kt_tab_overview">Overview</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#kt_tab_clusters">Clusters</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#kt_tab_attention">Requiring Attention</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#kt_tab_timeline">Timeline</a>
            </li>
        </ul>
        <!--end::Tabs-->

        <!--begin::Tab Content-->
        <div class="tab-content" id="myTabContent">
            <!--begin::Tab Overview-->
            <div class="tab-pane fade show active" id="kt_tab_overview" role="tabpanel">
                <!--begin::Summary Cards-->
                <div class="row g-5 g-xl-8 mb-5">
                    <!--begin::Col-->
                    <div class="col-xl-3">
                        <!--begin::Statistics Widget 5-->
                        <a href="#" class="card bg-body hoverable card-xl-stretch mb-xl-8">
                            <!--begin::Body-->
                            <div class="card-body">
                                <i class="ki-duotone ki-chart-pie-simple text-primary fs-2x ms-n1">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                <div class="text-gray-900 fw-bold fs-2 mb-2 mt-5">{{ $summary['totalClusters'] }}</div>
                                <div class="fw-semibold text-gray-400">Total Clusters</div>
                            </div>
                            <!--end::Body-->
                        </a>
                        <!--end::Statistics Widget 5-->
                    </div>
                    <!--end::Col-->
                    <!--begin::Col-->
                    <div class="col-xl-3">
                        <!--begin::Statistics Widget 5-->
                        <a href="#" class="card bg-dark hoverable card-xl-stretch mb-xl-8">
                            <!--begin::Body-->
                            <div class="card-body">
                                <i class="ki-duotone ki-chart-line-star text-gray-100 fs-2x ms-n1">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                                <div class="text-gray-100 fw-bold fs-2 mb-2 mt-5">{{ $summary['averageCompleteness'] }}%</div>
                                <div class="fw-semibold text-gray-100">Average Completeness</div>
                            </div>
                            <!--end::Body-->
                        </a>
                        <!--end::Statistics Widget 5-->
                    </div>
                    <!--end::Col-->
                    <!--begin::Col-->
                    <div class="col-xl-3">
                        <!--begin::Statistics Widget 5-->
                        <a href="#" class="card bg-success hoverable card-xl-stretch mb-xl-8">
                            <!--begin::Body-->
                            <div class="card-body">
                                <i class="ki-duotone ki-abstract-26 text-white fs-2x ms-n1">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                <div class="text-white fw-bold fs-2 mb-2 mt-5">{{ $summary['fullCompleteness'] }}</div>
                                <div class="fw-semibold text-white">100% Complete</div>
                            </div>
                            <!--end::Body-->
                        </a>
                        <!--end::Statistics Widget 5-->
                    </div>
                    <!--end::Col-->
                    <!--begin::Col-->
                    <div class="col-xl-3">
                        <!--begin::Statistics Widget 5-->
                        <a href="#" class="card bg-warning hoverable card-xl-stretch mb-xl-8">
                            <!--begin::Body-->
                            <div class="card-body">
                                <i class="ki-duotone ki-abstract-41 text-white fs-2x ms-n1">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                <div class="text-white fw-bold fs-2 mb-2 mt-5">{{ $summary['lowCompleteness'] }}</div>
                                <div class="fw-semibold text-white">Below 50% Complete</div>
                            </div>
                            <!--end::Body-->
                        </a>
                        <!--end::Statistics Widget 5-->
                    </div>
                    <!--end::Col-->
                </div>
                <!--end::Summary Cards-->

                <!--begin::Charts Row-->
                <div class="row g-5 g-xl-8 mb-5">
                    <!--begin::Col-->
                    <div class="col-xl-6">
                        <!--begin::Charts Widget 1-->
                        <div class="card card-xl-stretch mb-xl-8">
                            <!--begin::Header-->
                            <div class="card-header border-0 pt-5">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold fs-3 mb-1">Completeness Distribution</span>
                                    <span class="text-muted fw-semibold fs-7">Clusters by completeness level</span>
                                </h3>
                            </div>
                            <!--end::Header-->
                            <!--begin::Body-->
                            <div class="card-body">
                                <!--begin::Chart-->
                                <div id="kt_completeness_distribution_chart" style="height: 350px"></div>
                                <!--end::Chart-->
                            </div>
                            <!--end::Body-->
                        </div>
                        <!--end::Charts Widget 1-->
                    </div>
                    <!--end::Col-->
                    <!--begin::Col-->
                    <div class="col-xl-6">
                        <!--begin::Charts Widget 2-->
                        <div class="card card-xl-stretch mb-5 mb-xl-8">
                            <!--begin::Header-->
                            <div class="card-header border-0 pt-5">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold fs-3 mb-1">Timeline Completeness</span>
                                    <span class="text-muted fw-semibold fs-7">Quarterly completeness for {{ $year }}</span>
                                </h3>
                            </div>
                            <!--end::Header-->
                            <!--begin::Body-->
                            <div class="card-body">
                                <!--begin::Chart-->
                                <div id="kt_timeline_completeness_chart" style="height: 350px"></div>
                                <!--end::Chart-->
                            </div>
                            <!--end::Body-->
                        </div>
                        <!--end::Charts Widget 2-->
                    </div>
                    <!--end::Col-->
                </div>
                <!--end::Charts Row-->

                <!--begin::Action Buttons-->
                <div class="row mb-5">

                    <div class="col-md-6">
                        <a href="#" class="btn btn-info w-100" data-bs-toggle="modal" data-bs-target="#kt_modal_comparison">
                            <i class="ki-duotone ki-chart-line fs-2 me-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>Compare Clusters
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('completeness.filter') }}" class="btn btn-light-primary w-100">
                            <i class="ki-duotone ki-filter fs-2 me-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>Change Filters
                        </a>
                    </div>
                </div>
                <!--end::Action Buttons-->
            </div>
            <!--end::Tab Overview-->

            <!--begin::Tab Clusters-->
            <div class="tab-pane fade" id="kt_tab_clusters" role="tabpanel">
                <!--begin::Table-->
                <div class="table-responsive">
                    <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4" id="kt_clusters_table">
                        <thead>
                            <tr class="fw-bold text-muted">
                                <th class="min-w-150px">Cluster</th>
                                <th class="min-w-140px">Timeline</th>
                                <th class="min-w-120px">Total Indicators</th>
                                <th class="min-w-120px">Reported</th>
                                <th class="min-w-120px">Not Reported</th>
                                <th class="min-w-120px">Completeness</th>
                                <th class="min-w-100px text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reportData as $row)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="symbol symbol-45px me-5">
                                            <span class="symbol-label bg-light-primary text-primary">
                                                <i class="ki-duotone ki-abstract-{{ 10 + ($loop->index % 30) }} fs-2x">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </span>
                                        </div>
                                        <div class="d-flex justify-content-start flex-column">
                                            <a href="#" class="text-dark fw-bold text-hover-primary fs-6">{{ $row->cluster_name }}</a>
                                            <span class="text-muted fw-semibold text-muted d-block fs-7">ID: {{ $row->cluster_text_identifier }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <a href="#" class="text-dark fw-bold text-hover-primary d-block fs-6">{{ $row->timeline_name }}</a>
                                    <span class="text-muted fw-semibold text-muted d-block fs-7">Q{{ $row->timeline_quarter }}, {{ $row->timeline_year }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="text-dark fw-bold d-block fs-6">{{ $row->total_indicators }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="text-dark fw-bold d-block fs-6">{{ $row->reported_indicators }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="text-dark fw-bold d-block fs-6">{{ $row->not_reported_indicators }}</span>
                                </td>
                                <td>
                                    <div class="d-flex flex-column w-100 me-2">
                                        <div class="d-flex flex-stack mb-2">
                                            <span class="text-muted me-2 fs-7 fw-semibold">{{ $row->completeness_percentage }}%</span>
                                        </div>
                                        <div class="progress h-6px w-100">
                                            @if($row->completeness_percentage >= 90)
                                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $row->completeness_percentage }}%" aria-valuenow="{{ $row->completeness_percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                                            @elseif($row->completeness_percentage >= 70)
                                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $row->completeness_percentage }}%" aria-valuenow="{{ $row->completeness_percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                                            @elseif($row->completeness_percentage >= 50)
                                            <div class="progress-bar bg-info" role="progressbar" style="width: {{ $row->completeness_percentage }}%" aria-valuenow="{{ $row->completeness_percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                                            @elseif($row->completeness_percentage >= 30)
                                            <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $row->completeness_percentage }}%" aria-valuenow="{{ $row->completeness_percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                                            @else
                                            <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $row->completeness_percentage }}%" aria-valuenow="{{ $row->completeness_percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-end flex-shrink-0">
                                        <a href="{{ route('completeness.detail', ['clusterPk' => $row->cluster_pk, 'year' => $row->timeline_year]) }}" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1">
                                            <i class="ki-duotone ki-switch fs-2">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                        </a>
                                        {{-- <a href="#" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" data-bs-toggle="modal" data-bs-target="#kt_modal_cluster_detail" data-cluster-pk="{{ $row->cluster_pk }}" data-cluster-name="{{ $row->cluster_name }}" data-timeline-year="{{ $row->timeline_year }}" data-timeline-quarter="{{ $row->timeline_quarter }}">
                                            <i class="ki-duotone ki-magnifier fs-2">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                        </a> --}}
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!--end::Table-->
            </div>
            <!--end::Tab Clusters-->

            <!--begin::Tab Requiring Attention-->
            <div class="tab-pane fade" id="kt_tab_attention" role="tabpanel">
                <!--begin::Row-->
                <div class="row g-5 g-xl-8 mb-5">
                    <!--begin::Col-->
                    <div class="col-xl-6">
                        <!--begin::Charts Widget-->
                        <div class="card card-xl-stretch mb-xl-8">
                            <!--begin::Header-->
                            <div class="card-header border-0 pt-5">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold fs-3 mb-1">Clusters Requiring Attention</span>
                                    <span class="text-muted fw-semibold fs-7">Clusters with low completeness</span>
                                </h3>
                            </div>
                            <!--end::Header-->
                            <!--begin::Body-->
                            <div class="card-body">
                                <!--begin::Chart-->
                                <div id="kt_attention_clusters_chart" style="height: 350px"></div>
                                <!--end::Chart-->
                            </div>
                            <!--end::Body-->
                        </div>
                        <!--end::Charts Widget-->
                    </div>
                    <!--end::Col-->
                    <!--begin::Col-->
                    <div class="col-xl-6">
                        <!--begin::Charts Widget-->
                        <div class="card card-xl-stretch mb-5 mb-xl-8">
                            <!--begin::Header-->
                            <div class="card-header border-0 pt-5">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold fs-3 mb-1">Missing Indicators</span>
                                    <span class="text-muted fw-semibold fs-7">Number of unreported indicators by cluster</span>
                                </h3>
                            </div>
                            <!--end::Header-->
                            <!--begin::Body-->
                            <div class="card-body">
                                <!--begin::Chart-->
                                <div id="kt_missing_indicators_chart" style="height: 350px"></div>
                                <!--end::Chart-->
                            </div>
                            <!--end::Body-->
                        </div>
                        <!--end::Charts Widget-->
                    </div>
                    <!--end::Col-->
                </div>
                <!--end::Row-->

                <!--begin::Table-->
                <div class="table-responsive">
                    <table class="table table-row-bordered table-row-gray-100 align-middle gs-0 gy-3">
                        <thead>
                            <tr class="fw-bold text-muted">
                                <th class="min-w-150px">Cluster</th>
                                <th class="min-w-140px">Timeline</th>
                                <th class="min-w-140px">Completeness</th>
                                <th class="min-w-120px">Missing Indicators</th>
                                <th class="min-w-100px text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $lowCompleteness = $reportData->where('completeness_percentage', '<', 50)->sortBy('completeness_percentage');
                            @endphp

                            @foreach($lowCompleteness as $row)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="symbol symbol-45px me-5">
                                            <span class="symbol-label bg-light-danger text-danger">
                                                <i class="ki-duotone ki-abstract-{{ 10 + ($loop->index % 30) }} fs-2x">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </span>
                                        </div>
                                        <div class="d-flex justify-content-start flex-column">
                                            <a href="#" class="text-dark fw-bold text-hover-primary fs-6">{{ $row->cluster_name }}</a>
                                            <span class="text-muted fw-semibold text-muted d-block fs-7">ID: {{ $row->cluster_text_identifier }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <a href="#" class="text-dark fw-bold text-hover-primary d-block fs-6">{{ $row->timeline_name }}</a>
                                    <span class="text-muted fw-semibold text-muted d-block fs-7">Q{{ $row->timeline_quarter }}, {{ $row->timeline_year }}</span>
                                </td>
                                <td>
                                    <div class="d-flex flex-column w-100 me-2">
                                        <div class="d-flex flex-stack mb-2">
                                            <span class="text-muted me-2 fs-7 fw-semibold">{{ $row->completeness_percentage }}%</span>
                                        </div>
                                        <div class="progress h-6px w-100">
                                            <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $row->completeness_percentage }}%" aria-valuenow="{{ $row->completeness_percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="text-dark fw-bold d-block fs-6">{{ $row->not_reported_indicators }}</span>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-end flex-shrink-0">
                                        <a href="{{ route('completeness.detail', ['clusterPk' => $row->cluster_pk, 'year' => $row->timeline_year]) }}" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1">
                                            <i class="ki-duotone ki-switch fs-2">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                        </a>
                                        {{-- <a href="#" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm" data-bs-toggle="modal" data-bs-target="#kt_modal_cluster_detail" data-cluster-pk="{{ $row->cluster_pk }}" data-cluster-name="{{ $row->cluster_name }}" data-timeline-year="{{ $row->timeline_year }}" data-timeline-quarter="{{ $row->timeline_quarter }}">
                                            <i class="ki-duotone ki-magnifier fs-2">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                        </a> --}}
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!--end::Table-->
            </div>
            <!--end::Tab Requiring Attention-->

            <!--begin::Tab Timeline-->
            <div class="tab-pane fade" id="kt_tab_timeline" role="tabpanel">
                <!--begin::Row-->
                <div class="row g-5 g-xl-8 mb-5">
                    <!--begin::Col-->
                    <div class="col-xl-12">
                        <!--begin::Charts Widget-->
                        <div class="card card-xl-stretch mb-xl-8">
                            <!--begin::Header-->
                            <div class="card-header border-0 pt-5">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold fs-3 mb-1">Quarterly Completeness Trend</span>
                                    <span class="text-muted fw-semibold fs-7">Completeness percentage by quarter for {{ $year }}</span>
                                </h3>
                            </div>
                            <!--end::Header-->
                            <!--begin::Body-->
                            <div class="card-body">
                                <!--begin::Chart-->
                                <div id="kt_quarterly_trend_chart" style="height: 350px"></div>
                                <!--end::Chart-->
                            </div>
                            <!--end::Body-->
                        </div>
                        <!--end::Charts Widget-->
                    </div>
                    <!--end::Col-->
                </div>
                <!--end::Row-->

                <!--begin::Table-->
                <div class="table-responsive">
                    <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                        <thead>
                            <tr class="fw-bold text-muted">
                                <th class="min-w-150px">Quarter</th>
                                <th class="min-w-100px">Total Clusters</th>
                                <th class="min-w-100px">Total Indicators</th>
                                <th class="min-w-100px">Reported</th>
                                <th class="min-w-100px">Not Reported</th>
                                <th class="min-w-100px">Completeness</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $timelineGroups = $reportData->groupBy('timeline_quarter');
                            @endphp

                            @foreach($timelineGroups as $quarter => $timelineData)
                            @php
                                $totalClusters = $timelineData->unique('cluster_pk')->count();
                                $totalIndicators = $timelineData->sum('total_indicators');
                                $reportedIndicators = $timelineData->sum('reported_indicators');
                                $notReportedIndicators = $timelineData->sum('not_reported_indicators');
                                $avgCompleteness = round($reportedIndicators * 100 / ($totalIndicators ?: 1), 2);

                                $statusClass = 'success';

                                if ($avgCompleteness < 50) {
                                    $statusClass = 'danger';
                                } elseif ($avgCompleteness < 75) {
                                    $statusClass = 'warning';
                                } elseif ($avgCompleteness < 100) {
                                    $statusClass = 'info';
                                }
                            @endphp
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="symbol symbol-45px me-5">
                                            <span class="symbol-label bg-light-{{ $statusClass }} text-{{ $statusClass }} fw-bolder">
                                                Q{{ $quarter }}
                                            </span>
                                        </div>
                                        <div class="d-flex justify-content-start flex-column">
                                            <span class="text-dark fw-bolder text-hover-primary fs-6">Quarter {{ $quarter }}</span>
                                            <span class="text-muted fw-bold text-muted d-block fs-7">{{ $year }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-dark fw-bolder d-block fs-6">{{ $totalClusters }}</span>
                                </td>
                                <td>
                                    <span class="text-dark fw-bolder d-block fs-6">{{ $totalIndicators }}</span>
                                </td>
                                <td>
                                    <span class="text-dark fw-bolder d-block fs-6">{{ $reportedIndicators }}</span>
                                </td>
                                <td>
                                    <span class="text-dark fw-bolder d-block fs-6">{{ $notReportedIndicators }}</span>
                                </td>
                                <td>
                                    <div class="d-flex flex-column w-100 me-2">
                                        <div class="d-flex flex-stack mb-2">
                                            <span class="text-muted me-2 fs-7 fw-bold">{{ $avgCompleteness }}%</span>
                                        </div>
                                        <div class="progress h-6px w-100">
                                            <div class="progress-bar bg-{{ $statusClass }}" role="progressbar" style="width: {{ $avgCompleteness }}%" aria-valuenow="{{ $avgCompleteness }}" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!--end::Table-->
            </div>
            <!--end::Tab Timeline-->
        </div>
        <!--end::Tab Content-->
        @endif
    </div>
    <!--end::Body-->
</div>
<!--end::Cluster Completeness Report-->

<!--begin::Modals-->
<!--begin::Modal - Cluster Detail-->
{{-- <div class="modal fade" id="kt_modal_cluster_detail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Cluster Detail</h2>
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ki-duotone ki-cross fs-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </div>
            </div>
            <div class="modal-body">
                <div class="d-flex flex-column flex-xl-row">
                    <!--begin::Sidebar-->
                    <div class="flex-column flex-lg-row-auto w-100 w-xl-300px mb-10">
                        <!--begin::Card-->
                        <div class="card mb-5 mb-xl-8">
                            <!--begin::Card body-->
                            <div class="card-body pt-15">
                                <!--begin::Summary-->
                                <div class="d-flex flex-center flex-column mb-5">
                                    <!--begin::Avatar-->
                                    <div class="symbol symbol-100px symbol-circle mb-7">
                                        <span class="symbol-label bg-light-primary text-primary fs-1">
                                            <i class="ki-duotone ki-abstract-24 fs-2x">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                        </span>
                                    </div>
                                    <!--end::Avatar-->
                                    <!--begin::Name-->
                                    <a href="#" class="fs-3 text-gray-800 text-hover-primary fw-bold mb-1 cluster-name-display">Cluster Name</a>
                                    <!--end::Name-->
                                    <!--begin::Position-->
                                    <div class="fs-5 fw-semibold text-muted mb-6 timeline-period-display">Q1, 2023</div>
                                    <!--end::Position-->
                                </div>
                                <!--end::Summary-->
                                <!--begin::Details toggle-->
                                <div class="d-flex flex-stack fs-4 py-3">
                                    <div class="fw-bold rotate collapsible" data-bs-toggle="collapse" href="#kt_cluster_view_details" role="button" aria-expanded="false" aria-controls="kt_cluster_view_details">Details
                                    <span class="ms-2 rotate-180">
                                        <i class="ki-duotone ki-down fs-3"></i>
                                    </span></div>
                                </div>
                                <!--end::Details toggle-->
                                <div class="separator"></div>
                                <!--begin::Details content-->
                                <div id="kt_cluster_view_details" class="collapse show">
                                    <div class="pb-5 fs-6">
                                        <!--begin::Details item-->
                                        <div class="fw-bold mt-5">Cluster ID</div>
                                        <div class="text-gray-600 cluster-id-display">ID12345</div>
                                        <!--begin::Details item-->
                                        <!--begin::Details item-->
                                        <div class="fw-bold mt-5">Total Indicators</div>
                                        <div class="text-gray-600 total-indicators-display">25</div>
                                        <!--begin::Details item-->
                                        <!--begin::Details item-->
                                        <div class="fw-bold mt-5">Reported Indicators</div>
                                        <div class="text-gray-600 reported-indicators-display">20</div>
                                        <!--begin::Details item-->
                                        <!--begin::Details item-->
                                        <div class="fw-bold mt-5">Completeness</div>
                                        <div class="text-gray-600 completeness-display">80%</div>
                                        <!--begin::Details item-->
                                    </div>
                                </div>
                                <!--end::Details content-->
                            </div>
                            <!--end::Card body-->
                        </div>
                        <!--end::Card-->
                    </div>
                    <!--end::Sidebar-->
                    <!--begin::Content-->
                    <div class="flex-lg-row-fluid ms-lg-15">
                        <!--begin::Card-->
                        <div class="card pt-4 mb-6 mb-xl-9">
                            <!--begin::Card header-->
                            <div class="card-header border-0">
                                <!--begin::Card title-->
                                <div class="card-title">
                                    <h2>Indicator Details</h2>
                                </div>
                                <!--end::Card title-->
                            </div>
                            <!--end::Card header-->
                            <!--begin::Card body-->
                            <div class="card-body pt-0 pb-5">
                                <div id="cluster_detail_loading" class="text-center">
                                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span> Loading...
                                </div>
                                <div id="cluster_detail_content" class="d-none">
                                    <!--begin::Table-->
                                    <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_indicators">
                                        <thead>
                                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                                <th class="min-w-125px">Indicator</th>
                                                <th class="min-w-125px">Status</th>
                                                <th class="min-w-125px">Reported Date</th>
                                                <th class="min-w-125px">Reported By</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-gray-600 fw-semibold" id="indicator_details_body">
                                            <!-- Indicator details will be loaded here -->
                                        </tbody>
                                    </table>
                                    <!--end::Table-->
                                </div>
                            </div>
                            <!--end::Card body-->
                        </div>
                        <!--end::Card-->
                        <!--begin::Card-->
                        <div class="card pt-4 mb-6 mb-xl-9">
                            <!--begin::Card header-->
                            <div class="card-header border-0">
                                <!--begin::Card title-->
                                <div class="card-title">
                                    <h2>Completeness Trend</h2>
                                </div>
                                <!--end::Card title-->
                            </div>
                            <!--end::Card header-->
                            <!--begin::Card body-->
                            <div class="card-body pt-0 pb-5">
                                <!--begin::Chart-->
                                <div id="kt_modal_cluster_trend_chart" style="height: 350px"></div>
                                <!--end::Chart-->
                            </div>
                            <!--end::Card body-->
                        </div>
                        <!--end::Card-->
                    </div>
                    <!--end::Content-->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="export_cluster_detail">
                    <i class="ki-duotone ki-file-down fs-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>Export Details
                </button>
            </div>
        </div>
    </div>
</div> --}}
<!--end::Modal - Cluster Detail-->

<!--begin::Modal - Comparison-->
<div class="modal fade" id="kt_modal_comparison" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Cluster Comparison</h2>
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ki-duotone ki-cross fs-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </div>
            </div>
            <div class="modal-body">
                <form action="{{ route('completeness.compare') }}" method="GET">
                    <div class="row mb-5">
                        <div class="col-md-6">
                            <label class="form-label">Select Clusters to Compare</label>
                            <select class="form-select" name="cluster_pk[]" id="comparison_clusters" data-control="select2" data-placeholder="Select clusters" multiple required>
                                @foreach(collect($reportData)->unique('cluster_pk') as $cluster)
                                <option value="{{ $cluster->cluster_pk }}">{{ $cluster->cluster_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Year</label>
                            <select class="form-select" name="year" id="comparison_year">
                                <option value="{{ $year }}" selected>{{ $year }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="ki-duotone ki-chart-line-star fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>Compare Clusters
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!--end::Modal - Comparison-->
<!--end::Modals-->

<!--begin::Javascript-->
<script>
    // Initialize charts and data when the document is ready
    document.addEventListener("DOMContentLoaded", function() {
        // Initialize main charts
        initializeCompletionDistributionChart();
        initializeTimelineCompletenessChart();
        initializeQuarterlyTrendChart();

        // Initialize attention charts
        initializeAttentionClustersChart();
        initializeMissingIndicatorsChart();

        // Set up modal event handlers
        setupClusterDetailModal();

        // Initialize export buttons
        setupExportButtons();
    });

    // Initialize the completeness distribution chart
    function initializeCompletionDistributionChart() {
        var element = document.getElementById('kt_completeness_distribution_chart');

        if (!element) {
            return;
        }

        var options = {
            series: [{
                name: 'Clusters',
                data: [
                    @php
                    $ranges = [
                        '0-25%' => $reportData->where('completeness_percentage', '<=', 25)->count(),
                        '26-50%' => $reportData->where('completeness_percentage', '>', 25)->where('completeness_percentage', '<=', 50)->count(),
                        '51-75%' => $reportData->where('completeness_percentage', '>', 50)->where('completeness_percentage', '<=', 75)->count(),
                        '76-99%' => $reportData->where('completeness_percentage', '>', 75)->where('completeness_percentage', '<', 100)->count(),
                        '100%' => $reportData->where('completeness_percentage', '=', 100)->count()
                    ];
                    @endphp

                    @foreach($ranges as $count)
                    {{ $count }}@if(!$loop->last),@endif
                    @endforeach
                ]
            }],
            chart: {
                type: 'bar',
                height: 350,
                toolbar: {
                    show: false
                }
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '55%',
                    borderRadius: 5,
                    endingShape: 'rounded'
                },
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                show: true,
                width: 2,
                colors: ['transparent']
            },
            xaxis: {
                categories: ['0-25%', '26-50%', '51-75%', '76-99%', '100%'],
                axisBorder: {
                    show: false,
                },
                axisTicks: {
                    show: false
                },
                labels: {
                    style: {
                        colors: '#A1A5B7',
                        fontSize: '12px'
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: '#A1A5B7',
                        fontSize: '12px'
                    }
                }
            },
            fill: {
                opacity: 1,
                colors: ['#50CD89']
            },
            states: {
                normal: {
                    filter: {
                        type: 'none',
                        value: 0
                    }
                },
                hover: {
                    filter: {
                        type: 'none',
                        value: 0
                    }
                },
                active: {
                    allowMultipleDataPointsSelection: false,
                    filter: {
                        type: 'none',
                        value: 0
                    }
                }
            },
            tooltip: {
                style: {
                    fontSize: '12px'
                },
                y: {
                    formatter: function (val) {
                        return val + " clusters"
                    }
                }
            },
            colors: ['#50CD89'],
            grid: {
                borderColor: '#F3F3F3',
                strokeDashArray: 4,
                yaxis: {
                    lines: {
                        show: true
                    }
                }
            }
        };

        var chart = new ApexCharts(element, options);
        chart.render();
    }

    // Initialize the timeline completeness chart
    function initializeTimelineCompletenessChart() {
        var element = document.getElementById('kt_timeline_completeness_chart');

        if (!element) {
            return;
        }

        @php
        $quarterlyData = $reportData->groupBy('timeline_quarter')
            ->map(function($items) {
                return [
                    'quarter' => $items->first()->timeline_quarter,
                    'completeness' => round($items->avg('completeness_percentage'), 2)
                ];
            })
            ->sortBy('quarter')
            ->values();
        @endphp

        var options = {
            series: [{
                name: 'Completeness',
                data: [
                    @foreach($quarterlyData as $data)
                    {{ $data['completeness'] }}@if(!$loop->last),@endif
                    @endforeach
                ]
            }],
            chart: {
                type: 'bar',
                height: 350,
                toolbar: {
                    show: false
                }
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '30%',
                    borderRadius: 5,
                    endingShape: 'rounded'
                },
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                show: true,
                width: 2,
                colors: ['transparent']
            },
            xaxis: {
                categories: [
                    @foreach($quarterlyData as $data)
                    'Q{{ $data['quarter'] }}'@if(!$loop->last),@endif
                    @endforeach
                ],
                axisBorder: {
                    show: false,
                },
                axisTicks: {
                    show: false
                },
                labels: {
                    style: {
                        colors: '#A1A5B7',
                        fontSize: '12px'
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: '#A1A5B7',
                        fontSize: '12px'
                    }
                },
                min: 0,
                max: 100
            },
            fill: {
                opacity: 1,
                colors: ['#3E97FF']
            },
            states: {
                normal: {
                    filter: {
                        type: 'none',
                        value: 0
                    }
                },
                hover: {
                    filter: {
                        type: 'none',
                        value: 0
                    }
                },
                active: {
                    allowMultipleDataPointsSelection: false,
                    filter: {
                        type: 'none',
                        value: 0
                    }
                }
            },
            tooltip: {
                style: {
                    fontSize: '12px'
                },
                y: {
                    formatter: function (val) {
                        return val + "%"
                    }
                }
            },
            colors: ['#3E97FF'],
            grid: {
                borderColor: '#F3F3F3',
                strokeDashArray: 4,
                yaxis: {
                    lines: {
                        show: true
                    }
                }
            }
        };

        var chart = new ApexCharts(element, options);
        chart.render();
    }

    // Initialize the quarterly trend chart
    function initializeQuarterlyTrendChart() {
        var element = document.getElementById('kt_quarterly_trend_chart');

        if (!element) {
            return;
        }

        @php
        $quarterlyData = $reportData->groupBy('timeline_quarter')
            ->map(function($items) {
                return [
                    'quarter' => $items->first()->timeline_quarter,
                    'completeness' => round($items->avg('completeness_percentage'), 2)
                ];
            })
            ->sortBy('quarter')
            ->values();
        @endphp

        var options = {
            series: [{
                name: 'Completeness',
                data: [
                    @foreach($quarterlyData as $data)
                    {{ $data['completeness'] }}@if(!$loop->last),@endif
                    @endforeach
                ]
            }],
            chart: {
                type: 'line',
                height: 350,
                toolbar: {
                    show: false
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            xaxis: {
                categories: [
                    @foreach($quarterlyData as $data)
                    'Q{{ $data['quarter'] }}'@if(!$loop->last),@endif
                    @endforeach
                ],
                axisBorder: {
                    show: false,
                },
                axisTicks: {
                    show: false
                },
                labels: {
                    style: {
                        colors: '#A1A5B7',
                        fontSize: '12px'
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: '#A1A5B7',
                        fontSize: '12px'
                    },
                    formatter: function (val) {
                        return val + "%"
                    }
                },
                min: 0,
                max: 100
            },
            fill: {
                opacity: 0.3,
                colors: ['#50CD89']
            },
            tooltip: {
                style: {
                    fontSize: '12px'
                },
                y: {
                    formatter: function (val) {
                        return val + "%"
                    }
                }
            },
            colors: ['#50CD89'],
            grid: {
                borderColor: '#F3F3F3',
                strokeDashArray: 4,
                yaxis: {
                    lines: {
                        show: true
                    }
                }
            },
            markers: {
                size: 5,
                colors: ['#50CD89'],
                strokeColors: '#ffffff',
                strokeWidth: 2,
                hover: {
                    size: 7
                }
            }
        };

        var chart = new ApexCharts(element, options);
        chart.render();
    }

    // Initialize the attention clusters chart
    function initializeAttentionClustersChart() {
        var element = document.getElementById('kt_attention_clusters_chart');

        if (!element) {
            return;
        }

        @php
        $lowCompleteness = $reportData->where('completeness_percentage', '<', 50)
            ->sortBy('completeness_percentage')
            ->take(5);
        @endphp

        var options = {
            series: [{
                name: 'Completeness',
                data: [
                    @foreach($lowCompleteness as $cluster)
                    {{ $cluster->completeness_percentage }}@if(!$loop->last),@endif
                    @endforeach
                ]
            }],
            chart: {
                type: 'bar',
                height: 350,
                toolbar: {
                    show: false
                }
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    barHeight: '50%',
                    borderRadius: 5,
                    endingShape: 'rounded'
                },
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                show: true,
                width: 2,
                colors: ['transparent']
            },
            xaxis: {
                categories: [
                    @foreach($lowCompleteness as $cluster)
                    '{{ $cluster->cluster_name }}'@if(!$loop->last),@endif
                    @endforeach
                ],
                axisBorder: {
                    show: false,
                },
                axisTicks: {
                    show: false
                },
                labels: {
                    style: {
                        colors: '#A1A5B7',
                        fontSize: '12px'
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: '#A1A5B7',
                        fontSize: '12px'
                    }
                }
            },
            fill: {
                opacity: 1,
                colors: ['#F1416C']
            },
            states: {
                normal: {
                    filter: {
                        type: 'none',
                        value: 0
                    }
                },
                hover: {
                    filter: {
                        type: 'none',
                        value: 0
                    }
                },
                active: {
                    allowMultipleDataPointsSelection: false,
                    filter: {
                        type: 'none',
                        value: 0
                    }
                }
            },
            tooltip: {
                style: {
                    fontSize: '12px'
                },
                y: {
                    formatter: function (val) {
                        return val + "%"
                    }
                }
            },
            colors: ['#F1416C'],
            grid: {
                borderColor: '#F3F3F3',
                strokeDashArray: 4,
                xaxis: {
                    lines: {
                        show: true
                    }
                }
            }
        };

        var chart = new ApexCharts(element, options);
        chart.render();
    }

    // Initialize the missing indicators chart
    function initializeMissingIndicatorsChart() {
        var element = document.getElementById('kt_missing_indicators_chart');

        if (!element) {
            return;
        }

        @php
        $missingIndicators = $reportData->where('not_reported_indicators', '>', 0)
            ->sortByDesc('not_reported_indicators')
            ->take(5);
        @endphp

        var options = {
            series: [{
                name: 'Missing Indicators',
                data: [
                    @foreach($missingIndicators as $cluster)
                    {{ $cluster->not_reported_indicators }}@if(!$loop->last),@endif
                    @endforeach
                ]
            }],
            chart: {
                type: 'bar',
                height: 350,
                toolbar: {
                    show: false
                }
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    barHeight: '50%',
                    borderRadius: 5,
                    endingShape: 'rounded'
                },
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                show: true,
                width: 2,
                colors: ['transparent']
            },
            xaxis: {
                categories: [
                    @foreach($missingIndicators as $cluster)
                    '{{ $cluster->cluster_name }}'@if(!$loop->last),@endif
                    @endforeach
                ],
                axisBorder: {
                    show: false,
                },
                axisTicks: {
                    show: false
                },
                labels: {
                    style: {
                        colors: '#A1A5B7',
                        fontSize: '12px'
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: '#A1A5B7',
                        fontSize: '12px'
                    }
                }
            },
            fill: {
                opacity: 1,
                colors: ['#F1BC00']
            },
            states: {
                normal: {
                    filter: {
                        type: 'none',
                        value: 0
                    }
                },
                hover: {
                    filter: {
                        type: 'none',
                        value: 0
                    }
                },
                active: {
                    allowMultipleDataPointsSelection: false,
                    filter: {
                        type: 'none',
                        value: 0
                    }
                }
            },
            tooltip: {
                style: {
                    fontSize: '12px'
                },
                y: {
                    formatter: function (val) {
                        return val + " indicators"
                    }
                }
            },
            colors: ['#F1BC00'],
            grid: {
                borderColor: '#F3F3F3',
                strokeDashArray: 4,
                xaxis: {
                    lines: {
                        show: true
                    }
                }
            }
        };

        var chart = new ApexCharts(element, options);
        chart.render();
    }

    // Set up the cluster detail modal
    function setupClusterDetailModal() {
        var modal = document.getElementById('kt_modal_cluster_detail');

        if (!modal) {
            return;
        }

        modal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var clusterPk = button.getAttribute('data-cluster-pk');
            var clusterName = button.getAttribute('data-cluster-name');
            var timelineYear = button.getAttribute('data-timeline-year');
            var timelineQuarter = button.getAttribute('data-timeline-quarter');

            // Update modal content with cluster info
            modal.querySelector('.cluster-name-display').textContent = clusterName;
            modal.querySelector('.timeline-period-display').textContent = 'Q' + timelineQuarter + ', ' + timelineYear;

            // Show loading indicator
            document.getElementById('cluster_detail_loading').classList.remove('d-none');
            document.getElementById('cluster_detail_content').classList.add('d-none');

            // Fetch cluster details from API
            fetch(`{{ env('APP_URL') }}/api/cluster-detail?cluster_pk=${clusterPk}&year=${timelineYear}&quarter=${timelineQuarter}`)
                .then(response => response.json())
                .then(data => {
                    // Update sidebar details
                    modal.querySelector('.cluster-id-display').textContent = data.cluster_text_identifier;
                    modal.querySelector('.total-indicators-display').textContent = data.total_indicators;
                    modal.querySelector('.reported-indicators-display').textContent = data.reported_indicators;
                    modal.querySelector('.completeness-display').textContent = data.completeness_percentage + '%';

                    // Populate indicator details table
                    var tableBody = document.getElementById('indicator_details_body');
                    tableBody.innerHTML = '';

                    if (data.indicators && data.indicators.length > 0) {
                        data.indicators.forEach(function(indicator) {
                            var row = document.createElement('tr');

                            var statusClass = indicator.is_reported ? 'badge-light-success' : 'badge-light-danger';
                            var statusText = indicator.is_reported ? 'Reported' : 'Not Reported';

                            row.innerHTML = `
                                <td>${indicator.indicator_name}</td>
                                <td><span class="badge ${statusClass}">${statusText}</span></td>
                                <td>${indicator.reported_date || 'N/A'}</td>
                                <td>${indicator.reported_by || 'N/A'}</td>
                            `;

                            tableBody.appendChild(row);
                        });
                    } else {
                        tableBody.innerHTML = '<tr><td colspan="4" class="text-center">No indicator details available</td></tr>';
                    }

                    // Initialize trend chart
                    initializeClusterTrendChart(data.trend_data);

                    // Hide loading indicator
                    document.getElementById('cluster_detail_loading').classList.add('d-none');
                    document.getElementById('cluster_detail_content').classList.remove('d-none');
                })
                .catch(error => {
                    console.error('Error fetching cluster details:', error);
                    // Show error message
                    document.getElementById('cluster_detail_loading').innerHTML = 'Error loading data. Please try again.';
                });
        });
    }

    // Initialize the cluster trend chart in the detail modal
    function initializeClusterTrendChart(trendData) {
        var element = document.getElementById('kt_modal_cluster_trend_chart');

        if (!element) {
            return;
        }

        // Destroy previous chart if exists
        if (window.clusterTrendChart) {
            window.clusterTrendChart.destroy();
        }

        var options = {
            series: [{
                name: 'Completeness',
                data: trendData.map(item => item.completeness_percentage)
            }],
            chart: {
                type: 'line',
                height: 350,
                toolbar: {
                    show: false
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            xaxis: {
                categories: trendData.map(item => 'Q' + item.timeline_quarter + ' ' + item.timeline_year),
                axisBorder: {
                    show: false,
                },
                axisTicks: {
                    show: false
                },
                labels: {
                    style: {
                        colors: '#A1A5B7',
                        fontSize: '12px'
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: '#A1A5B7',
                        fontSize: '12px'
                    },
                    formatter: function (val) {
                        return val + "%"
                    }
                },
                min: 0,
                max: 100
            },
            fill: {
                opacity: 1,
                colors: ['#50CD89']
            },
            tooltip: {
                style: {
                    fontSize: '12px'
                },
                y: {
                    formatter: function (val) {
                        return val + "%"
                    }
                }
            },
            colors: ['#50CD89'],
            grid: {
                borderColor: '#F3F3F3',
                strokeDashArray: 4,
                yaxis: {
                    lines: {
                        show: true
                    }
                }
            },
            markers: {
                size: 5,
                colors: ['#50CD89'],
                strokeColors: '#ffffff',
                strokeWidth: 2,
                hover: {
                    size: 7
                }
            }
        };

        window.clusterTrendChart = new ApexCharts(element, options);
        window.clusterTrendChart.render();
    }

    // Set up export buttons
    function setupExportButtons() {
        // Cluster detail export
        document.getElementById('export_cluster_detail').addEventListener('click', function() {
            var clusterPk = document.querySelector('.cluster-id-display').textContent;
            var year = document.querySelector('.timeline-period-display').textContent.split(', ')[1];
            window.location.href = `{{ route('completeness.export') }}?format=csv&cluster_pk=${clusterPk}&year=${year}`;
        });
    }
</script>
<!--end::Javascript-->
