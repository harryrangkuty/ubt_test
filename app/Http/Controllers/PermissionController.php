<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permission;

class PermissionController extends Controller
{
    public function __invoke(Request $request)
    {
        $constant = [];

        $title = 'Manajemen Permission';
        $vue = "<permission-page :constant='" . json_encode($constant) . "' />";
        return response()->view('layouts.antd', compact('vue', 'title'));
    }
    public function read(Request $request)
    {
        if ($request->req == 'table') {
            $data = Permission::where('name', '<>', 'sudo')
                        ->where(function ($q) use ($request) {
                            if($request->search)
                            $q->where('name', 'like', "%{$request->search}%");
                        })
                        ->paginate($this->per_page());

            return response()->json(['models' => $data]);
        } 
    }
    public function write(Request $request)
    {
        if ($request->req == 'write') {
            
            $data = Permission::find($request->id) ?? new Permission();
            
            $data->name = $request->name;
            $data->display_name = $request->display_name;
            $data->description = $request->description;
            $data->save();
            return response()->json(true);
        }
    }
}
