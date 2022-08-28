<?php

namespace App\Http\Controllers;

use App\Models\Backup;
use App\Models\Device;
use App\Models\Location;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeviceController extends Controller
{
    public function store(Request $request)
    {
        $cate = $request->cate;
        $device_id = $request->device_id;
        $device_name = $request->device_name;
        $location = $request->location;
        $name = $request->name;
        $type = $request->type;
        $type_cam = $request->type_cam;

        try {
            $add = new Device();
            $add->widget_id = $cate;
            $add->device_id = $device_id;
            $add->device_name = $device_name;
            $add->location_name = $location;
            $add->name = $name;
            $add->type = $type;
            $add->type_cam = $type_cam;
            $add->save();
            if ($add) {
                return response()->json(['success' => true, 'message' => 'Data has been Save Successfully']);
            }
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);

        }
    }
    public function list_device(Request $request)
    {
        $itemPerpage = $request->itemperpage;
        $start = $request->start;

        $device = Device::with('widget')->orderBy('id', 'ASC');
        $count_all = $device->count();
        if (!empty($start) || !empty($itemPerpage)) {
            $device = $device->offset($start);
            $device = $device->limit($itemPerpage)->get();
        } else {
            $device = $device->get();
        }

        $data_ = [];
        $data_['list'] = $device;
        $data_['count_all'] = $count_all;

        return response()->json($data_);

    }
    public function list_device_all()
    {
        return Device::all();
    }
    public function device_by_id(Request $request, $id)
    {
        return Device::find($id);
    }
    public function update(Request $request, $id)
    {
        $cate = $request->cate;
        $device_id = $request->device_id;
        $device_name = $request->device_name;
        $location = $request->location;
        $name = $request->name;
        $type = $request->type;
        $type_cam = $request->type_cam;

        try {
            $update = Device::find($id);
            $update->widget_id = $cate;
            $update->device_id = $device_id;
            $update->device_name = $device_name;
            $update->location_name = $location;
            $update->name = $name;
            $update->type = $type;
            $update->type_cam = $type_cam;
            $update->save();
            if ($update) {
                return response()->json(['success' => true, 'message' => 'Update Successfully']);
            }
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }

    }
    public function destroy(Request $request, $id)
    {
        try {
            $del = Device::find($id);
            $del->delete();
            if ($del) {
                return response()->json(['success' => true, 'message' => 'Data has been Delete Successfully.']);
            }
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    public function list_by_cate(Request $request, $cate_id)
    {
        return Device::where('widget_id', $cate_id)->get();
    }
    public function backup_data_sensor(Request $request)
    {
        $arr_data = $request->data;

        DB::beginTransaction();
        try {
            foreach ($arr_data as $key => $value) {
            $chk = Backup::where('device_id',$value['device'])->first();
            if(!empty($chk)){
                $update = $chk;
                $update->data_value = json_encode($value['data']);
                $update->type = $value['type'];
                $update->save();
            }else{
                $add = new Backup();
                $add->device_id = $value['device'];
                $add->data_value = json_encode($value['data']);
                $add->type = $value['type'];
                $add->save();
            }
            }
            DB::commit();
            return response()->json(['success' => true, 'message' => '']);
            
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    public function get_data_backup(Request $request,$id)
    {
        $db_backup = Backup::where('device_id',$id)->first();
        return response()->json($db_backup);
    }
    public function backup_data_location(Request $request)
    {
        $arr_data = $request->data;
        DB::beginTransaction();
        try {
            foreach ($arr_data as $key => $value) {
                $chk = Location::where('device_id',$value['device'])->first();
                if(!empty($chk)){
                    $update = $chk;
                    $update->data_value = json_encode($value['data']);
                    $update->save();
                }else{
                    $add = new Location();
                    $add->device_id = $value['device'];
                    $add->data_value = json_encode($value['data']);
                    $add->save();
                }
            }
            
            DB::commit();
            return response()->json(['success' => true, 'message' => '']);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
