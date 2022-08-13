<?php

namespace App\Http\Controllers;

use App\Models\Widget;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WidgetController extends Controller
{
    public function list_widget(Request $request)
    {
        $itemPerpage = $request->itemperpage;
        $start = $request->start;

        $widget = Widget::orderBy('id', 'ASC');
        $count_all = $widget->count();
        if (!empty($start) || !empty($itemPerpage)) {
            $widget = $widget->offset($start);
            $widget = $widget->limit($itemPerpage)->get();
        } else {
            $widget = $widget->get();
        }
        $list = [];
        foreach ($widget as $key => $value) {
            $data['id'] = $value->id;
            $data['name'] = $value->name;
            $data['icon'] = Storage::disk('public_upload')->url('icon/' . $value->icon);
            $list[] = $data;
        }

        $data_ = [];
        $data_['list'] = $list;
        $data_['count_all'] = $count_all;

        return response()->json($data_);
    }
    public function list_widget_all()
    {
        return Widget::all();
    }
    public function addcate(Request $request)
    {
        $name = $request->name;
        $file = $request->file;

        $extension = $file->getClientOriginalExtension();
        $fullname = 'icon_widget_' . str_replace(' ', '_', Str::lower($name)) . "." . $extension;

        Storage::disk('public_upload')->put('icon/' . $fullname, File::get($file));

        try {
            $add = new Widget();
            $add->name = $name;
            $add->slug = str_replace(' ', '-', Str::lower($name));
            $add->icon = $fullname;
            $add->save();
            if ($add) {
                return response()->json(['success' => true, 'message' => 'Data has been Save Successfully']);
            }
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }

    }
    public function updatecate(Request $request)
    {
        $id = $request->id;
        $name = $request->name;
        $file = $request->file;

        try {
            $update = Widget::find($id);

            if (!empty($file) || $file != "") {
                $extension = $file->getClientOriginalExtension();
                $fullname = 'icon_widget_' . Str::lower($name) . "." . $extension;

                Storage::disk('public_upload')->put('icon/' . $fullname, File::get($file));

                $update->name = $name;
                $update->icon = $fullname;

            } else {

                $update->name = $name;
            }
            $update->save();
            if ($update) {
                return response()->json(['success' => true, 'message' => 'Update Successfully']);
            }
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    public function destroy(Request $request)
    {
        try {
            $del = Widget::find($request->id);
            Storage::disk('public_upload')->delete('icon/' . $del->icon, File::delete($del->icon));
            $del->delete();
            return response()->json(['success' => false, 'message' => 'Data has been Delete Successfully.']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
