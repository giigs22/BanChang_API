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
        if ($check_name) {
            try {
                $template = new Template();
                $template->name = $name;
                $template->save();

                $template->widgets()->sync($widget);
                if ($template) {
                    return response()->json(['success' => true, 'message' => 'Create Template Successfully']);
                }
            } catch (Exception $e) {
                return response()->json(['success' => false, 'messsage' => $e->getMessage()]);
            }

        } else {
            return response()->json(['success' => false, 'message' => 'This Name is Already Use.']);
        }
    }
    public function checkName($name)
    {
        $count = Template::where('name', $name)->count();
        if ($count > 0) {
            return false;
        } else {
            return true;
        }
    }
    public function list_template(Request $request)
    {
        $itemPerpage = $request->itemperpage;
        $start = $request->start;

        $temp = Template::orderBy('id', 'ASC');
        $count_all = $temp->count();
        if (!empty($start) || !empty($itemPerpage)) {
            $temp = $temp->offset($start);
            $temp = $temp->limit($itemPerpage)->get();
        } else {
            $temp = $temp->get();
        }

        $list = [];
        foreach ($temp as $key => $value) {
            $data['id'] = $value->id;
            $data['name'] = $value->name;
            $list[] = $data;
        }

        $data_ = [];
        $data_['list'] = $list;
        $data_['count_all'] = $count_all;

        return response()->json($data_);
    }
}
