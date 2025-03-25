@php
    use Illuminate\Support\Str;
@endphp

<div class="container-xxl">
    <!-- Begin::Header -->
    <div class="card mb-8">
        <div class="card-body py-5">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center">
                <div class="mb-5 mb-lg-0">
                    <h1 class="fs-2x fw-bolder text-dark mb-0">ECSA-HC Clusters</h1>
                    <span class="text-gray-600 fs-6">Manage your organization's cluster structure</span>
                </div>
                <div class="d-flex flex-column flex-sm-row gap-3">
                    <div class="position-relative">
                        <input type="text" id="search" placeholder="Search clusters..."
                            class="form-control form-control-solid pe-10" oninput="filterClusters()">
                        <span class="position-absolute top-50 end-0 translate-middle-y pe-3">
                            <i class="ki-duotone ki-magnifier fs-3 text-gray-500"></i>
                        </span>
                    </div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add_entity_modal">
                        <i class="ki-duotone ki-plus fs-2 me-2"></i>
                        Add Cluster
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- End::Header -->

    <!-- Begin::Clusters Grid -->
    <div id="clusters-grid" class="row g-6">
        @forelse ($clusters as $cluster)
            <div class="col-md-6 col-lg-4 cluster-card">
                <div class="card card-flush h-100 hover-elevate-up">
                    <div class="card-body d-flex flex-column p-8">
                        <div class="d-flex align-items-center mb-5">
                            <div class="symbol symbol-50px me-5">
                                <div class="symbol-label bg-light-primary">
                                    <i class="ki-duotone ki-abstract-26 fs-1 text-primary">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                </div>
                            </div>
                            <div class="d-flex flex-column">
                                <h3 class="text-dark fw-bold fs-4 mb-1">{{ $cluster->Cluster_Name }}</h3>
                                <span class="badge badge-light-primary fs-7">ID: {{ $cluster->id }}</span>
                            </div>
                        </div>
                        <div class="mb-5">
                            <p class="text-gray-600 fs-6">{{ Str::limit($cluster->Description, 100) }}</p>
                        </div>
                        <div class="d-flex justify-content-end mt-auto pt-4 border-top border-gray-200">
                            <button class="btn btn-light-primary btn-sm me-2"
                                data-bs-toggle="modal" data-bs-target="#edit_entity_modal_{{ $cluster->id }}">
                                <i class="ki-duotone ki-pencil fs-6 me-1"></i>
                                Edit
                            </button>
                            <button class="btn btn-light-danger btn-sm" onclick="confirmDelete({{ $cluster->id }})">
                                <i class="ki-duotone ki-trash fs-6 me-1"></i>
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card card-flush py-10">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center">
                        <div class="mb-5">
                            <i class="ki-duotone ki-element-11 fs-5tx text-gray-300">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                                <span class="path4"></span>
                            </i>
                        </div>
                        <div class="text-center">
                            <h3 class="fs-2x fw-bold mb-3">No clusters found</h3>
                            <p class="fs-5 text-gray-600 mb-5">Start by adding a new cluster to your organization</p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add_entity_modal">
                                <i class="ki-duotone ki-plus fs-2 me-2"></i>
                                Add Your First Cluster
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
    <!-- End::Clusters Grid -->
</div>

<!-- Add New Entity Modal -->
<div class="modal fade" id="add_entity_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bolder">Add New ECSA-HC Cluster</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </div>
            </div>
            <div class="modal-body scroll-y mx-5 mx-xl-15 my-5">
                <form id="kt_modal_add_cluster_form" class="form" action="{{ route('MassInsert') }}" method="POST">
                    @csrf
                    <input type="hidden" name="TableName" value="clusters">
                    <div class="d-flex flex-column mb-8 fv-row">
                        <label class="d-flex align-items-center fs-6 fw-semibold mb-2" for="Cluster_Name">
                            <span class="required">Cluster Name</span>
                            <i class="fas fa-exclamation-circle ms-2 fs-7" data-bs-toggle="tooltip"
                                title="Specify the unique cluster name"></i>
                        </label>
                        <input type="text" id="Cluster_Name" name="Cluster_Name"
                            class="form-control form-control-solid" placeholder="Enter Cluster Name" required>
                    </div>
                    <div class="d-flex flex-column mb-8 fv-row">
                        <label class="fs-6 fw-semibold mb-2" for="ClusterID">
                            <span>Cluster ID</span>
                        </label>
                        <input readonly value="{{ md5(uniqid() . strtotime('now')) }}" type="text" id="ClusterID"
                            name="ClusterID" class="form-control form-control-solid bg-light" required>
                        <div class="text-muted fs-7 mt-2">Automatically generated unique identifier</div>
                    </div>
                    <div class="d-flex flex-column mb-8 fv-row">
                        <label class="fs-6 fw-semibold mb-2" for="Description">
                            <span>Details</span>
                        </label>
                        <textarea id="Description" name="Description" class="form-control form-control-solid"
                            rows="5" placeholder="Enter cluster description"></textarea>
                    </div>
                    <div class="text-center">
                        <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="kt_modal_add_cluster_submit" class="btn btn-primary">
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

<!-- Edit Entity Modals -->
@foreach ($clusters as $cluster)
    <div class="modal fade" id="edit_entity_modal_{{ $cluster->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-650px">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="fw-bolder">Edit Cluster</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <i class="ki-duotone ki-cross fs-1">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </div>
                </div>
                <div class="modal-body scroll-y mx-5 mx-xl-15 my-5">
                    <form id="kt_modal_edit_cluster_form_{{ $cluster->id }}" class="form"
                        action="{{ route('MassUpdate') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="id" value="{{ $cluster->id }}">
                        <input type="hidden" name="TableName" value="clusters">
                        <div class="d-flex flex-column mb-8 fv-row">
                            <label class="d-flex align-items-center fs-6 fw-semibold mb-2" for="Cluster_Name_{{ $cluster->id }}">
                                <span class="required">Cluster Name</span>
                                <i class="fas fa-exclamation-circle ms-2 fs-7" data-bs-toggle="tooltip"
                                    title="Specify the unique cluster name"></i>
                            </label>
                            <input type="text" id="Cluster_Name_{{ $cluster->id }}" name="Cluster_Name"
                                value="{{ $cluster->Cluster_Name }}" class="form-control form-control-solid" required>
                        </div>
                        <div class="d-flex flex-column mb-8 fv-row">
                            <label class="fs-6 fw-semibold mb-2" for="Description_{{ $cluster->id }}">
                                <span>Details</span>
                            </label>
                            <textarea id="Description_{{ $cluster->id }}" name="Description"
                                class="form-control form-control-solid" rows="5">{{ $cluster->Description }}</textarea>
                        </div>
                        <div class="text-center">
                            <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" id="kt_modal_edit_cluster_submit_{{ $cluster->id }}" class="btn btn-primary">
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
<div class="modal fade" id="delete_confirm_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-500px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bolder text-danger">Confirm Deletion</h2>
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
                    <div class="fs-3 fw-bold text-dark mb-5">Are you sure you want to delete this cluster?</div>
                    <div class="fs-6 text-gray-600 mb-5">This action cannot be undone and may affect related data.</div>
                    <form id="delete-form" action="{{ route('MassDelete') }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="id" id="delete-id">
                        <input type="hidden" name="TableName" value="clusters">
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

<!-- Status Notification Toast -->
<div class="position-fixed top-0 end-0 p-3" style="z-index: 11">
    <div id="kt_docs_toast_success" class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false">
        <div class="toast-header">
            <i class="ki-duotone ki-abstract-26 fs-2 text-success me-3">
                <span class="path1"></span>
                <span class="path2"></span>
            </i>
            <strong class="me-auto">Success</strong>
            <small>Just now</small>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body bg-light-success" id="status-message"></div>
    </div>
</div>

<!-- Error Notification Toast -->
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

<!-- Validation Errors Toast -->
<div class="position-fixed top-0 end-0 p-3" style="z-index: 11">
    <div id="kt_docs_toast_validation" class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false">
        <div class="toast-header">
            <i class="ki-duotone ki-information-5 fs-2 text-warning me-3">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
            </i>
            <strong class="me-auto">Validation Errors</strong>
            <small>Just now</small>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body bg-light-warning">
            <ul class="list-unstyled mb-0" id="validation-errors-list"></ul>
        </div>
    </div>
</div>

<script>
    function filterClusters() {
        const searchTerm = document.getElementById('search').value.toLowerCase();
        const clusterCards = document.querySelectorAll('.cluster-card');

        clusterCards.forEach(card => {
            const clusterName = card.querySelector('h3').textContent.toLowerCase();
            const clusterDescription = card.querySelector('p').textContent.toLowerCase();

            if (clusterName.includes(searchTerm) || clusterDescription.includes(searchTerm)) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });

        // Check if any cards are visible
        const visibleCards = Array.from(clusterCards).filter(card => card.style.display !== 'none');
        const noResultsElement = document.getElementById('no-results-message');

        if (visibleCards.length === 0 && !noResultsElement) {
            const clustersGrid = document.getElementById('clusters-grid');
            const noResults = document.createElement('div');
            noResults.id = 'no-results-message';
            noResults.className = 'col-12';
            noResults.innerHTML = `
                <div class="card card-flush py-10">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center">
                        <div class="mb-5">
                            <i class="ki-duotone ki-search fs-5tx text-gray-300">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                        </div>
                        <div class="text-center">
                            <h3 class="fs-2x fw-bold mb-3">No matching clusters found</h3>
                            <p class="fs-5 text-gray-600">Try adjusting your search criteria</p>
                        </div>
                    </div>
                </div>
            `;
            clustersGrid.appendChild(noResults);
        } else if (visibleCards.length > 0 && noResultsElement) {
            noResultsElement.remove();
        }
    }

    function confirmDelete(id) {
        const deleteForm = document.getElementById('delete-form');
        const deleteIdInput = document.getElementById('delete-id');
        deleteIdInput.value = id;

        const deleteModal = new bootstrap.Modal(document.getElementById('delete_confirm_modal'));
        deleteModal.show();
    }

    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
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

        // Show notifications if they exist in session
        @if (session('status'))
            document.getElementById('status-message').textContent = "{{ session('status') }}";
            const successToast = new bootstrap.Toast(document.getElementById('kt_docs_toast_success'));
            successToast.show();
        @endif

        @if (session('error'))
            document.getElementById('error-message').textContent = "{{ session('error') }}";
            const errorToast = new bootstrap.Toast(document.getElementById('kt_docs_toast_error'));
            errorToast.show();
        @endif

        @if ($errors->any())
            const validationErrorsList = document.getElementById('validation-errors-list');
            validationErrorsList.innerHTML = '';
            @foreach ($errors->all() as $error)
                const li = document.createElement('li');
                li.textContent = "{{ $error }}";
                li.className = 'mb-2';
                validationErrorsList.appendChild(li);
            @endforeach
            const validationToast = new bootstrap.Toast(document.getElementById('kt_docs_toast_validation'));
            validationToast.show();
        @endif
    });
</script>
