<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AcademicPeer;

class HomeController extends Controller
{
    public function __invoke()
    {
        $constant = [];

        $title = "Dashboard";

        $vue = "<dashboard-page :constant='" . json_encode($constant) . "' />";

        return view('layouts.antd', compact('vue', 'title'));
    }

    public function read(Request $request)
    {
        if ($request->req == 'chart_data') {
            
            $data = AcademicPeer::get()
                                ->groupBy('country_name')
                                ->map(function ($val, $key) {
                                    return [
                                        'name' => $key,
                                        'value' => $val->count(),
                                    ];
                                })
                                ->values()
                                ->toArray();

            return response()->json(['models' => $data]);
        }
    }
}
