<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Exception;
use Illuminate\Http\Request;

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

        try {
            $add = new Device();
            $add->widget_id = $cate;
            $add->device_id = $device_id;
            $add->device_name = $device_name;
            $add->location_name = $location;
            $add->name = $name;
            $add->type = $type;
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

        try {
            $update = Device::find($id);
            $update->widget_id = $cate;
            $update->device_id = $device_id;
            $update->device_name = $device_name;
            $update->location_name = $location;
            $update->name = $name;
            $update->type = $type;
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
}
