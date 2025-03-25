@php
    $isAdmin = auth()->check() && auth()->user()->AccountRole === 'Admin';
@endphp

<!--begin::Indicators management-->
<div class="container-fluid px-4 py-5">
    <!--begin::Header-->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-5 gap-4">
        <!--begin::Premium Search-->
        <div class="position-relative w-100 w-md-350px">
            <div class="input-group input-group-solid">
                <span class="input-group-text">
                    <i class="ki-duotone ki-magnifier fs-4"></i>
                </span>
                <input type="text" id="premium-search" class="form-control form-control-solid" placeholder="Search indicators..."/>
            </div>
            <div id="search-filters" class="position-absolute start-0 end-0 top-100 z-index-3 d-none bg-white rounded-bottom shadow-sm px-3 py-3 border border-top-0">
                <div class="d-flex flex-wrap gap-2 mb-2">
                    <span class="badge badge-primary search-filter cursor-pointer" data-column="all">All</span>
                    <span class="badge badge-light-primary search-filter cursor-pointer" data-column="number">Number</span>
                    <span class="badge badge-light-primary search-filter cursor-pointer" data-column="name">Name</span>
                    <span class="badge badge-light-primary search-filter cursor-pointer" data-column="type">Response Type</span>
                    <span class="badge badge-light-primary search-filter cursor-pointer" data-column="cluster">Cluster</span>
                </div>
            </div>
        </div>

        <!--begin::Actions-->
        @if ($isAdmin)
            <div class="d-flex">
                <button type="button" class="btn btn-primary d-none d-md-inline-flex" data-bs-toggle="modal" data-bs-target="#addIndicatorModal">
                    <i class="ki-duotone ki-plus fs-2 me-1"></i>
                    Add New Indicator
                </button>
                <button type="button" class="btn btn-primary btn-icon d-md-none" data-bs-toggle="modal" data-bs-target="#addIndicatorModal" aria-label="Add New Indicator">
                    <i class="ki-duotone ki-plus fs-2"></i>
                </button>
            </div>
        @endif
        <!--end::Actions-->
    </div>
    <!--end::Header-->

    <!--begin::Card-->
    <div class="card shadow-sm">
        <div class="card-body">
            <!--begin::Table-->
            <div class="table-responsive">
                <table class="table table-rounded table-row-bordered table-row-dashed table-striped gy-5 gs-7" id="indicators-table">
                    <thead>
                        <tr class="fw-bold fs-6 text-gray-800 border-bottom border-gray-200">
                            <th class="cursor-pointer min-w-100px" data-sort="number">
                                Number <i class="ki-duotone ki-arrow-up-down fs-7 ms-1 sort-icon"></i>
                            </th>
                            <th class="cursor-pointer min-w-200px" data-sort="name">
                                Indicator <i class="ki-duotone ki-arrow-up-down fs-7 ms-1 sort-icon"></i>
                            </th>
                            <th class="cursor-pointer min-w-125px" data-sort="type">
                                Response Type <i class="ki-duotone ki-arrow-up-down fs-7 ms-1 sort-icon"></i>
                            </th>
                            <th class="cursor-pointer min-w-150px" data-sort="cluster">
                                Cluster(s) <i class="ki-duotone ki-arrow-up-down fs-7 ms-1 sort-icon"></i>
                            </th>
                            @if ($isAdmin)
                                <th class="text-end min-w-100px">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($indicators as $indicator)
                            <tr class="indicator-row">
                                <td>{{ $indicator->Indicator_Number }}</td>
                                <td>{{ $indicator->Indicator_Name }}</td>
                                <td>{{ $indicator->ResponseType }}</td>
                                <td>{{ $indicator->Responsible_Cluster }}</td>
                                @if ($isAdmin)
                                    <td class="text-end">
                                        <a href="#" class="btn btn-icon btn-light-primary btn-sm me-1" data-bs-toggle="modal" data-bs-target="#editIndicatorModal-{{ $indicator->id }}">
                                            <i class="ki-duotone ki-pencil fs-2">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                        </a>
                                        <form id="delete-form-{{ $indicator->id }}" action="{{ route('DeleteEcsahcIndicators') }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="id" value="{{ $indicator->id }}">
                                            <button type="button" class="btn btn-icon btn-light-danger btn-sm" onclick="confirmDelete('{{ $indicator->id }}')">
                                                <i class="ki-duotone ki-trash fs-2">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                    <span class="path3"></span>
                                                    <span class="path4"></span>
                                                    <span class="path5"></span>
                                                </i>
                                            </button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr id="no-results-row" class="d-none">
                                <td colspan="{{ $isAdmin ? 5 : 4 }}" class="text-center py-10">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="ki-duotone ki-file-search fs-3x text-gray-400 mb-5">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        <p class="fw-semibold fs-6 mb-3">No indicators found matching your search</p>
                                        <button id="reset-search" class="btn btn-sm btn-light-primary">Reset Search</button>
                                    </div>
                                </td>
                            </tr>
                            <tr id="empty-row" class="{{ count($indicators) > 0 ? 'd-none' : '' }}">
                                <td colspan="{{ $isAdmin ? 5 : 4 }}" class="text-center py-10">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="ki-duotone ki-clipboard-text fs-3x text-gray-400 mb-5">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        <p class="fw-semibold fs-6 mb-3">No indicators available</p>
                                        @if ($isAdmin)
                                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addIndicatorModal">
                                                Add Your First Indicator
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <!--end::Table-->

            <!--begin::Search Summary-->
            <div id="search-summary" class="d-none text-end mt-5">
                <span class="badge badge-light-primary p-2">
                    <span id="results-count"></span> results found
                    <button id="clear-search" class="btn btn-icon btn-sm btn-active-light-primary ms-2 btn-clear-search">
                        <i class="ki-duotone ki-cross fs-2"></i>
                    </button>
                </span>
            </div>
            <!--end::Search Summary-->
        </div>
    </div>
    <!--end::Card-->
</div>
<!--end::Indicators management-->

@if ($isAdmin)
    <!--begin::Add Indicator Modal-->
    <div class="modal fade" id="addIndicatorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Add New Indicator ({{ $strategicObjectives->SO_Name }})</h5>
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                </div>
                <form action="{{ route('AddEcsahcIndicators') }}" method="POST" id="addIndicatorForm">
                    <div class="modal-body py-10">
                        @csrf
                        <input type="hidden" name="StrategicObjectiveID" value="{{ $StrategicObjectiveID }}">
                        <input type="hidden" name="IndicatorID"
                            value="{{ md5(md5(uniqid() . date('now') . $StrategicObjectiveID)) }}">

                        <div class="row g-5">
                            <div class="col-md-4">
                                <div class="form-floating mb-7">
                                    <input type="text" class="form-control" id="Indicator_Number" name="Indicator_Number" placeholder="Enter indicator number" required>
                                    <label for="Indicator_Number">Indicator Number</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating mb-7">
                                    <input type="text" class="form-control" id="Indicator_Name" name="Indicator_Name" placeholder="Enter indicator name" required>
                                    <label for="Indicator_Name">Indicator Name</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating mb-7">
                                    <select class="form-select" id="ResponseType" name="ResponseType" required>
                                        <option value="Number">Number</option>
                                        <option value="Text">Text</option>
                                        <option value="Boolean">Boolean</option>
                                        <option value="Yes/No">Yes/No</option>
                                    </select>
                                    <label for="ResponseType">Response Type</label>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-bold mb-3">Responsible Cluster(s)</label>
                                <select name="Responsible_Cluster[]" class="form-select tomselect-multiple" id="select-states" multiple>
                                    @foreach ($clusters as $cluster)
                                        <option value="{{ $cluster->ClusterID }}">{{ $cluster->Cluster_Name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="ki-duotone ki-disk fs-2 me-1"><span class="path1"></span><span class="path2"></span></i>
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!--end::Add Indicator Modal-->

    <!--begin::Edit Indicator Modals-->
    @foreach ($indicators as $indicator)
        <div class="modal fade" id="editIndicatorModal-{{ $indicator->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Edit Indicator</h5>
                        <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                            <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                        </div>
                    </div>
                    <form action="{{ route('UpdateEcsahcIndicators') }}" method="POST" id="editIndicatorForm-{{ $indicator->id }}">
                        <div class="modal-body py-10">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="id" value="{{ $indicator->id }}">
                            <input type="hidden" name="StrategicObjectiveID" value="{{ $StrategicObjectiveID }}">

                            <div class="row g-5">
                                <div class="col-md-4">
                                    <div class="form-floating mb-7">
                                        <input type="text" class="form-control" id="Indicator_Number-{{ $indicator->id }}" name="Indicator_Number" value="{{ $indicator->Indicator_Number }}" placeholder="Enter indicator number" required>
                                        <label for="Indicator_Number-{{ $indicator->id }}">Indicator Number</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating mb-7">
                                        <input type="text" class="form-control" id="Indicator_Name-{{ $indicator->id }}" name="Indicator_Name" value="{{ $indicator->Indicator_Name }}" placeholder="Enter indicator name" required>
                                        <label for="Indicator_Name-{{ $indicator->id }}">Indicator Name</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating mb-7">
                                        <select class="form-select" id="ResponseType-{{ $indicator->id }}" name="ResponseType" required>
                                            <option value="Number" @if ($indicator->ResponseType === 'Number') selected @endif>Number</option>
                                            <option value="Text" @if ($indicator->ResponseType === 'Text') selected @endif>Text</option>
                                            <option value="Boolean" @if ($indicator->ResponseType === 'Boolean') selected @endif>Boolean</option>
                                            <option value="Yes/No" @if ($indicator->ResponseType === 'Yes/No') selected @endif>Yes/No</option>
                                        </select>
                                        <label for="ResponseType-{{ $indicator->id }}">Response Type</label>
                                    </div>
                                </div>
                                @php
                                    // Decode the clusters stored in the database
                                    $existingClusters = json_decode($indicator->Responsible_Cluster, true) ?? [];
                                @endphp
                                <div class="col-md-8">
                                    <label class="form-label fw-bold mb-3">Responsible Cluster(s)</label>
                                    <select name="Responsible_Cluster[]" class="form-select tomselect-multiple"
                                        id="select-states-{{ $indicator->id }}" multiple>
                                        @foreach ($clusters as $cluster)
                                            <option value="{{ $cluster->ClusterID }}"
                                                @if (in_array($cluster->ClusterID, $existingClusters)) selected @endif>
                                                {{ $cluster->Cluster_Name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="ki-duotone ki-disk fs-2 me-1"><span class="path1"></span><span class="path2"></span></i>
                                Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
    <!--end::Edit Indicator Modals-->
@endif

<!--begin::Page Vendors-->
<!--begin::TomSelect-->
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/js/tom-select.complete.min.js"></script>
<!--end::TomSelect-->
<!--end::Page Vendors-->

<!--begin::Custom Styles-->
<style>
    /* Enhanced Dropdown Styling */
    .ts-dropdown {
        z-index: 99999 !important;
        box-shadow: 0px 0px 50px 0px rgba(82, 63, 105, 0.15);
        border: 0;
        padding: 0.75rem 0;
        border-radius: 0.475rem;
    }

    .ts-dropdown .optgroup-header {
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        font-size: 0.95rem;
        color: var(--kt-gray-700);
    }

    .ts-dropdown .option {
        padding: 0.75rem 1.5rem;
        color: var(--kt-gray-700);
    }

    .ts-dropdown .active {
        background-color: var(--kt-primary-light);
        color: var(--kt-primary);
    }

    .ts-control {
        border-radius: 0.475rem;
        border: 1px solid var(--kt-gray-300);
        padding: 0.55rem 1rem;
        min-height: calc(1.5em + 1.5rem + 2px);
    }

    .ts-control:focus {
        border-color: var(--kt-primary);
        box-shadow: 0 0 0 0.25rem rgba(var(--kt-primary-rgb), 0.25);
    }

    .ts-control .item {
        background-color: var(--kt-primary-light);
        color: var(--kt-primary);
        border-radius: 0.475rem;
        margin: 0.125rem;
        padding: 0.25rem 0.5rem;
    }

    /* Premium Search Animation */
    .search-animation {
        animation: fadeIn 0.3s ease-in-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-5px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Highlight text in search results */
    .highlight {
        background-color: rgba(var(--kt-primary-rgb), 0.2);
        border-radius: 0.25rem;
        padding: 0 2px;
    }

    /* Hover effect on table rows */
    #indicators-table tbody tr:hover {
        transition: all 0.3s ease;
        box-shadow: 0px 10px 30px 0px rgba(82, 63, 105, 0.08);
        transform: translateY(-2px);
    }

    /* Sort icons */
    .sort-icon {
        opacity: 0.5;
        transition: all 0.3s ease;
    }

    .sort-active {
        opacity: 1;
        color: var(--kt-primary);
    }

    /* Cursor styling */
    .cursor-pointer {
        cursor: pointer;
    }

    /* Button clear search custom styling */
    .btn-clear-search:hover {
        background-color: var(--kt-danger-light);
        color: var(--kt-danger);
    }

    /* Enhanced filter badges */
    .badge {
        transition: all 0.3s ease;
    }

    .badge:hover {
        transform: translateY(-1px);
    }
</style>
<!--end::Custom Styles-->

<!--begin::Page Scripts-->
<script>
    // Delete confirmation with SweetAlert2
    function confirmDelete(indicatorId) {
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
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + indicatorId).submit();
            }
        });
    }

    // Document ready function
    document.addEventListener("DOMContentLoaded", function() {
        // Initialize TomSelect for multiple select inputs
        if (window.TomSelect) {
            document.querySelectorAll(".tomselect-multiple").forEach(function(selectEl) {
                const tomInstance = new TomSelect(selectEl, {
                    plugins: ['remove_button', 'clear_button'],
                    maxItems: null,
                    valueField: 'value',
                    labelField: 'text',
                    searchField: 'text',
                    create: false,
                    persist: false,
                    createOnBlur: true,
                    closeAfterSelect: false,

                    // Enhanced rendering
                    render: {
                        // Premium item display
                        item: function(data, escape) {
                            return `<div class="d-flex align-items-center gap-1 py-1 px-2 rounded" style="background-color: var(--kt-primary-light); color: var(--kt-primary);">
                                <span>${escape(data.text)}</span>
                            </div>`;
                        },

                        // Premium option display
                        option: function(data, escape) {
                            return `<div class="d-flex align-items-center gap-2 p-3 border-hover border-transparent rounded-2 cursor-pointer">
                                <div class="d-flex flex-column flex-grow-1">
                                    <span class="fw-semibold fs-7">${escape(data.text)}</span>
                                    ${data.description ? `<span class="text-gray-600 fs-8">${escape(data.description)}</span>` : ''}
                                </div>
                            </div>`;
                        },

                        // No results message
                        no_results: function(data, escape) {
                            return `<div class="py-6 d-flex flex-column align-items-center">
                                <i class="ki-duotone ki-information-5 fs-5x text-gray-400 mb-5">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                                <span class="fs-6 text-gray-500">No results found</span>
                            </div>`;
                        }
                    },

                    // Premium dropdown position
                    dropdownParent: 'body',
                    controlInput: '<input>',

                    // Performance enhancements
                    loadThrottle: 300
                });

                // Add premium animation effects
                tomInstance.on('dropdown_open', function($dropdown) {
                    $dropdown.style.display = 'none';
                    setTimeout(function() {
                        $dropdown.style.display = 'block';
                        $dropdown.style.animation = 'fadeInUp 0.3s ease';
                    }, 10);
                });

                // Store instance for reference
                selectEl.tomInstance = tomInstance;
            });
        }

        // Initialize Premium Search
        initPremiumSearch();
    });

    // Premium Search Functionality with enhanced UX
    function initPremiumSearch() {
        const searchInput = document.getElementById('premium-search');
        const searchFilters = document.getElementById('search-filters');
        const filterButtons = document.querySelectorAll('.search-filter');
        const table = document.getElementById('indicators-table');

        // Exit if required elements don't exist
        if (!searchInput || !table) {
            console.log('Premium search elements not found');
            return;
        }

        const rows = table.querySelectorAll('tbody tr.indicator-row');
        const noResultsRow = document.getElementById('no-results-row');
        const emptyRow = document.getElementById('empty-row');
        const searchSummary = document.getElementById('search-summary');
        const resultsCount = document.getElementById('results-count');
        const clearSearchBtn = document.getElementById('clear-search');
        const resetSearchBtn = document.getElementById('reset-search');
        const sortHeaders = document.querySelectorAll('th[data-sort]');

        let activeFilter = 'all';
        let currentSort = { column: null, direction: 'asc' };
        let debounceTimer;

        // Toggle search filters
        if (searchInput && searchFilters) {
            searchInput.addEventListener('focus', () => {
                searchFilters.classList.remove('d-none');
                // Add animation for filter dropdown
                searchFilters.style.animation = 'fadeIn 0.2s ease';
            });

            document.addEventListener('click', (e) => {
                if (!searchInput.contains(e.target) && !searchFilters.contains(e.target)) {
                    searchFilters.classList.add('d-none');
                }
            });
        }

        // Set active filter with premium animation
        if (filterButtons.length > 0) {
            filterButtons.forEach(btn => {
                btn.addEventListener('click', () => {
                    filterButtons.forEach(b => b.classList.remove('badge-primary'));
                    filterButtons.forEach(b => b.classList.add('badge-light-primary'));
                    btn.classList.remove('badge-light-primary');
                    btn.classList.add('badge-primary');
                    // Add transition effect
                    btn.style.transform = 'scale(1.05)';
                    setTimeout(() => {
                        btn.style.transform = '';
                    }, 200);

                    activeFilter = btn.dataset.column;
                    performSearch(searchInput.value);
                });
            });
        }

        // Search with debounce for performance
        if (searchInput) {
            searchInput.addEventListener('input', () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    performSearch(searchInput.value);
                }, 300);
            });
        }

        // Clear search with animation
        if (clearSearchBtn) {
            clearSearchBtn.addEventListener('click', () => {
                if (searchInput) {
                    searchInput.value = '';
                    performSearch('');
                    // Add animation effect
                    searchSummary.style.animation = 'fadeOut 0.3s ease';
                    setTimeout(() => {
                        searchSummary.classList.add('d-none');
                        searchSummary.style.animation = '';
                    }, 280);
                }
            });
        }

        // Reset search from no results
        if (resetSearchBtn) {
            resetSearchBtn.addEventListener('click', () => {
                if (searchInput) {
                    searchInput.value = '';
                    performSearch('');
                }
            });
        }

        // Enhanced sorting with visual feedback
        if (sortHeaders.length > 0) {
            sortHeaders.forEach(header => {
                header.addEventListener('click', () => {
                    const column = header.dataset.sort;

                    // Update sort direction
                    if (currentSort.column === column) {
                        currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
                    } else {
                        currentSort.column = column;
                        currentSort.direction = 'asc';
                    }

                    // Update sort icons with animation
                    sortHeaders.forEach(h => {
                        const icon = h.querySelector('.sort-icon');
                        if (icon) {
                            icon.classList.remove('sort-active');
                            icon.classList.remove('ki-arrow-up', 'ki-arrow-down');
                            icon.classList.add('ki-arrow-up-down');

                            if (h.dataset.sort === currentSort.column) {
                                icon.classList.add('sort-active');
                                // Add animation effect
                                icon.style.animation = 'pulse 0.3s ease';
                                setTimeout(() => {
                                    icon.style.animation = '';
                                }, 300);

                                if (currentSort.direction === 'asc') {
                                    icon.classList.remove('ki-arrow-up-down');
                                    icon.classList.add('ki-arrow-up');
                                } else {
                                    icon.classList.remove('ki-arrow-up-down');
                                    icon.classList.add('ki-arrow-down');
                                }
                            }
                        }
                    });

                    sortTable();
                });
            });
        }

        // Enhanced search function with highlighting
        function performSearch(query) {
            if (!rows.length) return;

            query = query.trim().toLowerCase();
            let matchCount = 0;

            // Clear previous highlights
            table.querySelectorAll('.highlight').forEach(el => {
                el.outerHTML = el.innerHTML;
            });

            // Process each row
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                let match = false;

                if (query === '') {
                    match = true;
                } else {
                    if (activeFilter === 'all') {
                        // Search all columns
                        for (let i = 0; i < cells.length - 1; i++) {
                            const cellText = cells[i].textContent.toLowerCase();
                            if (cellText.includes(query)) {
                                match = true;
                                highlightText(cells[i], query);
                            }
                        }
                    } else {
                        // Search specific column
                        let columnIndex;
                        switch (activeFilter) {
                            case 'number': columnIndex = 0; break;
                            case 'name': columnIndex = 1; break;
                            case 'type': columnIndex = 2; break;
                            case 'cluster': columnIndex = 3; break;
                            default: columnIndex = 0;
                        }

                        if (cells[columnIndex]) {
                            const cellText = cells[columnIndex].textContent.toLowerCase();
                            if (cellText.includes(query)) {
                                match = true;
                                highlightText(cells[columnIndex], query);
                            }
                        }
                    }
                }

                // Show/hide rows with animation
                if (match) {
                    row.classList.remove('d-none');
                    row.classList.add('search-animation');
                    matchCount++;
                } else {
                    row.classList.add('d-none');
                    row.classList.remove('search-animation');
                }
            });

            // Show/hide no results message
            if (noResultsRow) {
                if (matchCount === 0 && rows.length > 0) {
                    noResultsRow.classList.remove('d-none');
                    if (emptyRow) emptyRow.classList.add('d-none');
                } else {
                    noResultsRow.classList.add('d-none');
                    if (emptyRow && rows.length === 0) {
                        emptyRow.classList.remove('d-none');
                    }
                }
            }

            // Update search summary with animation
            if (searchSummary && resultsCount) {
                if (query !== '') {
                    searchSummary.classList.remove('d-none');
                    resultsCount.textContent = matchCount;
                    searchSummary.style.animation = 'fadeIn 0.3s ease';
                } else {
                    searchSummary.classList.add('d-none');
                }
            }

            // Re-apply the current sort
            if (currentSort.column) {
                sortTable();
            }
        }

        // Enhanced text highlighting for search results
        function highlightText(cell, query) {
            if (!cell) return;

            const content = cell.innerHTML;
            const regex = new RegExp(`(${escapeRegExp(query)})`, 'gi');
            cell.innerHTML = content.replace(regex, '<span class="highlight">$1</span>');
        }

        // Enhanced sorting with animation
        function sortTable() {
            if (!table) return;

            const tbody = table.querySelector('tbody');
            if (!tbody) return;

            const visibleRows = Array.from(rows).filter(row => !row.classList.contains('d-none'));

            visibleRows.sort((a, b) => {
                const columnIndex = getColumnIndex(currentSort.column);

                if (!a.cells[columnIndex] || !b.cells[columnIndex]) return 0;

                const aValue = a.cells[columnIndex].textContent.trim();
                const bValue = b.cells[columnIndex].textContent.trim();

                // Check if values are numbers
                const aNum = parseFloat(aValue);
                const bNum = parseFloat(bValue);

                let comparison;
                if (!isNaN(aNum) && !isNaN(bNum)) {
                    comparison = aNum - bNum;
                } else {
                    comparison = aValue.localeCompare(bValue);
                }

                return currentSort.direction === 'asc' ? comparison : -comparison;
            });

            // Apply animation effect before reordering
            visibleRows.forEach(row => {
                row.style.transition = 'opacity 0.2s ease';
                row.style.opacity = '0.5';
            });

            // Delay reordering to allow animation
            setTimeout(() => {
                // Reorder rows
                visibleRows.forEach(row => {
                    tbody.appendChild(row);
                    // Fade back in
                    setTimeout(() => {
                        row.style.opacity = '1';
                    }, 50);
                });

                // Keep special rows at the end
                if (noResultsRow) tbody.appendChild(noResultsRow);
                if (emptyRow) tbody.appendChild(emptyRow);
            }, 200);
        }

        // Helper function to get column index from sort key
        function getColumnIndex(column) {
            switch (column) {
                case 'number': return 0;
                case 'name': return 1;
                case 'type': return 2;
                case 'cluster': return 3;
                default: return 0;
            }
        }

        // Helper function to escape special characters in regex
        function escapeRegExp(string) {
            return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }
    }

    // Add animation keyframes
    document.head.insertAdjacentHTML('beforeend', `
        <style>
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(-5px); }
                to { opacity: 1; transform: translateY(0); }
            }

            @keyframes fadeOut {
                from { opacity: 1; transform: translateY(0); }
                to { opacity: 0; transform: translateY(5px); }
            }

            @keyframes fadeInUp {
                from { opacity: 0; transform: translateY(10px); }
                to { opacity: 1; transform: translateY(0); }
            }

            @keyframes pulse {
                0% { transform: scale(1); }
                50% { transform: scale(1.2); }
                100% { transform: scale(1); }
            }
        </style>
    `);
</script>
<!--end::Page Scripts-->
