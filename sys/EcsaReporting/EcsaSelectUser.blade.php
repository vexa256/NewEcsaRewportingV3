<div class="container-fluid py-5 px-lg-5">
    <div class="row justify-content-center">
        <div class="col-xxl-10">
            <div class="mb-5">
                <h2 class="fs-2 fw-bold mb-2">
                    Select ECSA-HC User to Begin Reporting
                </h2>
                <p class="text-muted fs-7">{{ $Desc }}</p>
            </div>

            <div class="card shadow-sm">
                <div class="card-body p-lg-8">
                    <div class="row">
                        <div class="col-12">
                            <form action="{{ route('Ecsa_SelectCluster') }}" method="GET">
                                @csrf
                                <div class="mb-5">
                                    <label class="form-label fw-semibold" for="UserID">
                                        Select ECSA-HC User
                                    </label>
                                    <select class="form-select form-select-solid @error('UserID') is-invalid @enderror"
                                        id="UserID" name="UserID" required data-control="select2"
                                        data-placeholder="Select a user...">
                                        <option value="">Select a user...</option>
                                        @foreach ($users as $user)
                                            @if (Auth::user()->AccountRole === 'Admin' || $user->UserID === Auth::user()->UserID)
                                                <option value="{{ $user->UserID }}"
                                                    {{ old('UserID') == $user->UserID ? 'selected' : '' }}>
                                                    {{ $user->name }} - {{ $user->email }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                    @error('UserID')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="mt-8">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-person-check fs-4 me-2"></i>
                                        Continue with Selected User
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
        // Initialize Select2 for enhanced dropdown
        if (typeof $.fn.select2 !== 'undefined') {
            $('#UserID').select2({
                dropdownParent: $('#UserID').parent(),
                templateResult: formatOption,
                templateSelection: formatOption,
                placeholder: "Select a user...",
                allowClear: true
            });
        }

        function formatOption(option) {
            if (!option.id) {
                return option.text;
            }

            return $('<div><span class="fw-bold">' + option.text + '</span></div>');
        }
    });
</script>
