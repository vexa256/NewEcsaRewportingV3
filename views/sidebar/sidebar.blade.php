@php
    // Performance Monitoring Reports
    $performanceMonitoringItems = [
        ['title' => 'Report Indicators ', 'route' => 'Ecsa_SelectUser', 'icon' => 'ki-duotone ki-abstract-26'],
        // ['title' => 'General  Performance ', 'route' => 'ecsahc.performance.quarterly.filter', 'icon' => 'ki-duotone ki-abstract-26'],
        ['title' => 'Aggregated Report ', 'route' => 'performance-analytics.dashboard', 'icon' => 'ki-duotone ki-abstract-26'],
        ['title' => 'DQA', 'route' => 'completeness.filter', 'icon' => 'ki-duotone ki-abstract-26'],
    ];

    // Data Management
    $dataManagementItems = [
        ['title' => 'Manage Indicators', 'route' => 'SelectSo', 'icon' => 'ki-duotone ki-chart-simple-3'],
        ['title' => 'Manage Targets', 'route' => 'targets.index', 'icon' => 'ki-duotone ki-target'],
        ['title' => 'Manage Clusters', 'route' => 'MgtClusters', 'icon' => 'ki-duotone ki-element-11'],
        ['title' => 'Manage Objectives', 'route' => 'MgtSO', 'icon' => 'ki-duotone ki-flag'],
        ['title' => 'Manage Timeframes', 'route' => 'MgtEcsaTimelines', 'icon' => 'ki-duotone ki-calendar'],
        ['title' => 'Reporting Status', 'route' => 'MgtEcsaTimelinesStatus', 'icon' => 'ki-duotone ki-calendar'],
    ];

    // Admin Options
    $adminItems = [
        ['title' => 'User Management', 'route' => 'MgtEcsaUsers', 'icon' => 'ki-duotone ki-profile-user'],
        ['title' => 'Logout', 'route' => 'logout', 'icon' => 'ki-duotone ki-profile-user'],
    ];

    // Get the current authenticated user
    $user = Auth::user();
    $isAdmin = $user && $user->AccountRole === 'Admin';
@endphp

<div id="kt_app_sidebar" class="app-sidebar flex-column" data-kt-drawer="true" data-kt-drawer-name="app-sidebar" data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="250px" data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle">
    <!--begin::Main-->
    <div class="d-flex flex-column justify-content-between h-100 hover-scroll-overlay-y my-2 d-flex flex-column" id="kt_app_sidebar_main" data-kt-scroll="true" data-kt-scroll-activate="true" data-kt-scroll-height="auto" data-kt-scroll-dependencies="#kt_app_header" data-kt-scroll-wrappers="#kt_app_main" data-kt-scroll-offset="5px">
        <!--begin::Sidebar menu-->
        <div id="#kt_app_sidebar_menu" data-kt-menu="true" data-kt-menu-expand="false" class="flex-column-fluid menu menu-sub-indention menu-column menu-rounded menu-active-bg mb-7">

            <!--begin:Menu item - Performance (visible to all users)-->
            <div data-kt-menu-trigger="click" class="menu-item menu-accordion show">
                <!--begin:Menu link-->
                <span class="menu-link">
                    <span class="menu-icon">
                        <i class="ki-duotone ki-chart-line-star fs-1">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                            <span class="path4"></span>
                        </i>
                    </span>
                    <span class="menu-title">Performance </span>
                    <span class="menu-arrow"></span>
                </span>
                <!--end:Menu link-->
                <!--begin:Menu sub-->
                <div class="menu-sub menu-sub-accordion">
                    @foreach($performanceMonitoringItems as $item)
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <!--begin:Menu link-->
                        <a class="menu-link" href="{{ route($item['route']) }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">{{ $item['title'] }}</span>
                        </a>
                        <!--end:Menu link-->
                    </div>
                    <!--end:Menu item-->
                    @endforeach
                </div>
                <!--end:Menu sub-->
            </div>
            <!--end:Menu item-->

            @if($isAdmin)
            <!--begin:Menu item - Data Management (Admin only)-->
            <div data-kt-menu-trigger="click" class="menu-item menu-accordion show">
                <!--begin:Menu link-->
                <span class="menu-link">
                    <span class="menu-icon">
                        <i class="ki-duotone ki-data fs-1">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                            <span class="path4"></span>
                        </i>
                    </span>
                    <span class="menu-title">Data Management</span>
                    <span class="menu-arrow"></span>
                </span>
                <!--end:Menu link-->
                <!--begin:Menu sub-->
                <div class="menu-sub menu-sub-accordion">
                    @foreach($dataManagementItems as $item)
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <!--begin:Menu link-->
                        <a class="menu-link" href="{{ route($item['route']) }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">{{ $item['title'] }}</span>
                        </a>
                        <!--end:Menu link-->
                    </div>
                    <!--end:Menu item-->
                    @endforeach
                </div>
                <!--end:Menu sub-->
            </div>
            <!--end:Menu item-->

            <!--begin:Menu item - Administration (Admin only)-->
            <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                <!--begin:Menu link-->
                <span class="menu-link">
                    <span class="menu-icon">
                        <i class="ki-duotone ki-setting-2 fs-1">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                            <span class="path4"></span>
                        </i>
                    </span>
                    <span class="menu-title">Administration</span>
                    <span class="menu-arrow"></span>
                </span>
                <!--end:Menu link-->
                <!--begin:Menu sub-->
                <div class="menu-sub menu-sub-accordion">
                    @foreach($adminItems as $item)
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <!--begin:Menu link-->
                        <a class="menu-link" href="{{ route($item['route']) }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">{{ $item['title'] }}</span>
                        </a>
                        <!--end:Menu link-->
                    </div>
                    <!--end:Menu item-->
                    @endforeach
                </div>
                <!--end:Menu sub-->
            </div>
            <!--end:Menu item-->
            @else
            <!--begin:Menu item - Logout (for non-admin users)-->
            <div class="menu-item">
                <!--begin:Menu link-->
                <a class="menu-link" href="{{ route('logout') }}">
                    <span class="menu-icon">
                        <i class="ki-duotone ki-profile-user fs-1">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </span>
                    <span class="menu-title">Logout</span>
                </a>
                <!--end:Menu link-->
            </div>
            <!--end:Menu item-->
            @endif

        </div>
        <!--end::Sidebar menu-->
    </div>
    <!--end::Main-->
</div>
