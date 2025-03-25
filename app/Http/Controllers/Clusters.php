<?php
namespace App\Http\Controllers;

use DB;

class Clusters extends Controller
{
    public function MgtClusters()
    {
        $data = [

            "Desc"     => "Manage all ECSA-HC reporting clusters",
            "Page"     => "clusters.MgtClusters",
            "clusters" => DB::table("clusters")->get(),

        ];

        return view('scrn', $data);
    }
}