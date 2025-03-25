<?php
namespace App\Http\Controllers;

use DB;

class UsersController extends Controller
{
    public function MgtMpaUsers()
    {
        $users = DB::table('users as U')
            ->join('mpa_entities as E', 'E.EntityID', '=', 'U.EntityID')
            ->where('U.UserType', 'MPA')
            ->select('U.*', 'E.Entity')
            ->get();

        $entities = DB::table('mpa_entities')->get();

        $data = [

            "Desc"     => "Manage all MPA system users",
            "Page"     => "users.MgtMpaUsers",
            "entities" => $entities,
            "users"    => $users,

        ];

        return view('scrn', $data);
    }

    public function MgtEcsaUsers()
    {
        $users = DB::table('users as U')
            ->join('clusters as C', 'C.ClusterID', '=', 'U.ClusterID')
            ->where('U.UserType', 'ECSA-HC')
            ->select('U.*', 'C.Cluster_Name')
            ->get();

        $clusters = DB::table('clusters')->get();

        $data = [

            "Desc"     => "Manage all ECSA-HC system users",
            "Page"     => "users.MgtEcsaUsers",
            "clusters" => $clusters,
            "users"    => $users,

        ];

        return view('scrn', $data);
    }
}