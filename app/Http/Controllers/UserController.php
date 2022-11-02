<?php

namespace App\Http\Controllers;

use App\Models\ImageProfile;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
        
        //Default Role User for Register
        $role_user = Role::where('name','User')->first();
        $role = (isset($request->role)) ? $request->role : $role_user->id;

        $status = (isset($request->status)) ? $request->status : '1';

        $profile = $request->profile;

        if(empty($username) || empty($password) || empty($email) || empty($phone)){
            return response()->json(['success' => false, 'message' => 'Field Username/Password/Email/Phone Not Null']); 
        }

        $check_mail = $this->uniqEmail($email);
        $check_user = $this->uniqUser($username);
        if ($check_mail || $check_user) {
            return response()->json(['success' => false, 'message' => 'Username or Email has already been used']);
        }
        DB::beginTransaction();
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
            if (!empty($profile)) {
                $this->uploadProfile($add->id, $profile);
            }

            //Set Template
            $set_temp = $this->setTemplate($add->id, $role);
            if (!$set_temp) {
                return response()->json(['success' => false, 'message' => 'Group User Not Setup Template']);
            }
            DB::commit();
            if ($add) {
                return response()->json(['success' => true, 'message' => 'Register Successfully.']);
            }

        } catch (Exception $e) {
            DB::rollBack();
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
    public function uploadProfile($id, $file)
    {
        $base64Image = explode(";base64,", $file);
        $explodeImage = explode("image/", $base64Image[0]);
        $imageType = $explodeImage[1];
        $image_base64 = base64_decode($base64Image[1]);
        $file_decode = uniqid() . '.' . $imageType;

        Storage::disk('public_upload')->put($file_decode, $image_base64);

        $hasimg = ImageProfile::where('user_id', $id)->first();
        if (!empty($hasimg)) {
            Storage::disk('public_upload')->delete($hasimg->filename, File::delete($hasimg->filename));
            $hasimg->filename = $file_decode;
            $hasimg->save();
        } else {
            $add = new ImageProfile();
            $add->user_id = $id;
            $add->filename = $file_decode;
            $add->save();
        }

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
        if (!empty($img_profile)) {
            $img_url = Storage::disk('public_upload')->url($img_profile->filename);
        } else {
            $img_url = null;
        }
        return response()->json(['data' => $user, 'img_profile' => $img_url]);
    }
    public function user_update(Request $request)
    {
        $id = $request->id;
        $idcard = $request->idcard;
        $name = $request->name;
        $username = $request->username;
        $password = $request->password;
        $position = $request->position;
        $location = $request->location;
        $email = $request->email;
        $phone = $request->phone;
        $role = $request->role;
        $status = $request->status;
        $profile = $request->profile;

        DB::beginTransaction();
        try {
            $update = User::find($id);
            $update->idcard = $idcard;
            $update->name = $name;
            $update->username = $username;
            if (!empty($password)) {
                $update->password = Hash::make($password);
            }
            $update->position = $position;
            $update->location = $location;
            $update->email = $email;
            $update->phone = $phone;
            $update->status = $status;
            $update->save();

            $db_role = DB::table('users_roles')->where('user_id', $id)->first();
            if ($db_role->role_id !== $role) {
                DB::table('users_roles')->where('user_id', $id)->update(['role_id' => $role]);
            }

            if (!empty($profile)) {
                $db_img = ImageProfile::where('user_id', $id)->first();
                Storage::disk('public_upload')->delete($db_img->filename, File::delete($db_img->filename));

                $this->uploadProfile($id, $profile);
            }
            DB::commit();
            if ($update) {
                return response()->json(['success' => true, 'message' => 'Update Successfully.']);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(["success" => false, "message" => $e->getMessage()]);
        }
    }
    public function user_destroy(Request $request, $id)
    {
        $del = User::find($id);
        $img = ImageProfile::where('user_id', $id)->first();
        if(!empty($img)){
        Storage::disk('public_upload')->delete($img->filename, File::delete($img->filename));
        }

        $del->delete();
        if ($del) {
            return response()->json(['success' => true, 'message' => 'Data has been Delete Successfully.']);
        }

    }
    public function user_by_id(Request $request)
    {
        $user = $request->user();
        $data['data']['id'] = $user->id;
        $data['data']['name'] = $user->name;
        $data['data']['user_group'] = $user->roles[0]->slug;
        $img_profile = ImageProfile::where('user_id', $user->id)->first();
        if (!empty($img_profile)) {
            $img_url = Storage::disk('public_upload')->url($img_profile->filename);

        } else {
            $img_url = null;
        }
        $data['img_profile'] = $img_url;
        $widget = User::with('templates.widgets')->find($user->id);
        $data['widgets'] = $widget->templates[0]->widgets;
        return response()->json($data);

    }
    public function setTemplate($user_id, $role_id)
    {
        $role = Role::with('templates')->find($role_id);

        if (!empty($role->templates[0])) {
            DB::table('users_templates')->insert([
                'user_id' => $user_id,
                'template_id' => $role->templates[0]->id,
            ]);
            return true;
        } else {
            return false;
        }
    }
    public function forgot(Request $request)
    {
        $email = $request->email;
        $token = Str::random(64);
        $url_frontend = env('FRONTEND_URL');

        $check_mail_user = $this->checkMailUser($email);
        if ($check_mail_user) {
            $check_reset_mail = $this->checkResetMail($email);

            if (!$check_reset_mail) {
                DB::table('password_resets')->insert([
                    'email' => $request->email,
                    'token' => $token,
                    'created_at' => Carbon::now(),
                ]);
            } else {
                DB::table('password_resets')
                    ->where('email', $email)
                    ->update(['token' => $token, 'created_at' => Carbon::now()]);
            }
            //return view('email.forgetPassword',compact('url_frontend','token'));

            Mail::send('email.forgetPassword', ['url_frontend' => $url_frontend, 'token' => $token], function ($message) use ($request) {
                $message->to($request->email);
                $message->subject('Reset Password');
            });

            if (count(Mail::failures()) > 0) {
                return response()->json(['success' => false, 'message' => 'Email not Send!.Please try again']);
            } else {
                return response()->json(['success' => true, 'message' => 'An email has been sent. Please check your inbox.']);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'Your Email has not found.']);
        }

    }
    public function checkResetMail($email)
    {
        $check = DB::table('password_resets')->where('email', $email)->first();
        return (!empty($check)) ? true : false;
    }
    public function checkMailUser($email)
    {
        $check = User::where('email', $email)->first();
        if (!empty($check)) {
            return true;
        } else {
            return false;
        }
    }
    public function changepass(Request $request)
    {
        $pass = $request->password;
        $token = $request->token;
        $url_frontend = env('FRONTEND_URL');

        $check_expire = $this->checkExpireToken($token);
        if ($check_expire) {
            return response()->json(['success' => false, 'message' => 'Token has Expire.Please send forgot password again']);
        } else {
            $data = DB::table('password_resets')->where('token', $token)->first();

            $user = User::where('email', $data->email)->first();
            $user->update(['password' => Hash::make($pass)]);

            DB::table('password_resets')->where('token', $token)->delete();

            Mail::send('email.changePassword', ['url_frontend' => $url_frontend], function ($message) use ($user) {
                $message->to($user->email);
                $message->subject('Change Password Successfully');
            });

            if (count(Mail::failures()) > 0) {
                return response()->json(['success' => false, 'message' => 'Email not Send!.Please try again']);
            } else {
                return response()->json(['success' => true, 'message' => 'An email has been sent. Please check your inbox.']);
            }

        }
    }
    public function checkExpireToken($token)
    {
        $data = DB::table('password_resets')->where('token', $token)->first();
        if (!empty($data)) {
            if ($data->created_at > now()->addHour()) {
                $data->delete();
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }
}
