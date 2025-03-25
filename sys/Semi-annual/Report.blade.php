@php
// Set default year to previous year, fallback to current year if no data
$currentYear = date('Y');
$availableYears = $availableYears ?? [];
$defaultYear = !empty($availableYears) ?
    (in_array($currentYear - 1, $availableYears) ? $currentYear - 1 : $availableYears[0]) :
    $currentYear;

// Set selected values or defaults
$selectedYear = $selectedYear ?? $defaultYear;
$selectedSemiAnnual = $selectedSemiAnnual ?? 'All';
$selectedCluster = $selectedCluster ?? 'All';
$selectedSO = $selectedSO ?? 'All';

// Define status colors for consistency
$statusColors = $statusColors ?? [
    'Needs Attention' => '#dc3545',
    'In Progress' => '#ffc107',
    'On Track' => '#17a2b8',
    'Met' => '#28a745',
    'Over Achieved' => '#6f42c1'
];

// Helper function to get color class based on status
function getStatusColorClass($status) {
    switch($status) {
        case 'Needs Attention': return 'danger';
        case 'In Progress': return 'warning';
        case 'On Track': return 'info';
        case 'Met': return 'success';
        case 'Over Achieved': return 'primary';
        default: return 'secondary';
    }
}

// Helper function to format percentage with color
function formatPercentWithColor($percent, $status) {
    $colorClass = getStatusColorClass($status);
    return "<span class='badge badge-light-$colorClass'>$percent%</span>";
}

// Check if we have data
$hasData = isset($dashboardData) && isset($dashboardData['summary']) && $dashboardData['summary']->total_indicators > 0;
@endphp

<!--begin::Performance Analytics Dashboard-->
<div class="card card-flush mb-5 mb-xl-10">
    <!--begin::Card header-->
    <div class="card-header border-0 pt-6">
        <h3 class="card-title align-items-start flex-column">
            <span class="card-label fw-bold fs-3 mb-1">Performance Analytics

                for {{ $selectedYear ?? 'All' }} |  Use filter options to change data context
            </span>
            <span class="text-muted mt-1 fw-semibold fs-7">
                {{ $hasData ? $dashboardData['summary']->total_indicators . ' indicators across ' . $dashboardData['summary']->total_clusters . ' clusters' : 'No data available' }}
            </span>
        </h3>
        <div class="card-toolbar">
            <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base">
                <!--begin::Filter-->
                <button type="button" class="btn btn-light-primary me-3" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                    <i class="ki-duotone ki-filter fs-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    Data Export
                </button>
                <!--begin::Menu 1-->
               <!-- Full Screen Modal with improved design -->
<div class="modal fade" id="kt_modal_filter_options" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header pb-0 border-0 justify-content-end">
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <span class="svg-icon svg-icon-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1" transform="rotate(-45 6 17.3137)" fill="currentColor"></rect>
                            <rect x="7.41422" y="6" width="16" height="2" rx="1" transform="rotate(45 7.41422 6)" fill="currentColor"></rect>
                        </svg>
                    </span>
                </div>
            </div>

            <div class="modal-body py-10">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-8">
                            <!-- Card container for filters -->
                            <div class="card shadow-sm">
                                <div class="card-header">
                                    <h3 class="card-title fw-bolder text-dark">Filter Options</h3>
                                    <div class="card-toolbar">
                                        <span class="badge badge-light-primary">Performance Analytics</span>
                                    </div>
                                </div>

                                <div class="card-body">
                                    <form action="{{ route('performance-analytics.dashboard') }}" method="GET">
                                        <div class="row g-5">
                                            <!-- Year filter -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label fw-bold fs-6 mb-2">Year</label>
                                                    <select class="form-select form-select-solid" name="year" data-control="select2" data-placeholder="Select year">
                                                        @foreach($availableYears as $year)
                                                            <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>{{ $year }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Semi Annual filter -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label fw-bold fs-6 mb-2">Semi Annual</label>
                                                    <select class="form-select form-select-solid" name="semi_annual" data-control="select2" data-placeholder="Select period">
                                                        <option value="All" {{ $selectedSemiAnnual == 'All' ? 'selected' : '' }}>All</option>
                                                        @isset($availableSemiAnnuals)
                                                            @foreach($availableSemiAnnuals as $period)
                                                                <option value="{{ $period }}" {{ $selectedSemiAnnual == $period ? 'selected' : '' }}>{{ $period }}</option>
                                                            @endforeach
                                                        @endisset
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Cluster filter -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label fw-bold fs-6 mb-2">Cluster</label>
                                                    <select class="form-select form-select-solid" name="cluster" data-control="select2" data-placeholder="Select cluster">
                                                        <option value="All" {{ $selectedCluster == 'All' ? 'selected' : '' }}>All Clusters</option>
                                                        @isset($clusters)
                                                            @foreach($clusters as $cluster)
                                                                <option value="{{ $cluster->cluster_pk }}" {{ $selectedCluster == $cluster->cluster_pk ? 'selected' : '' }}>{{ $cluster->cluster_name }}</option>
                                                            @endforeach
                                                        @endisset
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Strategic Objective filter -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label fw-bold fs-6 mb-2">Strategic Objective</label>
                                                    <select class="form-select form-select-solid" name="strategic_objective" data-control="select2" data-placeholder="Select strategic objective">
                                                        <option value="All" {{ $selectedSO == 'All' ? 'selected' : '' }}>All Strategic Objectives</option>
                                                        @isset($strategicObjectives)
                                                            @foreach($strategicObjectives as $so)
                                                                <option value="{{ $so->so_pk }}" {{ $selectedSO == $so->so_pk ? 'selected' : '' }}>{{ $so->so_number }} - {{ $so->so_name }}</option>
                                                            @endforeach
                                                        @endisset
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Divider -->
                                        <div class="separator separator-dashed my-8"></div>

                                        <!-- Action buttons -->
                                        <div class="d-flex justify-content-end">
                                            <button type="reset" class="btn btn-light me-3" data-kt-menu-dismiss="true">
                                                <span class="svg-icon svg-icon-2 me-0">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                        <path d="M21 7H3C2.4 7 2 6.6 2 6V4C2 3.4 2.4 3 3 3H21C21.6 3 22 3.4 22 4V6C22 6.6 21.6 7 21 7Z" fill="currentColor"/>
                                                        <path opacity="0.3" d="M21 14H3C2.4 14 2 13.6 2 13V11C2 10.4 2.4 10 3 10H21C21.6 10 22 10.4 22 11V13C22 13.6 21.6 14 21 14ZM22 20V18C22 17.4 21.6 17 21 17H3C2.4 17 2 17.4 2 18V20C2 20.6 2.4 21 3 21H21C21.6 21 22 20.6 22 20Z" fill="currentColor"/>
                                                    </svg>
                                                </span>
                                                Reset
                                            </button>
                                            <button type="submit" class="btn btn-primary">
                                                <span class="svg-icon svg-icon-2 me-0">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                        <path opacity="0.3" d="M10 18C9.7 18 9.5 17.9 9.3 17.7L2.3 10.7C1.9 10.3 1.9 9.7 2.3 9.3C2.7 8.9 3.29999 8.9 3.69999 9.3L10.7 16.3C11.1 16.7 11.1 17.3 10.7 17.7C10.5 17.9 10.3 18 10 18Z" fill="currentColor"/>
                                                        <path d="M10 18C9.7 18 9.5 17.9 9.3 17.7C8.9 17.3 8.9 16.7 9.3 16.3L20.3 5.3C20.7 4.9 21.3 4.9 21.7 5.3C22.1 5.7 22.1 6.30002 21.7 6.70002L10.7 17.7C10.5 17.9 10.3 18 10 18Z" fill="currentColor"/>
                                                    </svg>
                                                </span>
                                                Apply Filters
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Additional information card -->
                            <div class="card shadow-sm mt-5">
                                <div class="card-body p-lg-15">
                                    <div class="d-flex flex-stack mb-5">
                                        <div class="fs-5 fw-bold text-gray-800">Filter Information</div>
                                    </div>

                                    <div class="separator separator-dashed mb-5"></div>

                                    <div class="row g-5">
                                        <div class="col-md-6">
                                            <div class="d-flex flex-column mb-5">
                                                <div class="fw-bold text-muted mb-1">Current Year</div>
                                                <div class="fw-bolder fs-5">{{ $selectedYear ?? 'All' }}</div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="d-flex flex-column mb-5">
                                                <div class="fw-bold text-muted mb-1">Current Period</div>
                                                <div class="fw-bolder fs-5">{{ $selectedSemiAnnual ?? 'All' }}</div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="d-flex flex-column mb-5">
                                                <div class="fw-bold text-muted mb-1">Selected Cluster</div>
                                                <div class="fw-bolder fs-5">
                                                    @if($selectedCluster == 'All')
                                                        All Clusters
                                                    @else
                                                        @isset($clusters)
                                                            @foreach($clusters as $cluster)
                                                                @if($selectedCluster == $cluster->cluster_pk)
                                                                    {{ $cluster->cluster_name }}
                                                                @endif
                                                            @endforeach
                                                        @endisset
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="d-flex flex-column mb-5">
                                                <div class="fw-bold text-muted mb-1">Selected Strategic Objective</div>
                                                <div class="fw-bolder fs-5">
                                                    @if($selectedSO == 'All')
                                                        All Strategic Objectives
                                                    @else
                                                        @isset($strategicObjectives)
                                                            @foreach($strategicObjectives as $so)
                                                                @if($selectedSO == $so->so_pk)
                                                                    {{ $so->so_number }} - {{ $so->so_name }}
                                                                @endif
                                                            @endforeach
                                                        @endisset
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Button to trigger the modal -->
<button type="button" class="btn btn-danger mx-3 shadow-lg" data-bs-toggle="modal" data-bs-target="#kt_modal_filter_options">
    <span class="svg-icon svg-icon-2">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path d="M19.0759 3H4.72777C3.95892 3 3.47768 3.83148 3.86067 4.49814L8.56967 12.6949C9.17923 13.7559 9.5 14.9582 9.5 16.1819V19.5072C9.5 20.2189 10.2223 20.7028 10.8805 20.432L13.8805 19.1977C14.2553 19.0435 14.5 18.6783 14.5 18.273V13.8372C14.5 12.8089 14.8171 11.8056 15.408 10.964L19.8943 4.57465C20.3596 3.912 19.8856 3 19.0759 3Z" fill="currentColor"/>
        </svg>
    </span>
    Filter Options
</button>
                <!--end::Menu 1-->
                <!--end::Filter-->

                <!--begin::Export-->
                {{-- <button type="button" class="btn btn-light-primary me-3" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                    <i class="ki-duotone ki-exit-up fs-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    Export
                </button> --}}
                <!--begin::Menu-->
                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold w-200px" data-kt-menu="true">
                    <!--begin::Menu item-->
<div class="menu-item px-3">
    <a href="{{ route('performance-analytics.export-detailed') }}?year={{ $selectedYear }}&semi_annual={{ $selectedSemiAnnual }}&cluster={{ $selectedCluster }}&strategic_objective={{ $selectedSO }}" class="menu-link px-3">
        <i class="ki-duotone ki-file-excel fs-3 me-3">
            <span class="path1"></span>
            <span class="path2"></span>
        </i>
        Detailed Performance Excel
    </a>
</div>
<!--end::Menu item-->
                    <!--begin::Menu item-->
                    {{-- <div class="menu-item px-3">
                        <a href="{{ route('performance-analytics.export-excel') }}?year={{ $selectedYear }}&semi_annual={{ $selectedSemiAnnual }}&cluster={{ $selectedCluster }}&strategic_objective={{ $selectedSO }}" class="menu-link px-3">
                            <i class="ki-duotone ki-file-excel fs-3 me-3">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Excel Report
                        </a>
                    </div> --}}
                    <!--end::Menu item-->
                    <!--begin::Menu item-->
                    <div class="menu-item px-3">
                        <a href="{{ route('performance-analytics.export-csv') }}?year={{ $selectedYear }}&semi_annual={{ $selectedSemiAnnual }}&cluster={{ $selectedCluster }}&strategic_objective={{ $selectedSO }}&export_type=summary" class="menu-link px-3">
                            <i class="ki-duotone ki-file fs-3 me-3">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Summary CSV
                        </a>
                    </div>
                    <!--end::Menu item-->
                    <!--begin::Menu item-->
                    <div class="menu-item px-3">
                        <a href="{{ route('performance-analytics.export-csv') }}?year={{ $selectedYear }}&semi_annual={{ $selectedSemiAnnual }}&cluster={{ $selectedCluster }}&strategic_objective={{ $selectedSO }}&export_type=cluster" class="menu-link px-3">
                            <i class="ki-duotone ki-file fs-3 me-3">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Cluster CSV
                        </a>
                    </div>
                    <!--end::Menu item-->
                    <!--begin::Menu item-->
                    <div class="menu-item px-3">
                        <a href="{{ route('performance-analytics.export-csv') }}?year={{ $selectedYear }}&semi_annual={{ $selectedSemiAnnual }}&cluster={{ $selectedCluster }}&strategic_objective={{ $selectedSO }}&export_type=strategic_objective" class="menu-link px-3">
                            <i class="ki-duotone ki-file fs-3 me-3">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            SO CSV
                        </a>
                    </div>
                    <!--end::Menu item-->
                    <!--begin::Menu item-->
                    <div class="menu-item px-3">
                        <a href="{{ route('performance-analytics.export-csv') }}?year={{ $selectedYear }}&semi_annual={{ $selectedSemiAnnual }}&cluster={{ $selectedCluster }}&strategic_objective={{ $selectedSO }}&export_type=indicator" class="menu-link px-3">
                            <i class="ki-duotone ki-file fs-3 me-3">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Indicator CSV
                        </a>
                    </div>
                    <!--end::Menu item-->
                </div>
                <!--end::Menu-->
                <!--end::Export-->

                <!--begin::Performance Wizard-->
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_performance_wizard">
                    <i class="ki-duotone ki-chart-line-star fs-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    Performance Wizard
                </button>
                <!--end::Performance Wizard-->
            </div>
        </div>
    </div>
    <!--end::Card header-->

    @if(isset($error))
    <!--begin::Error Message-->
    <div class="card-body">
        <div class="alert alert-danger">
            <div class="d-flex flex-column">
                <h4 class="mb-1 text-danger">Error</h4>
                <span>{{ $error }}</span>
            </div>
        </div>
    </div>
    <!--end::Error Message-->
    @elseif(!$hasData)
    <!--begin::No Data Message-->
    <div class="card-body">
        <div class="alert alert-warning">
            <div class="d-flex flex-column">
                <h4 class="mb-1 text-warning">No Data Available</h4>
                <span>There is no performance data available for the selected filters. Please try different filter options.</span>
            </div>
        </div>
    </div>
    <!--end::No Data Message-->
    @else
    <!--begin::Card body-->
    <div class="card-body py-4">
        <!--begin::Tabs-->
        <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-4 fw-semibold mb-8">
            <!--begin::Tab item-->
            <li class="nav-item">
                <a class="nav-link text-active-primary pb-4 active" data-bs-toggle="tab" href="#kt_dashboard_overview_tab">Overview</a>
            </li>
            <!--end::Tab item-->
            <!--begin::Tab item-->
            <li class="nav-item">
                <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab" href="#kt_dashboard_strategic_tab">Strategic Objectives</a>
            </li>
            <!--end::Tab item-->
            <!--begin::Tab item-->
            <li class="nav-item">
                <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab" href="#kt_dashboard_clusters_tab">Clusters</a>
            </li>
            <!--end::Tab item-->
            <!--begin::Tab item-->
            <li class="nav-item">
                <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab" href="#kt_dashboard_indicators_tab">Indicators</a>
            </li>
            <!--end::Tab item-->
            <!--begin::Tab item-->
            <li class="nav-item">
                <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab" href="#kt_dashboard_insights_tab">Insights</a>
            </li>
            <!--end::Tab item-->
        </ul>
        <!--end::Tabs-->

        <!--begin::Tab content-->
        <div class="tab-content">
            <!--begin::Tab pane - Overview-->
            <div class="tab-pane fade show active" id="kt_dashboard_overview_tab">
                <!--begin::Row-->
                <div class="row g-5 g-xl-8">
                    <!--begin::Col-->
                    <div class="col-xl-4">
                        <!--begin::Mixed Widget 1-->
                        <div class="card card-xl-stretch mb-xl-8">
                            <!--begin::Body-->
                            <div class="card-body p-0">
                                <!--begin::Header-->
                                <div class="px-9 pt-7 card-rounded h-275px w-100 bg-{{ getStatusColorClass($dashboardData['summary']->overall_status) }}">
                                    <!--begin::Heading-->
                                    <div class="d-flex flex-stack">
                                        <h3 class="m-0 text-white fw-bold fs-3">Overall Performance</h3>
                                        <div class="ms-1">
                                            <!--begin::Menu-->
                                            <button type="button" class="btn btn-sm btn-icon btn-color-white btn-active-white btn-active-color-{{ getStatusColorClass($dashboardData['summary']->overall_status) }} border-0 me-n3" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
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
                                                    <div class="menu-content text-muted pb-2 px-3 fs-7 text-uppercase">Options</div>
                                                </div>
                                                <!--end::Heading-->
                                                <!--begin::Menu item-->
                                                <div class="menu-item px-3">
                                                    <a href="#" class="menu-link px-3" data-bs-toggle="modal" data-bs-target="#kt_modal_detailed_summary">
                                                        Detailed Summary
                                                    </a>
                                                </div>
                                                <!--end::Menu item-->
                                                <!--begin::Menu item-->
                                                <div class="menu-item px-3">
                                                    <a href="#" class="menu-link px-3" data-bs-toggle="modal" data-bs-target="#kt_modal_performance_trends">
                                                        Performance Trends
                                                    </a>
                                                </div>
                                                <!--end::Menu item-->
                                            </div>
                                            <!--end::Menu 3-->
                                            <!--end::Menu-->
                                        </div>
                                    </div>
                                    <!--end::Heading-->
                                    <!--begin::Balance-->
                                    <div class="d-flex text-center flex-column text-white pt-8">
                                        <span class="fw-semibold fs-7">Achievement</span>
                                        <span class="fw-bold fs-2x pt-1">{{ round($dashboardData['summary']->overall_achievement_percent, 1) }}%</span>
                                    </div>
                                    <!--end::Balance-->
                                </div>
                                <!--end::Header-->
                                <!--begin::Items-->
                                <div class="bg-body shadow-sm card-rounded mx-9 mb-9 px-6 py-9 position-relative z-index-1" style="margin-top: -100px">
                                    <!--begin::Item-->
                                    <div class="d-flex align-items-center mb-6">
                                        <!--begin::Symbol-->
                                        <div class="symbol symbol-45px w-40px me-5">
                                            <span class="symbol-label bg-light-danger">
                                                <i class="ki-duotone ki-arrow-down fs-1 text-danger">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </span>
                                        </div>
                                        <!--end::Symbol-->
                                        <!--begin::Description-->
                                        <div class="d-flex align-items-center flex-wrap w-100">
                                            <!--begin::Title-->
                                            <div class="mb-1 pe-3 flex-grow-1">
                                                <a href="#" class="fs-5 text-gray-800 text-hover-primary fw-bold">Needs Attention</a>
                                            </div>
                                            <!--end::Title-->
                                            <!--begin::Label-->
                                            <div class="d-flex align-items-center">
                                                <div class="fw-bold fs-5 text-danger py-1">{{ $dashboardData['summary']->needs_attention_count }}</div>
                                            </div>
                                            <!--end::Label-->
                                        </div>
                                        <!--end::Description-->
                                    </div>
                                    <!--end::Item-->
                                    <!--begin::Item-->
                                    <div class="d-flex align-items-center mb-6">
                                        <!--begin::Symbol-->
                                        <div class="symbol symbol-45px w-40px me-5">
                                            <span class="symbol-label bg-light-warning">
                                                <i class="ki-duotone ki-arrow-right fs-1 text-warning">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </span>
                                        </div>
                                        <!--end::Symbol-->
                                        <!--begin::Description-->
                                        <div class="d-flex align-items-center flex-wrap w-100">
                                            <!--begin::Title-->
                                            <div class="mb-1 pe-3 flex-grow-1">
                                                <a href="#" class="fs-5 text-gray-800 text-hover-primary fw-bold">In Progress</a>
                                            </div>
                                            <!--end::Title-->
                                            <!--begin::Label-->
                                            <div class="d-flex align-items-center">
                                                <div class="fw-bold fs-5 text-warning py-1">{{ $dashboardData['summary']->in_progress_count }}</div>
                                            </div>
                                            <!--end::Label-->
                                        </div>
                                        <!--end::Description-->
                                    </div>
                                    <!--end::Item-->
                                    <!--begin::Item-->
                                    <div class="d-flex align-items-center mb-6">
                                        <!--begin::Symbol-->
                                        <div class="symbol symbol-45px w-40px me-5">
                                            <span class="symbol-label bg-light-info">
                                                <i class="ki-duotone ki-arrow-up-right fs-1 text-info">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </span>
                                        </div>
                                        <!--end::Symbol-->
                                        <!--begin::Description-->
                                        <div class="d-flex align-items-center flex-wrap w-100">
                                            <!--begin::Title-->
                                            <div class="mb-1 pe-3 flex-grow-1">
                                                <a href="#" class="fs-5 text-gray-800 text-hover-primary fw-bold">On Track</a>
                                            </div>
                                            <!--end::Title-->
                                            <!--begin::Label-->
                                            <div class="d-flex align-items-center">
                                                <div class="fw-bold fs-5 text-info py-1">{{ $dashboardData['summary']->on_track_count }}</div>
                                            </div>
                                            <!--end::Label-->
                                        </div>
                                        <!--end::Description-->
                                    </div>
                                    <!--end::Item-->
                                    <!--begin::Item-->
                                    <div class="d-flex align-items-center">
                                        <!--begin::Symbol-->
                                        <div class="symbol symbol-45px w-40px me-5">
                                            <span class="symbol-label bg-light-success">
                                                <i class="ki-duotone ki-check-circle fs-1 text-success">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </span>
                                        </div>
                                        <!--end::Symbol-->
                                        <!--begin::Description-->
                                        <div class="d-flex align-items-center flex-wrap w-100">
                                            <!--begin::Title-->
                                            <div class="mb-1 pe-3 flex-grow-1">
                                                <a href="#" class="fs-5 text-gray-800 text-hover-primary fw-bold">Met / Over Achieved</a>
                                            </div>
                                            <!--end::Title-->
                                            <!--begin::Label-->
                                            <div class="d-flex align-items-center">
                                                <div class="fw-bold fs-5 text-success py-1">{{ $dashboardData['summary']->met_count + $dashboardData['summary']->over_achieved_count }}</div>
                                            </div>
                                            <!--end::Label-->
                                        </div>
                                        <!--end::Description-->
                                    </div>
                                    <!--end::Item-->
                                </div>
                                <!--end::Items-->
                            </div>
                            <!--end::Body-->
                        </div>
                        <!--end::Mixed Widget 1-->
                    </div>
                    <!--end::Col-->

                    <!--begin::Col-->
                    <div class="col-xl-8">
                        <!--begin::Charts Widget 1-->
                        <div class="card card-xl-stretch mb-5 mb-xl-8">
                            <!--begin::Header-->
                            <div class="card-header border-0 pt-5">
                                <!--begin::Title-->
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold fs-3 mb-1">Performance by Status</span>
                                    <span class="text-muted fw-semibold fs-7">Distribution of indicators by status</span>
                                </h3>
                                <!--end::Title-->
                                <!--begin::Toolbar-->
                                <div class="card-toolbar">
                                    <!--begin::Menu-->
                                    <button type="button" class="btn btn-danger" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                        <i class="ki-duotone ki-category fs-6">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                            <span class="path3"></span>
                                            <span class="path4"></span>
                                        </i>
                                        Critical Insights
                                    </button>
                                    <!--begin::Menu 3-->
                                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-semibold w-200px py-3" data-kt-menu="true">
                                        <!--begin::Heading-->
                                        <div class="menu-item px-3">
                                            <div class="menu-content text-muted pb-2 px-3 fs-7 text-uppercase">Options</div>
                                        </div>
                                        <!--end::Heading-->
                                        <!--begin::Menu item-->
                                        <div class="menu-item px-3">
                                            <a href="#" class="menu-link px-3" data-bs-toggle="modal" data-bs-target="#kt_modal_status_details">
                                                Status Details
                                            </a>
                                        </div>
                                        <!--end::Menu item-->
                                        <!--begin::Menu item-->
                                        <div class="menu-item px-3">
                                            <a href="#" class="menu-link px-3" data-bs-toggle="modal" data-bs-target="#kt_modal_attention_items">
                                                Items Needing Attention
                                            </a>
                                        </div>
                                        <!--end::Menu item-->
                                    </div>
                                    <!--end::Menu 3-->
                                    <!--end::Menu-->
                                </div>
                                <!--end::Toolbar-->
                            </div>
                            <!--end::Header-->
                            <!--begin::Body-->
                            <div class="card-body">
                                <!--begin::Chart-->
                                <div id="kt_charts_widget_1_chart_ecsa" style="height: 350px"></div>
                                <!--end::Chart-->
                            </div>
                            <!--end::Body-->
                        </div>
                        <!--end::Charts Widget 1-->
                    </div>
                    <!--end::Col-->
                </div>
                <!--end::Row-->

                <!--begin::Row-->
                <div class="row g-5 g-xl-8">
                    <!--begin::Col-->
                    <div class="col-xl-6">
                        <!--begin::List Widget 5-->
                        <div class="card card-xl-stretch mb-xl-8">
                            <!--begin::Header-->
                            <div class="card-header align-items-center border-0 mt-4">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="fw-bold mb-2 text-dark">Top Performing Clusters</span>
                                    <span class="text-muted fw-semibold fs-7">Clusters with highest achievement</span>
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
                                    <!--begin::Menu 1-->
                                    <div class="menu menu-sub menu-sub-dropdown w-250px w-md-300px" data-kt-menu="true" id="kt_menu_62444c0c5b8c5">
                                        <!--begin::Header-->
                                        <div class="px-7 py-5">
                                            <div class="fs-5 text-dark fw-bold">Cluster Options</div>
                                        </div>
                                        <!--end::Header-->
                                        <!--begin::Menu separator-->
                                        <div class="separator border-gray-200"></div>
                                        <!--end::Menu separator-->
                                        <!--begin::Form-->
                                        <div class="px-7 py-5">
                                            <!--begin::Input group-->
                                            <div class="mb-10">
                                                <a href="#" class="btn btn-primary fw-semibold w-100" data-bs-toggle="modal" data-bs-target="#kt_modal_cluster_performance">
                                                    View All Clusters
                                                </a>
                                            </div>
                                            <!--end::Input group-->
                                        </div>
                                        <!--end::Form-->
                                    </div>
                                    <!--end::Menu 1-->
                                    <!--end::Menu-->
                                </div>
                            </div>
                            <!--end::Header-->
                            <!--begin::Body-->
                            <div class="card-body pt-5">
                                @php
                                    $topClusters = collect($dashboardData['clusterPerformance'])
                                        ->sortByDesc('avg_achievement_percent')
                                        ->take(5);
                                @endphp

                                @foreach($topClusters as $index => $cluster)
                                <!--begin::Item-->
                                <div class="d-flex align-items-sm-center mb-7">
                                    <!--begin::Symbol-->
                                    <div class="symbol symbol-50px me-5">
                                        <span class="symbol-label bg-light-{{ getStatusColorClass($cluster->status) }}">
                                            <span class="fs-2 fw-bold text-{{ getStatusColorClass($cluster->status) }}">{{ $index + 1 }}</span>
                                        </span>
                                    </div>
                                    <!--end::Symbol-->
                                    <!--begin::Section-->
                                    <div class="d-flex align-items-center flex-row-fluid flex-wrap">
                                        <div class="flex-grow-1 me-2">
                                            <a href="#" class="text-gray-800 text-hover-primary fs-6 fw-bold">{{ $cluster->cluster_name }}</a>
                                            <span class="text-muted fw-semibold d-block fs-7">{{ $cluster->indicator_count }} indicators</span>
                                        </div>
                                        <span class="badge badge-light-{{ getStatusColorClass($cluster->status) }} fw-bold my-2">{{ round($cluster->avg_achievement_percent, 1) }}%</span>
                                    </div>
                                    <!--end::Section-->
                                </div>
                                <!--end::Item-->
                                @endforeach
                            </div>
                            <!--end::Body-->
                        </div>
                        <!--end::List Widget 5-->
                    </div>
                    <!--end::Col-->

                    <!--begin::Col-->
                    <div class="col-xl-6">
                        <!--begin::List Widget 6-->
                        <div class="card card-xl-stretch mb-xl-8">
                            <!--begin::Header-->
                            <div class="card-header border-0">
                                <h3 class="card-title fw-bold text-dark">Key Insights</h3>
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
                                            <div class="menu-content text-muted pb-2 px-3 fs-7 text-uppercase">Options</div>
                                        </div>
                                        <!--end::Heading-->
                                        <!--begin::Menu item-->
                                        <div class="menu-item px-3">
                                            <a href="#" class="menu-link px-3" data-bs-toggle="modal" data-bs-target="#kt_modal_insights_recommendations">
                                                All Insights & Recommendations
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
                            <div class="card-body pt-0">
                                @php
                                    $insights = $dashboardData['insights'] ?? [];
                                    $topInsights = collect($insights)->take(5);
                                @endphp

                                @foreach($topInsights as $insight)
                                <!--begin::Item-->
                                <div class="d-flex align-items-center bg-light-{{ $insight['priority'] == 'high' ? 'danger' : ($insight['priority'] == 'medium' ? 'warning' : 'success') }} rounded p-5 mb-7">
                                    <!--begin::Icon-->
                                    <span class="svg-icon svg-icon-{{ $insight['priority'] == 'high' ? 'danger' : ($insight['priority'] == 'medium' ? 'warning' : 'success') }} me-5">
                                        <i class="ki-duotone ki-abstract-{{ $insight['priority'] == 'high' ? '26' : ($insight['priority'] == 'medium' ? '25' : '24') }} fs-1 text-{{ $insight['priority'] == 'high' ? 'danger' : ($insight['priority'] == 'medium' ? 'warning' : 'success') }}">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                    </span>
                                    <!--end::Icon-->
                                    <!--begin::Title-->
                                    <div class="flex-grow-1 me-2">
                                        <a href="#" class="fw-bold text-gray-800 text-hover-primary fs-6">{{ ucfirst($insight['type']) }}</a>
                                        <span class="text-muted fw-semibold d-block">{{ $insight['message'] }}</span>
                                    </div>
                                    <!--end::Title-->
                                </div>
                                <!--end::Item-->
                                @endforeach

                                @if(count($topInsights) == 0)
                                <div class="alert alert-info">
                                    No insights available for the current selection.
                                </div>
                                @endif
                            </div>
                            <!--end::Body-->
                        </div>
                        <!--end::List Widget 6-->
                    </div>
                    <!--end::Col-->
                </div>
                <!--end::Row-->

                @if(isset($dashboardData['trends']) && isset($dashboardData['trends']['overall']) && count($dashboardData['trends']['overall']) > 1)
                <!--begin::Row-->
                <div class="row g-5 g-xl-8 mt-5">
                    <div class="col-xl-12">
                        <div class="card card-xl-stretch mb-5">
                            <div class="card-header border-0 pt-5">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold fs-3 mb-1">Performance Trends</span>
                                    <span class="text-muted fw-semibold fs-7">Historical performance over time</span>
                                </h3>
                                <div class="card-toolbar">
                                    <button type="button" class="btn btn-sm btn-icon btn-color-primary btn-active-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                        <i class="ki-duotone ki-category fs-6">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                            <span class="path3"></span>
                                            <span class="path4"></span>
                                        </i>
                                    </button>
                                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-semibold w-200px py-3" data-kt-menu="true">
                                        <div class="menu-item px-3">
                                            <a href="#" class="menu-link px-3" data-bs-toggle="modal" data-bs-target="#kt_modal_performance_trends">
                                                Detailed Trends Analysis
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="kt_overview_trends_chart" style="height: 350px"></div>

                                @if(isset($dashboardData['trends']['growthRates']) && $dashboardData['trends']['growthRates']['overall_growth'] !== null)
                                <div class="d-flex flex-wrap mt-5">
                                    <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="fs-4 fw-bold">{{ $dashboardData['trends']['growthRates']['overall_growth'] }}%</div>
                                        </div>
                                        <div class="fw-semibold fs-6 text-gray-400">Overall Growth</div>
                                    </div>

                                    <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="fs-4 fw-bold">{{ $dashboardData['trends']['growthRates']['cagr'] }}%</div>
                                        </div>
                                        <div class="fw-semibold fs-6 text-gray-400">CAGR</div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Row-->
                @endif
            </div>
            <!--end::Tab pane - Overview-->            <!--begin::Tab pane - Strategic Objectives-->
            <div class="tab-pane fade" id="kt_dashboard_strategic_tab">
                @if(isset($dashboardData['strategicObjectives']) && count($dashboardData['strategicObjectives']) > 0)
                <!--begin::Strategic Objectives Performance Card-->
                <div class="card card-xl-stretch mb-5">
                    <div class="card-header border-0 pt-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold fs-3 mb-1">Strategic Objectives Performance</span>
                            <span class="text-muted fw-semibold fs-7">Achievement across {{ count($dashboardData['strategicObjectives']) }} strategic objectives</span>
                        </h3>
                    </div>
                    <div class="card-body">
                        <div id="kt_strategic_objectives_chart" style="height: 350px" class="mb-10"></div>

                        <div class="separator separator-dashed my-8"></div>

                        <div class="table-responsive">
                            <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4" id="kt_strategic_objectives_table">
                                <thead>
                                    <tr class="fw-bold text-muted bg-light">
                                        <th class="min-w-100px ps-4 rounded-start">SO Number</th>
                                        <th class="min-w-200px">Strategic Objective</th>
                                        <th class="min-w-100px">Indicators</th>
                                        <th class="min-w-100px">Achievement</th>
                                        <th class="min-w-100px rounded-end">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dashboardData['strategicObjectives'] as $so)
                                    <tr>
                                        <td>{{ $so->so_number }}</td>
                                        <td>{{ $so->so_name }}</td>
                                        <td>{{ $so->indicator_count }}</td>
                                        <td>{{ round($so->avg_achievement_percent, 1) }}%</td>
                                        <td>
                                            <span class="badge badge-light-{{ getStatusColorClass($so->status) }}">{{ $so->status }}</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!--end::Strategic Objectives Performance Card-->

                <!--begin::Strategic Objectives Status Card-->
                <div class="card card-xl-stretch mb-5">
                    <div class="card-header border-0 pt-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold fs-3 mb-1">Strategic Objectives Status Distribution</span>
                            <span class="text-muted fw-semibold fs-7">Status breakdown by strategic objective</span>
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-5 g-xl-8">
                            @foreach($dashboardData['strategicObjectives'] as $so)
                            <div class="col-xl-4 col-md-6">
                                <div class="card card-xl-stretch mb-xl-8">
                                    <div class="card-header border-0">
                                        <h3 class="card-title fw-bold text-dark">{{ $so->so_number }}</h3>
                                        <div class="card-toolbar">
                                            <span class="badge badge-light-{{ getStatusColorClass($so->status) }}">{{ round($so->avg_achievement_percent, 1) }}%</span>
                                        </div>
                                    </div>
                                    <div class="card-body pt-0">
                                        <p class="text-gray-800 fw-semibold mb-5">{{ $so->so_name }}</p>

                                        <div class="d-flex align-items-center mb-2">
                                            <span class="bullet bullet-dot bg-danger me-2"></span>
                                            <span class="text-muted fs-7 me-auto">Needs Attention</span>
                                            <span class="badge badge-light-danger">{{ $so->needs_attention_count }}</span>
                                        </div>

                                        <div class="d-flex align-items-center mb-2">
                                            <span class="bullet bullet-dot bg-warning me-2"></span>
                                            <span class="text-muted fs-7 me-auto">In Progress</span>
                                            <span class="badge badge-light-warning">{{ $so->in_progress_count }}</span>
                                        </div>

                                        <div class="d-flex align-items-center mb-2">
                                            <span class="bullet bullet-dot bg-info me-2"></span>
                                            <span class="text-muted fs-7 me-auto">On Track</span>
                                            <span class="badge badge-light-info">{{ $so->on_track_count }}</span>
                                        </div>

                                        <div class="d-flex align-items-center mb-2">
                                            <span class="bullet bullet-dot bg-success me-2"></span>
                                            <span class="text-muted fs-7 me-auto">Met</span>
                                            <span class="badge badge-light-success">{{ $so->met_count }}</span>
                                        </div>

                                        <div class="d-flex align-items-center">
                                            <span class="bullet bullet-dot bg-primary me-2"></span>
                                            <span class="text-muted fs-7 me-auto">Over Achieved</span>
                                            <span class="badge badge-light-primary">{{ $so->over_achieved_count }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <!--end::Strategic Objectives Status Card-->
                @else
                <div class="alert alert-warning">
                    <div class="d-flex flex-column">
                        <h4 class="mb-1 text-warning">No Strategic Objectives Data</h4>
                        <span>There is no strategic objectives data available for the selected filters.</span>
                    </div>
                </div>
                @endif
            </div>
            <!--end::Tab pane - Strategic Objectives-->

            <!--begin::Tab pane - Clusters-->
            <div class="tab-pane fade" id="kt_dashboard_clusters_tab">
                @if(isset($dashboardData['clusterPerformance']) && count($dashboardData['clusterPerformance']) > 0)
                <!--begin::Cluster Performance Card-->
                <div class="card card-xl-stretch mb-5">
                    <div class="card-header border-0 pt-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold fs-3 mb-1">Cluster Performance Comparison</span>
                            <span class="text-muted fw-semibold fs-7">Achievement across {{ count($dashboardData['clusterPerformance']) }} clusters</span>
                        </h3>
                    </div>
                    <div class="card-body">
                        <div id="kt_clusters_chart" style="height: 350px" class="mb-10"></div>

                        <div class="separator separator-dashed my-8"></div>

                        <div class="table-responsive">
                            <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4" id="kt_clusters_table">
                                <thead>
                                    <tr class="fw-bold text-muted bg-light">
                                        <th class="min-w-100px ps-4 rounded-start">Code</th>
                                        <th class="min-w-200px">Cluster Name</th>
                                        <th class="min-w-100px">Indicators</th>
                                        <th class="min-w-100px">SOs</th>
                                        <th class="min-w-100px">Achievement</th>
                                        <th class="min-w-100px rounded-end">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dashboardData['clusterPerformance'] as $cluster)
                                    <tr>
                                        <td>{{ $cluster->cluster_code }}</td>
                                        <td>{{ $cluster->cluster_name }}</td>
                                        <td>{{ $cluster->indicator_count }}</td>
                                        <td>{{ $cluster->so_count }}</td>
                                        <td>{{ round($cluster->avg_achievement_percent, 1) }}%</td>
                                        <td>
                                            <span class="badge badge-light-{{ getStatusColorClass($cluster->status) }}">{{ $cluster->status }}</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!--end::Cluster Performance Card-->

                <!--begin::Top & Bottom Performers Card-->
                <div class="card card-xl-stretch mb-5">
                    <div class="card-header border-0 pt-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold fs-3 mb-1">Top & Bottom Performers</span>
                            <span class="text-muted fw-semibold fs-7">Highest and lowest performing clusters</span>
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-5 g-xl-8">
                            <div class="col-xl-6">
                                <h4 class="fs-4 fw-bold mb-5">Top 5 Performing Clusters</h4>
                                @php
                                    $topClusters = collect($dashboardData['clusterPerformance'])
                                        ->sortByDesc('avg_achievement_percent')
                                        ->take(5);
                                @endphp

                                @foreach($topClusters as $index => $cluster)
                                <div class="d-flex align-items-center bg-light-{{ getStatusColorClass($cluster->status) }} rounded p-5 mb-7">
                                    <div class="symbol symbol-45px me-5">
                                        <span class="symbol-label bg-{{ getStatusColorClass($cluster->status) }}">
                                            <span class="fs-2 fw-bold text-white">{{ $index + 1 }}</span>
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-center flex-row-fluid flex-wrap">
                                        <div class="flex-grow-1 me-2">
                                            <a href="#" class="text-gray-800 text-hover-primary fs-6 fw-bold">{{ $cluster->cluster_name }}</a>
                                            <span class="text-muted fw-semibold d-block fs-7">{{ $cluster->indicator_count }} indicators</span>
                                        </div>
                                        <span class="badge badge-{{ getStatusColorClass($cluster->status) }} fw-bold my-2">{{ round($cluster->avg_achievement_percent, 1) }}%</span>
                                    </div>
                                </div>
                                @endforeach
                            </div>

                            <div class="col-xl-6">
                                <h4 class="fs-4 fw-bold mb-5">Bottom 5 Performing Clusters</h4>
                                @php
                                    $bottomClusters = collect($dashboardData['clusterPerformance'])
                                        ->sortBy('avg_achievement_percent')
                                        ->take(5);
                                @endphp

                                @foreach($bottomClusters as $index => $cluster)
                                <div class="d-flex align-items-center bg-light-{{ getStatusColorClass($cluster->status) }} rounded p-5 mb-7">
                                    <div class="symbol symbol-45px me-5">
                                        <span class="symbol-label bg-{{ getStatusColorClass($cluster->status) }}">
                                            <span class="fs-2 fw-bold text-white">{{ $index + 1 }}</span>
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-center flex-row-fluid flex-wrap">
                                        <div class="flex-grow-1 me-2">
                                            <a href="#" class="text-gray-800 text-hover-primary fs-6 fw-bold">{{ $cluster->cluster_name }}</a>
                                            <span class="text-muted fw-semibold d-block fs-7">{{ $cluster->indicator_count }} indicators</span>
                                        </div>
                                        <span class="badge badge-{{ getStatusColorClass($cluster->status) }} fw-bold my-2">{{ round($cluster->avg_achievement_percent, 1) }}%</span>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Top & Bottom Performers Card-->
                @else
                <div class="alert alert-warning">
                    <div class="d-flex flex-column">
                        <h4 class="mb-1 text-warning">No Cluster Data</h4>
                        <span>There is no cluster data available for the selected filters.</span>
                    </div>
                </div>
                @endif
            </div>
            <!--end::Tab pane - Clusters-->

            <!--begin::Tab pane - Indicators-->
            <div class="tab-pane fade" id="kt_dashboard_indicators_tab">
                @if(isset($dashboardData['indicatorPerformance']) && count($dashboardData['indicatorPerformance']) > 0)
                <!--begin::Indicators Performance Card-->
                <div class="card card-xl-stretch mb-5">
                    <div class="card-header border-0 pt-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold fs-3 mb-1">Indicator Performance</span>
                            <span class="text-muted fw-semibold fs-7">Achievement across {{ count($dashboardData['indicatorPerformance']) }} indicators</span>
                        </h3>
                        <div class="card-toolbar">
                            <ul class="nav nav-pills nav-pills-sm nav-light">
                                <li class="nav-item">
                                    <a class="nav-link btn btn-active-light btn-color-muted py-2 px-4 active" data-bs-toggle="tab" href="#kt_indicators_tab_all">All</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link btn btn-active-light btn-color-muted py-2 px-4" data-bs-toggle="tab" href="#kt_indicators_tab_attention">Needs Attention</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link btn btn-active-light btn-color-muted py-2 px-4" data-bs-toggle="tab" href="#kt_indicators_tab_achieved">Achieved</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="kt_indicators_tab_all">
                                <div class="table-responsive">
                                    <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4" id="kt_indicators_table">
                                        <thead>
                                            <tr class="fw-bold text-muted bg-light">
                                                <th class="min-w-100px ps-4 rounded-start">SO</th>
                                                <th class="min-w-150px">Indicator</th>
                                                <th class="min-w-100px">Target</th>
                                                <th class="min-w-100px">Actual</th>
                                                <th class="min-w-100px">Achievement</th>
                                                <th class="min-w-100px rounded-end">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($dashboardData['indicatorPerformance'] as $indicator)
                                            <tr>
                                                <td>{{ $indicator->so_number }}</td>
                                                <td>{{ $indicator->indicator_name }}</td>
                                                <td>{{ $indicator->total_target_value }}</td>
                                                <td>{{ $indicator->total_actual_value }}</td>
                                                <td>{{ round($indicator->avg_achievement_percent, 1) }}%</td>
                                                <td>
                                                    <span class="badge badge-light-{{ getStatusColorClass($indicator->status) }}">{{ $indicator->status }}</span>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="kt_indicators_tab_attention">
                                <div class="table-responsive">
                                    <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                        <thead>
                                            <tr class="fw-bold text-muted bg-light">
                                                <th class="min-w-100px ps-4 rounded-start">SO</th>
                                                <th class="min-w-150px">Indicator</th>
                                                <th class="min-w-100px">Target</th>
                                                <th class="min-w-100px">Actual</th>
                                                <th class="min-w-100px">Achievement</th>
                                                <th class="min-w-100px rounded-end">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $attentionIndicators = collect($dashboardData['indicatorPerformance'])
                                                    ->filter(function($indicator) {
                                                        return $indicator->status == 'Needs Attention' || $indicator->status == 'In Progress';
                                                    });
                                            @endphp

                                            @if($attentionIndicators->count() > 0)
                                                @foreach($attentionIndicators as $indicator)
                                                <tr>
                                                    <td>{{ $indicator->so_number }}</td>
                                                    <td>{{ $indicator->indicator_name }}</td>
                                                    <td>{{ $indicator->total_target_value }}</td>
                                                    <td>{{ $indicator->total_actual_value }}</td>
                                                    <td>{{ round($indicator->avg_achievement_percent, 1) }}%</td>
                                                    <td>
                                                        <span class="badge badge-light-{{ getStatusColorClass($indicator->status) }}">{{ $indicator->status }}</span>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="6" class="text-center">
                                                        <div class="alert alert-success m-0">
                                                            No indicators need attention.
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="kt_indicators_tab_achieved">
                                <div class="table-responsive">
                                    <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                        <thead>
                                            <tr class="fw-bold text-muted bg-light">
                                                <th class="min-w-100px ps-4 rounded-start">SO</th>
                                                <th class="min-w-150px">Indicator</th>
                                                <th class="min-w-100px">Target</th>
                                                <th class="min-w-100px">Actual</th>
                                                <th class="min-w-100px">Achievement</th>
                                                <th class="min-w-100px rounded-end">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $achievedIndicators = collect($dashboardData['indicatorPerformance'])
                                                    ->filter(function($indicator) {
                                                        return $indicator->status == 'Met' || $indicator->status == 'Over Achieved';
                                                    });
                                            @endphp

                                            @if($achievedIndicators->count() > 0)
                                                @foreach($achievedIndicators as $indicator)
                                                <tr>
                                                    <td>{{ $indicator->so_number }}</td>
                                                    <td>{{ $indicator->indicator_name }}</td>
                                                    <td>{{ $indicator->total_target_value }}</td>
                                                    <td>{{ $indicator->total_actual_value }}</td>
                                                    <td>{{ round($indicator->avg_achievement_percent, 1) }}%</td>
                                                    <td>
                                                        <span class="badge badge-light-{{ getStatusColorClass($indicator->status) }}">{{ $indicator->status }}</span>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="6" class="text-center">
                                                        <div class="alert alert-warning m-0">
                                                            No indicators have met or exceeded targets.
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Indicators Performance Card-->

                @if(isset($dashboardData['attentionItems']) &&
                    (count($dashboardData['attentionItems']['indicators']) > 0 ||
                    count($dashboardData['attentionItems']['clusters']) > 0 ||
                    count($dashboardData['attentionItems']['strategicObjectives']) > 0))
                <!--begin::Attention Items Card-->
                <div class="card card-xl-stretch mb-5">
                    <div class="card-header border-0 pt-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold fs-3 mb-1">Items Needing Attention</span>
                            <span class="text-muted fw-semibold fs-7">Critical areas requiring immediate focus</span>
                        </h3>
                        <div class="card-toolbar">
                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#kt_modal_attention_items">
                                View All Items
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-danger">
                            <div class="d-flex flex-column">
                                <h4 class="mb-1 text-danger">Attention Required</h4>
                                <span>The following items require immediate attention due to low performance:</span>
                            </div>
                        </div>

                        <div class="tab-content mt-5">
                            <ul class="nav nav-tabs nav-line-tabs mb-5 fs-6">
                                @if(count($dashboardData['attentionItems']['indicators']) > 0)
                                <li class="nav-item">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#kt_attention_indicators">Indicators</a>
                                </li>
                                @endif

                                @if(count($dashboardData['attentionItems']['clusters']) > 0)
                                <li class="nav-item">
                                    <a class="nav-link {{ count($dashboardData['attentionItems']['indicators']) == 0 ? 'active' : '' }}" data-bs-toggle="tab" href="#kt_attention_clusters">Clusters</a>
                                </li>
                                @endif

                                @if(count($dashboardData['attentionItems']['strategicObjectives']) > 0)
                                <li class="nav-item">
                                    <a class="nav-link {{ count($dashboardData['attentionItems']['indicators']) == 0 && count($dashboardData['attentionItems']['clusters']) == 0 ? 'active' : '' }}" data-bs-toggle="tab" href="#kt_attention_sos">Strategic Objectives</a>
                                </li>
                                @endif
                            </ul>

                            <div class="tab-content">
                                @if(count($dashboardData['attentionItems']['indicators']) > 0)
                                <div class="tab-pane fade show active" id="kt_attention_indicators">
                                    <div class="table-responsive">
                                        <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                            <thead>
                                                <tr class="fw-bold text-muted bg-light">
                                                    <th class="min-w-100px ps-4 rounded-start">SO</th>
                                                    <th class="min-w-200px">Indicator</th>
                                                    <th class="min-w-100px">Achievement</th>
                                                    <th class="min-w-100px rounded-end">Clusters</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($dashboardData['attentionItems']['indicators'] as $item)
                                                <tr>
                                                    <td>{{ $item->so_number }}</td>
                                                    <td>{{ $item->indicator_name }}</td>
                                                    <td>
                                                        <span class="badge badge-light-danger">{{ round($item->avg_achievement, 1) }}%</span>
                                                    </td>
                                                    <td>{{ $item->cluster_count }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                @endif

                                @if(count($dashboardData['attentionItems']['clusters']) > 0)
                                <div class="tab-pane fade {{ count($dashboardData['attentionItems']['indicators']) == 0 ? 'show active' : '' }}" id="kt_attention_clusters">
                                    <div class="table-responsive">
                                        <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                            <thead>
                                                <tr class="fw-bold text-muted bg-light">
                                                    <th class="min-w-100px ps-4 rounded-start">Cluster</th>
                                                    <th class="min-w-200px">Name</th>
                                                    <th class="min-w-100px">Achievement</th>
                                                    <th class="min-w-100px rounded-end">Indicators</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($dashboardData['attentionItems']['clusters'] as $item)
                                                <tr>
                                                    <td>{{ $item->cluster_pk }}</td>
                                                    <td>{{ $item->cluster_name }}</td>
                                                    <td>
                                                        <span class="badge badge-light-danger">{{ round($item->avg_achievement, 1) }}%</span>
                                                    </td>
                                                    <td>{{ $item->indicator_count }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                @endif

                                @if(count($dashboardData['attentionItems']['strategicObjectives']) > 0)
                                <div class="tab-pane fade {{ count($dashboardData['attentionItems']['indicators']) == 0 && count($dashboardData['attentionItems']['clusters']) == 0 ? 'show active' : '' }}" id="kt_attention_sos">
                                    <div class="table-responsive">
                                        <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                            <thead>
                                                <tr class="fw-bold text-muted bg-light">
                                                    <th class="min-w-100px ps-4 rounded-start">SO Number</th>
                                                    <th class="min-w-200px">Name</th>
                                                    <th class="min-w-100px">Achievement</th>
                                                    <th class="min-w-100px rounded-end">Indicators</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($dashboardData['attentionItems']['strategicObjectives'] as $item)
                                                <tr>
                                                    <td>{{ $item->so_number }}</td>
                                                    <td>{{ $item->so_name }}</td>
                                                    <td>
                                                        <span class="badge badge-light-danger">{{ round($item->avg_achievement, 1) }}%</span>
                                                    </td>
                                                    <td>{{ $item->indicator_count }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Attention Items Card-->
                @endif
                @else
                <div class="alert alert-warning">
                    <div class="d-flex flex-column">
                        <h4 class="mb-1 text-warning">No Indicator Data</h4>
                        <span>There is no indicator data available for the selected filters.</span>
                    </div>
                </div>
                @endif
            </div>
            <!--end::Tab pane - Indicators-->

            <!--begin::Tab pane - Insights-->
            <div class="tab-pane fade" id="kt_dashboard_insights_tab">
                @if($hasData)
                <div class="row g-5 g-xl-8">
                    <div class="col-xl-6">
                        <div class="card card-xl-stretch mb-5">
                            <div class="card-header border-0 pt-5">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold fs-3 mb-1">Key Insights</span>
                                    <span class="text-muted fw-semibold fs-7">Performance analysis insights</span>
                                </h3>
                            </div>
                            <div class="card-body">
                                @if(isset($dashboardData['insights']) && count($dashboardData['insights']) > 0)
                                    @foreach($dashboardData['insights'] as $insight)
                                    <div class="d-flex align-items-center bg-light-{{ $insight['priority'] == 'high' ? 'danger' : ($insight['priority'] == 'medium' ? 'warning' : 'success') }} rounded p-5 mb-7">
                                        <span class="svg-icon svg-icon-{{ $insight['priority'] == 'high' ? 'danger' : ($insight['priority'] == 'medium' ? 'warning' : 'success') }} me-5">
                                            <i class="ki-duotone ki-abstract-{{ $insight['priority'] == 'high' ? '26' : ($insight['priority'] == 'medium' ? '25' : '24') }} fs-1 text-{{ $insight['priority'] == 'high' ? 'danger' : ($insight['priority'] == 'medium' ? 'warning' : 'success') }}">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                        </span>
                                        <div class="flex-grow-1 me-2">
                                            <a href="#" class="fw-bold text-gray-800 text-hover-primary fs-6">{{ ucfirst($insight['type']) }} ({{ ucfirst($insight['category'] ?? 'General') }})</a>
                                            <span class="text-muted fw-semibold d-block">{{ $insight['message'] }}</span>
                                        </div>
                                        <span class="badge badge-{{ $insight['priority'] == 'high' ? 'danger' : ($insight['priority'] == 'medium' ? 'warning' : 'success') }} fs-8 fw-bold">{{ ucfirst($insight['priority']) }}</span>
                                    </div>
                                    @endforeach
                                @else
                                    <div class="alert alert-info">
                                        <div class="d-flex flex-column">
                                            <h4 class="mb-1 text-info">No Insights Available</h4>
                                            <span>There are no insights available for the selected period.</span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-6">
                        <div class="card card-xl-stretch mb-5">
                            <div class="card-header border-0 pt-5">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold fs-3 mb-1">Recommendations</span>
                                    <span class="text-muted fw-semibold fs-7">Actionable recommendations</span>
                                </h3>
                            </div>
                            <div class="card-body">
                                @if(isset($dashboardData['recommendations']) && count($dashboardData['recommendations']) > 0)
                                    @foreach($dashboardData['recommendations'] as $recommendation)
                                    <div class="d-flex align-items-center bg-light-primary rounded p-5 mb-7">
                                        <span class="svg-icon svg-icon-primary me-5">
                                            <i class="ki-duotone ki-rocket fs-1 text-primary">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                        </span>
                                        <div class="flex-grow-1 me-2">
                                            <a href="#" class="fw-bold text-gray-800 text-hover-primary fs-6">{{ ucfirst($recommendation['type']) }} ({{ ucfirst($recommendation['category'] ?? 'General') }})</a>
                                            <span class="text-muted fw-semibold d-block">{{ $recommendation['message'] }}</span>
                                        </div>
                                        <span class="badge badge-{{ $recommendation['priority'] == 'high' ? 'danger' : ($recommendation['priority'] == 'medium' ? 'warning' : 'success') }} fs-8 fw-bold">{{ ucfirst($recommendation['priority']) }}</span>
                                    </div>
                                    @endforeach
                                @else
                                    <div class="alert alert-info">
                                        <div class="d-flex flex-column">
                                            <h4 class="mb-1 text-info">No Recommendations Available</h4>
                                            <span>There are no recommendations available for the selected period.</span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                @if(isset($dashboardData['trends']) && isset($dashboardData['trends']['overall']) && count($dashboardData['trends']['overall']) > 1)
                <div class="card card-xl-stretch mb-5">
                    <div class="card-header border-0 pt-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold fs-3 mb-1">Performance Trends</span>
                            <span class="text-muted fw-semibold fs-7">Historical performance analysis</span>
                        </h3>
                    </div>
                    <div class="card-body">
                        <div id="kt_insights_trends_chart" style="height: 350px" class="mb-10"></div>

                        @if(isset($dashboardData['trends']['growthRates']) && $dashboardData['trends']['growthRates']['overall_growth'] !== null)
                        <div class="row g-5 g-xl-8 mt-5">
                            <div class="col-xl-6">
                                <h4 class="fs-4 fw-bold mb-5">Growth Metrics</h4>
                                <div class="d-flex flex-wrap">
                                    <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="fs-4 fw-bold">{{ $dashboardData['trends']['growthRates']['overall_growth'] }}%</div>
                                        </div>
                                        <div class="fw-semibold fs-6 text-gray-400">Overall Growth</div>
                                    </div>

                                    <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="fs-4 fw-bold">{{ $dashboardData['trends']['growthRates']['cagr'] }}%</div>
                                        </div>
                                        <div class="fw-semibold fs-6 text-gray-400">CAGR</div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-6">
                                <h4 class="fs-4 fw-bold mb-5">Period-to-Period Growth</h4>
                                <div class="table-responsive">
                                    <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                        <thead>
                                            <tr class="fw-bold text-muted bg-light">
                                                <th class="min-w-150px ps-4 rounded-start">From Period</th>
                                                <th class="min-w-150px">To Period</th>
                                                <th class="min-w-100px rounded-end">Growth Rate</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($dashboardData['trends']['growthRates']['period_growth'] as $growth)
                                            <tr>
                                                <td>{{ $growth['from_period'] }}</td>
                                                <td>{{ $growth['to_period'] }}</td>
                                                <td>
                                                    <span class="badge badge-light-{{ $growth['growth_rate'] >= 0 ? 'success' : 'danger' }}">
                                                        {{ $growth['growth_rate'] }}%
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
                </div>
                @endif

                @if(isset($dashboardData['anomalies']) &&
                    (count($dashboardData['anomalies']['outliers']) > 0 ||
                    count($dashboardData['anomalies']['inconsistencies']) > 0 ||
                    count($dashboardData['anomalies']['data_quality_issues']) > 0))
                <div class="card card-xl-stretch mb-5">
                    <div class="card-header border-0 pt-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold fs-3 mb-1">Data Anomalies & Quality Issues</span>
                            <span class="text-muted fw-semibold fs-7">Detected data issues requiring attention</span>
                        </h3>
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-tabs nav-line-tabs mb-5 fs-6">
                            @if(count($dashboardData['anomalies']['outliers']) > 0)
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#kt_anomalies_outliers">Outliers</a>
                            </li>
                            @endif

                            @if(count($dashboardData['anomalies']['inconsistencies']) > 0)
                            <li class="nav-item">
                                <a class="nav-link {{ count($dashboardData['anomalies']['outliers']) == 0 ? 'active' : '' }}" data-bs-toggle="tab" href="#kt_anomalies_inconsistencies">Inconsistencies</a>
                            </li>
                            @endif

                            @if(count($dashboardData['anomalies']['data_quality_issues']) > 0)
                            <li class="nav-item">
                                <a class="nav-link {{ count($dashboardData['anomalies']['outliers']) == 0 && count($dashboardData['anomalies']['inconsistencies']) == 0 ? 'active' : '' }}" data-bs-toggle="tab" href="#kt_anomalies_quality">Quality Issues</a>
                            </li>
                            @endif
                        </ul>

                        <div class="tab-content">
                            @if(count($dashboardData['anomalies']['outliers']) > 0)
                            <div class="tab-pane fade show active" id="kt_anomalies_outliers">
                                <div class="table-responsive">
                                    <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                        <thead>
                                            <tr class="fw-bold text-muted bg-light">
                                                <th class="min-w-150px ps-4 rounded-start">Indicator</th>
                                                <th class="min-w-150px">Cluster</th>
                                                <th class="min-w-100px">Achievement %</th>
                                                <th class="min-w-100px rounded-end">Z-Score</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($dashboardData['anomalies']['outliers'] as $outlier)
                                            <tr>
                                                <td>{{ $outlier->indicator_name }}</td>
                                                <td>{{ $outlier->cluster_name }}</td>
                                                <td>{{ $outlier->achievement_percent }}%</td>
                                                <td>
                                                    <span class="badge badge-light-{{ $outlier->is_high_outlier ? 'success' : 'danger' }}">
                                                        {{ $outlier->z_score }}
                                                    </span>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @endif

                            @if(count($dashboardData['anomalies']['inconsistencies']) > 0)
                            <div class="tab-pane fade {{ count($dashboardData['anomalies']['outliers']) == 0 ? 'show active' : '' }}" id="kt_anomalies_inconsistencies">
                                <div class="table-responsive">
                                    <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                        <thead>
                                            <tr class="fw-bold text-muted bg-light">
                                                <th class="min-w-150px ps-4 rounded-start">Indicator</th>
                                                <th class="min-w-150px">Cluster</th>
                                                <th class="min-w-100px">Reported %</th>
                                                <th class="min-w-100px">Calculated %</th>
                                                <th class="min-w-100px rounded-end">Difference</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($dashboardData['anomalies']['inconsistencies'] as $inconsistency)
                                            <tr>
                                                <td>{{ $inconsistency->indicator_name }}</td>
                                                <td>{{ $inconsistency->cluster_name }}</td>
                                                <td>{{ $inconsistency->achievement_percent }}%</td>
                                                <td>{{ $inconsistency->calculated_percent }}%</td>
                                                <td>
                                                    <span class="badge badge-light-{{ $inconsistency->difference > 10 ? 'danger' : 'warning' }}">
                                                        {{ $inconsistency->difference }}%
                                                    </span>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @endif

                            @if(count($dashboardData['anomalies']['data_quality_issues']) > 0)
                            <div class="tab-pane fade {{ count($dashboardData['anomalies']['outliers']) == 0 && count($dashboardData['anomalies']['inconsistencies']) == 0 ? 'show active' : '' }}" id="kt_anomalies_quality">
                                <div class="table-responsive">
                                    <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                        <thead>
                                            <tr class="fw-bold text-muted bg-light">
                                                <th class="min-w-150px ps-4 rounded-start">Indicator</th>
                                                <th class="min-w-150px">Cluster</th>
                                                <th class="min-w-100px">Missing Target</th>
                                                <th class="min-w-100px">Missing Actual</th>
                                                <th class="min-w-100px rounded-end">Missing Achievement</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($dashboardData['anomalies']['data_quality_issues'] as $issue)
                                            <tr>
                                                <td>{{ $issue->indicator_name }}</td>
                                                <td>{{ $issue->cluster_name }}</td>
                                                <td>
                                                    @if($issue->missing_target)
                                                    <span class="badge badge-light-danger">Yes</span>
                                                    @else
                                                    <span class="badge badge-light-success">No</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($issue->missing_actual)
                                                    <span class="badge badge-light-danger">Yes</span>
                                                    @else
                                                    <span class="badge badge-light-success">No</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($issue->missing_achievement)
                                                    <span class="badge badge-light-danger">Yes</span>
                                                    @else
                                                    <span class="badge badge-light-success">No</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
                @else
                <div class="alert alert-warning">
                    <div class="d-flex flex-column">
                        <h4 class="mb-1 text-warning">No Data Available</h4>
                        <span>There is no data available to generate insights and recommendations.</span>
                    </div>
                </div>
                @endif
            </div>
            <!--end::Tab pane - Insights-->
        </div>
        <!--end::Tab content-->
    </div>
    <!--end::Card body-->
    @endif
</div>
<!--end::Performance Analytics Dashboard--><!--begin::Modals-->

<!--begin::Modal - Performance Wizard-->
<div class="modal fade" id="kt_modal_performance_wizard" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Performance Analytics Wizard</h2>
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </div>
            </div>
            <div class="modal-body py-lg-10 px-lg-10">
                <div class="stepper stepper-pills stepper-horizontal d-flex flex-column flex-row-fluid" id="kt_modal_dashboard_stepper">
                    <!-- Horizontal stepper navigation at the top -->
                    <div class="d-flex justify-content-center w-100 mb-10">
                        <div class="stepper-nav d-flex flex-row flex-center flex-wrap">
                            <div class="stepper-item current mx-2" data-kt-stepper-element="nav">
                                <div class="stepper-wrapper d-flex align-items-center">
                                    <div class="stepper-icon w-40px h-40px">
                                        <i class="stepper-check fas fa-check"></i>
                                        <span class="stepper-number">1</span>
                                    </div>
                                    <div class="stepper-label ms-2">
                                        <h3 class="stepper-title">Overview</h3>
                                        <div class="stepper-desc">Performance Summary</div>
                                    </div>
                                </div>
                            </div>

                            <div class="stepper-item mx-2" data-kt-stepper-element="nav">
                                <div class="stepper-wrapper d-flex align-items-center">
                                    <div class="stepper-icon w-40px h-40px">
                                        <i class="stepper-check fas fa-check"></i>
                                        <span class="stepper-number">2</span>
                                    </div>
                                    <div class="stepper-label ms-2">
                                        <h3 class="stepper-title">Strategic Objectives</h3>
                                        <div class="stepper-desc">SO Performance</div>
                                    </div>
                                </div>
                            </div>

                            <div class="stepper-item mx-2" data-kt-stepper-element="nav">
                                <div class="stepper-wrapper d-flex align-items-center">
                                    <div class="stepper-icon w-40px h-40px">
                                        <i class="stepper-check fas fa-check"></i>
                                        <span class="stepper-number">3</span>
                                    </div>
                                    <div class="stepper-label ms-2">
                                        <h3 class="stepper-title">Clusters</h3>
                                        <div class="stepper-desc">Cluster Performance</div>
                                    </div>
                                </div>
                            </div>

                            <div class="stepper-item mx-2" data-kt-stepper-element="nav">
                                <div class="stepper-wrapper d-flex align-items-center">
                                    <div class="stepper-icon w-40px h-40px">
                                        <i class="stepper-check fas fa-check"></i>
                                        <span class="stepper-number">4</span>
                                    </div>
                                    <div class="stepper-label ms-2">
                                        <h3 class="stepper-title">Indicators</h3>
                                        <div class="stepper-desc">Indicator Performance</div>
                                    </div>
                                </div>
                            </div>

                            <div class="stepper-item mx-2" data-kt-stepper-element="nav">
                                <div class="stepper-wrapper d-flex align-items-center">
                                    <div class="stepper-icon w-40px h-40px">
                                        <i class="stepper-check fas fa-check"></i>
                                        <span class="stepper-number">5</span>
                                    </div>
                                    <div class="stepper-label ms-2">
                                        <h3 class="stepper-title">Trends</h3>
                                        <div class="stepper-desc">Performance Trends</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Full width content area -->
                    <div class="flex-row-fluid py-lg-5 px-lg-15 w-100">
                        <form class="form" novalidate="novalidate" id="kt_modal_dashboard_form">
                            <div class="current" data-kt-stepper-element="content">
                                <div class="w-100">
                                    <div class="pb-10 pb-lg-15">
                                        <h2 class="fw-bold d-flex align-items-center text-dark">Performance Overview
                                            <i class="fas fa-exclamation-circle ms-2 fs-7" data-bs-toggle="tooltip" title="Overall performance summary"></i>
                                        </h2>
                                        <div class="text-muted fw-semibold fs-6">Summary of key performance metrics</div>
                                    </div>

                                    @if($hasData)
                                    <div class="row g-5 g-xl-8 mb-5">
                                        <div class="col-xl-4">
                                            <div class="card card-xl-stretch mb-xl-8">
                                                <div class="card-body d-flex flex-column p-0">
                                                    <div class="d-flex flex-stack flex-grow-1 card-p">
                                                        <div class="d-flex flex-column me-2">
                                                            <a href="#" class="text-dark text-hover-primary fw-bold fs-3">Overall Achievement</a>
                                                            <span class="text-muted fw-semibold mt-1">Performance across all indicators</span>
                                                        </div>
                                                        <div class="symbol symbol-50px">
                                                            <span class="symbol-label fs-5 fw-bold bg-light-{{ getStatusColorClass($dashboardData['summary']->overall_status) }} text-{{ getStatusColorClass($dashboardData['summary']->overall_status) }}">
                                                                {{ round($dashboardData['summary']->overall_achievement_percent, 1) }}%
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="mixed-widget-7-chart card-rounded-bottom" data-kt-chart-color="{{ getStatusColorClass($dashboardData['summary']->overall_status) }}" style="height: 150px"></div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-xl-8">
                                            <div class="row g-5 g-xl-8">
                                                <div class="col-xl-6">
                                                    <div class="card card-xl-stretch mb-xl-8">
                                                        <div class="card-body p-0">
                                                            <div class="d-flex flex-stack card-p flex-grow-1">
                                                                <span class="symbol symbol-50px me-2">
                                                                    <span class="symbol-label bg-light-info">
                                                                        <i class="ki-duotone ki-abstract-26 fs-2x text-info">
                                                                            <span class="path1"></span>
                                                                            <span class="path2"></span>
                                                                        </i>
                                                                    </span>
                                                                </span>
                                                                <div class="d-flex flex-column text-end">
                                                                    <span class="text-dark fw-bold fs-2">{{ $dashboardData['summary']->total_indicators }}</span>
                                                                    <span class="text-muted fw-semibold mt-1">Total Indicators</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xl-6">
                                                    <div class="card card-xl-stretch mb-xl-8">
                                                        <div class="card-body p-0">
                                                            <div class="d-flex flex-stack card-p flex-grow-1">
                                                                <span class="symbol symbol-50px me-2">
                                                                    <span class="symbol-label bg-light-primary">
                                                                        <i class="ki-duotone ki-abstract-41 fs-2x text-primary">
                                                                            <span class="path1"></span>
                                                                            <span class="path2"></span>
                                                                        </i>
                                                                    </span>
                                                                </span>
                                                                <div class="d-flex flex-column text-end">
                                                                    <span class="text-dark fw-bold fs-2">{{ $dashboardData['summary']->total_clusters }}</span>
                                                                    <span class="text-muted fw-semibold mt-1">Total Clusters</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xl-6">
                                                    <div class="card card-xl-stretch mb-xl-8">
                                                        <div class="card-body p-0">
                                                            <div class="d-flex flex-stack card-p flex-grow-1">
                                                                <span class="symbol symbol-50px me-2">
                                                                    <span class="symbol-label bg-light-danger">
                                                                        <i class="ki-duotone ki-arrow-down fs-2x text-danger">
                                                                            <span class="path1"></span>
                                                                            <span class="path2"></span>
                                                                        </i>
                                                                    </span>
                                                                </span>
                                                                <div class="d-flex flex-column text-end">
                                                                    <span class="text-dark fw-bold fs-2">{{ $dashboardData['summary']->needs_attention_count }}</span>
                                                                    <span class="text-muted fw-semibold mt-1">Needs Attention</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xl-6">
                                                    <div class="card card-xl-stretch mb-xl-8">
                                                        <div class="card-body p-0">
                                                            <div class="d-flex flex-stack card-p flex-grow-1">
                                                                <span class="symbol symbol-50px me-2">
                                                                    <span class="symbol-label bg-light-success">
                                                                        <i class="ki-duotone ki-check-circle fs-2x text-success">
                                                                            <span class="path1"></span>
                                                                            <span class="path2"></span>
                                                                        </i>
                                                                    </span>
                                                                </span>
                                                                <div class="d-flex flex-column text-end">
                                                                    <span class="text-dark fw-bold fs-2">{{ $dashboardData['summary']->met_count + $dashboardData['summary']->over_achieved_count }}</span>
                                                                    <span class="text-muted fw-semibold mt-1">Met/Over Achieved</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row g-5 g-xl-8">
                                        <div class="col-xl-12">
                                            <div id="kt_explorer_status_chart" style="height: 300px"></div>
                                        </div>
                                    </div>
                                    @else
                                    <div class="alert alert-warning">
                                        <div class="d-flex flex-column">
                                            <h4 class="mb-1 text-warning">No Data Available</h4>
                                            <span>There is no performance data available for the selected filters. Please try different filter options.</span>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <div data-kt-stepper-element="content">
                                <div class="w-100">
                                    <div class="pb-10 pb-lg-15">
                                        <h2 class="fw-bold d-flex align-items-center text-dark">Strategic Objectives Performance
                                            <i class="fas fa-exclamation-circle ms-2 fs-7" data-bs-toggle="tooltip" title="Strategic objectives performance analysis"></i>
                                        </h2>
                                        <div class="text-muted fw-semibold fs-6">Performance across strategic objectives</div>
                                    </div>

                                    @if($hasData && isset($dashboardData['strategicObjectives']) && count($dashboardData['strategicObjectives']) > 0)
                                    <div class="row g-5 g-xl-8 mb-5">
                                        <div class="col-xl-12">
                                            <div id="kt_explorer_so_chart" style="height: 300px"></div>
                                        </div>
                                    </div>

                                    <div class="separator separator-dashed my-10"></div>

                                    <div class="table-responsive">
                                        <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                            <thead>
                                                <tr class="fw-bold text-muted bg-light">
                                                    <th class="min-w-100px ps-4 rounded-start">SO Number</th>
                                                    <th class="min-w-200px">Strategic Objective</th>
                                                    <th class="min-w-100px">Indicators</th>
                                                    <th class="min-w-100px">Achievement</th>
                                                    <th class="min-w-100px rounded-end">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($dashboardData['strategicObjectives'] as $so)
                                                <tr>
                                                    <td>{{ $so->so_number }}</td>
                                                    <td>{{ $so->so_name }}</td>
                                                    <td>{{ $so->indicator_count }}</td>
                                                    <td>{{ round($so->avg_achievement_percent, 1) }}%</td>
                                                    <td>
                                                        <span class="badge badge-light-{{ getStatusColorClass($so->status) }}">{{ $so->status }}</span>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @else
                                    <div class="alert alert-warning">
                                        <div class="d-flex flex-column">
                                            <h4 class="mb-1 text-warning">No Strategic Objectives Data</h4>
                                            <span>There is no strategic objectives data available for the selected filters.</span>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <div data-kt-stepper-element="content">
                                <div class="w-100">
                                    <div class="pb-10 pb-lg-15">
                                        <h2 class="fw-bold d-flex align-items-center text-dark">Cluster Performance
                                            <i class="fas fa-exclamation-circle ms-2 fs-7" data-bs-toggle="tooltip" title="Cluster performance analysis"></i>
                                        </h2>
                                        <div class="text-muted fw-semibold fs-6">Performance across clusters</div>
                                    </div>

                                    @if($hasData && isset($dashboardData['clusterPerformance']) && count($dashboardData['clusterPerformance']) > 0)
                                    <div class="row g-5 g-xl-8 mb-5">
                                        <div class="col-xl-12">
                                            <div id="kt_explorer_cluster_chart" style="height: 300px"></div>
                                        </div>
                                    </div>

                                    <div class="separator separator-dashed my-10"></div>

                                    <div class="table-responsive">
                                        <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                            <thead>
                                                <tr class="fw-bold text-muted bg-light">
                                                    <th class="min-w-100px ps-4 rounded-start">Code</th>
                                                    <th class="min-w-200px">Cluster Name</th>
                                                    <th class="min-w-100px">Indicators</th>
                                                    <th class="min-w-100px">SOs</th>
                                                    <th class="min-w-100px">Achievement</th>
                                                    <th class="min-w-100px rounded-end">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($dashboardData['clusterPerformance'] as $cluster)
                                                <tr>
                                                    <td>{{ $cluster->cluster_code }}</td>
                                                    <td>{{ $cluster->cluster_name }}</td>
                                                    <td>{{ $cluster->indicator_count }}</td>
                                                    <td>{{ $cluster->so_count }}</td>
                                                    <td>{{ round($cluster->avg_achievement_percent, 1) }}%</td>
                                                    <td>
                                                        <span class="badge badge-light-{{ getStatusColorClass($cluster->status) }}">{{ $cluster->status }}</span>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @else
                                    <div class="alert alert-warning">
                                        <div class="d-flex flex-column">
                                            <h4 class="mb-1 text-warning">No Cluster Data</h4>
                                            <span>There is no cluster data available for the selected filters.</span>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <div data-kt-stepper-element="content">
                                <div class="w-100">
                                    <div class="pb-10 pb-lg-15">
                                        <h2 class="fw-bold d-flex align-items-center text-dark">Indicator Performance
                                            <i class="fas fa-exclamation-circle ms-2 fs-7" data-bs-toggle="tooltip" title="Indicator performance analysis"></i>
                                        </h2>
                                        <div class="text-muted fw-semibold fs-6">Performance across indicators</div>
                                    </div>

                                    @if($hasData && isset($dashboardData['indicatorPerformance']) && count($dashboardData['indicatorPerformance']) > 0)
                                    <div class="table-responsive">
                                        <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4" id="kt_explorer_indicators_table">
                                            <thead>
                                                <tr class="fw-bold text-muted bg-light">
                                                    <th class="min-w-100px ps-4 rounded-start">SO</th>
                                                    <th class="min-w-150px">Indicator</th>
                                                    <th class="min-w-100px">Target</th>
                                                    <th class="min-w-100px">Actual</th>
                                                    <th class="min-w-100px">Achievement</th>
                                                    <th class="min-w-100px rounded-end">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($dashboardData['indicatorPerformance'] as $indicator)
                                                <tr>
                                                    <td>{{ $indicator->so_number }}</td>
                                                    <td>{{ $indicator->indicator_name }}</td>
                                                    <td>{{ $indicator->total_target_value }}</td>
                                                    <td>{{ $indicator->total_actual_value }}</td>
                                                    <td>{{ round($indicator->avg_achievement_percent, 1) }}%</td>
                                                    <td>
                                                        <span class="badge badge-light-{{ getStatusColorClass($indicator->status) }}">{{ $indicator->status }}</span>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @else
                                    <div class="alert alert-warning">
                                        <div class="d-flex flex-column">
                                            <h4 class="mb-1 text-warning">No Indicator Data</h4>
                                            <span>There is no indicator data available for the selected filters.</span>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <div data-kt-stepper-element="content">
                                <div class="w-100">
                                    <div class="pb-10 pb-lg-15">
                                        <h2 class="fw-bold d-flex align-items-center text-dark">Performance Trends
                                            <i class="fas fa-exclamation-circle ms-2 fs-7" data-bs-toggle="tooltip" title="Performance trends over time"></i>
                                        </h2>
                                        <div class="text-muted fw-semibold fs-6">Historical performance analysis</div>
                                    </div>

                                    @if($hasData && isset($dashboardData['trends']) && isset($dashboardData['trends']['overall']) && count($dashboardData['trends']['overall']) > 1)
                                    <div class="row g-5 g-xl-8 mb-5">
                                        <div class="col-xl-12">
                                            <div id="kt_explorer_trends_chart" style="height: 300px"></div>
                                        </div>
                                    </div>

                                    @if(isset($dashboardData['trends']['growthRates']) && $dashboardData['trends']['growthRates']['overall_growth'] !== null)
                                    <div class="row g-5 g-xl-8 mt-5">
                                        <div class="col-xl-6">
                                            <h4 class="fs-4 fw-bold mb-5">Growth Metrics</h4>
                                            <div class="d-flex flex-wrap">
                                                <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <div class="fs-4 fw-bold">{{ $dashboardData['trends']['growthRates']['overall_growth'] }}%</div>
                                                    </div>
                                                    <div class="fw-semibold fs-6 text-gray-400">Overall Growth</div>
                                                </div>

                                                <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <div class="fs-4 fw-bold">{{ $dashboardData['trends']['growthRates']['cagr'] }}%</div>
                                                    </div>
                                                    <div class="fw-semibold fs-6 text-gray-400">CAGR</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                    @else
                                    <div class="alert alert-warning">
                                        <div class="d-flex flex-column">
                                            <h4 class="mb-1 text-warning">No Trend Data</h4>
                                            <span>There is not enough historical data to show performance trends.</span>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <div class="d-flex flex-stack pt-10">
                                <div class="me-2">
                                    <button type="button" class="btn btn-lg btn-light-primary me-3" data-kt-stepper-action="previous">
                                        <i class="ki-duotone ki-arrow-left fs-3 me-1">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>Back
                                    </button>
                                </div>

                                <div>
                                    <button type="button" class="btn btn-lg btn-primary" data-kt-stepper-action="next">
                                        Continue
                                        <i class="ki-duotone ki-arrow-right fs-3 ms-1 me-0">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                    </button>

                                    <button type="button" class="btn btn-lg btn-primary d-none" data-kt-stepper-action="submit">
                                        <span class="indicator-label">
                                            Finish
                                            <i class="ki-duotone ki-check fs-3 ms-2 me-0">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--end::Modal - Performance Wizard-->

<!--begin::Modal - Detailed Summary-->
<div class="modal fade" id="kt_modal_detailed_summary" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-900px">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Detailed Performance Summary</h2>
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </div>
            </div>
            <div class="modal-body py-lg-10 px-lg-10">
                @if($hasData)
                <div class="d-flex flex-column">
                    <div class="row g-5 g-xl-8 mb-5">
                        <div class="col-xl-6">
                            <div class="card card-xl-stretch mb-xl-8">
                                <div class="card-header border-0">
                                    <h3 class="card-title fw-bold text-dark">Overall Performance</h3>
                                </div>
                                <div class="card-body pt-0">
                                    <div class="d-flex align-items-center bg-light-{{ getStatusColorClass($dashboardData['summary']->overall_status) }} rounded p-5 mb-7">
                                        <span class="svg-icon svg-icon-{{ getStatusColorClass($dashboardData['summary']->overall_status) }} me-5">
                                            <i class="ki-duotone ki-abstract-{{ $dashboardData['summary']->overall_status == 'Needs Attention' ? '26' : ($dashboardData['summary']->overall_status == 'In Progress' ? '25' : '24') }} fs-1 text-{{ getStatusColorClass($dashboardData['summary']->overall_status) }}">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                        </span>
                                        <div class="flex-grow-1 me-2">
                                            <a href="#" class="fw-bold text-gray-800 text-hover-primary fs-6">Achievement</a>
                                            <span class="text-muted fw-semibold d-block">{{ round($dashboardData['summary']->overall_achievement_percent, 1) }}%</span>
                                        </div>
                                        <span class="badge badge-{{ getStatusColorClass($dashboardData['summary']->overall_status) }} fs-8 fw-bold">{{ $dashboardData['summary']->overall_status }}</span>
                                    </div>

                                    <div class="d-flex flex-wrap">
                                        <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                            <div class="d-flex align-items-center">
                                                <i class="ki-duotone ki-arrow-up fs-3 text-success me-2">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                                <div class="fs-4 fw-bold" data-kt-countup="true" data-kt-countup-value="{{ $dashboardData['summary']->total_indicators }}">0</div>
                                            </div>
                                            <div class="fw-semibold fs-6 text-gray-400">Total Indicators</div>
                                        </div>

                                        <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                            <div class="d-flex align-items-center">
                                                <i class="ki-duotone ki-arrow-up fs-3 text-success me-2">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                                <div class="fs-4 fw-bold" data-kt-countup="true" data-kt-countup-value="{{ $dashboardData['summary']->total_clusters }}">0</div>
                                            </div>
                                            <div class="fw-semibold fs-6 text-gray-400">Total Clusters</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-6">
                            <div id="kt_modal_detailed_summary_chart" style="height: 300px"></div>
                        </div>
                    </div>

                    <div class="separator separator-dashed my-10"></div>

                    <div class="row g-5 g-xl-8">
                        <div class="col-xl-12">
                            <h3 class="fw-bold mb-5">Status Distribution</h3>
                            <div class="table-responsive">
                                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                    <thead>
                                        <tr class="fw-bold text-muted bg-light">
                                            <th class="min-w-150px ps-4 rounded-start">Status</th>
                                            <th class="min-w-100px">Count</th>
                                            <th class="min-w-100px">Percentage</th>
                                            <th class="min-w-100px rounded-end">Trend</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <span class="badge badge-light-danger me-2">Needs Attention</span>
                                            </td>
                                            <td>{{ $dashboardData['summary']->needs_attention_count }}</td>
                                            <td>{{ round(($dashboardData['summary']->needs_attention_count / $dashboardData['summary']->total_indicators) * 100, 1) }}%</td>
                                            <td>
                                                @if(isset($dashboardData['trends']['statusTrends']['needs_attention_trend']))
                                                    @if($dashboardData['trends']['statusTrends']['needs_attention_trend'] > 0)
                                                        <span class="badge badge-light-danger">+{{ $dashboardData['trends']['statusTrends']['needs_attention_trend'] }}%</span>
                                                    @elseif($dashboardData['trends']['statusTrends']['needs_attention_trend'] < 0)
                                                        <span class="badge badge-light-success">{{ $dashboardData['trends']['statusTrends']['needs_attention_trend'] }}%</span>
                                                    @else
                                                        <span class="badge badge-light-secondary">0%</span>
                                                    @endif
                                                @else
                                                    <span class="badge badge-light-secondary">N/A</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <span class="badge badge-light-warning me-2">In Progress</span>
                                            </td>
                                            <td>{{ $dashboardData['summary']->in_progress_count }}</td>
                                            <td>{{ round(($dashboardData['summary']->in_progress_count / $dashboardData['summary']->total_indicators) * 100, 1) }}%</td>
                                            <td>
                                                @if(isset($dashboardData['trends']['statusTrends']['in_progress_trend']))
                                                    @if($dashboardData['trends']['statusTrends']['in_progress_trend'] > 0)
                                                        <span class="badge badge-light-warning">+{{ $dashboardData['trends']['statusTrends']['in_progress_trend'] }}%</span>
                                                    @elseif($dashboardData['trends']['statusTrends']['in_progress_trend'] < 0)
                                                        <span class="badge badge-light-success">{{ $dashboardData['trends']['statusTrends']['in_progress_trend'] }}%</span>
                                                    @else
                                                        <span class="badge badge-light-secondary">0%</span>
                                                    @endif
                                                @else
                                                    <span class="badge badge-light-secondary">N/A</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <span class="badge badge-light-info me-2">On Track</span>
                                            </td>
                                            <td>{{ $dashboardData['summary']->on_track_count }}</td>
                                            <td>{{ round(($dashboardData['summary']->on_track_count / $dashboardData['summary']->total_indicators) * 100, 1) }}%</td>
                                            <td>
                                                @if(isset($dashboardData['trends']['statusTrends']['on_track_trend']))
                                                    @if($dashboardData['trends']['statusTrends']['on_track_trend'] > 0)
                                                        <span class="badge badge-light-success">+{{ $dashboardData['trends']['statusTrends']['on_track_trend'] }}%</span>
                                                    @elseif($dashboardData['trends']['statusTrends']['on_track_trend'] < 0)
                                                        <span class="badge badge-light-danger">{{ $dashboardData['trends']['statusTrends']['on_track_trend'] }}%</span>
                                                    @else
                                                        <span class="badge badge-light-secondary">0%</span>
                                                    @endif
                                                @else
                                                    <span class="badge badge-light-secondary">N/A</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <span class="badge badge-light-success me-2">Met</span>
                                            </td>
                                            <td>{{ $dashboardData['summary']->met_count }}</td>
                                            <td>{{ round(($dashboardData['summary']->met_count / $dashboardData['summary']->total_indicators) * 100, 1) }}%</td>
                                            <td>
                                                @if(isset($dashboardData['trends']['statusTrends']['met_trend']))
                                                    @if($dashboardData['trends']['statusTrends']['met_trend'] > 0)
                                                        <span class="badge badge-light-success">+{{ $dashboardData['trends']['statusTrends']['met_trend'] }}%</span>
                                                    @elseif($dashboardData['trends']['statusTrends']['met_trend'] < 0)
                                                        <span class="badge badge-light-danger">{{ $dashboardData['trends']['statusTrends']['met_trend'] }}%</span>
                                                    @else
                                                        <span class="badge badge-light-secondary">0%</span>
                                                    @endif
                                                @else
                                                    <span class="badge badge-light-secondary">N/A</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <span class="badge badge-light-primary me-2">Over Achieved</span>
                                            </td>
                                            <td>{{ $dashboardData['summary']->over_achieved_count }}</td>
                                            <td>{{ round(($dashboardData['summary']->over_achieved_count / $dashboardData['summary']->total_indicators) * 100, 1) }}%</td>
                                            <td>
                                                @if(isset($dashboardData['trends']['statusTrends']['over_achieved_trend']))
                                                    @if($dashboardData['trends']['statusTrends']['over_achieved_trend'] > 0)
                                                        <span class="badge badge-light-success">+{{ $dashboardData['trends']['statusTrends']['over_achieved_trend'] }}%</span>
                                                    @elseif($dashboardData['trends']['statusTrends']['over_achieved_trend'] < 0)
                                                        <span class="badge badge-light-danger">{{ $dashboardData['trends']['statusTrends']['over_achieved_trend'] }}%</span>
                                                    @else
                                                        <span class="badge badge-light-secondary">0%</span>
                                                    @endif
                                                @else
                                                    <span class="badge badge-light-secondary">N/A</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                @else
                <div class="alert alert-warning">
                    <div class="d-flex flex-column">
                        <h4 class="mb-1 text-warning">No Data Available</h4>
                        <span>There is no performance data available for the selected filters. Please try different filter options.</span>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
<!--end::Modal - Detailed Summary-->

<!--begin::Modal - Performance Trends-->
<div class="modal fade" id="kt_modal_performance_trends" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-900px">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Performance Trends Analysis</h2>
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </div>
            </div>
            <div class="modal-body py-lg-10 px-lg-10">
                @if($hasData && isset($dashboardData['trends']) && isset($dashboardData['trends']['overall']) && count($dashboardData['trends']['overall']) > 1)
                <div class="d-flex flex-column">
                    <ul class="nav nav-tabs nav-line-tabs mb-5 fs-6">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#kt_trends_overall_tab">Overall Trends</a>
                        </li>
                        @if(isset($dashboardData['trends']['byStrategicObjective']) && count($dashboardData['trends']['byStrategicObjective']) > 0)
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#kt_trends_so_tab">Strategic Objectives</a>
                        </li>
                        @endif
                        @if(isset($dashboardData['trends']['byCluster']) && count($dashboardData['trends']['byCluster']) > 0)
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#kt_trends_cluster_tab">Clusters</a>
                        </li>
                        @endif
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="kt_trends_overall_tab">
                            <h3 class="fw-bold mb-5">Overall Performance Trends</h3>
                            <div id="kt_modal_trends_chart" style="height: 350px"></div>

                            @if(isset($dashboardData['trends']['growthRates']) && $dashboardData['trends']['growthRates']['overall_growth'] !== null)
                            <div class="d-flex flex-wrap mt-5">
                                <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="fs-4 fw-bold">{{ $dashboardData['trends']['growthRates']['overall_growth'] }}%</div>
                                    </div>
                                    <div class="fw-semibold fs-6 text-gray-400">Overall Growth</div>
                                </div>

                                <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="fs-4 fw-bold">{{ $dashboardData['trends']['growthRates']['cagr'] }}%</div>
                                    </div>
                                    <div class="fw-semibold fs-6 text-gray-400">CAGR</div>
                                </div>
                            </div>
                            @endif
                        </div>

                        @if(isset($dashboardData['trends']['byStrategicObjective']) && count($dashboardData['trends']['byStrategicObjective']) > 0)
                        <div class="tab-pane fade" id="kt_trends_so_tab">
                            <h3 class="fw-bold mb-5">Strategic Objectives Trends</h3>
                            <div id="kt_modal_trends_so_chart" style="height: 350px"></div>
                        </div>
                        @endif

                        @if(isset($dashboardData['trends']['byCluster']) && count($dashboardData['trends']['byCluster']) > 0)
                        <div class="tab-pane fade" id="kt_trends_cluster_tab">
                            <h3 class="fw-bold mb-5">Cluster Trends</h3>
                            <div id="kt_modal_trends_cluster_chart" style="height: 350px"></div>
                        </div>
                        @endif
                    </div>
                </div>
                @else
                <div class="alert alert-warning">
                    <div class="d-flex flex-column">
                        <h4 class="mb-1 text-warning">No Trend Data</h4>
                        <span>There is not enough historical data to show performance trends.</span>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
<!--end::Modal - Performance Trends-->

<!--begin::Modal - Status Details-->
<div class="modal fade" id="kt_modal_status_details" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-900px">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Status Distribution Details</h2>
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </div>
            </div>
            <div class="modal-body py-lg-10 px-lg-10">
                @if($hasData)
                <div class="d-flex flex-column">
                    <div class="table-responsive">
                        <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                            <thead>
                                <tr class="fw-bold text-muted bg-light">
                                    <th class="min-w-150px ps-4 rounded-start">Status</th>
                                    <th class="min-w-100px">Count</th>
                                    <th class="min-w-100px">Percentage</th>
                                    <th class="min-w-100px rounded-end">Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <span class="badge badge-light-danger me-2">Needs Attention</span>
                                    </td>
                                    <td>{{ $dashboardData['summary']->needs_attention_count }}</td>
                                    <td>{{ round(($dashboardData['summary']->needs_attention_count / $dashboardData['summary']->total_indicators) * 100, 1) }}%</td>
                                    <td>Achievement less than 10% of target</td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="badge badge-light-warning me-2">In Progress</span>
                                    </td>
                                    <td>{{ $dashboardData['summary']->in_progress_count }}</td>
                                    <td>{{ round(($dashboardData['summary']->in_progress_count / $dashboardData['summary']->total_indicators) * 100, 1) }}%</td>
                                    <td>Achievement between 10% and 50% of target</td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="badge badge-light-info me-2">On Track</span>
                                    </td>
                                    <td>{{ $dashboardData['summary']->on_track_count }}</td>
                                    <td>{{ round(($dashboardData['summary']->on_track_count / $dashboardData['summary']->total_indicators) * 100, 1) }}%</td>
                                    <td>Achievement between 50% and 90% of target</td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="badge badge-light-success me-2">Met</span>
                                    </td>
                                    <td>{{ $dashboardData['summary']->met_count }}</td>
                                    <td>{{ round(($dashboardData['summary']->met_count / $dashboardData['summary']->total_indicators) * 100, 1) }}%</td>
                                    <td>Achievement between 90% and 105% of target</td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="badge badge-light-primary me-2">Over Achieved</span>
                                    </td>
                                    <td>{{ $dashboardData['summary']->over_achieved_count }}</td>
                                    <td>{{ round(($dashboardData['summary']->over_achieved_count / $dashboardData['summary']->total_indicators) * 100, 1) }}%</td>
                                    <td>Achievement above 105% of target</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                @else
                <div class="alert alert-warning">
                    <div class="d-flex flex-column">
                        <h4 class="mb-1 text-warning">No Data Available</h4>
                        <span>There is no performance data available for the selected filters. Please try different filter options.</span>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
<!--end::Modal - Status Details-->

<!--begin::Modal - Attention Items-->
<div class="modal fade" id="kt_modal_attention_items" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-900px">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Items Needing Attention</h2>
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </div>
            </div>
            <div class="modal-body py-lg-10 px-lg-10">
                @if($hasData)
                <div class="d-flex flex-column">
                    <ul class="nav nav-tabs nav-line-tabs mb-5 fs-6">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#kt_attention_indicators_tab">Indicators</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#kt_attention_clusters_tab">Clusters</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#kt_attention_sos_tab">Strategic Objectives</a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="kt_attention_indicators_tab">
                            <h3 class="fw-bold mb-5">Indicators Needing Attention</h3>

                            @if(isset($dashboardData['attentionItems']) && isset($dashboardData['attentionItems']['indicators']) && count($dashboardData['attentionItems']['indicators']) > 0)
                            <div class="table-responsive">
                                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                    <thead>
                                        <tr class="fw-bold text-muted bg-light">
                                            <th class="min-w-100px ps-4 rounded-start">SO</th>
                                            <th class="min-w-200px">Indicator</th>
                                            <th class="min-w-100px">Achievement</th>
                                            <th class="min-w-100px rounded-end">Clusters</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($dashboardData['attentionItems']['indicators'] as $item)
                                        <tr>
                                            <td>{{ $item->so_number }}</td>
                                            <td>{{ $item->indicator_name }}</td>
                                            <td>
                                                <span class="badge badge-light-danger">{{ round($item->avg_achievement, 1) }}%</span>
                                            </td>
                                            <td>{{ $item->cluster_count }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <div class="d-flex flex-column">
                                <div class="alert alert-success">
                                    <div class="d-flex flex-column">
                                        <h4 class="mb-1 text-success">No Strategic Objectives Needing Attention</h4>
                                        <span>All strategic objectives are performing adequately for the selected period.</span>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>

                        <div class="tab-pane fade" id="kt_attention_clusters_tab">
                            <h3 class="fw-bold mb-5">Clusters Needing Attention</h3>

                            @if(isset($dashboardData['attentionItems']) && isset($dashboardData['attentionItems']['clusters']) && count($dashboardData['attentionItems']['clusters']) > 0)
                            <div class="table-responsive">
                                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                    <thead>
                                        <tr class="fw-bold text-muted bg-light">
                                            <th class="min-w-100px ps-4 rounded-start">Cluster</th>
                                            <th class="min-w-200px">Name</th>
                                            <th class="min-w-100px">Achievement</th>
                                            <th class="min-w-100px rounded-end">Indicators</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($dashboardData['attentionItems']['clusters'] as $item)
                                        <tr>
                                            <td>{{ $item->cluster_pk }}</td>
                                            <td>{{ $item->cluster_name }}</td>
                                            <td>
                                                <span class="badge badge-light-danger">{{ round($item->avg_achievement, 1) }}%</span>
                                            </td>
                                            <td>{{ $item->indicator_count }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <div class="d-flex flex-column">
                                <div class="alert alert-success">
                                    <div class="d-flex flex-column">
                                        <h4 class="mb-1 text-success">No Clusters Needing Attention</h4>
                                        <span>All clusters are performing adequately for the selected period.</span>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>

                        <div class="tab-pane fade" id="kt_attention_sos_tab">
                            <h3 class="fw-bold mb-5">Strategic Objectives Needing Attention</h3>

                            @if(isset($dashboardData['attentionItems']) && isset($dashboardData['attentionItems']['strategicObjectives']) && count($dashboardData['attentionItems']['strategicObjectives']) > 0)
                            <div class="table-responsive">
                                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                    <thead>
                                        <tr class="fw-bold text-muted bg-light">
                                            <th class="min-w-100px ps-4 rounded-start">SO Number</th>
                                            <th class="min-w-200px">Name</th>
                                            <th class="min-w-100px">Achievement</th>
                                            <th class="min-w-100px rounded-end">Indicators</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($dashboardData['attentionItems']['strategicObjectives'] as $item)
                                        <tr>
                                            <td>{{ $item->so_number }}</td>
                                            <td>{{ $item->so_name }}</td>
                                            <td>
                                                <span class="badge badge-light-danger">{{ round($item->avg_achievement, 1) }}%</span>
                                            </td>
                                            <td>{{ $item->indicator_count }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <div class="d-flex flex-column">
                                <div class="alert alert-success">
                                    <div class="d-flex flex-column">
                                        <h4 class="mb-1 text-success">No Strategic Objectives Needing Attention</h4>
                                        <span>All strategic objectives are performing adequately for the selected period.</span>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @else
                <div class="alert alert-warning">
                    <div class="d-flex flex-column">
                        <h4 class="mb-1 text-warning">No Data Available</h4>
                        <span>There is no performance data available for the selected filters. Please try different filter options.</span>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
<!--end::Modal - Attention Items-->

<!--begin::Modal - Insights & Recommendations-->
<div class="modal fade" id="kt_modal_insights_recommendations" tabindex="-1" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered mw-900px">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Insights & Recommendations</h2>
            <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                <i class="ki-duotone ki-cross fs-1">
                    <span class="path1"></span>
                    <span class="path2"></span>
                </i>
            </div>
        </div>
        <div class="modal-body py-lg-10 px-lg-10">
            @if($hasData)
            <div class="d-flex flex-column">
                <ul class="nav nav-tabs nav-line-tabs mb-5 fs-6">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#kt_insights_tab">Insights</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#kt_recommendations_tab">Recommendations</a>
                    </li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade show active" id="kt_insights_tab">
                        <h3 class="fw-bold mb-5">Key Insights</h3>

                        @if(isset($dashboardData['insights']) && count($dashboardData['insights']) > 0)
                            @foreach($dashboardData['insights'] as $insight)
                            <div class="d-flex align-items-center bg-light-{{ $insight['priority'] == 'high' ? 'danger' : ($insight['priority'] == 'medium' ? 'warning' : 'success') }} rounded p-5 mb-7">
                                <span class="svg-icon svg-icon-{{ $insight['priority'] == 'high' ? 'danger' : ($insight['priority'] == 'medium' ? 'warning' : 'success') }} me-5">
                                    <i class="ki-duotone ki-abstract-{{ $insight['priority'] == 'high' ? '26' : ($insight['priority'] == 'medium' ? '25' : '24') }} fs-1 text-{{ $insight['priority'] == 'high' ? 'danger' : ($insight['priority'] == 'medium' ? 'warning' : 'success') }}">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                </span>
                                <div class="flex-grow-1 me-2">
                                    <a href="#" class="fw-bold text-gray-800 text-hover-primary fs-6">{{ ucfirst($insight['type']) }} ({{ ucfirst($insight['category'] ?? 'General') }})</a>
                                    <span class="text-muted fw-semibold d-block">{{ $insight['message'] }}</span>
                                </div>
                                <span class="badge badge-{{ $insight['priority'] == 'high' ? 'danger' : ($insight['priority'] == 'medium' ? 'warning' : 'success') }} fs-8 fw-bold">{{ ucfirst($insight['priority']) }}</span>
                            </div>
                            @endforeach
                        @else
                            <div class="alert alert-info">
                                <div class="d-flex flex-column">
                                    <h4 class="mb-1 text-info">No Insights Available</h4>
                                    <span>There are no insights available for the selected period.</span>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="tab-pane fade" id="kt_recommendations_tab">
                        <h3 class="fw-bold mb-5">Recommendations</h3>

                        @if(isset($dashboardData['recommendations']) && count($dashboardData['recommendations']) > 0)
                            @foreach($dashboardData['recommendations'] as $recommendation)
                            <div class="d-flex align-items-center bg-light-primary rounded p-5 mb-7">
                                <span class="svg-icon svg-icon-primary me-5">
                                    <i class="ki-duotone ki-rocket fs-1 text-primary">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                </span>
                                <div class="flex-grow-1 me-2">
                                    <a href="#" class="fw-bold text-gray-800 text-hover-primary fs-6">{{ ucfirst($recommendation['type']) }} ({{ ucfirst($recommendation['category'] ?? 'General') }})</a>
                                    <span class="text-muted fw-semibold d-block">{{ $recommendation['message'] }}</span>
                                </div>
                                <span class="badge badge-{{ $recommendation['priority'] == 'high' ? 'danger' : ($recommendation['priority'] == 'medium' ? 'warning' : 'success') }} fs-8 fw-bold">{{ ucfirst($recommendation['priority']) }}</span>
                            </div>
                            @endforeach
                        @else
                            <div class="alert alert-info">
                                <div class="d-flex flex-column">
                                    <h4 class="mb-1 text-info">No Recommendations Available</h4>
                                    <span>There are no recommendations available for the selected period.</span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @else
            <div class="alert alert-warning">
                <div class="d-flex flex-column">
                    <h4 class="mb-1 text-warning">No Data Available</h4>
                    <span>There is no performance data available for the selected filters. Please try different filter options.</span>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
</div>
<!--end::Modal - Insights & Recommendations-->

<!--begin::Modal - Cluster Performance-->
<div class="modal fade" id="kt_modal_cluster_performance" tabindex="-1" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered mw-900px">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Cluster Performance</h2>
            <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                <i class="ki-duotone ki-cross fs-1">
                    <span class="path1"></span>
                    <span class="path2"></span>
                </i>
            </div>
        </div>
        <div class="modal-body py-lg-10 px-lg-10">
            @if($hasData && isset($dashboardData['clusterPerformance']) && count($dashboardData['clusterPerformance']) > 0)
            <div class="d-flex flex-column">
                <div class="table-responsive">
                    <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4" id="kt_modal_clusters_table">
                        <thead>
                            <tr class="fw-bold text-muted bg-light">
                                <th class="min-w-100px ps-4 rounded-start">Code</th>
                                <th class="min-w-200px">Cluster Name</th>
                                <th class="min-w-100px">Indicators</th>
                                <th class="min-w-100px">SOs</th>
                                <th class="min-w-100px">Achievement</th>
                                <th class="min-w-100px rounded-end">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dashboardData['clusterPerformance'] as $cluster)
                            <tr>
                                <td>{{ $cluster->cluster_code }}</td>
                                <td>{{ $cluster->cluster_name }}</td>
                                <td>{{ $cluster->indicator_count }}</td>
                                <td>{{ $cluster->so_count }}</td>
                                <td>{{ round($cluster->avg_achievement_percent, 1) }}%</td>
                                <td>
                                    <span class="badge badge-light-{{ getStatusColorClass($cluster->status) }}">{{ $cluster->status }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="separator my-10"></div>

                <div class="row g-5 g-xl-8">
                    <div class="col-xl-6">
                        <h4 class="fs-4 fw-bold mb-5">Top 5 Performing Clusters</h4>
                        @php
                            $topClusters = collect($dashboardData['clusterPerformance'])
                                ->sortByDesc('avg_achievement_percent')
                                ->take(5);
                        @endphp

                        @foreach($topClusters as $index => $cluster)
                        <div class="d-flex align-items-center bg-light-{{ getStatusColorClass($cluster->status) }} rounded p-5 mb-7">
                            <div class="symbol symbol-45px me-5">
                                <span class="symbol-label bg-{{ getStatusColorClass($cluster->status) }}">
                                    <span class="fs-2 fw-bold text-white">{{ $index + 1 }}</span>
                                </span>
                            </div>
                            <div class="d-flex align-items-center flex-row-fluid flex-wrap">
                                <div class="flex-grow-1 me-2">
                                    <a href="#" class="text-gray-800 text-hover-primary fs-6 fw-bold">{{ $cluster->cluster_name }}</a>
                                    <span class="text-muted fw-semibold d-block fs-7">{{ $cluster->indicator_count }} indicators</span>
                                </div>
                                <span class="badge badge-{{ getStatusColorClass($cluster->status) }} fw-bold my-2">{{ round($cluster->avg_achievement_percent, 1) }}%</span>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="col-xl-6">
                        <h4 class="fs-4 fw-bold mb-5">Bottom 5 Performing Clusters</h4>
                        @php
                            $bottomClusters = collect($dashboardData['clusterPerformance'])
                                ->sortBy('avg_achievement_percent')
                                ->take(5);
                        @endphp

                        @foreach($bottomClusters as $index => $cluster)
                        <div class="d-flex align-items-center bg-light-{{ getStatusColorClass($cluster->status) }} rounded p-5 mb-7">
                            <div class="symbol symbol-45px me-5">
                                <span class="symbol-label bg-{{ getStatusColorClass($cluster->status) }}">
                                    <span class="fs-2 fw-bold text-white">{{ $index + 1 }}</span>
                                </span>
                            </div>
                            <div class="d-flex align-items-center flex-row-fluid flex-wrap">
                                <div class="flex-grow-1 me-2">
                                    <a href="#" class="text-gray-800 text-hover-primary fs-6 fw-bold">{{ $cluster->cluster_name }}</a>
                                    <span class="text-muted fw-semibold d-block fs-7">{{ $cluster->indicator_count }} indicators</span>
                                </div>
                                <span class="badge badge-{{ getStatusColorClass($cluster->status) }} fw-bold my-2">{{ round($cluster->avg_achievement_percent, 1) }}%</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @else
            <div class="alert alert-warning">
                <div class="d-flex flex-column">
                    <h4 class="mb-1 text-warning">No Cluster Data</h4>
                    <span>There is no cluster data available for the selected filters. Please try different filter options.</span>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
</div>
<!--begin::CountUp.js Library (Browser Compatible Version)-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/countup.js/2.0.8/countUp.umd.min.js"></script>
<!--end::CountUp.js Library-->

<!--begin::JavaScript-->
<script>
document.addEventListener("DOMContentLoaded", function() {
// Initialize Dashboard Explorer Stepper
var stepperElement = document.querySelector("#kt_modal_dashboard_stepper");
if (stepperElement) {
    // Initialize Stepper
    var stepper = new KTStepper(stepperElement);

    // Stepper change event
    stepper.on("kt.stepper.changed", function(stepper) {
        if (stepper.getCurrentStepIndex() === 5) {
            stepper.getElement().querySelector('[data-kt-stepper-action="next"]').classList.add("d-none");
            stepper.getElement().querySelector('[data-kt-stepper-action="submit"]').classList.remove("d-none");
        } else {
            stepper.getElement().querySelector('[data-kt-stepper-action="next"]').classList.remove("d-none");
            stepper.getElement().querySelector('[data-kt-stepper-action="submit"]').classList.add("d-none");
        }
    });

    // Stepper submit button action
    stepper.on("kt.stepper.submit", function(stepper) {
        // Hide the modal
        const modal = document.getElementById('kt_modal_performance_wizard');
        const modalInstance = bootstrap.Modal.getInstance(modal);
        if (modalInstance) {
            modalInstance.hide();
        }
    });

    // Add click event listeners for the next and previous buttons
    const nextButton = stepperElement.querySelector('[data-kt-stepper-action="next"]');
    const prevButton = stepperElement.querySelector('[data-kt-stepper-action="previous"]');
    const submitButton = stepperElement.querySelector('[data-kt-stepper-action="submit"]');

    if (nextButton) {
        nextButton.addEventListener('click', function() {
            stepper.goNext(); // Go to next step
        });
    }

    if (prevButton) {
        prevButton.addEventListener('click', function() {
            stepper.goPrevious(); // Go to previous step
        });
    }

    if (submitButton) {
        submitButton.addEventListener('click', function() {
            stepper.goNext(); // Complete the stepper
        });
    }
}

// Initialize DataTables
const dataTableOptions = {
    "info": true,
    "order": [],
    "pageLength": 10,
    "lengthChange": true,
    "searching": true,
    "responsive": true
};

const tables = [
    "#kt_strategic_objectives_table",
    "#kt_clusters_table",
    "#kt_indicators_table",
    "#kt_explorer_indicators_table",
    "#kt_modal_clusters_table"
];

tables.forEach(tableId => {
    const table = document.querySelector(tableId);
    if (table) {
        $(tableId).DataTable(dataTableOptions);
    }
});

// Initialize Charts
@if($hasData)
// Status Distribution Chart
const statusDistributionElement = document.getElementById('kt_charts_widget_1_chart_ecsa');
if (statusDistributionElement) {
    const statusColors = {
        'Needs Attention': '#dc3545',
        'In Progress': '#ffc107',
        'On Track': '#17a2b8',
        'Met': '#28a745',
        'Over Achieved': '#6f42c1'
    };

    const statusData = [
        {{ $dashboardData['summary']->needs_attention_count }},
        {{ $dashboardData['summary']->in_progress_count }},
        {{ $dashboardData['summary']->on_track_count }},
        {{ $dashboardData['summary']->met_count }},
        {{ $dashboardData['summary']->over_achieved_count }}
    ];

    const statusLabels = [
        'Needs Attention',
        'In Progress',
        'On Track',
        'Met',
        'Over Achieved'
    ];

    const statusColors_array = [
        statusColors['Needs Attention'],
        statusColors['In Progress'],
        statusColors['On Track'],
        statusColors['Met'],
        statusColors['Over Achieved']
    ];

    const statusChart = new ApexCharts(statusDistributionElement, {
        series: statusData,
        chart: {
            type: 'donut',
            height: 350
        },
        labels: statusLabels,
        colors: statusColors_array,
        legend: {
            position: 'bottom'
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '50%'
                }
            }
        },
        dataLabels: {
            enabled: true,
            formatter: function(val, opt) {
                return opt.w.globals.series[opt.seriesIndex];
            }
        },
        tooltip: {
            y: {
                formatter: function(val) {
                    return val + " indicators";
                }
            }
        }
    });
    statusChart.render();
}

// Explorer Status Chart
const explorerStatusElement = document.getElementById('kt_explorer_status_chart');
if (explorerStatusElement) {
    const statusColors = {
        'Needs Attention': '#dc3545',
        'In Progress': '#ffc107',
        'On Track': '#17a2b8',
        'Met': '#28a745',
        'Over Achieved': '#6f42c1'
    };

    const statusData = [
        {{ $dashboardData['summary']->needs_attention_count }},
        {{ $dashboardData['summary']->in_progress_count }},
        {{ $dashboardData['summary']->on_track_count }},
        {{ $dashboardData['summary']->met_count }},
        {{ $dashboardData['summary']->over_achieved_count }}
    ];

    const statusLabels = [
        'Needs Attention',
        'In Progress',
        'On Track',
        'Met',
        'Over Achieved'
    ];

    const statusColors_array = [
        statusColors['Needs Attention'],
        statusColors['In Progress'],
        statusColors['On Track'],
        statusColors['Met'],
        statusColors['Over Achieved']
    ];

    const explorerStatusChart = new ApexCharts(explorerStatusElement, {
        series: statusData,
        chart: {
            type: 'donut',
            height: 300
        },
        labels: statusLabels,
        colors: statusColors_array,
        legend: {
            position: 'bottom'
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '50%'
                }
            }
        },
        dataLabels: {
            enabled: true,
            formatter: function(val, opt) {
                return opt.w.globals.series[opt.seriesIndex];
            }
        },
        tooltip: {
            y: {
                formatter: function(val) {
                    return val + " indicators";
                }
            }
        }
    });
    explorerStatusChart.render();
}

// Detailed Summary Chart
const detailedSummaryElement = document.getElementById('kt_modal_detailed_summary_chart');
if (detailedSummaryElement) {
    const statusColors = {
        'Needs Attention': '#dc3545',
        'In Progress': '#ffc107',
        'On Track': '#17a2b8',
        'Met': '#28a745',
        'Over Achieved': '#6f42c1'
    };

    const statusData = [
        {{ $dashboardData['summary']->needs_attention_count }},
        {{ $dashboardData['summary']->in_progress_count }},
        {{ $dashboardData['summary']->on_track_count }},
        {{ $dashboardData['summary']->met_count }},
        {{ $dashboardData['summary']->over_achieved_count }}
    ];

    const statusLabels = [
        'Needs Attention',
        'In Progress',
        'On Track',
        'Met',
        'Over Achieved'
    ];

    const statusColors_array = [
        statusColors['Needs Attention'],
        statusColors['In Progress'],
        statusColors['On Track'],
        statusColors['Met'],
        statusColors['Over Achieved']
    ];

    const detailedSummaryChart = new ApexCharts(detailedSummaryElement, {
        series: statusData,
        chart: {
            type: 'pie',
            height: 300
        },
        labels: statusLabels,
        colors: statusColors_array,
        legend: {
            position: 'bottom'
        },
        dataLabels: {
            enabled: true,
            formatter: function(val, opt) {
                return opt.w.globals.series[opt.seriesIndex];
            }
        },
        tooltip: {
            y: {
                formatter: function(val) {
                    return val + " indicators";
                }
            }
        }
    });
    detailedSummaryChart.render();
}

@if(isset($dashboardData['strategicObjectives']) && count($dashboardData['strategicObjectives']) > 0)
// Strategic Objectives Chart
const soChartElement = document.getElementById('kt_strategic_objectives_chart');
if (soChartElement) {
    const soData = [];
    const soLabels = [];
    const soColors = [];

    @foreach($dashboardData['strategicObjectives'] as $so)
    soData.push({{ round($so->avg_achievement_percent, 1) }});
    soLabels.push("{{ $so->so_number }}");
    soColors.push(getStatusColor("{{ $so->status }}"));
    @endforeach

    const soChart = new ApexCharts(soChartElement, {
        series: [{
            name: 'Achievement',
            data: soData
        }],
        chart: {
            type: 'bar',
            height: 350
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '55%',
                distributed: true
            },
        },
        dataLabels: {
            enabled: false
        },
        xaxis: {
            categories: soLabels,
            title: {
                text: 'Strategic Objectives'
            }
        },
        yaxis: {
            title: {
                text: 'Achievement (%)'
            },
            min: 0,
            max: 100
        },
        colors: soColors,
        legend: {
            show: false
        },
        tooltip: {
            y: {
                formatter: function(val) {
                    return val + "%";
                }
            }
        }
    });
    soChart.render();
}

// Explorer SO Chart
const explorerSoChartElement = document.getElementById('kt_explorer_so_chart');
if (explorerSoChartElement) {
    const soData = [];
    const soLabels = [];
    const soColors = [];

    @foreach($dashboardData['strategicObjectives'] as $so)
    soData.push({{ round($so->avg_achievement_percent, 1) }});
    soLabels.push("{{ $so->so_number }}");
    soColors.push(getStatusColor("{{ $so->status }}"));
    @endforeach

    const explorerSoChart = new ApexCharts(explorerSoChartElement, {
        series: [{
            name: 'Achievement',
            data: soData
        }],
        chart: {
            type: 'bar',
            height: 300
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '55%',
                distributed: true
            },
        },
        dataLabels: {
            enabled: false
        },
        xaxis: {
            categories: soLabels,
            title: {
                text: 'Strategic Objectives'
            }
        },
        yaxis: {
            title: {
                text: 'Achievement (%)'
            },
            min: 0,
            max: 100
        },
        colors: soColors,
        legend: {
            show: false
        },
        tooltip: {
            y: {
                formatter: function(val) {
                    return val + "%";
                }
            }
        }
    });
    explorerSoChart.render();
}
@endif

@if(isset($dashboardData['clusterPerformance']) && count($dashboardData['clusterPerformance']) > 0)
// Cluster Performance Chart
const clusterChartElement = document.getElementById('kt_clusters_chart');
if (clusterChartElement) {
    const clusterData = [];
    const clusterLabels = [];
    const clusterColors = [];

    @foreach($dashboardData['clusterPerformance'] as $cluster)
    clusterData.push({{ round($cluster->avg_achievement_percent, 1) }});
    clusterLabels.push("{{ $cluster->cluster_name }}");
    clusterColors.push(getStatusColor("{{ $cluster->status }}"));
    @endforeach

    const clusterChart = new ApexCharts(clusterChartElement, {
        series: [{
            name: 'Achievement',
            data: clusterData
        }],
        chart: {
            type: 'bar',
            height: 350
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '55%',
                distributed: true
            },
        },
        dataLabels: {
            enabled: false
        },
        xaxis: {
            categories: clusterLabels,
            title: {
                text: 'Clusters'
            },
            labels: {
                rotate: -45,
                style: {
                    fontSize: '12px'
                }
            }
        },
        yaxis: {
            title: {
                text: 'Achievement (%)'
            },
            min: 0,
            max: 100
        },
        colors: clusterColors,
        legend: {
            show: false
        },
        tooltip: {
            y: {
                formatter: function(val) {
                    return val + "%";
                }
            }
        }
    });
    clusterChart.render();
}

// Explorer Cluster Chart
const explorerClusterChartElement = document.getElementById('kt_explorer_cluster_chart');
if (explorerClusterChartElement) {
    const clusterData = [];
    const clusterLabels = [];
    const clusterColors = [];

    @foreach($dashboardData['clusterPerformance'] as $cluster)
    clusterData.push({{ round($cluster->avg_achievement_percent, 1) }});
    clusterLabels.push("{{ $cluster->cluster_name }}");
    clusterColors.push(getStatusColor("{{ $cluster->status }}"));
    @endforeach

    const explorerClusterChart = new ApexCharts(explorerClusterChartElement, {
        series: [{
            name: 'Achievement',
            data: clusterData
        }],
        chart: {
            type: 'bar',
            height: 300
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '55%',
                distributed: true
            },
        },
        dataLabels: {
            enabled: false
        },
        xaxis: {
            categories: clusterLabels,
            title: {
                text: 'Clusters'
            },
            labels: {
                rotate: -45,
                style: {
                    fontSize: '12px'
                }
            }
        },
        yaxis: {
            title: {
                text: 'Achievement (%)'
            },
            min: 0,
            max: 100
        },
        colors: clusterColors,
        legend: {
            show: false
        },
        tooltip: {
            y: {
                formatter: function(val) {
                    return val + "%";
                }
            }
        }
    });
    explorerClusterChart.render();
}
@endif

@if(isset($dashboardData['trends']) && isset($dashboardData['trends']['overall']) && count($dashboardData['trends']['overall']) > 1)
// Trends Chart
const trendsElements = [
    'kt_modal_trends_chart',
    'kt_overview_trends_chart',
    'kt_explorer_trends_chart',
    'kt_insights_trends_chart'
];

trendsElements.forEach(elementId => {
    const trendsElement = document.getElementById(elementId);
    if (trendsElement) {
        const trendsData = [];
        const trendsLabels = [];

        @foreach($dashboardData['trends']['overall'] as $trend)
        trendsData.push({{ $trend->avg_achievement }});
        trendsLabels.push("{{ $trend->timeline_year }} {{ $trend->semi_annual_label }}");
        @endforeach

        const trendsChart = new ApexCharts(trendsElement, {
            series: [{
                name: 'Overall Achievement',
                data: trendsData
            }],
            chart: {
                type: 'line',
                height: 350
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            markers: {
                size: 5
            },
            xaxis: {
                categories: trendsLabels,
                title: {
                    text: 'Period'
                }
            },
            yaxis: {
                title: {
                    text: 'Achievement (%)'
                },
                min: 0,
                max: 100
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return val + "%";
                    }
                }
            }
        });
        trendsChart.render();
    }
});

@if(isset($dashboardData['trends']['byStrategicObjective']) && count($dashboardData['trends']['byStrategicObjective']) > 0)
// SO Trends Chart
const soTrendsElement = document.getElementById('kt_modal_trends_so_chart');
if (soTrendsElement) {
    // Group data by SO
    const soData = {};
    const periods = [];

    @foreach($dashboardData['trends']['byStrategicObjective'] as $trend)
    if (!periods.includes("{{ $trend->timeline_year }} {{ $trend->semi_annual_label }}")) {
        periods.push("{{ $trend->timeline_year }} {{ $trend->semi_annual_label }}");
    }

    if (!soData["{{ $trend->so_number }}"]) {
        soData["{{ $trend->so_number }}"] = {
            name: "{{ $trend->so_number }}",
            data: []
        };
    }

    soData["{{ $trend->so_number }}"].data.push({{ $trend->avg_achievement }});
    @endforeach

    const soTrendsSeries = Object.values(soData);

    const soTrendsChart = new ApexCharts(soTrendsElement, {
        series: soTrendsSeries,
        chart: {
            type: 'line',
            height: 300
        },
        stroke: {
            curve: 'smooth',
            width: 2
        },
        markers: {
            size: 4
        },
        xaxis: {
            categories: periods,
            title: {
                text: 'Period'
            }
        },
        yaxis: {
            title: {
                text: 'Achievement (%)'
            },
            min: 0,
            max: 100
        },
        tooltip: {
            y: {
                formatter: function(val) {
                    return val + "%";
                }
            }
        },
        legend: {
            position: 'bottom'
        }
    });
    soTrendsChart.render();
}
@endif

@if(isset($dashboardData['trends']['byCluster']) && count($dashboardData['trends']['byCluster']) > 0)
// Cluster Trends Chart
const clusterTrendsElement = document.getElementById('kt_modal_trends_cluster_chart');
if (clusterTrendsElement) {
    // Group data by Cluster
    const clusterData = {};
    const periods = [];

    @foreach($dashboardData['trends']['byCluster'] as $trend)
    if (!periods.includes("{{ $trend->timeline_year }} {{ $trend->semi_annual_label }}")) {
        periods.push("{{ $trend->timeline_year }} {{ $trend->semi_annual_label }}");
    }

    if (!clusterData["{{ $trend->cluster_name }}"]) {
        clusterData["{{ $trend->cluster_name }}"] = {
            name: "{{ $trend->cluster_name }}",
            data: []
        };
    }

    clusterData["{{ $trend->cluster_name }}"].data.push({{ $trend->avg_achievement }});
    @endforeach

    const clusterTrendsSeries = Object.values(clusterData);

    const clusterTrendsChart = new ApexCharts(clusterTrendsElement, {
        series: clusterTrendsSeries,
        chart: {
            type: 'line',
            height: 300
        },
        stroke: {
            curve: 'smooth',
            width: 2
        },
        markers: {
            size: 4
        },
        xaxis: {
            categories: periods,
            title: {
                text: 'Period'
            }
        },
        yaxis: {
            title: {
                text: 'Achievement (%)'
            },
            min: 0,
            max: 100
        },
        tooltip: {
            y: {
                formatter: function(val) {
                    return val + "%";
                }
            }
        },
        legend: {
            position: 'bottom'
        }
    });
    clusterTrendsChart.render();
}
@endif
@endif
@endif

// Helper function to get status color
function getStatusColor(status) {
    const statusColors = {
        'Needs Attention': '#dc3545',
        'In Progress': '#ffc107',
        'On Track': '#17a2b8',
        'Met': '#28a745',
        'Over Achieved': '#6f42c1'
    };

    return statusColors[status] || '#6c757d';
}

// Initialize Select2
$('[data-control="select2"]').select2({
    minimumResultsForSearch: 10
});

// Initialize CountUp
// Define CountUp manually if it's not available
if (typeof CountUp === 'undefined') {
    // Simple CountUp implementation
    window.CountUp = function(target, endVal, options) {
        this.target = target;
        this.endVal = endVal;
        this.options = options || {};
        this.error = "";

        this.start = function() {
            if (typeof target === 'string') {
                target = document.getElementById(target);
            }
            if (!target) {
                this.error = "Target element not found";
                return false;
            }
            target.textContent = this.formatNumber(endVal);
            return true;
        };

        this.formatNumber = function(num) {
            let result = num.toString();
            if (this.options.separator) {
                result = result.replace(/\B(?=(\d{3})+(?!\d))/g, this.options.separator);
            }
            if (this.options.decimal && this.options.decimal !== '.') {
                result = result.replace('.', this.options.decimal);
            }
            return result;
        };
    };
}

const countUpElements = document.querySelectorAll('[data-kt-countup="true"]');
countUpElements.forEach(element => {
    const value = element.getAttribute('data-kt-countup-value');
    const countUp = new CountUp(element, value, {
        duration: 2,
        separator: ',',
        decimal: '.'
    });

    if (!countUp.error) {
        countUp.start();
    } else {
        console.error(countUp.error);
    }
});
});
</script>
<!--end::JavaScript-->


<!--end::JavaScript-->

