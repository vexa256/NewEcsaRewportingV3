<!--begin::Strategic Objectives-->
<div class="container-fluid px-5 py-10">
    <!--begin::Header-->
    <div class="card card-flush mb-9">
        <div class="card-body py-7 px-8">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-5">
                <!--begin::Title-->
                <h1 class="fs-2x fw-bolder mb-0 text-dark">
                    <span class="position-relative">
                        Strategic Objectives
                        <span class="position-absolute opacity-15 bottom-0 start-0 border-4 border-primary border-bottom w-100"></span>
                    </span>
                </h1>
                <!--end::Title-->

                <!--begin::Actions-->
                <div class="d-flex align-items-center gap-3">
                    <!--begin::Search-->
                    <div class="position-relative w-md-250px">
                        <span class="position-absolute top-50 translate-middle-y ms-4">
                            <i class="ki-duotone ki-magnifier fs-3 text-gray-500">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                        </span>
                        <input type="text" id="search-input" class="form-control form-control-solid ps-13" placeholder="Search objectives..."/>
                    </div>
                    <!--end::Search-->

                    <!--begin::Add button-->
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add_strategic_objective_modal">
                        <i class="ki-duotone ki-plus fs-2 me-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        Add New Objective
                    </button>
                    <!--end::Add button-->
                </div>
                <!--end::Actions-->
            </div>
        </div>
    </div>
    <!--end::Header-->

    <!--begin::Objectives Grid-->
    <div class="row g-5" id="objectives-grid">
        @forelse ($strategicObjectives as $objective)
            <div class="col-12 col-md-6 col-lg-4 objective-card">
                <!--begin::Card-->
                <div class="card card-custom h-100 hover-elevate-up">
                    <!--begin::Card Body-->
                    <div class="card-body d-flex flex-column p-9">
                        <!--begin::Title-->
                        <div class="d-flex align-items-center mb-5">
                            <span class="badge badge-light-primary fs-7 fw-bold me-2">SO</span>
                            <h3 class="card-title fw-bold text-dark fs-3 mb-0">{{ $objective->SO_Number }}</h3>
                        </div>
                        <!--end::Title-->

                        <!--begin::Description-->
                        <p class="text-gray-700 flex-grow-1 mb-7">{{ Str::limit($objective->Description, 100) }}</p>
                        <!--end::Description-->

                        <!--begin::Actions-->
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-light-primary btn-sm" data-bs-toggle="modal" data-bs-target="#view_more_dialog_{{ $objective->id }}">
                                <i class="ki-duotone ki-eye fs-6 me-1">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                                View
                            </button>
                            <button type="button" class="btn btn-light-primary btn-sm" data-bs-toggle="modal" data-bs-target="#edit_strategic_objective_modal_{{ $objective->id }}">
                                <i class="ki-duotone ki-pencil fs-6 me-1">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                Edit
                            </button>
                            <button type="button" class="btn btn-light-danger btn-sm" onclick="confirmDelete('{{ $objective->id }}', 'strategic_objectives')">
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
                <!--end::Card-->
            </div>
        @empty
            <div class="col-12">
                <!--begin::Empty state-->
                <div class="card card-dashed flex-center min-h-350px">
                    <div class="card-body d-flex flex-column justify-content-center text-center">
                        <i class="ki-duotone ki-element-11 fs-5tx text-gray-300 mb-5">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                            <span class="path4"></span>
                        </i>
                        <span class="fs-2 fw-semibold text-gray-600">No strategic objectives found</span>
                        <button type="button" class="btn btn-primary btn-sm mt-5" data-bs-toggle="modal" data-bs-target="#add_strategic_objective_modal">
                            <i class="ki-duotone ki-plus fs-2 me-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                            Add Your First Objective
                        </button>
                    </div>
                </div>
                <!--end::Empty state-->
            </div>
        @endforelse
    </div>
    <!--end::Objectives Grid-->
</div>
<!--end::Strategic Objectives-->

<!--begin::View More Details Modals-->
@foreach ($strategicObjectives as $objective)
    <div class="modal fade" id="view_more_dialog_{{ $objective->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title fw-bold">Strategic Objective Details</h3>
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                </div>

                <div class="modal-body py-10">
                    <div class="mb-5">
                        <div class="d-flex flex-column bg-light-primary rounded p-5 mb-5">
                            <span class="text-gray-600 fs-7 fw-semibold mb-1">SO Number</span>
                            <span class="fs-5 fw-bold text-dark">{{ $objective->SO_Number }}</span>
                        </div>

                        <div class="d-flex flex-column bg-light-primary rounded p-5 mb-5">
                            <span class="text-gray-600 fs-7 fw-semibold mb-1">SO Name</span>
                            <span class="fs-5 fw-bold text-dark">{{ $objective->SO_Name }}</span>
                        </div>

                        <div class="d-flex flex-column bg-light-primary rounded p-5">
                            <span class="text-gray-600 fs-7 fw-semibold mb-1">Description</span>
                            <span class="fs-5 fw-bold text-dark">{{ $objective->Description }}</span>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endforeach
<!--end::View More Details Modals-->

<!--begin::Edit Modals-->
@foreach ($strategicObjectives as $objective)
    <div class="modal fade" id="edit_strategic_objective_modal_{{ $objective->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title fw-bold">Edit Strategic Objective</h3>
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                </div>

                <form action="{{ route('MassUpdate') }}" method="POST" id="edit-form-{{ $objective->id }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="TableName" value="strategic_objectives">
                    <input type="hidden" name="id" value="{{ $objective->id }}">

                    <div class="modal-body py-10">
                        <div class="mb-5">
                            <label class="form-label required">SO Number</label>
                            <input type="text" class="form-control" name="SO_Number" value="{{ $objective->SO_Number }}" required />
                        </div>

                        <div class="mb-5">
                            <label class="form-label required">SO Name</label>
                            <input type="text" class="form-control" name="SO_Name" value="{{ $objective->SO_Name }}" required />
                        </div>

                        <div class="mb-5">
                            <label class="form-label required">Description</label>
                            <textarea class="form-control" name="Description" rows="4" required>{{ $objective->Description }}</textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
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
<!--end::Edit Modals-->

<!--begin::Delete Confirmation Modal-->
<div class="modal fade" id="delete_confirm_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title fw-bold">Confirm Delete</h3>
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>

            <div class="modal-body py-10 text-center">
                <i class="ki-duotone ki-information-5 fs-5tx text-warning mb-5">
                    <span class="path1"></span>
                    <span class="path2"></span>
                    <span class="path3"></span>
                </i>
                <p class="fs-3 fw-semibold text-gray-800 mb-1">Are you sure you want to delete this objective?</p>
                <p class="fs-6 text-gray-600">This action cannot be undone.</p>

                <form id="delete-form" action="{{ route('MassDelete') }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" id="delete-id" name="id" value="">
                    <input type="hidden" id="delete-table" name="TableName" value="">
                </form>
            </div>

            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="submitDeleteForm()">
                    <i class="ki-duotone ki-trash fs-2 me-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                        <span class="path4"></span>
                        <span class="path5"></span>
                    </i>
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>
<!--end::Delete Confirmation Modal-->

<!--begin::Status Message Modal-->
<div class="modal fade" id="status_dialog" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body py-10 text-center">
                <i class="ki-duotone ki-check-circle fs-5tx text-success mb-5">
                    <span class="path1"></span>
                    <span class="path2"></span>
                </i>
                <p id="status-message" class="fs-3 fw-semibold text-gray-800 mb-1"></p>
            </div>

            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>
<!--end::Status Message Modal-->

<!--begin::Error Message Modal-->
<div class="modal fade" id="error_dialog" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body py-10 text-center">
                <i class="ki-duotone ki-cross-circle fs-5tx text-danger mb-5">
                    <span class="path1"></span>
                    <span class="path2"></span>
                </i>
                <p id="error-message" class="fs-3 fw-semibold text-gray-800 mb-1"></p>
            </div>

            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>
<!--end::Error Message Modal-->

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

    /* Card content fade-in animation */
    .card-custom {
        animation: fadeIn 0.6s ease-in-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Search animation */
    #search-input {
        transition: all 0.3s ease;
    }

    #search-input:focus {
        border-color: var(--kt-primary);
        box-shadow: 0 0 0 0.25rem rgba(var(--kt-primary-rgb), 0.25);
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

    /* Search result animations */
    .objective-card {
        transition: all 0.5s ease;
    }

    .objective-card.hidden {
        opacity: 0;
        transform: scale(0.8);
    }

    /* Badge styling */
    .badge {
        transition: all 0.3s ease;
    }

    .badge:hover {
        transform: scale(1.05);
    }
</style>
<!--end::Page Styles-->

<!--begin::Page Scripts-->
<script>
    // Delete confirmation with table name parameter
    function confirmDelete(objectiveId, tableName) {
        const deleteIdInput = document.getElementById('delete-id');
        const deleteTableInput = document.getElementById('delete-table');

        deleteIdInput.value = objectiveId;
        deleteTableInput.value = tableName;

        // Show modal using Bootstrap 5
        const deleteModal = new bootstrap.Modal(document.getElementById('delete_confirm_modal'));
        deleteModal.show();
    }

    // Submit delete form
    function submitDeleteForm() {
        document.getElementById('delete-form').submit();
    }

    document.addEventListener('DOMContentLoaded', (event) => {
        // Show status/error messages if they exist
        @if (session('status'))
            document.getElementById('status-message').textContent = "{{ session('status') }}";
            const statusModal = new bootstrap.Modal(document.getElementById('status_dialog'));
            statusModal.show();
        @endif

        @if (session('error'))
            document.getElementById('error-message').textContent = "{{ session('error') }}";
            const errorModal = new bootstrap.Modal(document.getElementById('error_dialog'));
            errorModal.show();
        @endif

        // Enhanced search functionality with animations
        const searchInput = document.getElementById('search-input');
        const objectivesGrid = document.getElementById('objectives-grid');
        const objectives = document.querySelectorAll('.objective-card');

        if (searchInput) {
            // Add focus animation
            searchInput.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });

            searchInput.addEventListener('blur', function() {
                this.parentElement.classList.remove('focused');
            });

            // Enhanced search with debounce
            let debounceTimer;

            searchInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);

                debounceTimer = setTimeout(() => {
                    const searchTerm = this.value.toLowerCase();
                    let hasResults = false;

                    objectives.forEach(objective => {
                        const title = objective.querySelector('.card-title').textContent.toLowerCase();
                        const description = objective.querySelector('p').textContent.toLowerCase();

                        if (title.includes(searchTerm) || description.includes(searchTerm)) {
                            hasResults = true;
                            objective.classList.remove('hidden');
                            objective.style.display = '';

                            // Highlight matching text if search term is not empty
                            if (searchTerm !== '') {
                                highlightText(objective, searchTerm);
                            } else {
                                // Remove highlights
                                removeHighlights(objective);
                            }
                        } else {
                            objective.classList.add('hidden');
                            setTimeout(() => {
                                if (objective.classList.contains('hidden')) {
                                    objective.style.display = 'none';
                                }
                            }, 300);
                        }
                    });

                    // Show no results message if needed
                    const noResultsMessage = document.querySelector('.no-results-message');

                    if (!hasResults && searchTerm !== '') {
                        if (!noResultsMessage) {
                            const message = document.createElement('div');
                            message.className = 'col-12 no-results-message';
                            message.innerHTML = `
                                <div class="card card-dashed flex-center min-h-350px">
                                    <div class="card-body d-flex flex-column justify-content-center text-center">
                                        <i class="ki-duotone ki-search fs-5tx text-gray-300 mb-5">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        <span class="fs-2 fw-semibold text-gray-600">No matching objectives found</span>
                                        <button class="btn btn-light-primary btn-sm mt-5 reset-search">Clear Search</button>
                                    </div>
                                </div>
                            `;
                            objectivesGrid.appendChild(message);

                            // Add event listener to reset button
                            message.querySelector('.reset-search').addEventListener('click', function() {
                                searchInput.value = '';
                                searchInput.dispatchEvent(new Event('input'));
                            });
                        }
                    } else if ((hasResults || searchTerm === '') && noResultsMessage) {
                        noResultsMessage.remove();
                    }
                }, 300);
            });
        }

        // Function to highlight matching text
        function highlightText(element, searchTerm) {
            // Remove existing highlights first
            removeHighlights(element);

            const title = element.querySelector('.card-title');
            const description = element.querySelector('p');

            if (title) {
                const titleText = title.textContent;
                if (titleText.toLowerCase().includes(searchTerm)) {
                    title.innerHTML = titleText.replace(
                        new RegExp(searchTerm, 'gi'),
                        match => `<span class="highlight-text">${match}</span>`
                    );
                }
            }

            if (description) {
                const descText = description.textContent;
                if (descText.toLowerCase().includes(searchTerm)) {
                    description.innerHTML = descText.replace(
                        new RegExp(searchTerm, 'gi'),
                        match => `<span class="highlight-text">${match}</span>`
                    );
                }
            }
        }

        // Function to remove highlights
        function removeHighlights(element) {
            const highlights = element.querySelectorAll('.highlight-text');
            highlights.forEach(highlight => {
                const textNode = document.createTextNode(highlight.textContent);
                highlight.parentNode.replaceChild(textNode, highlight);
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

<!--begin::Additional Styles for Highlighting-->
<style>
    .highlight-text {
        background-color: rgba(var(--kt-primary-rgb), 0.2);
        border-radius: 0.25rem;
        padding: 0 2px;
        font-weight: bold;
        color: var(--kt-primary);
    }

    @keyframes pulse {
        0% { background-color: rgba(var(--kt-primary-rgb), 0.1); }
        50% { background-color: rgba(var(--kt-primary-rgb), 0.3); }
        100% { background-color: rgba(var(--kt-primary-rgb), 0.1); }
    }

    .highlight-text {
        animation: pulse 2s infinite;
    }
</style>
<!--end::Additional Styles for Highlighting-->
