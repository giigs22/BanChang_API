<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\ImgComplaint;
use App\Models\Reply;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ComplaintController extends Controller
{
    public function store(Request $request)
    {
        try {
            $user = $request->user();
            $add = new Complaint();

            $add->title = $request->title;
            $add->detail = $request->detail;
            $add->name_complaint = $user->name;
            $add->location = $request->location;
            $add->date_complaint = now();
            $add->respon_agen = $request->respon_agen;
            $add->type = $request->type;
            $add->status = $request->status;
            $add->save();

            //Image Upload max 4 file
            $file = $request->file_upload;
            $ext = ['jpg', 'jpeg', 'png'];
            if (!empty($file)) {
                $count_file = count($file);
                if ($count_file > 4) {
                    return response()->json(['success' => false, 'message' => 'You can upload a file with a maximum of 3 images']);
                } else {
                    foreach ($file as $key => $value) {
                        $extension = $value->getClientOriginalExtension();
                        if (in_array($extension, $ext)) {
                            $fileName = 'comp_' . rand() . "." . $extension;
                            Storage::disk('public_upload')->put('complaint/' . $fileName, File::get($value));

                            $img_add = new ImgComplaint();
                            $img_add->file= $fileName;
                            $img_add->comp_id = $add->id;
                            $img_add->created_at = Carbon::now();
                            $img_add->save();
                        }
                    }
                }
            }

            if ($add) {
                return response()->json(['success' => true, 'message' => 'Data has been Save Successfully.']);
            }
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);

        }

    }
    public function list_complaint(Request $request,$type)
    {
        $itemPerpage = $request->itemperpage;
        $start = $request->start;
        $search = $request->search;

        if($type == 'all'){
            $comp = Complaint::with(['img_comp','reply'])->orderBy('id', (empty($search['order_by'])) ? 'DESC' : $search['order_by']);
        }elseif($type == 'user'){
            $user = $request->user();
            $comp = Complaint::with(['img_comp','reply'])->where('name_complaint',$user->name)->orderBy('id', (empty($search['order_by'])) ? 'DESC' : $search['order_by']);
        }
        $stat = collect($comp->get())->countBy('type');
        $count_all = $comp->count();

        if (!empty($search['title'])) {
            $comp = $comp->where('title', 'like', '%' . $search['title'] . '%');
            $count_all = $comp->count();

        }
        if (!empty($search['agency'])) {
            $comp = $comp->where('respon_agen', 'like', '%' . $search['agency'] . '%');
            $count_all = $comp->count();

        }
        if (!empty($search['start_date']) && !empty($search['end_date'])) {

            $sdate = Carbon::parse($search['start_date']);
            $edate = Carbon::parse($search['end_date'].'23:59:59');
            $comp = $comp->whereBetween('date_complaint', [$sdate, $edate]);
            $count_all = $comp->count();

        } else {
            if (!empty($search['start_date']) && empty($search['end_date'])) {

                $comp = $comp->where('date_complaint', $search['start_date']);
                $count_all = $comp->count();

            }
            if (empty($search['start_date']) && !empty($search['end_date'])) {
                $comp = $comp->where('date_complaint', $search['end_date']);
                $count_all = $comp->count();

            }
        }

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
            $data['reply'] = $value->reply;
            $data['name_complaint'] = $value->name_complaint;
            $data['location'] = $value->location;
            $data['date_complaint'] = $value->date_complaint;
            $data['respon_agen'] = $value->respon_agen;
            $data['type'] = $value->type;
            $data['status'] = $value->status;
            $data['img_comp'] = $this->listImgComp($value->img_comp);

            $list_comp[] = $data;
        }

        $data_ = [];
        $data_['list_comp'] = $list_comp;
        $data_['count_all'] = $count_all;
        $data_['stat'] = $stat;

        return response()->json($data_);
    }
    public function list_complaint_user_id(Request $request,$user_id)
    {
        $user = User::find($user_id);
        $comp = Complaint::with('img_comp')->where('name_complaint',$user->name)->get();
        $list_comp = [];
        foreach ($comp as $key => $value) {
            $data['id'] = $value->id;
            $data['title'] = $value->title;
            $data['detail'] = $value->detail;
            $data['name_complaint'] = $value->name_complaint;
            $data['location'] = $value->location;
            $data['date_complaint'] = $value->date_complaint;
            $data['respon_agen'] = $value->respon_agen;
            $data['type'] = $value->type;
            $data['status'] = $value->status;
            $data['img_comp'] = $this->listImgComp($value->img_comp);

            $list_comp[] = $data;
        }
        return response()->json($list_comp);
    }
    public function complaint_by_id(Request $request, $id)
    {
        $comp =  Complaint::with('img_comp')->find($id);
        $data['id'] = $comp->id;
        $data['title'] = $comp->title;
        $data['detail'] = $comp->detail;
        $data['name_complaint'] = $comp->name_complaint;
        $data['location'] = $comp->location;
        $data['date_complaint'] = $comp->date_complaint;
        $data['respon_agen'] = $comp->respon_agen;
        $data['type'] = $comp->type;
        $data['status'] = $comp->status;
        $data['img_comp'] = $this->listImgComp($comp->img_comp);

        return response()->json($data);
    }
    public function reply(Request $request)
    {
        $comp_id = $request->comp_id;
        $text_reply = $request->text_reply;
        $user = $request->user();
        
        try {
            $add = new Reply();
            $add->comp_id = $comp_id;
            $add->text_reply = $text_reply;
            $add->user_reply = $user->id;
            $add->save();

            if($add){
                return response()->json(['success'=>true,'message'=>'Reply Success.']);
            }
        } catch (Exception $e) {
            return response()->json(['success'=>false,'message'=>$e->getMessage()]);
        }
    }
    public function destroy(Request $request, $id)
    {
        $del = Complaint::find($id);
        $del->delete();
        if ($del) {
            return response()->json(['success' => true, 'message' => 'Data has been Delete Successfully.']);
        }
    }
    public function listImgComp($data)
    {
        $list = [];
        foreach ($data as $key => $value) {
            $list[] = Storage::disk('public_upload')->url('complaint/' . $value->file);
        }
        return $list;
    }
}
