<!--begin::Performance Quarterly Report Filter-->
<div class="card shadow-sm">
    <div class="card-header border-0 pt-6">
        <h3 class="card-title align-items-start flex-column">
            <span class="card-label fw-bold fs-2 mb-1 text-primary">Performance Quarterly Report</span>
            <span class="text-muted mt-1 fw-semibold fs-7">Filter and generate performance analytics</span>
        </h3>
        <div class="card-toolbar">
            <button type="button" class="btn btn-icon btn-primary btn-active-light-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#kt_modal_filter_help">
                <i class="ki-duotone ki-information-5 fs-2">
                    <span class="path1"></span>
                    <span class="path2"></span>
                    <span class="path3"></span>
                </i>
            </button>
        </div>
    </div>
    <div class="card-body pt-0">
        @if(isset($error) && $error)
            <div class="alert alert-warning d-flex align-items-center p-5 mb-10 shadow-sm">
                <i class="ki-duotone ki-information-5 fs-2hx text-warning me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                <div class="d-flex flex-column">
                    <h4 class="mb-1 text-warning">Notice</h4>
                    <span>{{ $error }}</span>
                </div>
            </div>
        @endif

        <!--begin::Form-->
        <form class="form" novalidate="novalidate" id="kt_filter_form" method="post" action="{{ route('ecsahc.performance.quarterly.results') }}">
            @csrf

            <div class="row g-9 mb-8">
                <div class="col-md-6">
                    <label class="fs-6 fw-semibold required mb-2">Year</label>
                    <select name="year" class="form-select form-select-solid shadow-sm" data-control="select2" data-placeholder="Select Year" required>
                        <option></option>
                        @foreach($years as $year)
                            <option value="{{ $year }}" {{ $year == ($defaultYear ?? '') ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">Required field - Select the reporting year</div>
                </div>

                <div class="col-md-6">
                    <label class="fs-6 fw-semibold mb-2">Quarter</label>
                    <select name="quarter" class="form-select form-select-solid shadow-sm" data-control="select2" data-placeholder="All Quarters">
                        <option value="">All Quarters</option>
                        @foreach($quarters as $quarter => $reportName)
                            <option value="{{ $quarter }}">Q{{ $quarter }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">Optional - leave blank for all quarters</div>
                </div>
            </div>

            <div class="row g-9 mb-8" >
                <div class="col-md-6" >
                    <label class="fs-6 fw-semibold mb-2">Cluster</label>
                    <select name="cluster_id" class="form-select form-select-solid shadow-sm" data-control="select2" data-placeholder="All Clusters">
                        <option value="">All Clusters</option>
                        @if(isset($clusters) && $clusters->count() > 0)
                            @foreach($clusters as $cluster)
                                <option value="{{ $cluster->id }}">{{ $cluster->Cluster_Name }} ({{ $cluster->ClusterID }})</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <div class="col-md-6" style="display: none">
                    <label class="fs-6 fw-semibold mb-2">Indicator</label>
                    <select name="indicator_id" class="form-select form-select-solid shadow-sm" data-control="select2" data-placeholder="All Indicators">
                        <option value="">All Indicators</option>
                        @if(isset($indicators) && $indicators->count() > 0)
                            @foreach($indicators as $indicator)
                                <option value="{{ $indicator->id }}">{{ $indicator->Indicator_Number }} - {{ $indicator->Indicator_Name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>

            <div class="row g-9 mb-8" style="display: none">
                <div class="col-md-6">
                    <label class="fs-6 fw-semibold mb-2">Status</label>
                    <select name="status" class="form-select form-select-solid shadow-sm" data-control="select2" data-placeholder="All Statuses">
                        <option value="">All Statuses</option>
                        @if(isset($statuses) && $statuses->count() > 0)
                            @foreach($statuses as $status)
                                <option value="{{ $status }}">{{ $status }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="fs-6 fw-semibold mb-2">Sort By</label>
                    <div class="d-flex gap-4">
                        <select name="sort_by" class="form-select form-select-solid shadow-sm" data-control="select2">
                            <option value="achievement_percent" selected>Achievement</option>
                            <option value="cluster_name">Cluster</option>
                            <option value="indicator_name">Indicator</option>
                            <option value="status_label">Status</option>
                        </select>
                        <select name="sort_direction" class="form-select form-select-solid shadow-sm" data-control="select2">
                            <option value="desc" selected>Descending</option>
                            <option value="asc">Ascending</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end pt-10">
                <button type="submit" class="btn btn-primary fw-bold px-6 py-3 shadow-sm" id="submit-form">
                    <span class="indicator-label">
                        Generate Report
                        <i class="ki-duotone ki-chart-line-star fs-3 ms-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                    </span>
                    <span class="indicator-progress">
                        Please wait... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                    </span>
                </button>
            </div>
        </form>
        <!--end::Form-->
    </div>
</div>
<!--end::Performance Quarterly Report Filter-->

<!--begin::Help Modal-->
<div class="modal fade" id="kt_modal_filter_help" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content shadow-sm">
            <div class="modal-header">
                <h2 class="fw-bold">Filter Help</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                <div class="accordion" id="kt_accordion_filter_help">
                    <div class="accordion-item shadow-sm">
                        <h2 class="accordion-header" id="kt_accordion_filter_help_header_1">
                            <button class="accordion-button fs-4 fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#kt_accordion_filter_help_body_1" aria-expanded="true">
                                Basic Filters
                            </button>
                        </h2>
                        <div id="kt_accordion_filter_help_body_1" class="accordion-collapse collapse show" aria-labelledby="kt_accordion_filter_help_header_1" data-bs-parent="#kt_accordion_filter_help">
                            <div class="accordion-body">
                                <ul class="text-gray-800 fw-semibold">
                                    <li class="mb-2"><strong>Year</strong>: Required. Select the reporting year.</li>
                                    <li class="mb-2"><strong>Quarter</strong>: Optional. Select a specific quarter or leave blank to include all quarters.</li>
                                    <li class="mb-2"><strong>Cluster</strong>: Optional. Filter by specific cluster.</li>
                                    <li class="mb-2"><strong>Indicator</strong>: Optional. Filter by specific indicator.</li>
                                    <li class="mb-2"><strong>Status</strong>: Optional. Filter by performance status.</li>
                                    <li><strong>Sort By</strong>: Choose how to sort the results and in which direction.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary shadow-sm" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-check fs-2"></i>
                    Got it
                </button>
            </div>
        </div>
    </div>
</div>
<!--end::Help Modal-->

<!--begin::Javascript-->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Initialize Select2
        try {
            if ($.fn.select2) {
                $('[data-control="select2"]').select2({
                    minimumResultsForSearch: 10
                });
            }
        } catch (e) {
            console.warn("Select2 initialization failed:", e);
        }

        // Form submission
        const form = document.getElementById('kt_filter_form');
        const submitBtn = document.getElementById('submit-form');

        if (form && submitBtn) {
            form.addEventListener('submit', function(e) {
                // Validate year field
                const yearSelect = document.querySelector('select[name="year"]');
                if (yearSelect && !yearSelect.value) {
                    e.preventDefault();

                    // Show error message using SweetAlert if available, or alert
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            text: "Please select a year to continue.",
                            icon: "error",
                            buttonsStyling: false,
                            confirmButtonText: "Ok, got it!",
                            customClass: {
                                confirmButton: "btn btn-primary"
                            }
                        });
                    } else {
                        alert("Please select a year to continue.");
                    }
                    return false;
                }

                // Show loading indicator
                if (submitBtn) {
                    submitBtn.setAttribute('data-kt-indicator', 'on');
                }

                return true;
            });
        }

        // Add fallback year if none available
        const yearSelect = document.querySelector('select[name="year"]');
        if (yearSelect && yearSelect.options.length <= 1) {
            const currentYear = new Date().getFullYear();
            const option = document.createElement('option');
            option.value = currentYear;
            option.text = currentYear;
            option.selected = true;
            yearSelect.appendChild(option);

            // Refresh Select2 if available
            try {
                if ($.fn.select2) {
                    $(yearSelect).trigger('change');
                }
            } catch (e) {
                console.warn("Select2 refresh failed:", e);
            }
        }
    });
</script>
<!--end::Javascript-->
