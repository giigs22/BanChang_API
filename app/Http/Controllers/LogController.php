<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogController extends Controller
{
    public function save_log(Request $request)
    {
        try {
            $log = $request->type."|".$request->msg;
            Log::channel('error_api')->debug($log);
        
            return response()->json(['success'=>true]);
        
        } catch (Exception $e) {
            return response()->json(['success'=>false,'msg'=>$e->getMessage()]);
        }
        
    }
}
