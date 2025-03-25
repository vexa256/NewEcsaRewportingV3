<div id="kt_app_aside" class="app-aside flex-column" data-kt-drawer="true" data-kt-drawer-name="app-aside" data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="auto" data-kt-drawer-direction="end" data-kt-drawer-toggle="#kt_app_aside_mobile_toggle">
    <!--begin::Wrapper-->
    <div id="kt_app_aside_wrapper" class="d-flex flex-column align-items-center hover-scroll-y py-5 py-lg-0 gap-4" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-height="auto" data-kt-scroll-dependencies="#kt_app_header" data-kt-scroll-wrappers="#kt_app_aside_wrapper" data-kt-scroll-offset="5px">
        <a href="{{ route('Ecsa_SelectUser') }}" class="btn btn-icon btn-color-primary bg-hover-body h-45px w-45px flex-shrink-0 mb-4" data-bs-toggle="tooltip" title="Report Indicators" data-bs-custom-class="tooltip-inverse">
            <i class="ki-duotone ki-chart-line-star fs-2qx">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
                <span class="path4"></span>
            </i>
        </a>
        <a href="{{ route('ecsahc.performance.quarterly.filter') }}" class="btn btn-icon btn-color-success bg-hover-body h-45px w-45px flex-shrink-0 mb-4" data-bs-toggle="tooltip" title="General Performance" data-bs-custom-class="tooltip-inverse">
            <i class="ki-duotone ki-chart-simple fs-2qx">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
                <span class="path4"></span>
            </i>
        </a>
        <a href="{{ route('performance-analytics.dashboard') }}" class="btn btn-icon btn-color-info bg-hover-body h-45px w-45px flex-shrink-0 mb-4" data-bs-toggle="tooltip" title="Aggregated Report" data-bs-custom-class="tooltip-inverse">
            <i class="ki-duotone ki-abstract-26 fs-2qx">
                <span class="path1"></span>
                <span class="path2"></span>
            </i>
        </a>
    </div>
    <!--end::Wrapper-->
</div>
