<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Template;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
    public function template_by_id(Request $request)
    {
        $data = Template::with('widgets')->find($request->id);
        return response()->json($data);
    }
    public function update(Request $request)
    {
        $id = $request->id;
        $name = $request->name;
        $widget = $request->widget;

        try {
            $update = Template::find($id);
            $update->name = $name;
            $update->save();

            DB::table('template_widget_relations')->where('template_id', $id)->delete();

            $update->widgets()->sync($widget);

            return response()->json(['success' => true, 'message' => 'Update Successfully']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }

    }
    public function destroy(Request $request)
    {
        $id = $request->id;
        $data = $request->data;

        DB::beginTransaction();
        try {
            DB::table('roles_templates')->where('template_id', $id)->update(['template_id' => $data]);
            DB::table('users_templates')->where('template_id', $id)->update(['template_id' => $data]);

            $temp = Template::find($id);
            $temp->delete();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Data has been Delete Successfully.']);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);

        }

    }
    public function group_user_temp(Request $request)
    {
        $itemPerpage = $request->itemperpage;
        $start = $request->start;

        $role = Role::with('templates')->orderBy('id', 'ASC');
        $count_all = $role->count();
        if (!empty($start) || !empty($itemPerpage)) {
            $role = $role->offset($start);
            $role = $role->limit($itemPerpage)->get();
        } else {
            $role = $role->get();
        }

        $data_ = [];
        $data_['list'] = $role;
        $data_['count_all'] = $count_all;

        return response()->json($data_);
    }
    public function user_temp(Request $request)
    {
        $itemPerpage = $request->itemperpage;
        $start = $request->start;

        $role = User::with('templates')->orderBy('id', 'ASC');
        $count_all = $role->count();
        if (!empty($start) || !empty($itemPerpage)) {
            $role = $role->offset($start);
            $role = $role->limit($itemPerpage)->get();
        } else {
            $role = $role->get();
        }

        $data_ = [];
        $data_['list'] = $role;
        $data_['count_all'] = $count_all;

        return response()->json($data_);

    }
    public function update_group(Request $request)
    {
        $role_id = $request->role_id;
        $temp_id = $request->temp_id;

        try {
            $chk = DB::table('roles_templates')->where('role_id', $role_id)->count();
            if ($chk > 0) {
                DB::table('roles_templates')->where('role_id', $role_id)->update(['template_id' => $temp_id]);
            } else {
                DB::table('roles_templates')->insert([
                    'role_id' => $role_id,
                    'template_id' => $temp_id,
                ]);
            }
            return response()->json(['success' => true, 'message' => 'Setting Template Successfully']);

        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }

    }
    public function update_user(Request $request)
    {
        $user_id = $request->user_id;
        $temp_id = $request->temp_id;

        try {
            $chk = DB::table('users_templates')->where('user_id', $user_id)->count();
            if ($chk > 0) {
                DB::table('users_templates')->where('user_id', $user_id)->update(['template_id' => $temp_id]);
            } else {
                DB::table('users_templates')->insert([
                    'user_id' => $user_id,
                    'template_id' => $temp_id,
                ]);
            }
            return response()->json(['success' => true, 'message' => 'Setting Template Successfully']);

        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }

    }
}
