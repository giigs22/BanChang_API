<?php

namespace App\Http\Controllers;

use App\Classes\ApiHelper;
use App\Classes\Helpers;
use App\Models\Device;
use App\Models\Maintenance;
use Exception;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    public function __construct()
    {
        ini_set('max_execution_time', 0);
        $this->api_helper = new ApiHelper;
        $this->helpers = new Helpers;
    }
    public function store(Request $request)
    {
        $device_name = $request->device_name;
        $detail = $request->detail;
        $user= $request->user();

        $check_device = Maintenance::where('device_name',$device_name)->where('status','<>','success')->count();
        $check_has_device = Device::where('device_name',$device_name)->count();
        if($check_device > 0){
            return response()->json(['success'=>false,'msg'=>'This Device is being Maintenance']);
        }elseif($check_has_device == 0){
            return response()->json(['success'=>false,'msg'=>'Not Found Device ['.$device_name.'] in database']);
        }else{
            try {
                $add = new Maintenance();
                $add->device_name = $device_name;
                $add->detail = $detail;
                $add->user_report = $user->name;
                $add->status = 'pending';
                $add->save();

                if($add){
                    return response()->json(['success' => true, 'message' => 'Data has been Save Successfully.']);
                }
            } catch (Exception $e) {
                return response()->json(['success' => false, 'message' => $e->getMessage()]);
            }
        }
    }
    public function list()
    {
        $list = Maintenance::where('status','<>','success')->get();
        $mapdata = [];
        foreach ($list as $key => $value) {
            $device = Device::with('widget')->where('device_name',$value['device_name'])->first();
            $get_attr = $this->api_helper->getAttrDataAPIByDevice($device->device_id);
            $get_location = $this->helpers->getLocation($get_attr);
            $get_status = $this->helpers->getStatus($get_attr);
            $get_widget = $this->helpers->widgetKey($device->widget_id);

            $data['widget'] = $get_widget;
            $data['device_id'] = $device->id;
            $data['name'] = $device->device_name;
            $data['detail'] = $value['detail'];
            $data['location'] = $get_location;
            $data['status'] = $get_status;
            $data['status_ma'] = $value['status'];
            $mapdata[] = $data;
        }
        return $mapdata;

    }
    public function update_status(Request $request,$id)
    {
        $status = $request->status;
        $user = $request->user();
        $role =  $user->roles[0]->slug;
        $list_status = ['pending','maintenance','success'];
        if($role !='administrator' || $role != 'staff'){
            return response()->json(['message' => 'Not Authorized'], 403);
        }else{
            $ma = Maintenance::find($id);

            if(in_array($status,$list_status)){
                if($status == 'success'){
                    $ma->user_ma = $user->name;
                    $ma->status = $status;
                }else{
                    $ma->status = $status;
                }
                $ma->save();
                if($ma){
                    return response()->json(['success' => true, 'message' => 'Data has been Update Successfully.']);
                }
            }else{
                return response()->json(['success'=>false,'msg'=>'Status is [pending,maintenace,success]']);
            }
        }
    }
    public function filter_data(Request $request)
    {
        $cond = isset($request->filter['cond'])?$request->filter['cond']:"";
        $keyword = isset($request->filter['keyword'])?$request->filter['keyword']:"";
        $itemPerpage = $request->filter['itemperpage'];
        $start = $request->filter['start'];

        //DB::enableQueryLog();

        $malist = new Maintenance();
        $count_all = $malist->count();

        if (!empty($start) || !empty($itemPerpage)) {
            $malist = $malist->offset($start);
            $malist = $malist->limit($itemPerpage)->get();
        } else {
            $malist = $malist->get();
        }
        

        if (!empty($cond)) {
            if ($cond == 'device_name') {
                $malist = $malist->where('device_name', $keyword);
            }
            if ($cond == 'pending') {
                $malist = $malist->where('status', 'pending');
                if(!empty($keyword)){
                    $malist = $malist->where('device_name', $keyword);
                }
            }
            if ($cond == 'maintenance') {
                $malist = $malist->where('status', 'maintenance');
                if(!empty($keyword)){
                    $malist = $malist->where('device_name', $keyword);
                }
            }
            if ($cond == 'success') {
                $malist = $malist->where('status', 'success');
                if(!empty($keyword)){
                    $malist = $malist->where('device_name', $keyword);
                }
            }
            $count_all = $malist->count();
        }
        
        $result = [];
        foreach ($malist as $key => $value) {
            $device = Device::with('widget')->where('device_name',$value['device_name'])->first();

            $get_attr = $this->api_helper->getAttrDataAPIByDevice($device->device_id);
            $get_location = $this->helpers->getLocation($get_attr);
            $get_status = $this->helpers->getStatus($get_attr);

            $data['id'] = $value['id'];
            $data['device'] = $value['device_name'];
            $data['location'] = $get_location;
            $data['status'] = $get_status;
            $data['status_ma'] = $value['status'];
            $data['user_report'] = $value['user_report'];
            $data['user_ma'] = $value['user_ma'];
            $data['updated_at'] = $value['updated_at'];
            $data['detail'] = $value['detail'];

            $result[] = $data;
            
        }
        $data_ = [];
        $data_['list'] = $result;
        $data_['count_all'] = $count_all;

        return response()->json($data_);
    }
    public function destroy(Request $request,$id)
    {
        $user = $request->user();
        $role =  $user->roles[0]->slug;
        if($role !='administrator' || $role != 'staff'){
            return response()->json(['message' => 'Not Authorized'], 403);
        }else{
            $ma = Maintenance::find($id);
            $ma->delete();

            if($ma){
                return response()->json(['success' => true, 'message' => 'Data has been Remove Successfully.']);
            }
        }
    }
}
