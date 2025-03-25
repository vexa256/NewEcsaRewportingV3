{{-- Error Modal: This modal is shown automatically if there are errors --}}
@if($errors->any() || session('error'))
    <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="errorModalLabel">Error</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul class="list ps-5">
                        @foreach($errors->all() as $error)
                            <li class="text-danger">{{ $error }}</li>
                        @endforeach
                        @if(session('error'))
                            <li class="text-danger">{{ session('error') }}</li>
                        @endif
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endif

<div class="d-flex flex-column flex-root">
    <div class="page d-flex flex-row flex-column-fluid">
        <div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">
            <!-- Elegant Header with Subtle Gradient -->
            <div class="header bg-light py-4 shadow-sm animate__animated animate__fadeIn">
                <div class="container-fluid">
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                        <div>
                            <h3 class="fs-4 fw-bold mb-1">Performance Indicator Reporting | Report: {{ $timelineName }} |
                                <span class="badge bg-{{ $timelineStatus === 'In Progress' ? 'success' : ($timelineStatus === 'Not Started' ? 'warning' : 'danger') }}">
                                    {{ $timelineStatus }}
                                </span>
                            </h3>
                            <div class="mt-1 d-flex flex-wrap gap-1">
                                <span class="alert alert-warning py-1 px-2 mb-0 fs-7">
                                    SO: {{ $objectiveName }} | Cluster: {{ $clusterName }}
                                </span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ route('Ecsa_SelectStrategicObjective', [
                                'UserID' => $UserID,
                                'ClusterID' => $ClusterID,
                                'ReportingID' => $ReportingID
                            ]) }}" class="btn btn-sm btn-light-primary d-flex align-items-center gap-2">
                                <i class="bi bi-arrow-left"></i>
                                Go Back
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Progress Overview Card -->
            <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
                <div class="container-fluid py-6">
                    <div class="card mb-6  animate__delay-1s">
                        <div class="card-body p-0">
                            <div class="row g-0">
                                <!-- Progress Circle Section -->
                                <div class="col-md-4 bg-primary bg-opacity-10 p-4 d-flex align-items-center">
                                    <div class="d-flex align-items-center gap-4">
                                        <div class="position-relative">
                                            <div class="progress-circle" data-kt-size="90" data-kt-percent="{{ $progressPercentage }}">
                                                <span class="progress-circle-bar" style="background: conic-gradient(var(--kt-primary) {{ $progressPercentage }}%, var(--kt-gray-200) 0deg);"></span>
                                                <div class="progress-circle-value">
                                                    <span class="fs-2 fw-bold">{{ number_format($progressPercentage, 0) }}%</span>
                                                </div>
                                                <div class="position-absolute top-0 end-0 translate-middle badge badge-circle bg-success animate__animated animate__pulse animate__infinite">
                                                    <span class="visually-hidden">Progress</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <h2 class="fw-bold text-primary">Reporting Progress</h2>
                                            <p class="fs-7 text-gray-700">{{ $reportedIndicators }} of {{ $totalIndicators }} indicators reported</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Stats Section -->
                                <div class="col-md-4 bg-white p-4 d-flex align-items-center">
                                    <div class="row w-100 g-3">
                                        <div class="col-6">
                                            <div class="d-flex flex-column align-items-center justify-content-center p-3 bg-light rounded">
                                                <span class="fs-8 fw-semibold text-primary text-uppercase">Reported</span>
                                                <span class="fs-1 fw-bolder text-success">{{ $reportedIndicators }}</span>
                                                <span class="fs-8 text-muted">Completed</span>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="d-flex flex-column align-items-center justify-content-center p-3 bg-light rounded">
                                                <span class="fs-8 fw-semibold text-primary text-uppercase">Pending</span>
                                                <span class="fs-1 fw-bolder text-warning">{{ $totalIndicators - $reportedIndicators }}</span>
                                                <span class="fs-8 text-muted">Awaiting</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Actions Section -->
                                <div class="col-md-4 bg-white p-4 d-flex align-items-center justify-content-center border-start border-light">
                                    <div class="d-flex gap-2">
                                        <button id="bulkActionBtn" class="btn btn-sm btn-danger d-flex align-items-center gap-1 shadow-sm" disabled>
                                            <i class="bi bi-slash-circle"></i>
                                            Not Applicable
                                        </button>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-primary shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" id="exportBtn"><i class="bi bi-file-earmark-arrow-up me-2"></i> Export Data</a></li>
                                                <li><a class="dropdown-item" id="printBtn"><i class="bi bi-printer me-2"></i> Print Report</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Indicators Wizard Card -->
                    <div class="card shadow-sm  ">
                        <div class="card-header">
                            <div class="card-title">
                                <h2 class="fs-3 fw-bold mb-0">Performance Indicators</h2>
                            </div>
                            <div class="card-toolbar">
                                <div class="position-relative">
                                    <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-4 text-muted"></i>
                                    <input type="text" id="searchIndicators" class="form-control form-control-sm form-control-solid ps-12" placeholder="Search indicators...">
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <form id="bulkNotApplicableForm" action="{{ route('MarkIndicatorsNotApplicable') }}" method="POST">
                                @csrf
                                <input type="hidden" name="UserID" value="{{ $UserID }}">
                                <input type="hidden" name="ClusterID" value="{{ $ClusterID }}">
                                <input type="hidden" name="ReportingID" value="{{ $ReportingID }}">
                                <input type="hidden" name="StrategicObjectiveID" value="{{ $StrategicObjectiveID }}">

                                @php $indicatorsPerPage = 3; @endphp

                                <!-- Wizard Navigation -->
                                <div class="d-flex justify-content-center mb-5">
                                    <ul class="nav nav-pills nav-pills-custom" id="indicatorTabs" role="tablist">
                                        <li class="nav-item" role="presentation" style="color:black">
                                            <button class="nav-link active px-4" data-page="1" id="page-1-tab" data-bs-toggle="pill" data-bs-target="#page-1" type="button" role="tab" aria-controls="page-1" aria-selected="true">Page 1</button>
                                        </li>
                                        @php
                                            $totalIndicators = count($indicators);
                                            $totalPages = $totalIndicators > 0 ? ceil($totalIndicators / $indicatorsPerPage) : 1;
                                            for ($i = 2; $i <= $totalPages; $i++) {
                                                echo '<li class="nav-item" role="presentation">
                                                    <button class="nav-link px-4" data-page="' . $i . '" id="page-' . $i . '-tab" data-bs-toggle="pill" data-bs-target="#page-' . $i . '" type="button" role="tab" aria-controls="page-' . $i . '" aria-selected="false">Page ' . $i . '</button>
                                                </li>';
                                            }
                                        @endphp
                                    </ul>
                                </div>

                                <!-- Wizard Pages Container -->
                                <div class="tab-content" id="indicatorPages">
                                    @for ($page = 1; $page <= $totalPages; $page++)
                                        <div class="tab-pane fade {{ $page == 1 ? 'show active' : '' }} indicator-page animate__animated animate__fadeIn"
                                             id="page-{{ $page }}" data-page="{{ $page }}" role="tabpanel" aria-labelledby="page-{{ $page }}-tab">
                                            @php
                                                $startIndex = ($page - 1) * $indicatorsPerPage;
                                                $endIndex = min($startIndex + $indicatorsPerPage - 1, $totalIndicators - 1);
                                            @endphp

                                            @if ($startIndex <= $endIndex)
                                                @for ($i = $startIndex; $i <= $endIndex; $i++)
                                                    @php $indicator = $indicators[$i]; @endphp
                                                    <div class="card card-bordered mb-5 hover-elevate-up transition-all">
                                                        <div class="card-body p-5">
                                                            <div class="d-flex align-items-start gap-3">
                                                                <div class="form-check form-check-custom form-check-sm align-self-start pt-1">
                                                                    <input type="checkbox"
                                                                           class="form-check-input indicator-checkbox"
                                                                           name="IndicatorIDs[]" value="{{ $indicator->id }}"
                                                                           {{ isset($existingReports[$indicator->id]) && $existingReports[$indicator->id]->Response === 'Not Applicable' ? 'disabled' : '' }}>
                                                                </div>
                                                                <div class="flex-grow-1">
                                                                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 mb-3">
                                                                        <div>
                                                                            <h3 class="fs-5 fw-bold mb-1">{{ $indicator->Indicator_Name }}</h3>
                                                                            <div class="fs-7 text-muted font-monospace">{{ $indicator->Indicator_Number }}</div>
                                                                        </div>
                                                                        <div>
                                                                            @if (isset($existingReports[$indicator->id]))
                                                                                @if ($existingReports[$indicator->id]->Response === 'Not Applicable')
                                                                                    <span class="badge badge-light-dark d-inline-flex align-items-center gap-1">
                                                                                        <i class="bi bi-slash-circle"></i>
                                                                                        Not Applicable
                                                                                    </span>
                                                                                @else
                                                                                    <span class="badge badge-light-success d-inline-flex align-items-center gap-1">
                                                                                        <i class="bi bi-check-circle"></i>
                                                                                        Reported
                                                                                    </span>
                                                                                @endif
                                                                            @else
                                                                                <span class="badge badge-light-warning d-inline-flex align-items-center gap-1">
                                                                                    <i class="bi bi-clock"></i>
                                                                                    Pending
                                                                                </span>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                    <div class="separator my-3"></div>
                                                                    <div class="d-flex flex-wrap gap-2 justify-content-end">
                                                                        <button type="button"
                                                                                class="btn btn-sm btn-primary d-flex align-items-center gap-1 transition-hover"
                                                                                data-indicator-id="{{ $indicator->id }}"
                                                                                data-indicator-name="{{ $indicator->Indicator_Name }}"
                                                                                data-indicator-number="{{ $indicator->Indicator_Number }}"
                                                                                data-response-type="{{ $indicator->ResponseType }}"
                                                                                data-baseline="{{ $indicator->Baseline2024 }}"
                                                                                data-needs-baseline="{{ is_null($indicator->Baseline2024) && $indicator->ResponseType === 'Number' ? 'true' : 'false' }}"
                                                                                data-existing-response="{{ isset($existingReports[$indicator->id]) ? $existingReports[$indicator->id]->Response : '' }}"
                                                                                data-existing-comment="{{ isset($existingReports[$indicator->id]) ? $existingReports[$indicator->id]->ReportingComment : '' }}"
                                                                                data-bs-toggle="modal" data-bs-target="#reportModal" onclick="openReportModal(this)">
                                                                            <i class="bi bi-pencil"></i>
                                                                            {{ isset($existingReports[$indicator->id]) ? 'Edit' : 'Report' }}
                                                                        </button>
                                                                        @if (isset($existingReports[$indicator->id]))
                                                                            <button type="button"
                                                                                    class="btn btn-sm btn-light d-flex align-items-center gap-1 transition-hover"
                                                                                    data-indicator-id="{{ $indicator->id }}"
                                                                                    data-indicator-name="{{ $indicator->Indicator_Name }}"
                                                                                    data-indicator-number="{{ $indicator->Indicator_Number }}"
                                                                                    data-response-type="{{ $indicator->ResponseType }}"
                                                                                    data-existing-response="{{ $existingReports[$indicator->id]->Response }}"
                                                                                    data-existing-comment="{{ $existingReports[$indicator->id]->ReportingComment }}"
                                                                                    data-reporter-name="{{ $existingReports[$indicator->id]->reporter_name }}"
                                                                                    data-reporter-email="{{ $existingReports[$indicator->id]->reporter_email }}"
                                                                                    data-reported-at="{{ $existingReports[$indicator->id]->updated_at }}"
                                                                                    data-bs-toggle="modal" data-bs-target="#detailsModal" onclick="openDetailsModal(this)">
                                                                                <i class="bi bi-info-circle"></i>
                                                                                Details
                                                                            </button>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endfor
                                            @else
                                                <div class="alert alert-info d-flex align-items-center">
                                                    <i class="bi bi-info-circle fs-4 me-2"></i>
                                                    <div>No indicators found for this page.</div>
                                                </div>
                                            @endif

                                            <!-- Pagination Controls -->
                                            <div class="d-flex justify-content-between mt-5">
                                                <button type="button" class="btn btn-sm btn-light-primary prev-page d-flex align-items-center gap-1" {{ $page == 1 ? 'disabled' : '' }}>
                                                    <i class="bi bi-chevron-left"></i> Previous
                                                </button>
                                                <span class="d-flex align-items-center fs-7">Page {{ $page }} of {{ $totalPages }}</span>
                                                <button type="button" class="btn btn-sm btn-light-primary next-page d-flex align-items-center gap-1" {{ $page == $totalPages ? 'disabled' : '' }}>
                                                    Next <i class="bi bi-chevron-right"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endfor
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Report Modal with Baseline Input (if missing baseline) -->
<div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
        <div class="modal-content animate__animated animate__fadeIn">
            <div class="modal-header">
                <h5 class="modal-title fw-bold modal-title" id="reportModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="reportForm" action="{{ route('Ecsa_SavePerformanceReport') }}" method="POST">
                    @csrf
                    <input type="hidden" name="UserID" value="{{ $UserID }}">
                    <input type="hidden" name="ClusterID" value="{{ $ClusterID }}">
                    <input type="hidden" name="ReportingID" value="{{ $ReportingID }}">
                    <input type="hidden" name="StrategicObjectiveID" value="{{ $StrategicObjectiveID }}">
                    <input type="hidden" name="IndicatorID" id="modalIndicatorID">
                    <input type="hidden" name="ResponseType" id="modalResponseType">
                    <div class="row justify-content-center">
                        <div class="col-xl-8">
                            <div class="card bg-light-primary mb-5  animate__delay-1s">
                                <div class="card-body p-5">
                                    <h3 id="modalIndicatorName" class="fs-5 fw-bold mb-1"></h3>
                                    <p id="modalIndicatorNumber" class="fs-7 text-muted"></p>
                                    <div class="mt-2">
                                        <span class="fs-7">Response Type:</span>
                                        <span id="modalResponseTypeDisplay" class="badge badge-primary ms-2"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-5  ">
                                <label class="form-label fw-bold required">Response</label>
                                <div id="responseInputContainer"></div>
                            </div>
                            <!-- Baseline Input Container (shown only if baseline is missing) -->
                            <div id="baselineInputContainer" class="mb-5  " style="display: none;">
                                <label class="form-label fw-bold required">Baseline Value (2024)</label>
                                <input type="number" name="Baseline" id="baselineInput" class="form-control" step="any" />
                                <div class="form-text text-info">This indicator is missing baseline data. Please provide the baseline value.</div>
                            </div>
                            <div class="mb-5  animate__delay-3s">
                                <label class="form-label fw-bold">Comment</label>
                                <textarea class="form-control" name="Comment" rows="3" id="modalComment"></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                @if ($timelineStatus === 'In Progress')
                    <button type="button" class="btn btn-primary" onclick="submitReportForm()">
                        <span class="indicator-label">Save Report</span>
                        <span class="indicator-progress">Please wait...
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                        </span>
                    </button>
                @else
                    <button type="button" class="btn btn-danger" disabled>Report Closed</button>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
        <div class="modal-content animate__animated animate__fadeIn">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="detailsModalLabel">Indicator Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row justify-content-center">
                    <div class="col-xl-8">
                        <div class="card bg-light mb-5  animate__delay-1s">
                            <div class="card-body p-5">
                                <h3 id="detailsIndicatorName" class="fs-5 fw-bold mb-1"></h3>
                                <p id="detailsIndicatorNumber" class="fs-7 text-muted"></p>
                            </div>
                        </div>
                        <div class="row g-5">
                            <div class="col-md-6">
                                <div class="card shadow-sm  ">
                                    <div class="card-body p-5">
                                        <div class="d-flex flex-column">
                                            <div class="text-muted fs-7 mb-1">Response Type</div>
                                            <div id="detailsResponseType" class="fs-5 fw-bold"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card shadow-sm  ">
                                    <div class="card-body p-5">
                                        <div class="d-flex flex-column">
                                            <div class="text-muted fs-7 mb-1">Response</div>
                                            <div id="detailsResponse" class="fs-5 fw-bold"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="card shadow-sm  animate__delay-3s">
                                    <div class="card-body p-5">
                                        <div class="d-flex flex-column">
                                            <div class="text-muted fs-7 mb-1">Comment</div>
                                            <div id="detailsComment" class="fs-6"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card shadow-sm  animate__delay-4s">
                                    <div class="card-body p-5">
                                        <div class="d-flex flex-column">
                                            <div class="text-muted fs-7 mb-1">Reported By</div>
                                            <div id="detailsReporterName" class="fs-5 fw-bold"></div>
                                            <div id="detailsReporterEmail" class="fs-7 text-muted"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card shadow-sm  animate__delay-4s">
                                    <div class="card-body p-5">
                                        <div class="d-flex flex-column">
                                            <div class="text-muted fs-7 mb-1">Reported At</div>
                                            <div id="detailsReportedAt" class="fs-5 fw-bold"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<!-- Animate.css -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<!-- Custom Styles -->
<style>
    /* Progress Circle */
    .progress-circle {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 90px;
        height: 90px;
    }

    .progress-circle-bar {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        mask: radial-gradient(white 55%, transparent 0);
        -webkit-mask: radial-gradient(white 55%, transparent 0);
    }

    .progress-circle-value {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Nav Pills Custom */
    .nav-pills-custom .nav-link {
        color: var(--kt-gray-700);
        background: var(--kt-gray-200);
        position: relative;
    }

    .nav-pills-custom .nav-link.active {
        color: #fff;
        background: var(--kt-primary);
    }

    /* Hover Elevate Up */
    .hover-elevate-up {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .hover-elevate-up:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1.5rem 0.5rem rgba(0, 0, 0, 0.075);
    }

    /* Transition Hover */
    .transition-hover {
        transition: all 0.3s ease;
    }

    .transition-hover:hover:not(:disabled) {
        transform: translateY(-3px);
    }



    /* Error Modal */
    @if($errors->any() || session('error'))
    .modal-backdrop {
        opacity: 0.5 !important;
    }
    @endif
</style>

<!-- Core JS for Theme, Pagination, Checkbox Handling, Report and Details Modals -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show error modal if there are errors
    @if($errors->any() || session('error'))
    var errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
    errorModal.show();
    @endif

    // Initialize Bootstrap components
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Wizard pagination
    const tabs = document.querySelectorAll('[data-page]');
    const pages = document.querySelectorAll('.indicator-page');
    const prevButtons = document.querySelectorAll('.prev-page');
    const nextButtons = document.querySelectorAll('.next-page');

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const pageNum = this.getAttribute('data-page');
            showPage(pageNum);
        });
    });

    prevButtons.forEach(button => {
        button.addEventListener('click', function() {
            const currentPage = document.querySelector('.tab-pane.active');
            if (currentPage) {
                const currentPageNum = parseInt(currentPage.getAttribute('data-page'));
                if (currentPageNum > 1) {
                    showPage(currentPageNum - 1);
                }
            } else {
                showPage(1);
            }
        });
    });

    nextButtons.forEach(button => {
        button.addEventListener('click', function() {
            const currentPage = document.querySelector('.tab-pane.active');
            if (currentPage) {
                const currentPageNum = parseInt(currentPage.getAttribute('data-page'));
                const maxPage = document.querySelectorAll('.indicator-page').length;
                if (currentPageNum < maxPage) {
                    showPage(currentPageNum + 1);
                }
            } else {
                showPage(1);
            }
        });
    });

    function showPage(pageNum) {
        pageNum = parseInt(pageNum);

        // Update tab states
        tabs.forEach(tab => {
            const tabPage = parseInt(tab.getAttribute('data-page'));
            if (tabPage === pageNum) {
                tab.classList.add('active');
                tab.setAttribute('aria-selected', 'true');
            } else {
                tab.classList.remove('active');
                tab.setAttribute('aria-selected', 'false');
            }
        });

        // Update page visibility
        pages.forEach(page => {
            const pageNumber = parseInt(page.getAttribute('data-page'));
            if (pageNumber === pageNum) {
                page.classList.add('show', 'active');
                page.classList.add('animate__animated', 'animate__fadeIn');
                setTimeout(() => {
                    page.classList.remove('animate__animated', 'animate__fadeIn');
                }, 1000);
            } else {
                page.classList.remove('show', 'active');
            }
        });

        window.location.hash = `page-${pageNum}`;
    }

    // Checkbox handling
    const checkboxes = document.querySelectorAll('.indicator-checkbox:not([disabled])');
    const bulkActionBtn = document.getElementById('bulkActionBtn');

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateButtonState);
    });

    function updateButtonState() {
        const checkedBoxes = document.querySelectorAll('.indicator-checkbox:checked');
        const someChecked = checkedBoxes.length > 0;
        bulkActionBtn.disabled = !someChecked;
    }

    bulkActionBtn.addEventListener('click', function() {
        if (!this.disabled && confirm('Are you sure you want to mark the selected indicators as Not Applicable?')) {
            document.getElementById('bulkNotApplicableForm').submit();
        }
    });

    // Search functionality
    const searchInput = document.getElementById('searchIndicators');
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const indicators = document.querySelectorAll('.card.card-bordered');
        let foundAny = false;
        let foundOnPage = {};

        indicators.forEach(indicator => {
            const name = indicator.querySelector('h3').textContent.toLowerCase();
            const number = indicator.querySelector('.font-monospace').textContent.toLowerCase();
            const page = indicator.closest('.indicator-page');
            const pageNum = page ? page.getAttribute('data-page') : null;

            if (name.includes(searchTerm) || number.includes(searchTerm)) {
                indicator.style.display = '';
                foundAny = true;
                if (pageNum) {
                    foundOnPage[pageNum] = true;
                }
            } else {
                indicator.style.display = 'none';
            }
        });

        if (searchTerm === '') {
            indicators.forEach(indicator => {
                indicator.style.display = '';
            });
            showPage('1');
        } else if (foundAny) {
            const firstPageWithResults = Object.keys(foundOnPage)[0] || '1';
            showPage(firstPageWithResults);
        }

        const noResultsMsg = document.getElementById('noResultsMsg');
        if (!foundAny && searchTerm !== '') {
            if (!noResultsMsg) {
                const msg = document.createElement('div');
                msg.id = 'noResultsMsg';
                msg.className = 'alert alert-warning mt-4 animate__animated animate__fadeIn';
                msg.innerHTML = '<i class="bi bi-search-heart"></i> No indicators found matching "' + searchTerm + '"';
                document.querySelector('#indicatorPages').appendChild(msg);
            }
        } else if (noResultsMsg) {
            noResultsMsg.remove();
        }
    });

    updateButtonState();

    // Add hover effects to buttons
    const buttons = document.querySelectorAll('.btn:not(.btn-close)');
    buttons.forEach(button => {
        button.classList.add('transition-hover');
    });
});

function openReportModal(button) {
    const indicatorId = button.getAttribute('data-indicator-id');
    const indicatorName = button.getAttribute('data-indicator-name');
    const indicatorNumber = button.getAttribute('data-indicator-number');
    const responseType = button.getAttribute('data-response-type');
    const existingResponse = button.getAttribute('data-existing-response');
    const existingComment = button.getAttribute('data-existing-comment');
    const baseline = button.getAttribute('data-baseline');
    const needsBaseline = button.getAttribute('data-needs-baseline') === 'true';

    const modalTitle = document.querySelector('#reportModalLabel');
    const modalIndicatorName = document.querySelector('#modalIndicatorName');
    const modalIndicatorNumber = document.querySelector('#modalIndicatorNumber');
    const modalIndicatorID = document.querySelector('#modalIndicatorID');
    const modalResponseType = document.querySelector('#modalResponseType');
    const modalResponseTypeDisplay = document.querySelector('#modalResponseTypeDisplay');
    const modalComment = document.querySelector('#modalComment');
    const responseInputContainer = document.querySelector('#responseInputContainer');
    const baselineInputContainer = document.getElementById('baselineInputContainer');
    const baselineInput = document.getElementById('baselineInput');

    modalTitle.textContent = `Report Indicator ${indicatorNumber}`;
    modalIndicatorName.textContent = indicatorName;
    modalIndicatorNumber.textContent = `Indicator Number: ${indicatorNumber}`;
    modalIndicatorID.value = indicatorId;
    modalResponseType.value = responseType;
    modalResponseTypeDisplay.textContent = responseType;
    modalComment.value = existingComment || '';

    // Show baseline input only if needed
    if (needsBaseline && responseType === 'Number') {
        baselineInputContainer.style.display = '';
        baselineInput.required = true;
    } else {
        baselineInputContainer.style.display = 'none';
        baselineInput.required = false;
    }

    // Clear previous response input
    responseInputContainer.innerHTML = '';
    let inputElement;

    switch (responseType) {
        case 'Text':
            inputElement = document.createElement('textarea');
            inputElement.className = 'form-control animate__animated animate__fadeIn';
            inputElement.name = 'Response';
            inputElement.rows = '3';
            break;
        case 'Number':
            inputElement = document.createElement('input');
            inputElement.type = 'number';
            inputElement.className = 'form-control animate__animated animate__fadeIn';
            inputElement.name = 'Response';
            inputElement.step = 'any';
            break;
        case 'Boolean':
        case 'Yes/No':
            inputElement = document.createElement('select');
            inputElement.className = 'form-select animate__animated animate__fadeIn';
            inputElement.name = 'Response';
            const options = responseType === 'Boolean' ? ['True', 'False'] : ['Yes', 'No'];

            // Add empty option first
            const emptyOption = document.createElement('option');
            emptyOption.value = '';
            emptyOption.textContent = `Select ${responseType} value...`;
            inputElement.appendChild(emptyOption);

            // Add actual options
            options.forEach(option => {
                const optionElement = document.createElement('option');
                optionElement.value = option;
                optionElement.textContent = option;
                inputElement.appendChild(optionElement);
            });
            break;
        default:
            inputElement = document.createElement('input');
            inputElement.type = 'text';
            inputElement.className = 'form-control animate__animated animate__fadeIn';
            inputElement.name = 'Response';
    }

    if (existingResponse) {
        inputElement.value = existingResponse;
    }

    responseInputContainer.appendChild(inputElement);
}

function submitReportForm() {
    // Validate required fields before submission
    const responseInput = document.querySelector('#responseInputContainer input, #responseInputContainer textarea, #responseInputContainer select');
    if (!responseInput || !responseInput.value.trim()) {
        alert('Please provide a response.');
        if(responseInput) responseInput.focus();
        return;
    }

    const baselineInputContainer = document.getElementById('baselineInputContainer');
    const baselineInput = document.getElementById('baselineInput');
    if (baselineInputContainer.style.display !== 'none' && (!baselineInput.value || baselineInput.value.trim() === '')) {
        alert('Please provide a baseline value.');
        baselineInput.focus();
        return;
    }

    // Show loading indicator
    const submitBtn = document.querySelector('.modal-footer .btn-primary');
    submitBtn.setAttribute('data-kt-indicator', 'on');
    submitBtn.disabled = true;

    const form = document.getElementById('reportForm');
    form.classList.add('animate__animated', 'animate__fadeOutUp');
    setTimeout(() => {
        form.submit();
    }, 300);
}

function openDetailsModal(button) {
    const indicatorName = button.getAttribute('data-indicator-name');
    const indicatorNumber = button.getAttribute('data-indicator-number');
    const responseType = button.getAttribute('data-response-type');
    const existingResponse = button.getAttribute('data-existing-response');
    const existingComment = button.getAttribute('data-existing-comment');
    const reporterName = button.getAttribute('data-reporter-name');
    const reporterEmail = button.getAttribute('data-reporter-email');
    const reportedAt = button.getAttribute('data-reported-at');

    document.querySelector('#detailsIndicatorName').textContent = indicatorName;
    document.querySelector('#detailsIndicatorNumber').textContent = `Indicator Number: ${indicatorNumber}`;
    document.querySelector('#detailsResponseType').textContent = responseType;
    document.querySelector('#detailsResponse').textContent = existingResponse;
    document.querySelector('#detailsComment').textContent = existingComment || 'No comment provided';
    document.querySelector('#detailsReporterName').textContent = reporterName;
    document.querySelector('#detailsReporterEmail').textContent = reporterEmail;
    document.querySelector('#detailsReportedAt').textContent = new Date(reportedAt).toLocaleString();
}

// Export and Print functionality
document.getElementById('exportBtn').addEventListener('click', function() {
    alert('Export functionality will be implemented based on specific requirements.');
});

document.getElementById('printBtn').addEventListener('click', function() {
    window.print();
});
</script>
