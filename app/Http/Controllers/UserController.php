<?php

namespace App\Http\Controllers;

use App\Models\ImageProfile;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $idcard = $request->idcard;
        $name = $request->name;
        $username = $request->username;
        $password = $request->password;
        $position = $request->position;
        $location = $request->location;
        $email = $request->email;
        $phone = $request->phone;
        $role = (isset($request->role)) ? $request->role : 3;
        $status = (isset($request->status)) ? $request->status : '1';
        $profile = $request->profile;

        $check_mail = $this->uniqEmail($email);
        $check_user = $this->uniqUser($username);
        if ($check_mail || $check_user) {
            return response()->json(['success' => false, 'message' => 'Username or Email has already been used']);
        }

        try {
            $add = new User();
            $add->idcard = $idcard;
            $add->name = $name;
            $add->username = $username;
            $add->password = Hash::make($password);
            $add->position = $position;
            $add->location = $location;
            $add->email = $email;
            $add->phone = $phone;
            $add->status = $status;
            $add->save();

            DB::table('users_roles')->insert(['user_id' => $add->id, 'role_id' => $role]);

            //Upload Image Profile
            $this->uploadProfile($add->id, $profile);

            if ($add) {
                return response()->json(['success' => true, 'message' => 'Register Successfully.']);
            }

        } catch (Exception $e) {

            return response()->json(["success" => false, "message" => $e->getMessage()]);
        }

    }
    public function uniqEmail($mail)
    {
        $count = User::where('email', $mail)->count();
        if ($count > 0) {
            return true;
        } else {
            return false;
        }
    }
    public function uniqUser($user)
    {
        $count = User::where('username', $user)->count();
        if ($count > 0) {
            return true;
        } else {
            return false;
        }
    }
    public function login(Request $request)
    {
        if (!Auth::attempt(['username' => $request->username, 'password' => $request->password])) {
            return response()->json([
                'success' => false,
                'message' => 'Login information is invalid.',
            ]);
        }

        $user = User::where('username', $request->username)->firstOrFail();
        $check_user_token = DB::table('personal_access_tokens')->where('tokenable_id', $user->id)->first();
        if (!empty($check_user_token)) {
            DB::table('personal_access_tokens')->where('tokenable_id', $user->id)->delete();
        }
        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'token' => $token,
            'success' => true,
        ]);
    }
    public function username()
    {
        return 'username';
    }
    public function role_user()
    {
        return Role::all();
    }
    public function uploadProfile($id, $file)
    {
        $base64Image = explode(";base64,", $file);
        $explodeImage = explode("image/", $base64Image[0]);
        $imageType = $explodeImage[1];
        $image_base64 = base64_decode($base64Image[1]);
        $file_decode = uniqid() . '.' . $imageType;

        Storage::disk('public_upload')->put($file_decode, $image_base64);

        $add = new ImageProfile();
        $add->user_id = $id;
        $add->filename = $file_decode;
        $add->save();

    }
    public function all_user(Request $request)
    {
        $itemPerpage = $request->itemperpage;
        $start = $request->start;
        $filter = $request->filter;

        $user = User::orderBy('id', 'ASC');
        $count_all = $user->count();
        if (!empty($start) || !empty($itemPerpage)) {
            $user = $user->offset($start);
            $user = $user->limit($itemPerpage)->get();
        } else {
            $user = $user->get();
        }

        $list_user = [];
        foreach ($user as $key => $value) {
            $data['id'] = $value->id;
            $data['register_date'] = $value->created_at;
            $data['name'] = $value->name;
            $data['username'] = $value->username;
            $data['role'] = $value->roles[0]->name;
            $data['status'] = $value->status;
            $list_user[] = $data;
        }

        $data_ = [];
        $data_['list_user'] = $list_user;
        $data_['count_all'] = $count_all;

        return response()->json($data_);
    }
    public function user_profile(Request $request)
    {
        $id = $request->id;
        $user = User::with('roles')->find($id);
        $img_profile = ImageProfile::where('user_id', $id)->first();
        $img_url = Storage::disk('public_upload')->url($img_profile->filename);
        return response()->json(['data' => $user, 'img_profile' => $img_url]);
    }
}
