<div class="d-flex flex-column flex-root">
    <!-- Header Section -->
    <div class="content d-flex flex-column flex-column-fluid mb-8">
        <div class="container-xxl">
            <div class="card card-flush shadow-sm mb-5">
                <div class="card-body py-5">
                    <div class="d-flex flex-stack flex-wrap">
                        <div class="d-flex flex-column">
                            <h2 class="fs-1 fw-bolder text-dark mb-1">
                                Select Cluster for Reporting
                            </h2>
                            <p class="text-gray-600 fs-6">{{ $Desc }}</p>
                        </div>
                        <div class="d-flex align-items-center">
                            <a href="{{ route('Ecsa_SelectTimeline') }}" class="btn btn-light-primary btn-active-light-primary">
                                <span class="svg-icon svg-icon-2 me-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <path d="M12.5657 11.9343L8.56569 15.9343C8.25327 16.2467 7.74673 16.2467 7.43431 15.9343C7.12189 15.6219 7.12189 15.1153 7.43431 14.8029L10.6343 11.6029C10.9467 11.2905 10.9467 10.7839 10.6343 10.4715L7.43431 7.27147C7.12189 6.95905 7.12189 6.45251 7.43431 6.14009C7.74673 5.82767 8.25327 5.82767 8.56569 6.14009L12.5657 10.1401C12.8781 10.4525 12.8781 10.9591 12.5657 11.2715C12.5657 11.4919 12.5657 11.7139 12.5657 11.9343Z" fill="currentColor"/>
                                        <path opacity="0.3" d="M17.5657 11.9343L13.5657 15.9343C13.2533 16.2467 12.7467 16.2467 12.4343 15.9343C12.1219 15.6219 12.1219 15.1153 12.4343 14.8029L15.6343 11.6029C15.9467 11.2905 15.9467 10.7839 15.6343 10.4715L12.4343 7.27147C12.1219 6.95905 12.1219 6.45251 12.4343 6.14009C12.7467 5.82767 13.2533 5.82767 13.5657 6.14009L17.5657 10.1401C17.8781 10.4525 17.8781 10.9591 17.5657 11.2715C17.5657 11.4919 17.5657 11.7139 17.5657 11.9343Z" fill="currentColor"/>
                                    </svg>
                                </span>
                                Back to User Selection
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="row g-5">
                <div class="col-xl-8 col-lg-10 mx-auto">
                    <div class="card card-bordered shadow-lg hover-elevate-up">
                        <div class="card-header border-0 pt-9">
                            <div class="card-title flex-column">
                                <h3 class="fs-2 fw-bolder text-dark mb-1">Only applicable clusters are shown</h3>
                                <div class="text-gray-400 fw-bold fs-6">Choose the appropriate cluster to continue with reporting</div>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <form action="{{ route('Ecsa_SelectTimeline') }}" method="POST" id="cluster_selection_form">
                                @csrf
                                <input type="hidden" name="UserID" value="{{ $user->UserID }}">
                                <input type="hidden" name="userName" value="{{ $userName }}">

                                <div class="mb-10 fv-row">
                                    <label for="ClusterID" class="form-label fs-6 fw-bold text-dark required">Select Cluster</label>
                                    <div class="position-relative">
                                        <select class="form-select form-select-lg form-select-solid @error('ClusterID') is-invalid @enderror"
                                            id="ClusterID" name="ClusterID" required aria-required="true"
                                            data-control="select2" data-placeholder="Select a cluster..."
                                            data-allow-clear="true" data-hide-search="false">
                                            <option value=""></option>
                                            @foreach ($clusters as $cluster)
                                                <option value="{{ $cluster->ClusterID }}"
                                                    {{ old('ClusterID') == $cluster->ClusterID ? 'selected' : '' }}>
                                                    {{ $cluster->Cluster_Name }} - {{ $cluster->Description }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="position-absolute translate-middle-y end-0 me-5" style="top: 50%">
                                            <i class="bi bi-diagram-3 fs-2 text-gray-500"></i>
                                        </div>
                                        @error('ClusterID')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="d-flex flex-stack pt-8">
                                    <div class="me-2">
                                        <a href="{{ route('Ecsa_SelectTimeline') }}" class="btn btn-light">Cancel</a>
                                    </div>
                                    <button type="submit" id="submit_button" class="btn btn-lg btn-primary fw-bolder">
                                        <span class="indicator-label">
                                            Continue to Timeline Selection
                                            <span class="svg-icon svg-icon-3 ms-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                    <path d="M14.4 11H3C2.4 11 2 11.4 2 12C2 12.6 2.4 13 3 13H14.4V11Z" fill="currentColor"/>
                                                    <path opacity="0.3" d="M14.4 20V4L21.7 11.3C22.1 11.7 22.1 12.3 21.7 12.7L14.4 20Z" fill="currentColor"/>
                                                </svg>
                                            </span>
                                        </span>
                                        <span class="indicator-progress">
                                            Please wait... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                        </span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Initialize Select2 for premium dropdown experience
        if (typeof $.fn.select2 !== 'undefined') {
            $("#ClusterID").select2({
                minimumResultsForSearch: 5,
                dropdownParent: $("#ClusterID").parent(),
                templateResult: formatCluster,
                templateSelection: formatClusterSelection
            });
        }

        function formatCluster(cluster) {
            if (!cluster.id) {
                return cluster.text;
            }

            var $cluster = $(
                '<div class="d-flex align-items-center">' +
                    '<div class="symbol symbol-circle symbol-35px me-3 bg-light-primary">' +
                        '<span class="symbol-label bg-light-primary text-primary fw-bold">' + cluster.text.charAt(0) + '</span>' +
                    '</div>' +
                    '<div class="d-flex flex-column">' +
                        '<span class="fw-bolder">' + cluster.text.split(' - ')[0] + '</span>' +
                        '<span class="text-muted fs-7">' + (cluster.text.split(' - ')[1] || '') + '</span>' +
                    '</div>' +
                '</div>'
            );

            return $cluster;
        }

        function formatClusterSelection(cluster) {
            if (!cluster.id) {
                return cluster.text;
            }

            return cluster.text.split(' - ')[0];
        }

        // Form submission with loading state
        const form = document.getElementById('cluster_selection_form');
        const submitButton = document.getElementById('submit_button');

        form.addEventListener('submit', function() {
            // Disable button
            submitButton.setAttribute('data-kt-indicator', 'on');

            // Enable button after 1.5 seconds for better UX
            setTimeout(function() {
                submitButton.removeAttribute('data-kt-indicator');
            }, 1500);

            // Optional: Add haptic feedback for mobile devices
            if (window.navigator && window.navigator.vibrate) {
                window.navigator.vibrate(50); // Subtle vibration on submit
            }
        });

        // Enhanced keyboard navigation
        const selectElement = document.getElementById('ClusterID');
        selectElement.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                form.submit();
            }
        });
    });
</script>
