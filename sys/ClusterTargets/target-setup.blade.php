@php
// dd($indicators);
    // Define the valid target timeframe – the lower bound for comparison.
    $validStartYear = 2024; // Hard-coded starting year
    $validEndYear = $validStartYear + 3; // 3 years ahead from starting year

    // Helper function: returns true if the range matches "YYYY-YYYY" and the end year is exactly one greater than the start.
    $isValidRange = function($range) use ($validStartYear) {
        if (!preg_match('/^\d{4}-\d{4}$/', $range)) {
            return false;
        }
        $parts = explode('-', $range);
        $start = (int)$parts[0];
        $end   = (int)$parts[1];
        if (($end - $start) !== 1) {
            return false;
        }
        return true;
    };

    // Prepare Strategic Objective data arrays from the $indicators collection.
    $soCategories = [];
    $soWithTargets = [];
    $soWithoutTargets = [];

    foreach ($indicators as $objective => $indicatorGroup) {
        $totalForSO = count($indicatorGroup);
        $withTargetForSO = 0;
        foreach ($indicatorGroup as $indicator) {
            if ($existingTargets->has($indicator->id)) {
                $targets = $existingTargets[$indicator->id];

                // Filter targets: only consider those with a valid two-year range format and whose starting year is >= validStartYear.
                $validTargets = $targets->filter(function ($target) use ($validStartYear, $isValidRange) {
                    if (!$isValidRange($target->Target_Year)) {
                        return false;
                    }
                    $parts = explode('-', $target->Target_Year);
                    $startYear = (int)$parts[0];
                    return $startYear >= $validStartYear;
                });

                // Filter for legacy targets: valid ranges with a starting year below validStartYear.
                $legacyTargets = $targets->filter(function ($target) use ($validStartYear, $isValidRange) {
                    if (!$isValidRange($target->Target_Year)) {
                        return false;
                    }
                    $parts = explode('-', $target->Target_Year);
                    $startYear = (int)$parts[0];
                    return $startYear < $validStartYear;
                });

                // An indicator is considered to have a valid target if it has at least 1 valid target.
                if ($validTargets->count() >= 1) {
                    $withTargetForSO++;
                }
            }
        }
        $withoutTargetForSO = $totalForSO - $withTargetForSO;
        $soCategories[] = $objective;
        $soWithTargets[] = $withTargetForSO;
        $soWithoutTargets[] = $withoutTargetForSO;
    }

    // Prepare colors for charts.
    $colors = [
        'primary' => '#009EF7', // Metronic Primary Blue
        'success' => '#50CD89', // Metronic Success Green
        'warning' => '#FFC700', // Metronic Warning Yellow
        'danger'  => '#F1416C', // Metronic Danger Red
        'info'    => '#7239EA', // Metronic Info Purple
    ];

    // Prepare targets data for JavaScript.
    $targetsDataJson = json_encode(
        $existingTargets
            ->map(function ($targets) use ($isValidRange) {
                return $targets
                    ->filter(function ($t) use ($isValidRange) {
                        return $isValidRange($t->Target_Year);
                    })
                    ->map(function ($t) {
                        return ['year' => $t->Target_Year, 'value' => $t->Target_Value, 'id' => $t->id];
                    })
                    ->values();
            })
            ->all()
    );

    // Prepare indicator data for JavaScript.
    $indicatorsData = [];
    foreach ($indicators as $objective => $indicatorGroup) {
        foreach ($indicatorGroup as $indicator) {
            $targets = $existingTargets->has($indicator->id) ? $existingTargets[$indicator->id] : collect([]);
            $validTargets = $targets->filter(function ($target) use ($validStartYear, $isValidRange) {
                if (!$isValidRange($target->Target_Year)) {
                    return false;
                }
                $parts = explode('-', $target->Target_Year);
                $startYear = (int)$parts[0];
                return $startYear >= $validStartYear;
            });
            $legacyTargets = $targets->filter(function ($target) use ($validStartYear, $isValidRange) {
                if (!$isValidRange($target->Target_Year)) {
                    return false;
                }
                $parts = explode('-', $target->Target_Year);
                $startYear = (int)$parts[0];
                return $startYear < $validStartYear;
            });

            $indicatorsData[$indicator->id] = [
                'id'             => $indicator->id,
                'name'           => $indicator->Indicator_Name,
                'number'         => $indicator->Indicator_Number,
                'responseType'   => $indicator->ResponseType,
                'objective'      => $objective,
                'hasTargets'     => $validTargets->count() > 0,
                'hasValidTargets'=> $validTargets->count() >= 1,
                'validTargets'   => $validTargets->map(function ($t) {
                                        return [
                                            'id'    => $t->id,
                                            'year'  => $t->Target_Year,
                                            'value' => $t->Target_Value,
                                        ];
                                    })->values()->toArray(),
                'legacyTargets'  => $legacyTargets->map(function ($t) {
                                        return [
                                            'id'    => $t->id,
                                            'year'  => $t->Target_Year,
                                            'value' => $t->Target_Value,
                                        ];
                                    })->values()->toArray(),
                'allTargets'     => $targets->filter(function ($t) use ($isValidRange) {
                                        return $isValidRange($t->Target_Year);
                                    })->map(function ($t) use ($validStartYear) {
                                        $parts = explode('-', $t->Target_Year);
                                        $startYear = (int)$parts[0];
                                        return [
                                            'id'       => $t->id,
                                            'year'     => $t->Target_Year,
                                            'value'    => $t->Target_Value,
                                            'isLegacy' => $startYear < $validStartYear,
                                        ];
                                    })->values()->toArray(),
            ];
        }
    }
    $indicatorsDataJson = json_encode($indicatorsData);

    // Update soData based on new criteria.
    $soData = [];
    foreach ($indicators as $objective => $indicatorGroup) {
        $totalForSO = count($indicatorGroup);
        $withTargetForSO = 0;
        $withoutTargetForSO = 0;
        $indicatorsWithTargets = [];
        $indicatorsWithoutTargets = [];

        foreach ($indicatorGroup as $indicator) {
            if ($existingTargets->has($indicator->id)) {
                $targets = $existingTargets[$indicator->id];
                $validTargets = $targets->filter(function ($target) use ($validStartYear, $isValidRange) {
                    if (!$isValidRange($target->Target_Year)) {
                        return false;
                    }
                    $parts = explode('-', $target->Target_Year);
                    $startYear = (int)$parts[0];
                    return $startYear >= $validStartYear;
                });
                $legacyTargets = $targets->filter(function ($target) use ($validStartYear, $isValidRange) {
                    if (!$isValidRange($target->Target_Year)) {
                        return false;
                    }
                    $parts = explode('-', $target->Target_Year);
                    $startYear = (int)$parts[0];
                    return $startYear < $validStartYear;
                });

                if ($validTargets->count() >= 1) {
                    $withTargetForSO++;
                    $indicatorsWithTargets[] = [
                        'id'            => $indicator->id,
                        'number'        => $indicator->Indicator_Number,
                        'name'          => $indicator->Indicator_Name,
                        'validTargets'  => $validTargets->map(function ($t) {
                                                return [
                                                    'id'    => $t->id,
                                                    'year'  => $t->Target_Year,
                                                    'value' => $t->Target_Value,
                                                ];
                                            })->values()->toArray(),
                        'legacyTargets' => $legacyTargets->map(function ($t) {
                                                return [
                                                    'id'    => $t->id,
                                                    'year'  => $t->Target_Year,
                                                    'value' => $t->Target_Value,
                                                ];
                                            })->values()->toArray(),
                    ];
                } else {
                    $withoutTargetForSO++;
                    $indicatorsWithoutTargets[] = [
                        'id'            => $indicator->id,
                        'number'        => $indicator->Indicator_Number,
                        'name'          => $indicator->Indicator_Name,
                        'validTargets'  => $validTargets->map(function ($t) {
                                                return [
                                                    'id'    => $t->id,
                                                    'year'  => $t->Target_Year,
                                                    'value' => $t->Target_Value,
                                                ];
                                            })->values()->toArray(),
                        'legacyTargets' => $legacyTargets->map(function ($t) {
                                                return [
                                                    'id'    => $t->id,
                                                    'year'  => $t->Target_Year,
                                                    'value' => $t->Target_Value,
                                                ];
                                            })->values()->toArray(),
                        'reason'        => $validTargets->count() > 0
                                                ? 'Needs ' . (1 - $validTargets->count()) . ' more target (two-year range) from ' . $validStartYear . ' onwards'
                                                : 'Needs at least 1 target (two-year range) from ' . $validStartYear . ' onwards',
                    ];
                }
            } else {
                $withoutTargetForSO++;
                $indicatorsWithoutTargets[] = [
                    'id'            => $indicator->id,
                    'number'        => $indicator->Indicator_Number,
                    'name'          => $indicator->Indicator_Name,
                    'validTargets'  => [],
                    'legacyTargets' => [],
                    'reason'        => 'No targets set',
                ];
            }
        }

        $percentComplete = $totalForSO > 0 ? round(($withTargetForSO / $totalForSO) * 100) : 0;
        $soData[$objective] = [
            'objective'                => $objective,
            'total'                    => $totalForSO,
            'withTarget'               => $withTargetForSO,
            'withoutTarget'            => $withoutTargetForSO,
            'percentComplete'          => $percentComplete,
            'indicatorsWithTargets'    => $indicatorsWithTargets,
            'indicatorsWithoutTargets' => $indicatorsWithoutTargets,
            'validStartYear'           => $validStartYear,
            'validEndYear'             => $validEndYear,
        ];
    }
    $soDataJson = json_encode($soData);
@endphp


    <style>
        :root {
            --kt-primary: #009EF7;
            --kt-primary-light: #F1FAFF;
            --kt-primary-active: #0095E8;
            --kt-success: #50CD89;
            --kt-success-light: #E8FFF3;
            --kt-success-active: #47BE7D;
            --kt-info: #7239EA;
            --kt-info-light: #F8F5FF;
            --kt-info-active: #5014D0;
            --kt-warning: #FFC700;
            --kt-warning-light: #FFF8DD;
            --kt-warning-active: #F1BC00;
            --kt-danger: #F1416C;
            --kt-danger-light: #FFF5F8;
            --kt-danger-active: #D9214E;
            --kt-dark: #181C32;
            --kt-light: #F9F9F9;
            --kt-light-active: #F3F3F3;
            --kt-gray-100: #F9F9F9;
            --kt-gray-200: #F1F1F2;
            --kt-gray-300: #E1E3EA;
            --kt-gray-400: #B5B5C3;
            --kt-gray-500: #A1A5B7;
            --kt-gray-600: #7E8299;
            --kt-gray-700: #5E6278;
            --kt-gray-800: #3F4254;
            --kt-gray-900: #181C32;
            --kt-body-bg: #FFFFFF;
            --kt-card-bg: #FFFFFF;
            --kt-card-box-shadow: 0px 0px 20px 0px rgba(76, 87, 125, 0.02);
            --kt-font-family: 'Inter', sans-serif;
        }

        body {
            font-family: var(--kt-font-family);
            background-color: var(--kt-light);
            color: var(--kt-gray-800);
        }

        .card {
            box-shadow: var(--kt-card-box-shadow);
            border-radius: 0.625rem;
            border: 1px solid var(--kt-gray-200);
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: 0px 0px 30px 0px rgba(76, 87, 125, 0.05);
        }

        .card-header {
            background-color: transparent;
            border-bottom: 1px solid var(--kt-gray-200);
            padding: 1.5rem 2rem;
        }

        .card-body {
            padding: 2rem;
        }

        .card-footer {
            background-color: transparent;
            border-top: 1px solid var(--kt-gray-200);
            padding: 1.5rem 2rem;
        }

        .btn {
            font-weight: 500;
            border-radius: 0.475rem;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
        }

        .btn-sm {
            padding: 0.25rem 0.75rem;
            font-size: 0.875rem;
        }

        .btn-primary {
            background-color: var(--kt-primary);
            border-color: var(--kt-primary);
        }

        .btn-primary:hover {
            background-color: var(--kt-primary-active);
            border-color: var(--kt-primary-active);
        }

        .btn-success {
            background-color: var(--kt-success);
            border-color: var(--kt-success);
        }

        .btn-success:hover {
            background-color: var(--kt-success-active);
            border-color: var(--kt-success-active);
        }

        .btn-warning {
            background-color: var(--kt-warning);
            border-color: var(--kt-warning);
            color: #ffffff;
        }

        .btn-warning:hover {
            background-color: var(--kt-warning-active);
            border-color: var(--kt-warning-active);
            color: #ffffff;
        }

        .btn-danger {
            background-color: var(--kt-danger);
            border-color: var(--kt-danger);
        }

        .btn-danger:hover {
            background-color: var(--kt-danger-active);
            border-color: var(--kt-danger-active);
        }

        .btn-light {
            background-color: var(--kt-light);
            border-color: var(--kt-light);
            color: var(--kt-gray-700);
        }

        .btn-light:hover {
            background-color: var(--kt-light-active);
            border-color: var(--kt-light-active);
            color: var(--kt-gray-900);
        }

        .btn-outline-primary {
            border-color: var(--kt-primary);
            color: var(--kt-primary);
        }

        .btn-outline-primary:hover {
            background-color: var(--kt-primary);
            border-color: var(--kt-primary);
            color: #ffffff;
        }

        .badge {
            font-weight: 500;
            padding: 0.35rem 0.65rem;
            border-radius: 0.425rem;
        }

        .badge-primary {
            background-color: var(--kt-primary-light);
            color: var(--kt-primary);
        }

        .badge-success {
            background-color: var(--kt-success-light);
            color: var(--kt-success);
        }

        .badge-warning {
            background-color: var(--kt-warning-light);
            color: var(--kt-warning);
        }

        .badge-danger {
            background-color: var(--kt-danger-light);
            color: var(--kt-danger);
        }

        .badge-info {
            background-color: var(--kt-info-light);
            color: var(--kt-info);
        }

        .form-control, .form-select {
            padding: 0.75rem 1rem;
            border-radius: 0.475rem;
            border: 1px solid var(--kt-gray-300);
            color: var(--kt-gray-700);
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--kt-primary);
            box-shadow: 0 0 0 0.25rem rgba(0, 158, 247, 0.25);
        }

        .form-label {
            font-weight: 500;
            color: var(--kt-gray-700);
            margin-bottom: 0.5rem;
        }

        .alert {
            border-radius: 0.625rem;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
        }

        .alert-primary {
            background-color: var(--kt-primary-light);
            border-color: var(--kt-primary-light);
            color: var(--kt-primary);
        }

        .alert-success {
            background-color: var(--kt-success-light);
            border-color: var(--kt-success-light);
            color: var(--kt-success);
        }

        .alert-warning {
            background-color: var(--kt-warning-light);
            border-color: var(--kt-warning-light);
            color: var(--kt-warning);
        }

        .alert-danger {
            background-color: var(--kt-danger-light);
            border-color: var(--kt-danger-light);
            color: var(--kt-danger);
        }

        .alert-info {
            background-color: var(--kt-info-light);
            border-color: var(--kt-info-light);
            color: var(--kt-info);
        }

        .nav-pills .nav-link {
            color: var(--kt-gray-700);
            border-radius: 0.475rem;
            padding: 0.75rem 1.25rem;
            font-weight: 500;
        }

        .nav-pills .nav-link.active {
            background-color: var(--kt-primary);
            color: #ffffff;
        }

        .nav-pills .nav-link:hover:not(.active) {
            background-color: var(--kt-gray-200);
        }

        .modal-content {
            border-radius: 0.625rem;
            border: none;
            box-shadow: 0px 0px 50px 0px rgba(82, 63, 105, 0.15);
        }

        .modal-header {
            border-bottom: 1px solid var(--kt-gray-200);
            padding: 1.5rem 2rem;
        }

        .modal-body {
            padding: 2rem;
        }

        .modal-footer {
            border-top: 1px solid var(--kt-gray-200);
            padding: 1.5rem 2rem;
        }

        .progress {
            height: 0.5rem;
            border-radius: 0.625rem;
            background-color: var(--kt-gray-200);
        }

        .progress-bar {
            border-radius: 0.625rem;
        }

        .table {
            color: var(--kt-gray-700);
        }

        .table th {
            font-weight: 600;
            color: var(--kt-gray-800);
        }

        .symbol {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            position: relative;
            border-radius: 0.475rem;
        }

        .symbol-50px {
            width: 50px;
            height: 50px;
        }

        .symbol-circle {
            border-radius: 50%;
        }

        .symbol-light-primary {
            background-color: var(--kt-primary-light);
            color: var(--kt-primary);
        }

        .symbol-light-success {
            background-color: var(--kt-success-light);
            color: var(--kt-success);
        }

        .symbol-light-warning {
            background-color: var(--kt-warning-light);
            color: var(--kt-warning);
        }

        .symbol-light-danger {
            background-color: var(--kt-danger-light);
            color: var(--kt-danger);
        }

        .symbol-light-info {
            background-color: var(--kt-info-light);
            color: var(--kt-info);
        }

        .bg-light-primary {
            background-color: var(--kt-primary-light) !important;
        }

        .bg-light-success {
            background-color: var(--kt-success-light) !important;
        }

        .bg-light-warning {
            background-color: var(--kt-warning-light) !important;
        }

        .bg-light-danger {
            background-color: var(--kt-danger-light) !important;
        }

        .bg-light-info {
            background-color: var(--kt-info-light) !important;
        }

        .text-primary {
            color: var(--kt-primary) !important;
        }

        .text-success {
            color: var(--kt-success) !important;
        }

        .text-warning {
            color: var(--kt-warning) !important;
        }

        .text-danger {
            color: var(--kt-danger) !important;
        }

        .text-info {
            color: var(--kt-info) !important;
        }

        .text-muted {
            color: var(--kt-gray-600) !important;
        }

        .text-dark {
            color: var(--kt-gray-900) !important;
        }

        .border-dashed {
            border-style: dashed !important;
        }

        .border-primary {
            border-color: var(--kt-primary) !important;
        }

        .border-success {
            border-color: var(--kt-success) !important;
        }

        .border-warning {
            border-color: var(--kt-warning) !important;
        }

        .border-danger {
            border-color: var(--kt-danger) !important;
        }

        .border-info {
            border-color: var(--kt-info) !important;
        }

        .fs-1 {
            font-size: 2.25rem !important;
        }

        .fs-2 {
            font-size: 1.875rem !important;
        }

        .fs-3 {
            font-size: 1.5rem !important;
        }

        .fs-4 {
            font-size: 1.25rem !important;
        }

        .fs-5 {
            font-size: 1.125rem !important;
        }

        .fs-6 {
            font-size: 1rem !important;
        }

        .fs-7 {
            font-size: 0.875rem !important;
        }

        .fs-8 {
            font-size: 0.75rem !important;
        }

        .fw-bold {
            font-weight: 600 !important;
        }

        .fw-bolder {
            font-weight: 700 !important;
        }

        .fw-semibold {
            font-weight: 500 !important;
        }

        .rounded {
            border-radius: 0.475rem !important;
        }

        .rounded-circle {
            border-radius: 50% !important;
        }

        .rounded-pill {
            border-radius: 50rem !important;
        }

        .shadow-sm {
            box-shadow: 0 0.1rem 0.5rem rgba(0, 0, 0, 0.075) !important;
        }

        .shadow {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1) !important;
        }

        .shadow-lg {
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.125) !important;
        }

        .cursor-pointer {
            cursor: pointer !important;
        }

        .min-h-200px {
            min-height: 200px !important;
        }

        .min-h-300px {
            min-height: 300px !important;
        }

        .min-h-400px {
            min-height: 400px !important;
        }

        .min-w-200px {
            min-width: 200px !important;
        }

        .min-w-300px {
            min-width: 300px !important;
        }

        .min-w-400px {
            min-width: 400px !important;
        }

        .max-h-200px {
            max-height: 200px !important;
        }

        .max-h-300px {
            max-height: 300px !important;
        }

        .max-h-400px {
            max-height: 400px !important;
        }

        .max-w-200px {
            max-width: 200px !important;
        }

        .max-w-300px {
            max-width: 300px !important;
        }

        .max-w-400px {
            max-width: 400px !important;
        }

        .h-200px {
            height: 200px !important;
        }

        .h-300px {
            height: 300px !important;
        }

        .h-400px {
            height: 400px !important;
        }

        .w-200px {
            width: 200px !important;
        }

        .w-300px {
            width: 300px !important;
        }

        .w-400px {
            width: 400px !important;
        }

        .rotate-90 {
            transform: rotate(90deg) !important;
        }

        .rotate-180 {
            transform: rotate(180deg) !important;
        }

        .rotate-270 {
            transform: rotate(270deg) !important;
        }

        .flip-horizontal {
            transform: scaleX(-1) !important;
        }

        .flip-vertical {
            transform: scaleY(-1) !important;
        }

        .opacity-0 {
            opacity: 0 !important;
        }

        .opacity-25 {
            opacity: 0.25 !important;
        }

        .opacity-50 {
            opacity: 0.5 !important;
        }

        .opacity-75 {
            opacity: 0.75 !important;
        }

        .opacity-100 {
            opacity: 1 !important;
        }

        .hover-opacity-0:hover {
            opacity: 0 !important;
        }

        .hover-opacity-25:hover {
            opacity: 0.25 !important;
        }

        .hover-opacity-50:hover {
            opacity: 0.5 !important;
        }

        .hover-opacity-75:hover {
            opacity: 0.75 !important;
        }

        .hover-opacity-100:hover {
            opacity: 1 !important;
        }

        .overlay {
            position: relative;
        }

        .overlay::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1;
        }

        .overlay > * {
            position: relative;
            z-index: 2;
        }

        /* Custom styles for radial progress */
        .radial-progress {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .radial-progress-bar {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: conic-gradient(var(--kt-primary) var(--progress-value), var(--kt-gray-200) 0deg);
            mask: radial-gradient(white 55%, transparent 0);
            -webkit-mask: radial-gradient(white 55%, transparent 0);
        }

        .radial-progress-value {
            font-size: 1rem;
            font-weight: 600;
        }

        /* Animation classes */
        .animate-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: .5;
            }
        }

        .transition-all {
            transition-property: all;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            transition-duration: 300ms;
        }

        .scale-95 {
            transform: scale(.95);
        }

        .scale-100 {
            transform: scale(1);
        }

        /* Toast notifications */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }
    </style>

    <div class="d-flex flex-column min-vh-100">
        <!-- Sticky Header -->
        <div class="bg-white shadow-lg">
            <div class="container-fluid py-3 px-4">
                <div class="d-flex justify-content-between align-items-center">
                    <a href="#" class="text-decoration-none d-flex align-items-center">
                        <i class="bi bi-house-door fs-4 me-2 text-primary"></i>
                        <span class="fs-4 fw-bold text-dark">{{ $cluster->Cluster_Name ?? 'Unknown Cluster' }}</span>
                    </a>
                    <button class="btn btn-outline-primary btn-sm rounded-pill" onclick="window.history.back()">
                        <i class="bi bi-arrow-left me-1"></i>
                        Back
                    </button>
                </div>
            </div>
        </div>

        <!-- Analytics Summary Section (Premium Cards) -->
        <div class="container-fluid py-5 px-4">
            <div class="row g-5">
                @php
                    $totalIndicators = $indicators->flatten()->count();
                    $withTarget = 0;
                    foreach ($indicators->flatten() as $indicator) {
                        if ($existingTargets->has($indicator->id)) {
                            $validTargets = $existingTargets[$indicator->id]->filter(function ($target) use ($validStartYear, $isValidRange) {
                                if (!$isValidRange($target->Target_Year)) {
                                    return false;
                                }
                                $parts = explode('-', $target->Target_Year);
                                $startYear = (int)$parts[0];
                                return $startYear >= $validStartYear;
                            });
                            if ($validTargets->count() >= 1) {
                                $withTarget++;
                            }
                        }
                    }
                    $withoutTarget = $totalIndicators - $withTarget;
                    $completionPercentage = $totalIndicators > 0 ? round(($withTarget / $totalIndicators) * 100) : 0;
                @endphp
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="d-flex flex-grow-1 justify-content-between align-items-center">
                                <div>
                                    <h3 class="fs-6 text-gray-600 mb-2">Total Indicators</h3>
                                    <div class="fs-1 fw-bold text-primary">{{ $totalIndicators }}</div>
                                </div>
                                <div class="symbol symbol-50px symbol-circle bg-light-primary">
                                    <i class="bi bi-layers fs-2 text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="d-flex flex-grow-1 justify-content-between align-items-center">
                                <div>
                                    <h3 class="fs-6 text-gray-600 mb-2">With Valid Targets</h3>
                                    <div class="fs-1 fw-bold text-success">{{ $withTarget }}</div>
                                </div>
                                <div class="position-relative">
                                    <div class="symbol symbol-50px symbol-circle bg-light-success">
                                        <i class="bi bi-check-circle fs-2 text-success"></i>
                                    </div>
                                    <div class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success">
                                        {{ $completionPercentage }}%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="d-flex flex-grow-1 justify-content-between align-items-center">
                                <div>
                                    <h3 class="fs-6 text-gray-600 mb-2">Need More Targets</h3>
                                    <div class="fs-1 fw-bold text-warning">{{ $withoutTarget }}</div>
                                </div>
                                <div class="symbol symbol-50px symbol-circle bg-light-warning">
                                    <i class="bi bi-exclamation-circle fs-2 text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Target Timeframe Info Alert -->
            <div class="row mt-5">
                <div class="col-12">
                    <div class="alert alert-info d-flex align-items-center">
                        <i class="bi bi-info-circle fs-4 me-3"></i>
                        <div>
                            <h4 class="alert-heading fs-5 mb-1">Target Timeframe: {{ $validStartYear }} and beyond</h4>
                            <p class="mb-0 fs-7">Each indicator requires at least 1 valid target (as a two-year range, e.g. 2024-2025) from {{ $validStartYear }} onwards. Any target not matching the valid format is ignored.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Strategic Objective Metrics with Premium Graph -->
            <div class="row mt-5">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title fs-4 fw-bold m-0">Strategic Objective Metrics</h3>
                            <div class="d-flex gap-2">
                                <button id="viewAllSOBtn" class="btn btn-sm btn-light-primary">
                                    <i class="bi bi-grid me-1"></i>
                                    View All SOs
                                </button>
                                <button id="explainSOBtn" class="btn btn-sm btn-light-primary">
                                    <i class="bi bi-stars me-1"></i>
                                    Explain Graph
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="soChart" class="h-400px"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content: Tabs & Indicator Cards -->
            <div class="row mt-5">
                <div class="col-12">
                    <!-- Tabs -->
                    <ul class="nav nav-pills mb-5 bg-light p-2 rounded" id="objectiveTabs">
                        <li class="nav-item">
                            <button class="nav-link active" data-objective="all">
                                <i class="bi bi-layers me-1"></i>
                                All
                            </button>
                        </li>
                        @foreach ($strategicObjectives as $objective)
                            <li class="nav-item">
                                <button class="nav-link" data-objective="{{ $objective }}">
                                    <i class="bi bi-tag me-1"></i>
                                    {{ $objective }}
                                </button>
                            </li>
                        @endforeach
                    </ul>

                    <!-- Indicator Cards -->
                    <div class="row g-5" id="indicatorsGrid">
                        @foreach ($indicators as $objective => $indicatorGroup)
                            @foreach ($indicatorGroup as $indicator)
                                <div class="col-md-6 transition-all" data-objective="{{ $objective }}">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-4">
                                                <div>
                                                    <h3 class="fs-5 fw-bold mb-1">Indicator {{ $indicator->Indicator_Number }}</h3>
                                                    <p class="text-muted mb-1">{{ $indicator->Indicator_Name }}</p>
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-info-circle me-1 text-muted"></i>
                                                        <span class="fs-8 text-muted">{{ $indicator->ResponseType }}</span>
                                                    </div>
                                                </div>
                                                <span class="badge bg-light-primary">{{ $objective }}</span>
                                            </div>

                                            <!-- Existing Targets Display -->
                                            <div class="mb-4">
                                                @if ($existingTargets->has($indicator->id))
                                                    @foreach ($existingTargets[$indicator->id] as $target)
                                                        @php
                                                            if (!preg_match('/^\d{4}-\d{4}$/', $target->Target_Year)) {
                                                                continue;
                                                            }
                                                            $parts = explode('-', $target->Target_Year);
                                                            $startYear = (int)$parts[0];
                                                            $isLegacy = $startYear < $validStartYear;
                                                        @endphp
                                                        <div class="d-flex align-items-center justify-content-between p-3 mb-2 rounded {{ $isLegacy ? 'bg-light' : 'bg-light-primary' }}">
                                                            <span class="fw-semibold">{{ $target->Target_Year }}:</span>
                                                            <span class="fw-semibold">{{ $target->Target_Value }}</span>
                                                            <div class="d-flex gap-1">
                                                                @if ($isLegacy)
                                                                    <span class="badge bg-secondary">Legacy</span>
                                                                @else
                                                                    @if(Auth::user()->AccountRole == 'Admin')
                                                                        <button class="btn btn-sm btn-icon btn-light edit-target-btn" data-indicator-id="{{ $indicator->id }}" data-target-id="{{ $target->id }}">
                                                                            <i class="bi bi-pencil"></i>
                                                                        </button>
                                                                    @endif
                                                                    @if(Auth::user()->AccountRole == 'Admin')
                                                                        <button class="btn btn-sm btn-icon btn-light-danger delete-target-btn" data-target-id="{{ $target->id }}">
                                                                            <i class="bi bi-trash"></i>
                                                                        </button>
                                                                    @endif
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <div class="p-3 text-center bg-light rounded">
                                                        <span class="text-muted">No targets configured</span>
                                                    </div>
                                                @endif
                                            </div>

                                            @if(Auth::user()->AccountRole == 'Admin')
                                                <!-- Set Target Button -->
                                                <button class="btn btn-primary w-100 set-target-btn" data-indicator-id="{{ $indicator->id }}">
                                                    <i class="bi bi-plus-circle me-1"></i>
                                                    Set Target
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Full Screen Target Graph Modal -->
    <div class="modal fade" id="targetGraphModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="graphModalTitle">Targets for Indicator</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="closeTargetGraphBtn"></button>
                </div>
                <!-- Modal Body -->
                <div class="modal-body">
                    <div class="alert alert-info d-flex align-items-center mb-5">
                        <i class="bi bi-info-circle fs-4 me-3"></i>
                        <div>
                            <h4 class="alert-heading fs-5 mb-1">Valid Target Ranges (e.g. 2024-2025)</h4>
                            <p class="mb-0 fs-7">Each indicator requires at least 1 valid target (a two-year range) from {{ $validStartYear }} onwards. Any target not matching the valid format is ignored.</p>
                        </div>
                    </div>
                    <div id="targetLineChart" class="h-400px mb-5"></div>
                    <div class="mt-4">
                        <h4 class="fs-5 fw-bold mb-3">Target Status</h4>
                        <div id="targetStatusInfo"></div>
                    </div>
                </div>
                <!-- Modal Footer -->
                <div class="modal-footer">
                    <form id="targetGraphForm" method="POST" action="" class="w-100">
                        @csrf
                        <div id="methodOverride"></div>
                        <input type="hidden" name="ClusterID" value="{{ $cluster->ClusterID }}">
                        <input type="hidden" id="graphIndicatorID" name="IndicatorID">
                        <input type="hidden" id="graphResponseType" name="ResponseType">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="graphTargetYear" class="form-label">Target Range</label>
                                    <select name="Target_Year" class="form-select" required id="graphTargetYear">
                                        @foreach($validRanges as $range)
                                            <option value="{{ $range }}">{{ $range }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <!-- Target Value input rendered dynamically based on response type -->
                            <div class="col-md-6">
                                <div id="graphTargetValueContainer">
                                    <!-- Content injected via JavaScript -->
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-3 mt-5">
                            <button type="button" class="btn btn-light" id="cancelTargetBtn" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary" id="graphSubmitButton">Save Target</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Target Confirmation Modal -->
    <div class="modal fade" id="deleteTargetModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this target? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <form method="POST" id="deleteTargetForm">
                        @csrf
                        @method('DELETE')
                        <div class="d-flex gap-3">
                            <button type="button" class="btn btn-light" id="cancelDeleteBtn" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Full Screen Strategic Objectives Modal -->
    <div class="modal fade" id="allSOModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">All Strategic Objectives</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="closeAllSOBtn"></button>
                </div>
                <!-- Modal Body -->
                <div class="modal-body">
                    <!-- Target Timeframe Info Alert -->
                    <div class="alert alert-info d-flex align-items-center mb-5">
                        <i class="bi bi-info-circle fs-4 me-3"></i>
                        <div>
                            <h4 class="alert-heading fs-5 mb-1">Target Timeframe: {{ $validStartYear }} - {{ $validEndYear }}</h4>
                            <p class="mb-0 fs-7">Each indicator requires at least 1 valid target (a two-year range) within this timeframe for a strategic objective to be considered complete.</p>
                        </div>
                    </div>

                    <!-- Search and Filter -->
                    <div class="position-sticky top-0 bg-white py-3 mb-5 z-index-3">
                        <div class="position-relative">
                            <input type="text" id="soSearchInput" class="form-control" placeholder="Search strategic objectives...">
                            <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        </div>
                    </div>

                    <!-- Grid of SO Cards -->
                    <div class="row g-5" id="soCardsGrid">
                        @foreach ($indicators as $objective => $indicatorGroup)
                            @php
                                $totalForSO = count($indicatorGroup);
                                $withTargetForSO = 0;
                                foreach ($indicatorGroup as $indicator) {
                                    if ($existingTargets->has($indicator->id)) {
                                        $validTargets = $existingTargets[$indicator->id]->filter(function ($target) use ($validStartYear, $isValidRange) {
                                            if (!$isValidRange($target->Target_Year)) {
                                                return false;
                                            }
                                            $parts = explode('-', $target->Target_Year);
                                            $startYear = (int)$parts[0];
                                            return $startYear >= $validStartYear;
                                        });
                                        if ($validTargets->count() >= 1) {
                                            $withTargetForSO++;
                                        }
                                    }
                                }
                                $withoutTargetForSO = $totalForSO - $withTargetForSO;
                                $percentComplete = $totalForSO > 0 ? round(($withTargetForSO / $totalForSO) * 100) : 0;
                            @endphp
                            <div class="col-md-4 so-card" data-so-name="{{ $objective }}">
                                <div class="card h-100 transition-all">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-4">
                                            <h3 class="fs-5 fw-bold d-flex align-items-center m-0">
                                                <i class="bi bi-tag me-2 text-primary"></i>
                                                {{ $objective }}
                                            </h3>
                                            <button class="btn btn-icon btn-sm btn-light-primary explain-so-detail-btn" data-objective="{{ $objective }}">
                                                <i class="bi bi-stars text-primary"></i>
                                            </button>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mb-4">
                                            <div class="position-relative d-inline-block" style="width: 100px; height: 100px;">
                                                <div class="position-absolute top-0 start-0 w-100 h-100 rounded-circle" style="background: conic-gradient(var(--kt-primary) {{ $percentComplete }}%, var(--kt-gray-200) 0deg); mask: radial-gradient(white 55%, transparent 0); -webkit-mask: radial-gradient(white 55%, transparent 0);"></div>
                                                <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center">
                                                    <span class="fs-4 fw-bold text-primary">{{ $percentComplete }}%</span>
                                                </div>
                                            </div>
                                            <div class="d-flex flex-column gap-2">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="text-muted fs-7">Total:</span>
                                                    <span class="badge bg-light-primary">{{ $totalForSO }}</span>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="text-muted fs-7">With Target:</span>
                                                    <span class="badge bg-light-success">{{ $withTargetForSO }}</span>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="text-muted fs-7">Without:</span>
                                                    <span class="badge bg-light-warning">{{ $withoutTargetForSO }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $percentComplete }}%" aria-valuenow="{{ $percentComplete }}" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- AI Explanation Modal -->
    <div class="modal fade" id="aiExplanationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="aiModalTitle">Graph Explanation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="closeAIExplanationBtn"></button>
                </div>
                <!-- Modal Body -->
                <div class="modal-body">
                    <div class="d-flex align-items-center gap-4 mb-5">
                        <div class="symbol symbol-50px symbol-circle bg-light-primary">
                            <i class="bi bi-stars fs-2 text-primary"></i>
                        </div>
                        <div>
                            <h4 class="fs-5 fw-bold mb-1">AI Insights</h4>
                            <p class="text-muted mb-0 fs-7">Data interpretation and analysis</p>
                        </div>
                    </div>
                    <div id="aiExplanationContent" class="bg-light p-5 rounded border">
                        <div class="animate-pulse">
                            <div class="bg-secondary bg-opacity-25 h-15px w-75 mb-3 rounded"></div>
                            <div class="bg-secondary bg-opacity-25 h-15px w-50 mb-3 rounded"></div>
                            <div class="bg-secondary bg-opacity-25 h-15px w-85 mb-3 rounded"></div>
                            <div class="bg-secondary bg-opacity-25 h-15px w-65 mb-3 rounded"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification Toast -->
    <div class="toast-container">
        @if (session('notifications'))
            <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    <strong class="me-auto">Notification</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body bg-{{ session('notifications.type') }} text-white">
                    {{ session('notifications.message') }}
                </div>
            </div>
        @endif
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        function safeJSONParse(jsonString, fallback = {}) {
            try {
                return JSON.parse(jsonString);
            } catch (error) {
                console.error('Error parsing JSON:', error);
                return fallback;
            }
        }
        const targetsData = safeJSONParse('{!! addslashes($targetsDataJson) !!}', {});
        const indicatorsData = safeJSONParse('{!! addslashes($indicatorsDataJson) !!}', {});
        const soData = safeJSONParse('{!! addslashes($soDataJson) !!}', {});

        const soCategories = {!! json_encode($soCategories, JSON_HEX_APOS | JSON_HEX_QUOT) !!};
        const soWithTargets = {!! json_encode($soWithTargets, JSON_HEX_APOS | JSON_HEX_QUOT) !!};
        const soWithoutTargets = {!! json_encode($soWithoutTargets, JSON_HEX_APOS | JSON_HEX_QUOT) !!};

        const validStartYear = {{ $validStartYear }};
        const validEndYear = {{ $validEndYear }};

        const colors = {
            primary: '#009EF7',
            success: '#50CD89',
            warning: '#FFC700',
            danger: '#F1416C',
            info: '#7239EA',
            background: '#ffffff',
            backgroundDark: '#1e293b',
            text: '#181C32',
            textDark: '#f8fafc'
        };

        const prefersDarkMode = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        if (prefersDarkMode) { document.documentElement.classList.add('dark'); }

        function filterIndicators(objective) {
            const tabs = document.querySelectorAll('#objectiveTabs .nav-link');
            tabs.forEach(tab => {
                tab.classList.toggle('active', tab.dataset.objective === objective);
            });

            const cards = document.querySelectorAll('#indicatorsGrid [data-objective]');
            cards.forEach(card => {
                if (objective === 'all' || card.dataset.objective === objective) {
                    card.style.display = 'block';
                    card.classList.remove('opacity-0', 'scale-95');
                    card.classList.add('opacity-100', 'scale-100');
                } else {
                    card.classList.remove('opacity-100', 'scale-100');
                    card.classList.add('opacity-0', 'scale-95');
                    setTimeout(() => card.style.display = 'none', 300);
                }
            });
        }

        function getChartTheme() {
            const isDark = document.documentElement.classList.contains('dark');
            return {
                mode: isDark ? 'dark' : 'light',
                palette: 'palette1',
                monochrome: { enabled: false }
            };
        }

        function initMainChart() {
            var soChartOptions = {
                chart: {
                    type: 'bar',
                    height: 350,
                    stacked: true,
                    toolbar: {
                        show: true,
                        tools: {
                            download: true,
                            selection: false,
                            zoom: false,
                            zoomin: false,
                            zoomout: false,
                            pan: false,
                            reset: false
                        }
                    },
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800,
                        dynamicAnimation: {
                            enabled: true,
                            speed: 350
                        }
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        borderRadius: 8,
                        columnWidth: '60%',
                        dataLabels: {
                            position: 'top'
                        }
                    }
                },
                theme: getChartTheme(),
                stroke: { width: 2, colors: ['transparent'] },
                dataLabels: { enabled: false },
                series: [
                    { name: 'With Valid Targets', data: soWithTargets },
                    { name: 'Need More Targets', data: soWithoutTargets }
                ],
                xaxis: {
                    categories: soCategories,
                    labels: {
                        style: {
                            fontSize: '12px',
                            fontFamily: 'var(--kt-font-family)'
                        }
                    }
                },
                yaxis: {
                    title: {
                        text: 'Number of Indicators',
                        style: {
                            fontSize: '14px',
                            fontFamily: 'var(--kt-font-family)'
                        }
                    }
                },
                fill: {
                    opacity: 1,
                    colors: [colors.success, colors.warning]
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return val + " indicators";
                        }
                    }
                },
                title: {
                    text: 'Target Setting Status by Strategic Objective',
                    align: 'left',
                    style: {
                        fontSize: '16px',
                        fontWeight: 600,
                        fontFamily: 'var(--kt-font-family)'
                    }
                },
                legend: {
                    position: 'top',
                    fontSize: '14px',
                    fontFamily: 'var(--kt-font-family)',
                    labels: {
                        colors: document.documentElement.classList.contains('dark') ? colors.textDark : colors.text
                    }
                },
                grid: {
                    borderColor: document.documentElement.classList.contains('dark') ? '#334155' : '#e2e8f0',
                    strokeDashArray: 5
                }
            };

            if (window.soChart && typeof window.soChart.destroy === 'function') {
                window.soChart.destroy();
            }

            window.soChart = new ApexCharts(document.querySelector("#soChart"), soChartOptions);
            window.soChart.render();
        }

        // Bootstrap modal objects
        let targetGraphModal, deleteTargetModal, aiExplanationModal, allSOModal;
        let lineChart = null;

        function openTargetGraphModal(mode, indicatorId, targetId = null) {
            try {
                const indicator = indicatorsData[indicatorId];
                if (!indicator) {
                    console.error('Indicator not found:', indicatorId);
                    return;
                }

                document.getElementById('graphIndicatorID').value = indicator.id;
                document.getElementById('graphResponseType').value = indicator.responseType;

                const form = document.getElementById('targetGraphForm');
                const methodOverrideDiv = document.getElementById('methodOverride');
                const targetValueContainer = document.getElementById('graphTargetValueContainer');

                switch (indicator.responseType) {
                    case 'Number':
                        targetValueContainer.innerHTML = '<label class="form-label" for="graphTargetValue">Target Value</label><input type="number" name="Target_Value" class="form-control" min="0" required id="graphTargetValue">';
                        break;
                    case 'Boolean':
                        targetValueContainer.innerHTML = '<label class="form-label" for="graphTargetValue">Target Value</label><select name="Target_Value" class="form-select" required id="graphTargetValue"><option value="true">True</option><option value="false">False</option></select>';
                        break;
                    case 'Yes/No':
                        targetValueContainer.innerHTML = '<label class="form-label" for="graphTargetValue">Target Value</label><select name="Target_Value" class="form-select" required id="graphTargetValue"><option value="Yes">Yes</option><option value="No">No</option></select>';
                        break;
                    case 'Text':
                    default:
                        targetValueContainer.innerHTML = '<label class="form-label" for="graphTargetValue">Target Value</label><input type="text" name="Target_Value" class="form-control" required id="graphTargetValue">';
                        break;
                }

                if (mode === 'edit') {
                    const targetToEdit = indicator.allTargets.find(t => t.id == targetId);
                    if (!targetToEdit) {
                        console.error('Target not found for edit mode');
                        return;
                    }

                    if (targetToEdit.isLegacy) {
                        alert('Legacy targets cannot be edited. Please create new targets within the valid timeframe.');
                        return;
                    }

                    form.action = `{{ url('/targets') }}/${targetId}`;
                    methodOverrideDiv.innerHTML = `<input type="hidden" name="_method" value="PUT">`;
                    document.getElementById('graphTargetYear').value = targetToEdit.year;
                    document.getElementById('graphTargetValue').value = targetToEdit.value;
                    document.getElementById('graphSubmitButton').innerText = 'Update Target';
                } else {
                    form.action = `{{ route('targets.store') }}`;
                    methodOverrideDiv.innerHTML = ``;
                    document.getElementById('graphTargetYear').selectedIndex = 0;
                    document.getElementById('graphTargetValue').value = '';
                    document.getElementById('graphSubmitButton').innerText = 'Save Target';
                }

                document.getElementById('graphModalTitle').innerText = `Targets for: ${indicator.name}`;

                const allTargets = [...indicator.allTargets].sort((a, b) =>
                    parseInt(a.year.split('-')[0]) - parseInt(b.year.split('-')[0])
                );

                updateTargetStatusInfo(indicator);

                if (lineChart) {
                    lineChart.destroy();
                }

                const validTargets = allTargets.filter(t => !t.isLegacy);
                const legacyTargets = allTargets.filter(t => t.isLegacy);

                lineChart = new ApexCharts(document.querySelector("#targetLineChart"), {
                    chart: {
                        type: 'line',
                        height: 320,
                        animations: {
                            enabled: true,
                            easing: 'easeinout',
                            speed: 800
                        },
                        toolbar: {
                            show: true,
                            tools: {
                                download: true
                            }
                        }
                    },
                    theme: getChartTheme(),
                    series: [
                        {
                            name: 'Valid Targets',
                            data: validTargets.map(item => item.value)
                        },
                        {
                            name: 'Legacy Targets',
                            data: legacyTargets.map(item => item.value)
                        }
                    ],
                    xaxis: {
                        categories: allTargets.map(item => item.year),
                        title: {
                            text: 'Target Range',
                            style: {
                                fontSize: '14px',
                                fontFamily: 'var(--kt-font-family)'
                            }
                        },
                        labels: {
                            style: {
                                fontSize: '12px',
                                fontFamily: 'var(--kt-font-family)'
                            }
                        }
                    },
                    yaxis: {
                        title: {
                            text: 'Target Value',
                            style: {
                                fontSize: '14px',
                                fontFamily: 'var(--kt-font-family)'
                            }
                        },
                        labels: {
                            style: {
                                fontSize: '12px',
                                fontFamily: 'var(--kt-font-family)'
                            }
                        }
                    },
                    title: {
                        text: `Target Trend: ${indicator.name}`,
                        align: 'left',
                        style: {
                            fontSize: '16px',
                            fontWeight: 600,
                            fontFamily: 'var(--kt-font-family)'
                        }
                    },
                    colors: [colors.primary, colors.warning],
                    stroke: {
                        curve: 'smooth',
                        width: 3
                    },
                    markers: {
                        size: 6,
                        strokeWidth: 0,
                        hover: {
                            size: 8
                        }
                    },
                    grid: {
                        borderColor: document.documentElement.classList.contains('dark') ? '#334155' : '#e2e8f0',
                        strokeDashArray: 5
                    },
                    tooltip: {
                        x: {
                            format: 'yyyy'
                        }
                    },
                    annotations: {
                        xaxis: [{
                            x: validStartYear,
                            borderColor: colors.info,
                            label: {
                                text: 'Valid Start',
                                style: {
                                    color: '#fff',
                                    background: colors.info
                                }
                            }
                        }]
                    }
                });

                lineChart.render();
                targetGraphModal.show();
            } catch (error) {
                console.error('Error opening target graph modal:', error);
                alert('An error occurred while opening the target graph. Please try again.');
            }
        }

        function updateTargetStatusInfo(indicator) {
            const validTargets = indicator.validTargets || [];
            const legacyTargets = indicator.legacyTargets || [];
            const hasValidTargets = indicator.hasValidTargets;
            let statusHtml = '';

            if (hasValidTargets) {
                statusHtml = `
                <div class="alert alert-success d-flex align-items-center mb-4">
                    <i class="bi bi-check-circle fs-4 me-3"></i>
                    <div>This indicator has ${validTargets.length} valid target (two-year range) from 2024 onwards (minimum of 1 required).</div>
                </div>
                `;
            } else {
                const neededTargets = Math.max(0, 1 - validTargets.length);
                statusHtml = `
                <div class="alert alert-warning d-flex align-items-center mb-4">
                    <i class="bi bi-exclamation-triangle fs-4 me-3"></i>
                    <div>This indicator needs ${neededTargets} more target (two-year range) from 2024 onwards. Currently has ${validTargets.length} valid target.</div>
                </div>
                `;
            }

            if (legacyTargets.length > 0) {
                statusHtml += `
                <div class="alert alert-info d-flex align-items-center mb-4">
                    <i class="bi bi-info-circle fs-4 me-3"></i>
                    <div>This indicator has ${legacyTargets.length} legacy target (two-year range) from before 2024. Legacy targets cannot be edited or deleted and are not counted.</div>
                </div>
                `;
            }

            document.getElementById('targetStatusInfo').innerHTML = statusHtml;
        }

        function openDeleteTargetModal(targetId) {
            const deleteForm = document.getElementById('deleteTargetForm');
            deleteForm.action = `{{ url('/targets') }}/${targetId}`;
            deleteTargetModal.show();
        }

        let previousModalId = null;

        function filterSOCards() {
            const searchTerm = document.getElementById('soSearchInput').value.toLowerCase();
            const cards = document.querySelectorAll('.so-card');

            cards.forEach(card => {
                const soName = card.dataset.soName.toLowerCase();
                card.style.display = soName.includes(searchTerm) ? 'block' : 'none';
            });
        }

        function openAIExplanationModal(title, objective) {
            try {
                const aiModalTitle = document.getElementById('aiModalTitle');
                const aiExplanationContent = document.getElementById('aiExplanationContent');

                aiModalTitle.textContent = title;
                aiExplanationContent.innerHTML = `
                    <div class="animate-pulse">
                        <div class="bg-secondary bg-opacity-25 h-15px w-75 mb-3 rounded"></div>
                        <div class="bg-secondary bg-opacity-25 h-15px w-50 mb-3 rounded"></div>
                        <div class="bg-secondary bg-opacity-25 h-15px w-85 mb-3 rounded"></div>
                        <div class="bg-secondary bg-opacity-25 h-15px w-65 mb-3 rounded"></div>
                    </div>
                `;

                aiExplanationModal.show();

                if (objective) {
                    generateSOSpecificInsights(objective);
                } else {
                    generateOverallInsights();
                }
            } catch (error) {
                console.error('Error opening AI explanation modal:', error);
            }
        }

        function generateOverallInsights() {
            try {
                setTimeout(() => {
                    const soDataAnalysis = Object.values(soData);
                    let totalIndicators = {{ $indicators->flatten()->count() }};
                    let withTarget = 0;

                    soDataAnalysis.forEach(so => {
                        withTarget += so.withTarget;
                    });

                    let withoutTarget = totalIndicators - withTarget;
                    let percentComplete = Math.round((withTarget / totalIndicators) * 100);

                    soDataAnalysis.sort((a, b) => b.percentComplete - a.percentComplete);
                    const bestSO = soDataAnalysis[0];
                    const worstSO = soDataAnalysis[soDataAnalysis.length - 1];
                    const top5 = soDataAnalysis.slice(0, 5);
                    const bottom5 = [...soDataAnalysis].sort((a, b) => a.percentComplete - b.percentComplete).slice(0, 5);

                    let insights = `
                    <h3 class="fs-4 fw-bold mb-4">Target Setting Analysis for {{ $cluster->Cluster_Name }}</h3>

                    <div class="bg-light-info p-4 mb-5 rounded border border-info border-opacity-25">
                        <p class="fw-semibold text-info mb-2">About This Analysis</p>
                        <p class="fs-7">This analysis focuses on <strong>target setting progress</strong> using two-year ranges. A strategic objective is considered complete when:</p>
                        <ul class="ps-4 mt-2 fs-7">
                            <li>Each indicator has <strong>at least 1 valid target</strong> (as a two-year range starting from 2024)</li>
                            <li>Only targets with a starting year from 2024 and later are counted as valid</li>
                            <li>Any target not matching the valid format is ignored or considered legacy</li>
                        </ul>
                    </div>

                    <div class="bg-light p-4 mb-5 rounded">
                        <div class="row text-center">
                            <div class="col-md-3 mb-3">
                                <div class="fs-1 fw-bold text-primary">${percentComplete}%</div>
                                <div class="fs-7 text-muted">Target Setting Progress</div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="fs-1 fw-bold text-success">${withTarget}</div>
                                <div class="fs-7 text-muted">With Valid Targets</div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="fs-1 fw-bold text-warning">${withoutTarget}</div>
                                <div class="fs-7 text-muted">Need More Targets</div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="fs-1 fw-bold text-info">${soDataAnalysis.length}</div>
                                <div class="fs-7 text-muted">Strategic Objectives</div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-5">
                        <div class="col-md-6 mb-4">
                            <div class="bg-light-success p-4 rounded border border-success border-opacity-25 h-100">
                                <h4 class="fs-6 fw-semibold text-success mb-3">Indicators with Valid Targets</h4>
                                <div class="overflow-auto" style="max-height: 240px;">
                                    <ul class="list-unstyled">
                                        ${top5.map(so => `
                                            <li class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="fs-7 fw-semibold">${so.objective}</span>
                                                <span class="badge bg-light-success">${so.percentComplete}%</span>
                                            </li>
                                        `).join('')}
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="bg-light-warning p-4 rounded border border-warning border-opacity-25 h-100">
                                <h4 class="fs-6 fw-semibold text-warning mb-3">Needs Target Setting Attention</h4>
                                <div class="overflow-auto" style="max-height: 240px;">
                                    <ul class="list-unstyled">
                                        ${bottom5.map(so => `
                                            <li class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="fs-7 fw-semibold">${so.objective}</span>
                                                <span class="badge bg-light-warning">${so.percentComplete}%</span>
                                            </li>
                                        `).join('')}
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h4 class="fs-5 fw-bold mb-3">Strategic Recommendations:</h4>
                    <ul class="mb-5">
                        <li class="mb-2">Prioritize setting at least 1 valid target (two-year range) from 2024 onwards for indicators in ${worstSO.objective}</li>
                        <li class="mb-2">Review any legacy or invalid targets and consider setting a new valid target from 2024 onwards</li>
                        <li class="mb-2">Consider replicating the target-setting approach from ${bestSO.objective} to other areas</li>
                    </ul>

                    <div class="bg-light-primary p-4 rounded border border-primary border-opacity-25">
                        <p class="fw-semibold text-primary mb-0">This analysis focuses on target setting completeness using two-year ranges. Valid target setting is essential for effective monitoring and evaluation.</p>
                    </div>
                    `;

                    document.getElementById('aiExplanationContent').innerHTML = insights;
                }, 1500);
            } catch (error) {
                console.error('Error generating overall insights:', error);
                document.getElementById('aiExplanationContent').innerHTML = '<p class="text-danger">An error occurred while generating insights. Please try again.</p>';
            }
        }

        function generateSOSpecificInsights(objective) {
            try {
                setTimeout(() => {
                    const soInfo = soData[objective];
                    if (!soInfo) {
                        console.error('Strategic objective not found:', objective);
                        return;
                    }

                    let detailedAnalysis = `
                    <h3 class="fs-4 fw-bold mb-4">${objective} - Target Setting Analysis</h3>

                    <div class="bg-light-info p-4 mb-5 rounded border border-info border-opacity-25">
                        <p class="fw-semibold text-info mb-2">About This Analysis</p>
                        <p class="fs-7">This analysis focuses on <strong>target setting progress</strong> using two-year ranges. An indicator is considered complete when:</p>
                        <ul class="ps-4 mt-2 fs-7">
                            <li>It has <strong>at least 1 valid target</strong> (as a two-year range starting from 2024)</li>
                            <li>Only targets with a starting year from 2024 and later are counted as valid</li>
                            <li>Any target not matching the valid format is ignored or considered legacy</li>
                        </ul>
                    </div>

                    <div class="bg-light p-4 mb-5 rounded">
                        <div class="row text-center">
                            <div class="col-md-3 mb-3">
                                <div class="fs-1 fw-bold text-primary">${soInfo.percentComplete}%</div>
                                <div class="fs-7 text-muted">Target Setting Progress</div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="fs-1 fw-bold text-success">${soInfo.withTarget}</div>
                                <div class="fs-7 text-muted">With Valid Targets</div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="fs-1 fw-bold text-warning">${soInfo.withoutTarget}</div>
                                <div class="fs-7 text-muted">Need More Targets</div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="fs-1 fw-bold text-info">${soInfo.total}</div>
                                <div class="fs-7 text-muted">Total Indicators</div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-5">
                        <div class="col-md-6 mb-4">
                            <div class="bg-light-success p-4 rounded border border-success border-opacity-25 h-100">
                                <h4 class="fs-6 fw-semibold text-success mb-3">Indicators with Valid Targets</h4>
                                <div class="overflow-auto" style="max-height: 240px;">
                                    ${soInfo.indicatorsWithTargets.length > 0 ? `
                                        <ul class="list-unstyled">
                                            ${soInfo.indicatorsWithTargets.map(indicator => {
                                                let validRanges = indicator.validTargets.map(t => t.year).join(', ');
                                                let legacyRanges = indicator.legacyTargets.length > 0 ?
                                                    `<div class="mt-1 fs-8 text-muted">Legacy ranges (before 2024): ${indicator.legacyTargets.map(t => t.year).join(', ')}</div>` : '';
                                                return `
                                                    <li class="mb-3">
                                                        <div class="fw-semibold">${indicator.number}</div>
                                                        <div class="fs-8 text-muted">${indicator.name}</div>
                                                        <div class="mt-1 fs-8 text-success">Valid ranges (2024+): ${validRanges}</div>
                                                        ${legacyRanges}
                                                    </li>
                                                `;
                                            }).join('')}
                                        </ul>
                                    ` : `<p class="fs-7 text-muted">No indicators have valid targets set for this objective</p>`}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="bg-light-warning p-4 rounded border border-warning border-opacity-25 h-100">
                                <h4 class="fs-6 fw-semibold text-warning mb-3">Indicators Needing More Targets</h4>
                                <div class="overflow-auto" style="max-height: 240px;">
                                    ${soInfo.indicatorsWithoutTargets.length > 0 ? `
                                        <ul class="list-unstyled">
                                            ${soInfo.indicatorsWithoutTargets.map(indicator => {
                                                let validTargetsHtml = '';
                                                let legacyTargetsHtml = '';

                                                if (indicator.validTargets && indicator.validTargets.length > 0) {
                                                    validTargetsHtml = `
                                                        <div class="mt-1 fs-8 text-muted">
                                                            Current valid ranges (2024+): ${indicator.validTargets.map(t => t.year).join(', ')}
                                                        </div>
                                                    `;
                                                }

                                                if (indicator.legacyTargets && indicator.legacyTargets.length > 0) {
                                                    legacyTargetsHtml = `
                                                        <div class="mt-1 fs-8 text-muted">
                                                            Legacy ranges (before 2024): ${indicator.legacyTargets.map(t => t.year).join(', ')}
                                                        </div>
                                                    `;
                                                }

                                                return `
                                                    <li class="mb-3">
                                                        <div class="fw-semibold">${indicator.number}</div>
                                                        <div class="fs-8 text-muted">${indicator.name}</div>
                                                        ${validTargetsHtml}
                                                        ${legacyTargetsHtml}
                                                        <div class="mt-1 fs-8 text-warning d-flex align-items-center">
                                                            <i class="bi bi-exclamation-triangle me-1"></i>
                                                            ${indicator.reason}
                                                        </div>
                                                    </li>
                                                `;
                                            }).join('')}
                                        </ul>
                                    ` : `<p class="fs-7 text-muted">All indicators have valid targets set for this objective - Great job!</p>`}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-light-primary p-4 rounded border border-primary border-opacity-25">
                        <p class="fw-semibold text-primary mb-0">This analysis focuses on target setting completeness using two-year ranges. Valid target setting is essential for effective monitoring and evaluation.</p>
                    </div>
                    `;

                    document.getElementById('aiExplanationContent').innerHTML = detailedAnalysis;
                }, 1200);
            } catch (error) {
                console.error('Error generating SO specific insights:', error);
                document.getElementById('aiExplanationContent').innerHTML = '<p class="text-danger">An error occurred while generating insights. Please try again.</p>';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            try {
                // Initialize Bootstrap modals
                targetGraphModal = new bootstrap.Modal(document.getElementById('targetGraphModal'));
                deleteTargetModal = new bootstrap.Modal(document.getElementById('deleteTargetModal'));
                aiExplanationModal = new bootstrap.Modal(document.getElementById('aiExplanationModal'));
                allSOModal = new bootstrap.Modal(document.getElementById('allSOModal'));

                // Initialize main chart
                initMainChart();

                // Event listeners
                document.addEventListener('click', function(e) {
                    if (e.target.closest('.set-target-btn')) {
                        const btn = e.target.closest('.set-target-btn');
                        openTargetGraphModal('create', btn.dataset.indicatorId);
                    }

                    if (e.target.closest('.edit-target-btn')) {
                        const btn = e.target.closest('.edit-target-btn');
                        openTargetGraphModal('edit', btn.dataset.indicatorId, btn.dataset.targetId);
                    }

                    if (e.target.closest('.delete-target-btn')) {
                        const btn = e.target.closest('.delete-target-btn');
                        openDeleteTargetModal(btn.dataset.targetId);
                    }

                    if (e.target.closest('#objectiveTabs .nav-link')) {
                        const tab = e.target.closest('.nav-link');
                        filterIndicators(tab.dataset.objective);
                    }

                    if (e.target.closest('.explain-so-detail-btn')) {
                        const btn = e.target.closest('.explain-so-detail-btn');
                        const objective = btn.dataset.objective;

                        if (allSOModal._element.classList.contains('show')) {
                            previousModalId = 'allSOModal';
                            allSOModal.hide();
                        }

                        openAIExplanationModal(`${objective} Analysis`, objective);
                    }
                });

                document.getElementById('viewAllSOBtn').addEventListener('click', function() {
                    allSOModal.show();
                });

                document.getElementById('explainSOBtn').addEventListener('click', function() {
                    openAIExplanationModal('Strategic Objective Metrics Analysis', null);
                });

                document.getElementById('soSearchInput').addEventListener('input', filterSOCards);

                // Handle dark mode changes
                window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
                    if (e.matches) {
                        document.documentElement.classList.add('dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                    }

                    initMainChart();
                });

                // Initialize toasts
                const toastElList = [].slice.call(document.querySelectorAll('.toast'));
                toastElList.map(function(toastEl) {
                    return new bootstrap.Toast(toastEl, {
                        autohide: true,
                        delay: 5000
                    });
                });
            } catch (error) {
                console.error('Error initializing application:', error);
                alert('An error occurred while initializing the application. Please refresh the page and try again.');
            }
        });
    </script>

