<?php
namespace App\Http\Controllers;

use DB;

class EcsahcTimelines extends Controller
{
    public function MgtEcsaTimelines()
    {
        $data = [

            "Desc"      => "Manage all ECSA-HC reporting Timelines",
            "Page"      => "timelines.MgtEcsaTimelines",
            "timelines" => DB::table("ecsahc_timelines")->get(),

        ];

        return view('scrn', $data);
    }

    public function MgtEcsaTimelinesStatus()
    {
        $data = [

            "Desc"      => "Activate/Disable ECSA-HC Reporting Timelines",
            "Page"      => "timelines.EcsaTimelineStatus",
            "timelines" => DB::table("ecsahc_timelines")->get(),

        ];

        return view('scrn', $data);
    }

}