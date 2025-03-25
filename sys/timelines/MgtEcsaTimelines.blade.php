<!-- resources/views/timelines.blade.php -->
@php
    use Carbon\Carbon;

    // Generate year range for dropdown (3 years ago to 5 years from now)
    $currentYear = date('Y');
    $startYear = $currentYear - 3;
    $endYear = $currentYear + 5;
    $yearRange = range($startYear, $endYear);
@endphp

<div class="container-xxl">
    <!-- Begin::Header -->
    <div class="card mb-6">
        <div class="card-body py-5">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                <div class="mb-3 mb-md-0">
                    <h1 class="fs-2x fw-bold text-dark mb-0">ECSA-HC Timelines</h1>
                    <span class="text-gray-600 fs-6">Manage quarterly reporting schedules and deadlines</span>
                </div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add_timeline_modal">
                    <i class="ki-duotone ki-plus fs-2 me-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    Add New Timeline
                </button>
            </div>
        </div>
    </div>
    <!-- End::Header -->

    <!-- Begin::Timelines Table -->
    <div class="card card-flush">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-row-bordered table-row-gray-100 align-middle gs-0 gy-3">
                    <thead>
                        <tr class="fw-bold text-muted bg-light">
                            <th class="ps-4 min-w-50px rounded-start">ID</th>
                            <th class="min-w-200px">Report Name</th>
                            <th class="min-w-80px">Quarter</th>
                            <th class="min-w-80px">Year</th>
                            <th class="min-w-120px">Closing Date</th>
                            <th class="min-w-100px">Status</th>
                            <th class="min-w-200px text-end pe-4 rounded-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($timelines as $timeline)
                            <tr>
                                <td class="ps-4">{{ $timeline->id }}</td>
                                <td>{{ $timeline->ReportName }}</td>
                                <td>Q{{ $timeline->Quarter }}</td>
                                <td>{{ $timeline->Year }}</td>
                                <td>{{ Carbon::parse($timeline->ClosingDate)->format('Y-m-d') }}</td>
                                <td>
                                    @if($timeline->status == 'Completed')
                                        <span class="badge badge-light-success">Completed</span>
                                    @elseif($timeline->status == 'In Progress')
                                        <span class="badge badge-light-warning">In Progress</span>
                                    @else
                                        <span class="badge badge-light-danger">Pending</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-icon btn-light-primary btn-sm me-1"
                                        data-bs-toggle="modal" data-bs-target="#edit_timeline_modal_{{ $timeline->id }}">
                                        <i class="ki-duotone ki-pencil fs-3">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                    </button>
                                    <button class="btn btn-icon btn-light-danger btn-sm"
                                        onclick="confirmDelete('{{ $timeline->id }}')">
                                        <i class="ki-duotone ki-trash fs-3">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                            <span class="path3"></span>
                                            <span class="path4"></span>
                                            <span class="path5"></span>
                                        </i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-gray-500 py-10">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="ki-duotone ki-calendar-8 fs-5tx text-gray-300 mb-5">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                            <span class="path3"></span>
                                            <span class="path4"></span>
                                            <span class="path5"></span>
                                            <span class="path6"></span>
                                        </i>
                                        <div class="fs-3 fw-bold text-gray-700 mb-2">No timelines found</div>
                                        <div class="fs-6 text-gray-500 mb-5">Start by adding a new quarterly report timeline</div>
                                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add_timeline_modal">
                                            <i class="ki-duotone ki-plus fs-2 me-2">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                            Add Your First Timeline
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- End::Timelines Table -->
</div>

<!-- Add New Timeline Modal -->
<div class="modal fade" id="add_timeline_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">Add New Timeline</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </div>
            </div>
            <div class="modal-body scroll-y mx-5 mx-xl-15 my-5">
                <form id="kt_modal_add_timeline_form" class="form" action="{{ route('MassInsert') }}" method="POST">
                    @csrf
                    <input type="hidden" name="TableName" value="ecsahc_timelines">
                    <input type="hidden" name="ReportingID" value="{{ md5(uniqid() . now()) }}">
                    <input type="hidden" name="Type" value="Quarterly Reports">

                    <div class="d-flex flex-column mb-8 fv-row">
                        <label class="d-flex align-items-center fs-6 fw-semibold mb-2" for="ReportName">
                            <span class="required">Report Name</span>
                            <i class="fas fa-exclamation-circle ms-2 fs-7" data-bs-toggle="tooltip"
                                title="Select the quarterly report period"></i>
                        </label>
                        <select id="ReportName" name="ReportName" class="form-select form-select-solid" required>
                            <option value="">Select Quarter Report</option>
                            <option value="First Quarter (Q1): July 1 - September 30">First Quarter (Q1): July 1 - September 30</option>
                            <option value="Second Quarter (Q2): October 1 - December 31">Second Quarter (Q2): October 1 - December 31</option>
                            <option value="Third Quarter (Q3): January 1 - March 31">Third Quarter (Q3): January 1 - March 31</option>
                            <option value="Fourth Quarter (Q4): April 1 - June 30">Fourth Quarter (Q4): April 1 - June 30</option>
                        </select>
                    </div>

                    <div class="d-flex flex-column mb-8 fv-row">
                        <label class="d-flex align-items-center fs-6 fw-semibold mb-2" for="Quarter">
                            <span class="required">Quarter</span>
                            <i class="fas fa-exclamation-circle ms-2 fs-7" data-bs-toggle="tooltip"
                                title="This will be automatically set based on the Report Name"></i>
                        </label>
                        <select id="Quarter" name="Quarter" class="form-select form-select-solid" required readonly>
                            <option value="1">Q1</option>
                            <option value="2">Q2</option>
                            <option value="3">Q3</option>
                            <option value="4">Q4</option>
                        </select>
                        <div class="text-muted fs-7 mt-2">This field is automatically set based on the selected Report Name</div>
                    </div>

                    <div class="d-flex flex-column mb-8 fv-row">
                        <label class="fs-6 fw-semibold mb-2" for="Description">
                            <span>Description</span>
                        </label>
                        <textarea id="Description" name="Description" class="form-control form-control-solid"
                            rows="3" placeholder="Enter additional details about this quarterly report"></textarea>
                    </div>

                    <div class="row g-9 mb-8">
                        <div class="col-md-6 fv-row">
                            <label class="d-flex align-items-center fs-6 fw-semibold mb-2" for="Year">
                                <span class="required">Year</span>
                            </label>
                            <select id="Year" name="Year" class="form-select form-select-solid" required>
                                @foreach($yearRange as $year)
                                    <option value="{{ $year }}" {{ $year == $currentYear ? 'selected' : '' }}>{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 fv-row">
                            <label class="d-flex align-items-center fs-6 fw-semibold mb-2" for="ClosingDate">
                                <span class="required">Closing Date</span>
                            </label>
                            <input type="date" id="ClosingDate" name="ClosingDate" class="form-control form-control-solid" required>
                        </div>
                    </div>

                    <div class="d-flex flex-column mb-8 fv-row">
                        <label class="d-flex align-items-center fs-6 fw-semibold mb-2" for="status">
                            <span class="required">Status</span>
                        </label>
                        <select id="status" name="status" class="form-select form-select-solid" required>
                            <option value="Pending">Pending</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Completed">Completed</option>
                        </select>
                    </div>

                    <div class="text-center">
                        <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="kt_modal_add_timeline_submit" class="btn btn-primary">
                            <span class="indicator-label">Save</span>
                            <span class="indicator-progress">Please wait...
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Timeline Modals -->
@foreach ($timelines as $timeline)
    <div class="modal fade" id="edit_timeline_modal_{{ $timeline->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-650px">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="fw-bold">Edit Timeline</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <i class="ki-duotone ki-cross fs-1">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </div>
                </div>
                <div class="modal-body scroll-y mx-5 mx-xl-15 my-5">
                    <form id="kt_modal_edit_timeline_form_{{ $timeline->id }}" class="form"
                        action="{{ route('MassUpdate') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="TableName" value="ecsahc_timelines">
                        <input type="hidden" name="id" value="{{ $timeline->id }}">
                        <input type="hidden" name="Type" value="Quarterly Reports">

                        <div class="d-flex flex-column mb-8 fv-row">
                            <label class="d-flex align-items-center fs-6 fw-semibold mb-2" for="ReportName-{{ $timeline->id }}">
                                <span class="required">Report Name</span>
                                <i class="fas fa-exclamation-circle ms-2 fs-7" data-bs-toggle="tooltip"
                                    title="Select the quarterly report period"></i>
                            </label>
                            <select id="ReportName-{{ $timeline->id }}" name="ReportName" class="form-select form-select-solid" required>
                                <option value="First Quarter (Q1): July 1 - September 30"
                                    {{ $timeline->ReportName == 'First Quarter (Q1): July 1 - September 30' ? 'selected' : '' }}>
                                    First Quarter (Q1): July 1 - September 30
                                </option>
                                <option value="Second Quarter (Q2): October 1 - December 31"
                                    {{ $timeline->ReportName == 'Second Quarter (Q2): October 1 - December 31' ? 'selected' : '' }}>
                                    Second Quarter (Q2): October 1 - December 31
                                </option>
                                <option value="Third Quarter (Q3): January 1 - March 31"
                                    {{ $timeline->ReportName == 'Third Quarter (Q3): January 1 - March 31' ? 'selected' : '' }}>
                                    Third Quarter (Q3): January 1 - March 31
                                </option>
                                <option value="Fourth Quarter (Q4): April 1 - June 30"
                                    {{ $timeline->ReportName == 'Fourth Quarter (Q4): April 1 - June 30' ? 'selected' : '' }}>
                                    Fourth Quarter (Q4): April 1 - June 30
                                </option>
                            </select>
                        </div>

                        <div class="d-flex flex-column mb-8 fv-row">
                            <label class="d-flex align-items-center fs-6 fw-semibold mb-2" for="Quarter-{{ $timeline->id }}">
                                <span class="required">Quarter</span>
                                <i class="fas fa-exclamation-circle ms-2 fs-7" data-bs-toggle="tooltip"
                                    title="This will be automatically set based on the Report Name"></i>
                            </label>
                            <select id="Quarter-{{ $timeline->id }}" name="Quarter" class="form-select form-select-solid" required readonly>
                                <option value="1" {{ $timeline->Quarter == 1 ? 'selected' : '' }}>Q1</option>
                                <option value="2" {{ $timeline->Quarter == 2 ? 'selected' : '' }}>Q2</option>
                                <option value="3" {{ $timeline->Quarter == 3 ? 'selected' : '' }}>Q3</option>
                                <option value="4" {{ $timeline->Quarter == 4 ? 'selected' : '' }}>Q4</option>
                            </select>
                            <div class="text-muted fs-7 mt-2">This field is automatically set based on the selected Report Name</div>
                        </div>

                        <div class="d-flex flex-column mb-8 fv-row">
                            <label class="fs-6 fw-semibold mb-2" for="Description-{{ $timeline->id }}">
                                <span>Description</span>
                            </label>
                            <textarea id="Description-{{ $timeline->id }}" name="Description"
                                class="form-control form-control-solid" rows="3">{{ $timeline->Description }}</textarea>
                        </div>

                        <div class="row g-9 mb-8">
                            <div class="col-md-6 fv-row">
                                <label class="d-flex align-items-center fs-6 fw-semibold mb-2" for="Year-{{ $timeline->id }}">
                                    <span class="required">Year</span>
                                </label>
                                <select id="Year-{{ $timeline->id }}" name="Year" class="form-select form-select-solid" required>
                                    @foreach($yearRange as $year)
                                        <option value="{{ $year }}" {{ $timeline->Year == $year ? 'selected' : '' }}>{{ $year }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 fv-row">
                                <label class="d-flex align-items-center fs-6 fw-semibold mb-2" for="ClosingDate-{{ $timeline->id }}">
                                    <span class="required">Closing Date</span>
                                </label>
                                <input type="date" id="ClosingDate-{{ $timeline->id }}" name="ClosingDate"
                                    value="{{ $timeline->ClosingDate }}" class="form-control form-control-solid" required>
                            </div>
                        </div>

                        <div class="d-flex flex-column mb-8 fv-row">
                            <label class="d-flex align-items-center fs-6 fw-semibold mb-2" for="status-{{ $timeline->id }}">
                                <span class="required">Status</span>
                            </label>
                            <select id="status-{{ $timeline->id }}" name="status" class="form-select form-select-solid" required>
                                <option value="Pending" {{ $timeline->status == 'Pending' ? 'selected' : '' }}>Pending</option>
                                <option value="In Progress" {{ $timeline->status == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="Completed" {{ $timeline->status == 'Completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                        </div>

                        <div class="text-center">
                            <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" id="kt_modal_edit_timeline_submit_{{ $timeline->id }}" class="btn btn-primary">
                                <span class="indicator-label">Update</span>
                                <span class="indicator-progress">Please wait...
                                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="confirm_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-500px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold text-danger">Confirm Deletion</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </div>
            </div>
            <div class="modal-body scroll-y mx-5 mx-xl-15 my-5">
                <div class="d-flex flex-column text-center">
                    <div class="mb-5">
                        <i class="ki-duotone ki-trash fs-5tx text-danger">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                            <span class="path4"></span>
                            <span class="path5"></span>
                        </i>
                    </div>
                    <div class="fs-3 fw-bold text-dark mb-5">Are you sure you want to delete this timeline?</div>
                    <div class="fs-6 text-gray-600 mb-5">You won't be able to revert this action!</div>
                    <form id="deleteForm" method="POST" action="{{ route('MassDelete') }}">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="TableName" value="ecsahc_timelines">
                        <input type="hidden" id="deleteTimelineId" name="id" value="">
                        <div class="d-flex flex-center">
                            <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">
                                <span class="indicator-label">Delete Permanently</span>
                                <span class="indicator-progress">Please wait...
                                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success Toast -->
<div class="position-fixed top-0 end-0 p-3" style="z-index: 11">
    <div id="kt_docs_toast_success" class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false">
        <div class="toast-header">
            <i class="ki-duotone ki-check-circle fs-2 text-success me-3">
                <span class="path1"></span>
                <span class="path2"></span>
            </i>
            <strong class="me-auto">Success</strong>
            <small>Just now</small>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body bg-light-success" id="success-message"></div>
    </div>
</div>

<!-- Error Toast -->
<div class="position-fixed top-0 end-0 p-3" style="z-index: 11">
    <div id="kt_docs_toast_error" class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false">
        <div class="toast-header">
            <i class="ki-duotone ki-shield-cross fs-2 text-danger me-3">
                <span class="path1"></span>
                <span class="path2"></span>
            </i>
            <strong class="me-auto">Error</strong>
            <small>Just now</small>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body bg-light-danger" id="error-message"></div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        // Initialize tooltips
        [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]')).map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Form submission loading indicators
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function() {
                const submitButton = this.querySelector('button[type="submit"]');
                if (submitButton) {
                    submitButton.setAttribute('data-kt-indicator', 'on');
                    submitButton.disabled = true;
                }
            });
        });

        // Enhanced Quarter extraction from ReportName
        function extractQuarterFromReportName(reportName) {
            // Use regex to extract the quarter number from the report name
            const match = reportName.match(/$$Q(\d)$$/);
            if (match && match[1]) {
                return match[1]; // Return the captured quarter number
            }

            // Fallback to string includes method if regex fails
            if (reportName.includes('(Q1)')) return '1';
            if (reportName.includes('(Q2)')) return '2';
            if (reportName.includes('(Q3)')) return '3';
            if (reportName.includes('(Q4)')) return '4';

            return null; // Return null if no quarter found
        }

        // Auto-select Quarter based on ReportName selection
        document.querySelectorAll('select[name="ReportName"]').forEach(select => {
            select.addEventListener('change', function() {
                const form = this.closest('form');
                const quarterSelect = form.querySelector('select[name="Quarter"]');

                if (quarterSelect && this.value) {
                    const quarter = extractQuarterFromReportName(this.value);
                    if (quarter) {
                        quarterSelect.value = quarter;
                    }
                }
            });

            // Trigger change event on page load to set initial values
            if (select.value) {
                select.dispatchEvent(new Event('change'));
            }
        });

        // Make Quarter field read-only (can't use the readonly attribute on select)
        document.querySelectorAll('select[name="Quarter"]').forEach(select => {
            // Use a custom attribute to mark as read-only
            if (select.hasAttribute('readonly')) {
                // Disable direct user interaction
                select.addEventListener('mousedown', function(e) {
                    e.preventDefault();
                    this.blur();
                    return false;
                });

                // Add a visual indicator that it's read-only
                select.classList.add('bg-light-primary');
            }
        });

        // Show notifications if they exist in session
        @if (session('status'))
            document.getElementById('success-message').textContent = "{{ session('status') }}";
            const successToast = new bootstrap.Toast(document.getElementById('kt_docs_toast_success'));
            successToast.show();
        @endif

        @if (session('error'))
            document.getElementById('error-message').textContent = "{{ session('error') }}";
            const errorToast = new bootstrap.Toast(document.getElementById('kt_docs_toast_error'));
            errorToast.show();
        @endif
    });

    // Delete confirmation
    function confirmDelete(timelineId) {
        // Set the hidden field with the timeline ID
        document.getElementById('deleteTimelineId').value = timelineId;
        // Show the confirmation modal
        const deleteModal = new bootstrap.Modal(document.getElementById('confirm_modal'));
        deleteModal.show();
    }
</script>
