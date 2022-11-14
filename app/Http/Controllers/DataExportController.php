<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DataExportController extends Controller
{
    public function export_csv(Request $request)
    {
        $widget = $request->widget;
        $data = $request->data;
        $freq = $request->freq;
        $option = $request->option;

        if($widget == 'env'){

        }

    }
}
