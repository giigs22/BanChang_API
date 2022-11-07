<?php

namespace App\Http\Controllers;

use App\Classes\ApiHelper;
use App\Classes\Helpers;
use App\Models\Device;
use Exception;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function __construct()
    {
        $this->api_helper = new ApiHelper;
        $this->helpers = new Helpers;
    }
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
    public function map_data(Request $request)
    {
        $layer = $request->data;
        $mapdata = [];
        foreach ($layer as $key => $value) {
            if ($value == 'aqi') {
                $device = Device::where('widget_id', 1)->get();
                foreach ($device as $key2 => $value2) {
                    $get_attr = $this->api_helper->getAttrDataAPIByDevice($value2->device_id);
                    $get_data = $this->api_helper->getLastDataAPIByDevice($value2->device_id);
                    $get_location = $this->helpers->getLocation($get_attr);
                    $data['widget'] = $value;
                    $data['device_id'] = $value2['id'];
                    $data['name'] = !empty($value2['location_name']) ? $value2['location_name'] : $value2['device_name'];
                    $data['data'] = $get_data;
                    $data['location'] = $get_location;
                    $mapdata[] = $data;
                }
            }
            if ($value == 'smlight') {
                $device = Device::where('widget_id', 2)->get();
                foreach ($device as $key2 => $value2) {
                    $get_attr = $this->api_helper->getAttrDataAPIByDevice($value2->device_id);
                    $get_data = $this->api_helper->getLastDataAPIByDevice($value2->device_id);
                    $get_location = $this->helpers->getLocation($get_attr);
                    $data['widget'] = $value;
                    $data['device_id'] = $value2['id'];
                    $data['name'] = !empty($value2['location_name']) ? $value2['location_name'] : $value2['device_name'];
                    $data['data'] = $get_data;
                    $data['location'] = $get_location;
                    $mapdata[] = $data;
                }
            }
            if ($value == 'smpole') {
                $device = Device::where('widget_id', 3)->get();
                foreach ($device as $key2 => $value2) {
                    $get_attr = $this->api_helper->getAttrDataAPIByDevice($value2->device_id);
                    $get_data = $this->api_helper->getLastDataAPIByDevice($value2->device_id);
                    $get_location = $this->helpers->getLocation($get_attr);
                    $data['widget'] = $value;
                    $data['device_id'] = $value2['id'];
                    $data['name'] = !empty($value2['location_name']) ? $value2['location_name'] : $value2['device_name'];
                    $data['data'] = $get_data;
                    $data['location'] = $get_location;
                    $mapdata[] = $data;
                }
            }
            if ($value == 'cctv') {
                $device = Device::where('widget_id', 4)->get();
                foreach ($device as $key2 => $value2) {
                    $get_attr = $this->api_helper->getAttrDataAPIByDevice($value2->device_id);
                    $get_data = $this->api_helper->getLastDataAPIByDevice($value2->device_id);
                    $get_location = $this->helpers->getLocation($get_attr);
                    $data['widget'] = $value;
                    $data['device_id'] = $value2['id'];
                    $data['name'] = !empty($value2['location_name']) ? $value2['location_name'] : $value2['device_name'];
                    $data['data'] = $get_data;
                    $data['location'] = $get_location;
                    $mapdata[] = $data;
                }
            }
            if ($value == 'wifi') {
                $device = Device::where('widget_id', 9)->get();
                foreach ($device as $key2 => $value2) {
                    $get_attr = $this->api_helper->getAttrDataAPIByDevice($value2->device_id);
                    $get_data = $this->api_helper->getLastDataAPIByDevice($value2->device_id);
                    $get_location = $this->helpers->getLocation($get_attr);
                    $data['widget'] = $value;
                    $data['device_id'] = $value2['id'];
                    $data['name'] = !empty($value2['location_name']) ? $value2['location_name'] : $value2['device_name'];
                    $data['data'] = $get_data;
                    $data['location'] = $get_location;
                    $mapdata[] = $data;
                }
            }
        }
        return response()->json($mapdata);
    }
    public function map_data_device(Request $request, $id)
    {
        $device = Device::with(['backup', 'location'])->find($id);
        $data['widget'] = $this->widgetKey($device->widget_id);
        $data['device_id'] = $device->id;
        $data['name'] = !empty($device->location_name) ? $device->location_name : $device->device_name;
        $data['data'] = (!empty($device->backup)) ? $device->backup['data_value'] : null;
        $data['location'] = $device->location['data_value'];

        return response()->json($data);
    }
    public function device_ma(Request $request)
    {
        $device = Device::all();
        $data = [];
        foreach ($device as $key => $value) {
            $get_attr = $this->api_helper->getAttrDataAPIByDevice($value->device_id);
            $get_data = $this->api_helper->getLastDataAPIByDevice($value->device_id);
            $get_location = $this->helpers->getLocation($get_attr);
            $get_status = $this->helpers->getStatus($get_attr);
            $get_widget = $this->widgetKey($value->widget_id);

            $setdata['id'] = $value->id;
            $setdata['widget'] = $get_widget;
            $setdata['name'] = $value->location_name !== null ? $value->location_name : $value->device_name;
            $setdata['data'] = $get_data;
            $setdata['location'] = $get_location;
            $setdata['status'] = $get_status;
            $data[] = $setdata;
        }
        return response()->json($data);
    }
    public function widgetKey($id)
    {
        if ($id == '1') {
            $key = 'aqi';
        } elseif ($id == '2') {
            $key = 'smlight';
        } elseif ($id == '3') {
            $key = 'smpole';
        } elseif ($id == '4') {
            $key = 'cctv';
        } elseif ($id == '9') {
            $key = 'wifi';
        } elseif ($id == '12') {
            $key = 'sos';
        }
        return $key;
    }
    public function filter_data(Request $request)
    {
        $widget = $request->widget;
        $cond = $request->filter['cond'];
        $keyword = $request->filter['keyword'];
        $start_date = $request->filter['start_date'];
        $end_date = $request->filter['end_date'];

        //DB::enableQueryLog();

        if ($widget == 'env') {
            $device = Device::where('widget_id', '1');
            $keys = ['co2', 'pm25', 'pm10', 'humid', 'uv', 'voc', 'temp'];
        }
        if ($widget == 'smlight') {
            $device = Device::where('widget_id', '2');
            $keys = ['energy'];
        }
        if ($widget == 'smpole') {
            $device = Device::where('widget_id', '3');
        }

        if (!empty($cond)) {
            if ($cond == 'id') {
                $device = $device->where('id', $keyword);
            }
            if ($cond == 'device_id') {
                $device = $device->where('device_id', $keyword);
            }
            if ($cond == 'name') {
                $device = $device->where('name', 'like', '%' . $keyword . '%');
            }
            if ($cond == 'device_name') {
                $device = $device->where('device_name', 'like', '%' . $keyword . '%');
            }
        }

        $data_device = $device->get();
        $result = [];
        foreach ($data_device as $key => $value) {
            $get_attr = $this->api_helper->getAttrDataAPIByDevice($value->device_id);
            $get_location = $this->helpers->getLocation($get_attr);
            $get_status = $this->helpers->getStatus($get_attr);
            $get_data = $this->api_helper->getHistoryAPIByDevice($value->device_id, null, $start_date, $end_date, null);
            $process_data = $this->processData($get_data->all, $keys);

            $data['device'] = $value;
            $data['data'] = $process_data;
            $data['location'] = $get_location;
            $data['status'] = $get_status;
            $data['date_search'] = ['start_date' => $start_date, 'end_date' => $end_date];

            $result[] = $data;
        }

        return $result;
    }
    public function processData($data, $keys)
    {
        //$keys = collect($data[0])->keys();

        foreach ($keys as $key => $value) {
            foreach ($data as $key2 => $value2) {
                if (isset($value2->$value)) {
                    $group_keys[$value][] = $value2->$value[0]->value;
                }
            }
            $collect = collect($group_keys[$value]);
            $avg[$value] = $collect->avg();
        }
        return $avg;
    }

}
