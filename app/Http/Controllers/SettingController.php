<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    public function update(Request $request)
    {
        $sensor_api= $request->sensor_api;
        $sensor_api_pass= $request->sensor_api_pass;
        $sensor_api_token= $request->sensor_api_token;
        $sensor_api_user= $request->sensor_api_user;
        $web_api= $request->web_api;

        try {
            $list_value = [$sensor_api,$sensor_api_pass,$sensor_api_token,$sensor_api_user,$web_api];
            $list_field = ['sensor_api','sensor_api_pass','sensor_api_token','sensor_api_user','web_api'];
            for ($i=0; $i < 5 ; $i++) { 
                DB::table('setting')->where('name',$list_field[$i])->update(['value'=>$list_value[$i]]);
            }
            
            return response()->json(['success' => true, 'message' => 'Update Successfully']);
          
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
       
    }
    public function getlist()
    {
        $list =  DB::table('setting')->get();
        $list_data = [];
        foreach ($list as $key => $value) {
            $data[$value->name] = $value->value;
            $list_data = $data;
        }
        return $list_data;

    }
}
