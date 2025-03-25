<!--begin::Timelines View-->
<div class="container-fluid px-5 py-10">
    <!--begin::Header-->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5 gap-4">
        <!--begin::Search-->
        <div class="position-relative d-flex align-items-center w-md-300px">
            <span class="position-absolute ms-4">
                <i class="ki-duotone ki-magnifier fs-3 text-gray-500">
                    <span class="path1"></span>
                    <span class="path2"></span>
                </i>
            </span>
            <input type="text" id="searchInput" class="form-control form-control-solid ps-13" placeholder="Search Timelines..."/>
        </div>
        <!--end::Search-->
    </div>
    <!--end::Header-->

    <!--begin::Timeline Cards-->
    <div class="row g-5" id="timelineCards">
        @forelse ($timelines as $timeline)
            <div class="col-12 col-md-6 col-lg-4">
                <!--begin::Timeline Card-->
                <div class="card card-custom card-stretch-half shadow-sm hover-elevate-up overflow-hidden h-100">
                    <!--begin::Card Body-->
                    <div class="card-body p-9">
                        <!--begin::Header-->
                        <div class="mb-5">
                            <h3 class="card-title fw-bold text-dark mb-0 fs-4">
                                {{ $timeline->ReportName }}
                            </h3>
                        </div>
                        <!--end::Header-->

                        <!--begin::Info-->
                        <div class="row g-4 mb-7">
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <div class="d-flex align-items-center">
                                        <i class="ki-duotone ki-document fs-3 text-gray-500 me-2">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        <div class="fs-7 fw-semibold text-gray-600">Type</div>
                                    </div>
                                    <div class="ms-auto fw-bold fs-7">{{ $timeline->Type }}</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <div class="d-flex align-items-center">
                                        <i class="ki-duotone ki-calendar fs-3 text-gray-500 me-2">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        <div class="fs-7 fw-semibold text-gray-600">Year</div>
                                    </div>
                                    <div class="ms-auto fw-bold fs-7">{{ $timeline->Year }}</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <div class="d-flex align-items-center">
                                        <i class="ki-duotone ki-flag fs-3 text-gray-500 me-2">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        <div class="fs-7 fw-semibold text-gray-600">Status</div>
                                    </div>
                                    <div class="ms-auto">
                                        <span class="badge fs-8 fw-semibold
                                            @if ($timeline->status == 'Completed')
                                                badge-light-success
                                            @elseif($timeline->status == 'In Progress')
                                                badge-light-warning
                                            @else
                                                badge-light-danger
                                            @endif">
                                            {{ $timeline->status }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <div class="d-flex align-items-center">
                                        <i class="ki-duotone ki-check-circle fs-3 text-gray-500 me-2">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        <div class="fs-7 fw-semibold text-gray-600">Last Bi-Annual</div>
                                    </div>
                                    <div class="ms-auto fw-bold fs-7">
                                        @if ($timeline->Type === 'Bi-Annual')
                                            {{ $timeline->LastBiAnnual ? 'Yes' : 'No' }}
                                        @else
                                            -
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end::Info-->

                        <!--begin::Actions-->
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-light-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editTimelineModal-{{ $timeline->id }}">
                                <i class="ki-duotone ki-pencil fs-6 me-1">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                Edit
                            </button>
                            <button type="button" class="btn btn-light-danger btn-sm" onclick="confirmDelete({{ $timeline->id }})">
                                <i class="ki-duotone ki-trash fs-6 me-1">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                    <span class="path4"></span>
                                    <span class="path5"></span>
                                </i>
                                Delete
                            </button>
                        </div>
                        <!--end::Actions-->
                    </div>
                    <!--end::Card Body-->
                </div>
                <!--end::Timeline Card-->
            </div>
        @empty
            <div class="col-12">
                <div class="card card-dashed card-px-0 flex-center min-h-175px">
                    <div class="card-body d-flex flex-column justify-content-center text-center">
                        <i class="ki-duotone ki-calendar-8 fs-5tx text-gray-300 mb-5">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                            <span class="path4"></span>
                            <span class="path5"></span>
                            <span class="path6"></span>
                        </i>
                        <span class="fs-3 fw-semibold text-gray-600">No timelines found</span>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
    <!--end::Timeline Cards-->
</div>
<!--end::Timelines View-->

<!--begin::Edit Timeline Modals-->
@foreach ($timelines as $timeline)
    <div class="modal fade" id="editTimelineModal-{{ $timeline->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title fw-bold">Edit Reporting Status: {{ $timeline->ReportName }}</h3>
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                </div>

                <form action="{{ route('MassUpdate', $timeline->id) }}" method="POST" id="editTimelineForm-{{ $timeline->id }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="TableName" value="ecsahc_timelines">
                    <input type="hidden" name="id" value="{{ $timeline->id }}">

                    <div class="modal-body py-10">
                        <div class="mb-5">
                            <label class="form-label fw-semibold fs-6" for="status-{{ $timeline->id }}">Status</label>
                            <select id="status-{{ $timeline->id }}" name="status" class="form-select form-select-solid" required>
                                <option value="Pending" @if ($timeline->status === 'Pending') selected @endif>Pending</option>
                                <option value="In Progress" @if ($timeline->status === 'In Progress') selected @endif>In Progress</option>
                                <option value="Completed" @if ($timeline->status === 'Completed') selected @endif>Completed</option>
                            </select>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            <i class="ki-duotone ki-cross-circle fs-2 me-1">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="ki-duotone ki-check-circle fs-2 me-1">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach
<!--end::Edit Timeline Modals-->

<!--begin::Delete Timeline Forms-->
@foreach ($timelines as $timeline)
    <form id="delete-form-{{ $timeline->id }}" action="{{ route('MassDelete', $timeline->id) }}" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
@endforeach
<!--end::Delete Timeline Forms-->

<!--begin::Page Styles-->
<style>
    /* Premium card hover effect */
    .hover-elevate-up {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .hover-elevate-up:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1.5rem 0.5rem rgba(0, 0, 0, 0.08) !important;
    }

    /* Status badge animations */
    .badge {
        transition: all 0.3s ease;
    }

    .badge:hover {
        transform: scale(1.05);
    }

    /* Search input animation */
    #searchInput {
        transition: all 0.3s ease;
    }

    #searchInput:focus {
        border-color: var(--kt-primary);
        box-shadow: 0 0 0 0.25rem rgba(var(--kt-primary-rgb), 0.25);
    }

    /* Card content fade-in animation */
    .card-custom {
        animation: fadeIn 0.6s ease-in-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Button hover effects */
    .btn {
        transition: all 0.3s ease;
    }

    .btn:hover {
        transform: translateY(-2px);
    }

    /* Modal animation enhancements */
    .modal.fade .modal-dialog {
        transition: transform 0.3s ease-out;
        transform: scale(0.95);
    }

    .modal.show .modal-dialog {
        transform: scale(1);
    }
</style>
<!--end::Page Styles-->

<!--begin::Page Scripts-->
<script>
    // SweetAlert2 Delete Confirmation with premium styling
    function confirmDelete(timelineId) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'No, cancel',
            buttonsStyling: false,
            customClass: {
                confirmButton: "btn btn-danger",
                cancelButton: "btn btn-light-primary me-3"
            },
            heightAuto: false,
            reverseButtons: true,
            padding: '2rem'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + timelineId).submit();
            }
        });
    }

    // Enhanced search functionality with animations
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const cardSelector = '#timelineCards > div';

        if (searchInput) {
            // Add focus animation
            searchInput.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });

            searchInput.addEventListener('blur', function() {
                this.parentElement.classList.remove('focused');
            });

            // Enhanced search with debounce and animations
            let debounceTimer;

            searchInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);

                debounceTimer = setTimeout(() => {
                    const query = this.value.toLowerCase();
                    const cards = document.querySelectorAll(cardSelector);
                    let hasResults = false;

                    cards.forEach((card) => {
                        const cardText = card.textContent.toLowerCase();
                        const matches = cardText.includes(query);

                        // Apply animations
                        if (matches) {
                            hasResults = true;
                            card.style.display = '';
                            card.querySelector('.card').style.animation = 'fadeIn 0.5s ease forwards';
                        } else {
                            card.style.display = 'none';
                        }
                    });

                    // Show no results message if needed
                    const noResultsEl = document.querySelector('.no-results-message');

                    if (!hasResults && query !== '' && !noResultsEl) {
                        const noResultsMsg = document.createElement('div');
                        noResultsMsg.className = 'col-12 no-results-message';
                        noResultsMsg.innerHTML = `
                            <div class="card card-dashed card-px-0 flex-center min-h-175px">
                                <div class="card-body d-flex flex-column justify-content-center text-center">
                                    <i class="ki-duotone ki-search fs-5tx text-gray-300 mb-5">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    <span class="fs-3 fw-semibold text-gray-600">No results found for "${query}"</span>
                                    <button class="btn btn-light-primary btn-sm mt-5 reset-search">Clear Search</button>
                                </div>
                            </div>
                        `;
                        document.getElementById('timelineCards').appendChild(noResultsMsg);

                        // Add event listener to reset button
                        noResultsMsg.querySelector('.reset-search').addEventListener('click', function() {
                            searchInput.value = '';
                            searchInput.dispatchEvent(new Event('input'));
                        });
                    } else if ((hasResults || query === '') && noResultsEl) {
                        noResultsEl.remove();
                    }
                }, 300);
            });
        }

        // Add animation to modals
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            modal.addEventListener('show.bs.modal', function() {
                setTimeout(() => {
                    const modalContent = this.querySelector('.modal-content');
                    modalContent.style.animation = 'fadeIn 0.3s ease forwards';
                }, 50);
            });
        });
    });
</script>
<!--end::Page Scripts-->
