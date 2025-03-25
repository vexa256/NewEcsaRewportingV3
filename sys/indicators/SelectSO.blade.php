<div class="container-fluid px-4 py-4">
    <!--begin::Card-->
    <div class="card card-custom gutter-b shadow-sm">
        <!--begin::Card header-->
        <div class="card-header border-0 pt-6">
            <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bold text-dark">{{ $Desc }}</span>
            </h3>
        </div>
        <!--end::Card header-->

        <!--begin::Card body-->
        <div class="card-body py-4">
            <!--begin::Form-->
            <form action="{{ route('MgtEcsaIndicators') }}" method="GET">
                @csrf
                <!--begin::Input group-->
                <div class="mb-5">
                    <label class="form-label fw-semibold fs-6" for="StrategicObjectiveID">Strategic Objective</label>
                    <select id="StrategicObjectiveID" name="StrategicObjectiveID"
                            class="form-select form-select-solid"
                            data-control="select2"
                            data-placeholder="Please select..."
                            required>
                        <option value="" disabled selected>Please select...</option>
                        @foreach ($strategicObjectives as $obj)
                            <option value="{{ $obj->StrategicObjectiveID }}">
                                {{ $obj->SO_Number }} {{ $obj->Description }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <!--end::Input group-->

                <!--begin::Actions-->
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">
                        <span class="indicator-label">Attach Indicators</span>
                        <span class="indicator-progress">Please wait...
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                        </span>
                    </button>
                </div>
                <!--end::Actions-->
            </form>
            <!--end::Form-->
        </div>
        <!--end::Card body-->
    </div>
    <!--end::Card-->
</div>

<!-- SweetAlert2 Notification for messages passed from the controller -->
@if (isset($message) && $message)
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Notification',
                text: "{{ $message }}",
                icon: 'info',
                confirmButtonText: 'OK',
                buttonsStyling: false,
                customClass: {
                    confirmButton: "btn btn-primary"
                }
            });
        });
    </script>
@endif

<!-- Initialize Select2 for enhanced dropdown -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Select2 for enhanced dropdown experience
        $("#StrategicObjectiveID").select2({
            dropdownParent: $("#StrategicObjectiveID").parent()
        });

        // Add loading indicator to button when form is submitted
        $("form").on("submit", function() {
            const btn = $(this).find("[type='submit']");
            btn.attr("data-kt-indicator", "on");
            setTimeout(function() {
                btn.removeAttr("data-kt-indicator");
            }, 3000); // Remove after 3 seconds if page doesn't reload
        });
    });
</script>
