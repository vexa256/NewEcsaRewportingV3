<div class="d-flex flex-column flex-root">
    <div class="content d-flex flex-column flex-column-fluid">
        <div class="container-xxl">
            <!-- Header Section -->
            <div class="card mb-9 bg-light-primary border-0">
                <div class="card-body py-8">
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-5">
                        <div>
                            <h1 class="fs-1 fw-bolder text-dark mb-2">Select Strategic Objective</h1>
                            <p class="text-gray-600 fs-6 mb-0">{{ $Desc }}</p>
                        </div>
                        <a href="{{ route('Ecsa_SelectTimeline', ['UserID' => $UserID, 'ClusterID' => $ClusterID]) }}"
                           class="btn btn-light-primary btn-active-primary">
                            <i class="bi bi-arrow-left fs-4 me-2"></i>
                            Back to Timeline Selection
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="row g-5">
                <!-- Strategic Objective Selection Form -->
                <div class="col-xl-8 mx-auto">
                    <div class="card card-flush shadow-lg hover-elevate-up">
                        <div class="card-header pt-9">
                            <div class="card-title">
                                <h2 class="fs-2 fw-bold text-dark mb-0">Select a Strategic Objective</h2>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <form action="{{ route('Ecsa_ReportPerformanceIndicators') }}" method="GET" id="strategicObjectiveForm">
                                @csrf
                                <input type="hidden" name="UserID" value="{{ $UserID }}">
                                <input type="hidden" name="ClusterID" value="{{ $ClusterID }}">
                                <input type="hidden" name="ReportingID" value="{{ $ReportingID }}">
                                <input type="hidden" name="userName" value="{{ $userName }}">
                                <input type="hidden" name="clusterName" value="{{ $clusterName }}">
                                <input type="hidden" name="timelineName" value="{{ $timelineName }}">

                                <div class="mb-8 fv-row">
                                    <label for="StrategicObjectiveID" class="form-label fs-6 fw-bold text-dark required">Strategic Objective</label>
                                    <div class="position-relative">
                                        <select class="form-select form-select-lg form-select-solid @error('StrategicObjectiveID') is-invalid @enderror"
                                                id="StrategicObjectiveID" name="StrategicObjectiveID" required
                                                data-control="select2" data-placeholder="Select a strategic objective...">
                                            <option value=""></option>
                                            @foreach ($strategicObjectives as $objective)
                                                <option value="{{ $objective->StrategicObjectiveID }}"
                                                        data-description="{{ $objective->Description }}"
                                                        {{ old('StrategicObjectiveID') == $objective->StrategicObjectiveID ? 'selected' : '' }}>
                                                    {{ $objective->SO_Number }} - {{ $objective->SO_Name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="position-absolute translate-middle-y end-0 me-5" style="top: 50%">
                                            <i class="bi bi-diagram-3 fs-2 text-gray-500"></i>
                                        </div>
                                        @error('StrategicObjectiveID')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>

                                <div id="objectiveDescription" class="d-none mb-8">
                                    <div class="notice d-flex bg-light-info rounded border-info border border-dashed p-6">
                                        <i class="bi bi-info-circle fs-2tx text-info me-4"></i>
                                        <div class="d-flex flex-stack flex-grow-1">
                                            <div class="fw-bold">
                                                <h4 class="text-gray-900 fw-bolder">Objective Description</h4>
                                                <div class="fs-6 text-gray-700 description-text"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex flex-stack pt-8">
                                    <div class="me-2">
                                        <a href="{{ route('Ecsa_SelectTimeline', ['UserID' => $UserID, 'ClusterID' => $ClusterID]) }}" class="btn btn-light">Cancel</a>
                                    </div>
                                    <button type="submit" class="btn btn-lg btn-primary" id="submitBtn" disabled>
                                        <span class="indicator-label">
                                            Continue to Performance Indicators
                                            <i class="bi bi-play-fill fs-3 ms-2"></i>
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

                <!-- Contextual Information (Hidden by default) -->
                <div class="col-xl-4 d-none">
                    <div class="mb-5">
                        <div class="card bg-primary">
                            <div class="card-body p-8">
                                <h3 class="card-title fs-3 fw-bolder text-white mb-5">Why Select a Strategic Objective?</h3>
                                <p class="text-white opacity-75 mb-4">Choosing a strategic objective allows you to:</p>
                                <div class="d-flex flex-column gap-4">
                                    <div class="d-flex align-items-start">
                                        <span class="bullet bullet-dot bg-white me-3 mt-1"></span>
                                        <span class="text-white">Focus on specific organizational goals</span>
                                    </div>
                                    <div class="d-flex align-items-start">
                                        <span class="bullet bullet-dot bg-white me-3 mt-1"></span>
                                        <span class="text-white">Align reporting with strategic priorities</span>
                                    </div>
                                    <div class="d-flex align-items-start">
                                        <span class="bullet bullet-dot bg-white me-3 mt-1"></span>
                                        <span class="text-white">Track progress towards key objectives</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card bg-warning">
                        <div class="card-body p-8">
                            <h3 class="card-title fs-3 fw-bolder text-white mb-5">Reporting Context</h3>
                            <div class="d-flex flex-column gap-4">
                                <div>
                                    <span class="text-white opacity-75 fw-semibold">Selected User:</span>
                                    <p class="text-white fs-5 mt-1">{{ $userName }}</p>
                                </div>
                                <div>
                                    <span class="text-white opacity-75 fw-semibold">Selected Cluster:</span>
                                    <p class="text-white fs-5 mt-1">{{ $clusterName }}</p>
                                </div>
                                <div>
                                    <span class="text-white opacity-75 fw-semibold">Reporting Timeline:</span>
                                    <p class="text-white fs-5 mt-1">{{ $timelineName }}</p>
                                </div>
                            </div>
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
            $("#StrategicObjectiveID").select2({
                minimumResultsForSearch: 5,
                dropdownParent: $("#StrategicObjectiveID").parent(),
                templateResult: formatObjective,
                templateSelection: formatObjectiveSelection
            });

            // Trigger change event to handle initial state
            $("#StrategicObjectiveID").on('change', function() {
                handleObjectiveChange(this);
            });
        }

        function formatObjective(objective) {
            if (!objective.id) {
                return objective.text;
            }

            var parts = objective.text.split(' - ');
            var number = parts[0];
            var name = parts.length > 1 ? parts[1] : '';

            var $objective = $(
                '<div class="d-flex align-items-center">' +
                    '<div class="symbol symbol-circle symbol-35px me-3 bg-light-primary">' +
                        '<span class="symbol-label bg-light-primary text-primary fw-bold">' + number + '</span>' +
                    '</div>' +
                    '<div class="d-flex flex-column">' +
                        '<span class="fw-bolder">' + name + '</span>' +
                        '<span class="text-muted fs-7">Objective ' + number + '</span>' +
                    '</div>' +
                '</div>'
            );

            return $objective;
        }

        function formatObjectiveSelection(objective) {
            if (!objective.id) {
                return objective.text;
            }

            var parts = objective.text.split(' - ');
            var number = parts[0];
            var name = parts.length > 1 ? parts[1] : '';

            return number + ' - ' + name;
        }

        function handleObjectiveChange(selectElement) {
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            const description = selectedOption ? selectedOption.dataset.description : '';
            const descriptionElement = document.getElementById('objectiveDescription');
            const submitBtn = document.getElementById('submitBtn');

            if (description) {
                descriptionElement.querySelector('.description-text').textContent = description;
                descriptionElement.classList.remove('d-none');
            } else {
                descriptionElement.classList.add('d-none');
            }

            submitBtn.disabled = !selectElement.value;

            // Haptic feedback for mobile devices
            if ('vibrate' in navigator) {
                navigator.vibrate(50);
            }
        }

        // Form submission with loading state
        const form = document.getElementById('strategicObjectiveForm');
        const submitBtn = document.getElementById('submitBtn');

        form.addEventListener('submit', function() {
            // Disable button and show loading indicator
            submitBtn.setAttribute('data-kt-indicator', 'on');
            submitBtn.disabled = true;

            // Optional: Add haptic feedback for mobile devices
            if (window.navigator && window.navigator.vibrate) {
                window.navigator.vibrate([30, 50, 30]); // Pattern vibration for form submission
            }
        });

        // Button hover effect
        submitBtn.addEventListener('mouseenter', function() {
            if (!this.disabled) {
                this.classList.add('btn-active-primary');
            }
        });

        submitBtn.addEventListener('mouseleave', function() {
            this.classList.remove('btn-active-primary');
        });

        // Enhanced keyboard navigation
        const selectElement = document.getElementById('StrategicObjectiveID');
        selectElement.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && this.value) {
                e.preventDefault();
                form.submit();
            }
        });
    });
</script>
