<?php

namespace App\Http\Controllers;

use App\Models\Template;
use Exception;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function store(Request $request)
    {
        $name = $request->name;
        $widget = $request->widget;

        $check_name = $this->checkName($name);
        if($check_name){
            try {
                $template = new Template();
                $template->name = $name;
                $template->save();

                $template->widgets()->sync($widget);
                if($template){
                    return response()->json(['success'=>true,'message'=>'Create Template Successfully']);
                }
            } catch (Exception $e) {
                return response()->json(['success'=>false,'messsage'=>$e->getMessage()]);
            }
       
        }else{
            return response()->json(['success'=>false,'message'=>'This Name is Already Use.']);
        }
    }
    public function checkName($name)
    {
        $count =  Template::where('name',$name)->count();
        if($count > 0){
            return false;
        }else{
            return true;
        }
    }
}
