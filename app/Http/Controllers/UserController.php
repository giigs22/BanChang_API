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
        $username = $request->username;
        $password = $request->password;
        $position = $request->position;
        $location = $request->location;
        $email = $request->email;
        $phone = $request->phone;
        $role = (isset($request->role)) ? $request->role : 3;
        $status = (isset($request->status))? $request->status:1;
        $profile = $request->profile;

        $check_mail = $this->uniqEmail($email);
        $check_user = $this->uniqUser($username);
        if ($check_mail || $check_user) {
            return response()->json(['success' => false, 'message' => 'Username or Email has already been used']);
        }

    
        try {
            $add = new User();
            $add->idcard = $idcard;
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
            $this->uploadProfile($add->id,$profile);

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
    public function uploadProfile($id,$file)
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
}
