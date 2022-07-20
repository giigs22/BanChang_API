<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

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

        $check_mail = $this->uniqidEmail($email);
        if ($check_mail) {
            return response()->json(['success' => false, 'message' => 'Email has already been used']);
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
            $add->save();

            DB::table('users_roles')->insert(['user_id' => $add->id, 'role_id' => $role]);

            if ($add) {
                return response()->json(['success' => true, 'message' => 'Register Successfully.']);
            }

        } catch (Exception $e) {
            return response()->json(["success" => false, "message" => $e->getMessage()]);
        }

    }
    public function uniqidEmail($mail)
    {
        $count = User::where('email', $mail)->count();
        if ($count > 0) {
            return true;
        } else {
            return false;
        }
    }
    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
             'message' => 'Login information is invalid.'
           ], 401);
     }

     $user = User::where('email', $request['email'])->firstOrFail();
     $check_user_token = DB::table('personal_access_tokens')->where('tokenable_id',$user->id)->first();
     if(!empty($check_user_token)){
        DB::table('personal_access_tokens')->where('tokenable_id',$user->id)->delete();
     }
     $token = $user->createToken('authToken')->plainTextToken;
    
    


         return response()->json([
         'token' => $token,
         ]);
    }
}
