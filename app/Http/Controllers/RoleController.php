<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use Exception;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    public function role_user()
    {
        return Role::all();
    }
    public function all_role(Request $request)
    {
        $itemPerpage = $request->itemperpage;
        $start = $request->start;

        $role = Role::orderBy('id', 'ASC');
        $count_all = $role->count();
        if (!empty($start) || !empty($itemPerpage)) {
            $role = $role->offset($start);
            $role = $role->limit($itemPerpage)->get();
        } else {
            $role = $role->get();
        }
        $list = [];
        foreach ($role as $key => $value) {
            $data['id'] = $value->id;
            $data['name'] = $value->name;
            $list[] = $data;
        }

        $data_ = [];
        $data_['list'] = $list;
        $data_['count_all'] = $count_all;

        return response()->json($data_);
    }
    public function store(Request $request)
    {
      
        try {
            $add = new Role();
            $add->name = $request->name;
            $add->save();
            return response()->json(['success'=>true,'message'=>'Data has been Save Successfully']);

        } catch (Exception $e) {
            return response()->json(['success'=>false,'message'=>$e->getMessage()]);
        }
    }
    public function role_by_id(Request $request)
    {
        return Role::find($request->id);
    }
    public function update(Request $request)
    {
        $id = $request->id;
        $name = $request->name;
      
        try {
            $update = Role::find($id);
            $update->name =$name;
            $update->save();

            if($update){
                return response()->json(['success'=>true,'message'=>'Update data Successfully']);
            }
        } catch (Exception $e) {
            return response()->json(['success'=>false,'message'=>$e->getMessage()]);
        }
    }
    public function destroy(Request $request)
    {
        $id = $request->id;
        $data = $request->data;

        DB::beginTransaction();
        try {
            $role = Role::with('users')->find($id);
            $users = $role->users;
            if($users->count() > 0){
                foreach ($users as $key => $value) {
                    DB::table('users_roles')->where('user_id',$value->id)->update(['role_id'=>$data]);
                }
            }
            $role->delete();
            DB::commit();
            return response()->json(['success'=>true,'message'=>'Data has been Delete Successfully.']);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success'=>true,'message'=>$e->getMessage()]);
        }
        
    }
}
