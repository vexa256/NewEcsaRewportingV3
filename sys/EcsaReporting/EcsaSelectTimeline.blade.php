<div class="d-flex flex-column flex-root">
    <div class="content d-flex flex-column flex-column-fluid">
        <div class="container-xxl">
            <!-- Header Section -->
            <div class="card mb-8 bg-light-primary bg-opacity-50 border-0">
                <div class="card-body py-7">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-5">
                        <div class="d-flex align-items-center gap-5">
                            <div class="symbol symbol-circle symbol-60px overflow-hidden bg-primary">
                                <span class="symbol-label">
                                    <i class="bi bi-file-earmark-text text-white fs-1"></i>
                                </span>
                            </div>
                            <div>
                                <h1 class="fs-1 fw-bolder text-dark mb-1">Available Reports</h1>
                                <p class="text-gray-600 fs-6 mb-0">{{ $Desc }}</p>
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('Ecsa_SelectCluster') }}" class="btn btn-light-primary btn-active-primary">
                                <i class="bi bi-arrow-left fs-4 me-2"></i>
                                Back to Cluster Selection
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="card card-flush shadow-xl mb-10 hover-elevate-up">
                <div class="card-header pt-8">
                    <div class="card-title">
                        <h2 class="fw-bolder fs-2 mb-0">Available Reports</h2>
                    </div>
                    <div class="card-toolbar">
                        <div class="d-flex align-items-center position-relative">
                            <i class="bi bi-search fs-4 position-absolute ms-4"></i>
                            <input type="text" id="reportSearch" class="form-control form-control-solid ps-12" placeholder="Search reports...">
                        </div>
                    </div>
                </div>
                <div class="card-body pt-6">
                    <div class="table-responsive">
                        <table class="table table-row-bordered table-row-dashed gy-4 align-middle" id="reportsTable">
                            <thead>
                                <tr class="fs-7 fw-bolder text-gray-500 border-bottom-0">
                                    <th class="ps-4 min-w-200px">REPORT NAME</th>
                                    <th class="min-w-100px">TYPE</th>
                                    <th class="min-w-150px">CLOSING DATE</th>
                                    <th class="min-w-100px">STATUS</th>
                                    <th class="min-w-100px text-end pe-4">ACTION</th>
                                </tr>
                            </thead>
                            <tbody class="fs-7 fw-semibold">
                                @foreach ($timelines as $timeline)
                                    <tr class="report-row" data-report-name="{{ $timeline->ReportName }}">
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="symbol symbol-40px me-3">
                                                    <span class="symbol-label bg-light-primary">
                                                        <i class="bi bi-file-earmark-text text-primary fs-4"></i>
                                                    </span>
                                                </div>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-bold text-dark">{{ $timeline->ReportName }}</span>
                                                    <span class="text-muted fs-8">ID: {{ $timeline->ReportingID }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $timeline->Type }}</td>
                                        <td>{{ \Carbon\Carbon::parse($timeline->ClosingDate)->format('M d, Y') }}</td>
                                        <td>
                                            @php
                                                $statusClass = 'badge-light-secondary';
                                                if($timeline->status === 'Completed') {
                                                    $statusClass = 'badge-light-success';
                                                } elseif($timeline->status === 'In Progress') {
                                                    $statusClass = 'badge-light-warning';
                                                }
                                            @endphp
                                            <span class="badge fs-7 fw-bold {{ $statusClass }}">{{ $timeline->status }}</span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <form action="{{ route('Ecsa_SelectStrategicObjective') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="UserID" value="{{ $UserID }}">
                                                <input type="hidden" name="ClusterID" value="{{ $ClusterID }}">
                                                <input type="hidden" name="ReportingID" value="{{ $timeline->ReportingID }}">
                                                <button type="submit" class="btn btn-sm btn-primary select-report-btn">
                                                    <span class="indicator-label">Select</span>
                                                    <span class="indicator-progress">
                                                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                                    </span>
                                                </button>
                                            </form>
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
</div>

<style>
    /* Custom styles to enhance the premium look */
    .hover-elevate-up {
        transition: box-shadow 0.3s ease, transform 0.3s ease;
    }

    .hover-elevate-up:hover {
        box-shadow: 0 0.5rem 1.5rem 0.5rem rgba(0, 0, 0, 0.08);
    }

    .report-row {
        transition: background-color 0.3s ease, transform 0.2s ease;
        cursor: pointer;
    }

    .report-row:hover {
        background-color: #f9f9f9;
    }

    .report-row.selected {
        background-color: #f1faff;
        transform: scale(1.01);
    }

    .badge-light-success {
        color: #50cd89;
        background-color: #e8fff3;
    }

    .badge-light-warning {
        color: #ffc700;
        background-color: #fff8dd;
    }

    .badge-light-secondary {
        color: #a1a5b7;
        background-color: #f9f9f9;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Table row hover effect
        const rows = document.querySelectorAll('.report-row');
        rows.forEach(row => {
            row.addEventListener('mouseenter', function() {
                this.classList.add('selected');
            });

            row.addEventListener('mouseleave', function() {
                this.classList.remove('selected');
            });

            // Add haptic feedback for mobile devices
            row.addEventListener('click', function(e) {
                if (!e.target.closest('button') && !e.target.closest('a')) {
                    const selectBtn = this.querySelector('.select-report-btn');
                    if (selectBtn) {
                        selectBtn.click();
                    }
                }

                if ('vibrate' in navigator) {
                    navigator.vibrate(50);
                }
            });
        });

        // Button loading state
        // const buttons = document.querySelectorAll('.select-report-btn');
        // buttons.forEach(button => {
        //     button.addEventListener('click', function() {
        //         this.setAttribute('data-kt-indicator', 'on');
        //         this.disabled = true;
        //     });
        // });

        // Search functionality
        const searchInput = document.getElementById('reportSearch');
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = document.querySelectorAll('.report-row');

                rows.forEach(row => {
                    const reportName = row.getAttribute('data-report-name').toLowerCase();
                    if (reportName.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        }

        // Initialize tooltips
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl, {
                    container: 'body',
                    boundary: 'window'
                });
            });
        }
    });
</script>
