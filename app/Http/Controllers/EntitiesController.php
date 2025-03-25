<?php
namespace App\Http\Controllers;

use DB;

class EntitiesController extends Controller
{

    public function MgtEntities()
    {
        $data = [

            "Desc"     => "Manage all MPA reporting entities",
            "Page"     => "Entities.MgtEntities",
            "entities" => DB::table("mpa_entities")->get(),

        ];

        return view('scrn', $data);
    }

}