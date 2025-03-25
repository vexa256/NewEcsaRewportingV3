<div class="card shadow-sm">
    <div class="card-header">
        <h3 class="card-title">Filter Cluster Completeness Report</h3>
        <div class="card-toolbar">
            <a href="{{ route('completeness.filter') }}" class="btn btn-sm btn-light-primary">
                <i class="ki-duotone ki-arrows-circle fs-2">
                    <span class="path1"></span>
                    <span class="path2"></span>
                </i>
                Reset Filters
            </a>
        </div>
    </div>
    <div class="card-body">
        @if(isset($error))
            <div class="alert alert-danger">
                <div class="d-flex flex-column">
                    <h4 class="mb-1 text-danger">Error</h4>
                    <span>{{ $error }}</span>
                </div>
            </div>
        @endif

        <form action="{{ route('completeness.report') }}" method="GET" id="filter-form">
            <div class="row g-5 mb-5">
                <!-- Year Selection -->
                <div class="col-md-6">
                    <label class="form-label fw-bold">Reporting Year</label>
                    <select name="year" class="form-select form-select-solid" data-control="select2" data-placeholder="Select a year">
                        <option value="">Select a year</option>
                        @foreach($years ?? [] as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">Select a specific reporting year to view completeness data.</div>
                </div>

                <!-- Cluster Selection -->
                <div class="col-md-6">
                    <label class="form-label fw-bold">Cluster (Optional)</label>
                    <select name="cluster_pk[]" class="form-select form-select-solid" data-control="select2" data-placeholder="All Clusters" multiple>
                        <option value="">All Clusters</option>
                        @foreach($clusters ?? [] as $cluster)
                            <option value="{{ $cluster->cluster_pk }}">{{ $cluster->cluster_name }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">Filter by specific clusters (optional).</div>
                </div>
            </div>

            <div class="row g-5 mb-5">
                <!-- Completeness Range -->
                <div class="col-md-6">
                    <label class="form-label fw-bold">Completeness Range (Optional)</label>
                    <div class="row g-3">
                        <div class="col-6">
                            <input type="number" name="min_completeness" class="form-control form-control-solid" placeholder="Min %" min="0" max="100">
                        </div>
                        <div class="col-6">
                            <input type="number" name="max_completeness" class="form-control form-control-solid" placeholder="Max %" min="0" max="100">
                        </div>
                    </div>
                    <div class="form-text">Filter by completeness percentage range (0-100%).</div>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-5">
                <button type="submit" class="btn btn-primary">
                    <i class="ki-duotone ki-filter fs-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    Generate Report
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Help Card -->
<div class="card shadow-sm mt-5">
    <div class="card-header">
        <h3 class="card-title">Help & Information</h3>
        <div class="card-toolbar">
            <button type="button" class="btn btn-sm btn-light" data-bs-toggle="collapse" data-bs-target="#helpContent">
                <i class="ki-duotone ki-information-5 fs-2">
                    <span class="path1"></span>
                    <span class="path2"></span>
                    <span class="path3"></span>
                </i>
                Toggle Help
            </button>
        </div>
    </div>
    <div class="card-body collapse" id="helpContent">
        <div class="alert alert-primary d-flex align-items-center p-5 mb-5">
            <i class="ki-duotone ki-information-5 fs-2hx text-primary me-4">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
            </i>
            <div class="d-flex flex-column">
                <h4 class="mb-1 text-primary">About Cluster Completeness Report</h4>
                <span>This report shows the completeness of indicator reporting by clusters across different timelines. It helps identify which clusters have fully reported their indicators and which ones need attention.</span>
            </div>
        </div>

        <div class="mb-5">
            <h4 class="fw-bold mb-3">How to use this filter:</h4>
            <ol class="list-group list-group-numbered mb-5">
                <li class="list-group-item d-flex align-items-start">
                    <div class="ms-2 me-auto">
                        <div class="fw-bold">Select a Year</div>
                        Choose a year to view completeness data.
                    </div>
                </li>
                <li class="list-group-item d-flex align-items-start">
                    <div class="ms-2 me-auto">
                        <div class="fw-bold">Optional: Filter by Cluster</div>
                        You can narrow down results to specific clusters.
                    </div>
                </li>
                <li class="list-group-item d-flex align-items-start">
                    <div class="ms-2 me-auto">
                        <div class="fw-bold">Optional: Set Completeness Range</div>
                        Filter by completeness percentage (e.g., show only clusters with >50% completeness).
                    </div>
                </li>
                <li class="list-group-item d-flex align-items-start">
                    <div class="ms-2 me-auto">
                        <div class="fw-bold">Generate Report</div>
                        Click the "Generate Report" button to view the results.
                    </div>
                </li>
            </ol>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Select2 dropdowns if available
        if (typeof $.fn.select2 !== 'undefined') {
            $('[data-control="select2"]').select2({
                minimumResultsForSearch: 10
            });
        }

        // Form validation
        const form = document.getElementById('filter-form');
        form.addEventListener('submit', function(e) {
            // Ensure at least one filter is selected (year)
            const year = form.querySelector('[name="year"]').value;

            if (!year) {
                e.preventDefault();
                alert('Please select a Year to generate the report.');
                return false;
            }

            // Validate completeness range if provided
            const minCompleteness = form.querySelector('[name="min_completeness"]').value;
            const maxCompleteness = form.querySelector('[name="max_completeness"]').value;

            if (minCompleteness && maxCompleteness && Number(minCompleteness) > Number(maxCompleteness)) {
                e.preventDefault();
                alert('Minimum completeness cannot be greater than maximum completeness.');
                return false;
            }
        });
    });
</script>
