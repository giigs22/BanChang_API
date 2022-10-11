<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ComplaintController extends Controller
{
    public function list_complanit(Request $request)
    {
        $itemPerpage = $request->itemperpage;
        $start = $request->start;
        $filter = $request->filter;

        $comp = Complaint::orderBy('id', 'ASC');
        $count_all = $comp->count();
        if (!empty($start) || !empty($itemPerpage)) {
            $comp = $comp->offset($start);
            $comp = $comp->limit($itemPerpage)->get();
        } else {
            $comp = $comp->get();
        }

        $list_comp = [];
        foreach ($comp as $key => $value) {
            $data['id'] = $value->id;
            $data['title'] = $value->title;
            $data['detail'] = $value->detail;
            $data['name_complaint'] = $value->name_complaint;
            $data['location'] = $value->location;
            $data['date_complaint'] = $value->date_complaint;
            $data['respon_agen'] = $value->respon_agen;
            $data['img_cover'] = Storage::disk('public_upload')->url('complaint/'.$value->img_cover);
            $data['type'] = $value->type;
            $data['status'] = $value->status;

            $list_comp[] = $data;
        }

        $data_ = [];
        $data_['list_comp'] = $list_comp;
        $data_['count_all'] = $count_all;

        return response()->json($data_);
    }
    public function destroy(Request $request)
    {
        echo 'del';
        // try {
        //     $del = Complaint::find($request->id);
        //     Storage::disk('public_upload')->delete('complaint/' . $del->img_cover, File::delete($del->img_cover));
        //     $del->delete();
        //     return response()->json(['success' => false, 'message' => 'Data has been Delete Successfully.']);
        // } catch (Exception $e) {
        //     return response()->json(['success' => false, 'message' => $e->getMessage()]);
        // }
    }
}
