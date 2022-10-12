<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\Device;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Faker\Factory as Faker;

class ComplaintController extends Controller
{
    public function store(Request $request)
    {
        $fake = Faker::create();

        $add = new Complaint();
        $add->title = $fake->sentence(10);
        $add->detail = $fake->sentence(20);
        $add->name_complaint = $fake->name();
        $add->location = 'Banchang';
        $add->date_complaint = $fake->dateTimeBetween('now','+2 month');
        $add->respon_agen = $fake->name().' Unit';
        $add->img_cover = 'img_ex_complaint.png';
        $add->type = $fake->randomElement(['disturbance','electricity','water','etc','disturbance']);
        $add->status = 'pending';
        $add->save();

    }
    public function list_complanit(Request $request)
    {
        $itemPerpage = $request->itemperpage;
        $start = $request->start;
        $filter = $request->filter;

        $comp = Complaint::orderBy('id', 'ASC');
        $stat = collect($comp->get())->countBy('type');
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
        $data_['stat'] = $stat;

        return response()->json($data_);
    }
    public function complaint_by_id(Request $request,$id)
    {
        return Complaint::find($id);
    }
    public function destroy(Request $request)
    {
        
    }
}
