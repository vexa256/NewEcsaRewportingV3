<div class="container-xxl py-8">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-8 gap-4">
        <h1 class="fs-2x fw-bolder text-primary">
            ECSA-HC User Management
        </h1>
        <div class="d-flex align-items-center gap-4">
            <div class="position-relative">
                <input type="text" id="search-input" placeholder="Search users..."
                    class="form-control form-control-sm form-control-solid pe-10" />
                <span class="position-absolute top-50 end-0 translate-middle-y pe-3">
                    <i class="ki-duotone ki-magnifier fs-5 text-gray-500"></i>
                </span>
            </div>
            <button class="btn btn-light-primary btn-sm" data-bs-toggle="modal" data-bs-target="#add_user_modal">
                <i class="ki-duotone ki-plus fs-2 me-2"></i>
                Add New User
            </button>
        </div>
    </div>

    <div class="row g-6" id="users-grid">
        @foreach ($users as $user)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h2 class="card-title fs-3 fw-bold text-primary">{{ $user->name }}</h2>
                        <p class="text-gray-600 mb-1">{{ $user->Cluster_Name }}</p>
                        <p class="mb-1">{{ $user->email }}</p>
                        <p class="mb-3">{{ $user->Phone }}</p>
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <button class="btn btn-light-primary btn-sm"
                                data-bs-toggle="modal" data-bs-target="#view_user_modal_{{ $user->id }}">
                                <i class="ki-duotone ki-eye fs-6 me-1"></i>
                                View
                            </button>
                            <button class="btn btn-light-primary btn-sm"
                                data-bs-toggle="modal" data-bs-target="#edit_user_modal_{{ $user->id }}">
                                <i class="ki-duotone ki-pencil fs-6 me-1"></i>
                                Edit
                            </button>
                            <button class="btn btn-light-danger btn-sm" onclick="confirmDelete('{{ $user->id }}')">
                                <i class="ki-duotone ki-trash fs-6 me-1"></i>
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Add New User Modal -->
<div class="modal fade" id="add_user_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title fw-bolder fs-1 text-primary">Add New ECSA-HC User</h3>
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ki-duotone ki-cross fs-1"></i>
                </div>
            </div>
            <div class="modal-body py-4">
                <form action="{{ route('MassInsert') }}" method="POST" id="addUserForm" class="form">
                    @csrf
                    <input type="hidden" name="TableName" value="users">
                    <input type="hidden" name="UserType" value="ECSA-HC">
                    <input type="hidden" name="UserCode" value="{{ md5(uniqid() . date('now')) }}">
                    <input type="hidden" name="UserID" value="{{ md5(uniqid() . date('now')) }}">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="fv-row">
                                <label class="form-label fw-semibold" for="name">Name</label>
                                <input type="text" id="name" name="name" class="form-control form-control-solid" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="fv-row">
                                <label class="form-label fw-semibold" for="email">Email</label>
                                <input type="email" id="email" name="email" class="form-control form-control-solid" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="fv-row">
                                <label class="form-label fw-semibold" for="password">Password</label>
                                <input type="password" id="password" name="password" class="form-control form-control-solid" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="fv-row">
                                <label class="form-label fw-semibold" for="ClusterID">Cluster</label>
                                <select id="ClusterID" name="ClusterID" class="form-select form-select-solid">
                                    <option value="">Select Cluster</option>
                                    @foreach ($clusters as $cluster)
                                        <option value="{{ $cluster->ClusterID }}">{{ $cluster->Cluster_Name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="fv-row">
                                <label class="form-label fw-semibold" for="Phone">Phone</label>
                                <input type="text" id="Phone" name="Phone" class="form-control form-control-solid">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="fv-row">
                                <label class="form-label fw-semibold" for="Nationality">Nationality</label>
                                <input type="text" id="Nationality" name="Nationality" class="form-control form-control-solid">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="fv-row">
                                <label class="form-label fw-semibold" for="Sex">Sex</label>
                                <select id="Sex" name="Sex" class="form-select form-select-solid">
                                    <option value="">Select Sex</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="fv-row">
                                <label class="form-label fw-semibold" for="JobTitle">Job Title</label>
                                <input type="text" id="JobTitle" name="JobTitle" class="form-control form-control-solid">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="fv-row">
                                <label class="form-label fw-semibold" for="AccountRole">Account Role</label>
                                <select id="AccountRole" name="AccountRole" class="form-select form-select-solid">
                                    <option value="Admin">Admin</option>
                                    <option value="User" selected>User</option>
                                    <option value="Viewer">Viewer</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="fv-row">
                                <label class="form-label fw-semibold" for="Address">Address</label>
                                <textarea id="Address" name="Address" class="form-control form-control-solid" rows="4"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="text-end mt-5">
                        <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modals -->
@foreach ($users as $user)
    <div class="modal fade" id="edit_user_modal_{{ $user->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-650px">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title fw-bolder fs-1 text-primary">Edit ECSA-HC User</h3>
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ki-duotone ki-cross fs-1"></i>
                    </div>
                </div>
                <div class="modal-body py-4">
                    <form action="{{ route('MassUpdate') }}" method="POST" id="editUserForm-{{ $user->id }}" class="form">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="id" value="{{ $user->id }}">
                        <input type="hidden" name="TableName" value="users">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="fv-row">
                                    <label class="form-label fw-semibold" for="name-{{ $user->id }}">Name</label>
                                    <input type="text" id="name-{{ $user->id }}" name="name" value="{{ $user->name }}"
                                        class="form-control form-control-solid" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="fv-row">
                                    <label class="form-label fw-semibold" for="email-{{ $user->id }}">Email</label>
                                    <input type="email" id="email-{{ $user->id }}" name="email" value="{{ $user->email }}"
                                        class="form-control form-control-solid" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="fv-row">
                                    <label class="form-label fw-semibold" for="password-{{ $user->id }}">
                                        Password (leave blank to keep current)
                                    </label>
                                    <input type="password" id="password-{{ $user->id }}" name="password"
                                        class="form-control form-control-solid">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="fv-row">
                                    <label class="form-label fw-semibold" for="ClusterID-{{ $user->id }}">Cluster</label>
                                    <select id="ClusterID-{{ $user->id }}" name="ClusterID" class="form-select form-select-solid">
                                        <option value="">Select Cluster</option>
                                        @foreach ($clusters as $cluster)
                                            <option value="{{ $cluster->ClusterID }}"
                                                {{ $user->ClusterID == $cluster->ClusterID ? 'selected' : '' }}>
                                                {{ $cluster->Cluster_Name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="fv-row">
                                    <label class="form-label fw-semibold" for="Phone-{{ $user->id }}">Phone</label>
                                    <input type="text" id="Phone-{{ $user->id }}" name="Phone" value="{{ $user->Phone }}"
                                        class="form-control form-control-solid">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="fv-row">
                                    <label class="form-label fw-semibold" for="Nationality-{{ $user->id }}">Nationality</label>
                                    <input type="text" id="Nationality-{{ $user->id }}" name="Nationality"
                                        value="{{ $user->Nationality }}" class="form-control form-control-solid">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="fv-row">
                                    <label class="form-label fw-semibold" for="Sex-{{ $user->id }}">Sex</label>
                                    <select id="Sex-{{ $user->id }}" name="Sex" class="form-select form-select-solid">
                                        <option value="">Select Sex</option>
                                        <option value="Male" {{ $user->Sex == 'Male' ? 'selected' : '' }}>Male</option>
                                        <option value="Female" {{ $user->Sex == 'Female' ? 'selected' : '' }}>Female</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="fv-row">
                                    <label class="form-label fw-semibold" for="JobTitle-{{ $user->id }}">Job Title</label>
                                    <input type="text" id="JobTitle-{{ $user->id }}" name="JobTitle"
                                        value="{{ $user->JobTitle }}" class="form-control form-control-solid">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="fv-row">
                                    <label class="form-label fw-semibold" for="AccountRole-{{ $user->id }}">Account Role</label>
                                    <select id="AccountRole-{{ $user->id }}" name="AccountRole" class="form-select form-select-solid">
                                        <option value="Admin" {{ $user->AccountRole == 'Admin' ? 'selected' : '' }}>Admin</option>
                                        <option value="User" {{ $user->AccountRole == 'User' ? 'selected' : '' }}>User</option>
                                        <option value="Viewer" {{ $user->AccountRole == 'Viewer' ? 'selected' : '' }}>Viewer</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="fv-row">
                                    <label class="form-label fw-semibold" for="Address-{{ $user->id }}">Address</label>
                                    <textarea id="Address-{{ $user->id }}" name="Address"
                                        class="form-control form-control-solid" rows="4">{{ $user->Address }}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="text-end mt-5">
                            <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach

<!-- View User Modals -->
@foreach ($users as $user)
    <div class="modal fade" id="view_user_modal_{{ $user->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-650px">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title fw-bolder fs-1 text-primary">User Details</h3>
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ki-duotone ki-cross fs-1"></i>
                    </div>
                </div>
                <div class="modal-body py-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="bg-light-primary rounded p-4">
                                <h4 class="fw-semibold fs-5 mb-2">Name</h4>
                                <p class="text-gray-700">{{ $user->name }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="bg-light-primary rounded p-4">
                                <h4 class="fw-semibold fs-5 mb-2">Email</h4>
                                <p class="text-gray-700">{{ $user->email }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="bg-light-primary rounded p-4">
                                <h4 class="fw-semibold fs-5 mb-2">Cluster</h4>
                                <p class="text-gray-700">{{ $user->Cluster_Name }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="bg-light-primary rounded p-4">
                                <h4 class="fw-semibold fs-5 mb-2">Phone</h4>
                                <p class="text-gray-700">{{ $user->Phone }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="bg-light-primary rounded p-4">
                                <h4 class="fw-semibold fs-5 mb-2">Job Title</h4>
                                <p class="text-gray-700">{{ $user->JobTitle }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="bg-light-primary rounded p-4">
                                <h4 class="fw-semibold fs-5 mb-2">Account Role</h4>
                                <p class="text-gray-700">{{ $user->AccountRole }}</p>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="bg-light-primary rounded p-4">
                                <h4 class="fw-semibold fs-5 mb-2">Address</h4>
                                <p class="text-gray-700">{{ $user->Address }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="text-end mt-5">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endforeach

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="delete_confirm_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title fw-bolder fs-1 text-danger">Confirm Deletion</h3>
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ki-duotone ki-cross fs-1"></i>
                </div>
            </div>
            <div class="modal-body py-4">
                <p class="fs-6 text-gray-700">Are you sure you want to delete this user? This action cannot be undone.</p>
                <form id="delete-form" action="{{ route('MassDelete') }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="id" id="delete-id">
                    <input type="hidden" name="TableName" value="users">
                    <div class="text-end mt-5">
                        <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmDelete(userId) {
        const deleteForm = document.getElementById('delete-form');
        const deleteIdInput = document.getElementById('delete-id');
        deleteIdInput.value = userId;

        // Using Bootstrap 5 modal method
        const deleteModal = new bootstrap.Modal(document.getElementById('delete_confirm_modal'));
        deleteModal.show();
    }

    document.addEventListener('DOMContentLoaded', (event) => {
        const searchInput = document.getElementById('search-input');
        const usersGrid = document.getElementById('users-grid');
        const userCards = usersGrid.querySelectorAll('.card');

        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();

            userCards.forEach(card => {
                const name = card.querySelector('.card-title').textContent.toLowerCase();
                const cluster = card.querySelector('.text-gray-600').textContent.toLowerCase();
                const email = card.querySelector('p:nth-of-type(2)').textContent.toLowerCase();
                const phone = card.querySelector('p:nth-of-type(3)').textContent.toLowerCase();

                if (name.includes(searchTerm) || cluster.includes(searchTerm) ||
                    email.includes(searchTerm) || phone.includes(searchTerm)) {
                    card.closest('.col-12').style.display = '';
                } else {
                    card.closest('.col-12').style.display = 'none';
                }
            });

            // Check if there are any visible cards
            const visibleCards = Array.from(userCards).filter(card =>
                card.closest('.col-12').style.display !== 'none'
            );

            const noResultsMessage = usersGrid.querySelector('.no-results-message');

            if (visibleCards.length === 0) {
                if (!noResultsMessage) {
                    const message = document.createElement('p');
                    message.className = 'no-results-message text-center py-4 text-gray-600 col-12';
                    message.textContent = 'No matching users found';
                    usersGrid.appendChild(message);
                }
            } else if (noResultsMessage) {
                noResultsMessage.remove();
            }
        });
    });
</script>
