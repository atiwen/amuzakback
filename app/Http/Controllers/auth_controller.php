<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\UserToken;
use App\Models\User;
use Laravel\Jetstream\Jetstream;
use Illuminate\Support\Carbon;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;

use App\Models\reg_req;

class auth_controller extends Controller
{


    // ثبت نام
    public function reg_step_1(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'integer', 'max:10000000000', 'unique:users'],
        ], [
            'phone.unique' => 'با این شماره قبلا ثبت نام شده',
        ]);
        $error = '';
        $status = "nothing";
        $code = rand(10000, 99999);

        $respons = send_ksms($request->phone, $code, 'code');
        $sms_res = json_decode($respons);

        if ($sms_res->return->status == 200) {
            try {
                reg_req::updateOrCreate(
                    ['phone' => $request->phone],
                    ['code' => $code]
                );
                $status = "success";
            } catch (\Throwable $e) {
                $error = $e->getMessage();
                $status = "failed";
            }
        } else {
            $error = $sms_res;
            $status = "failed";
        }
        return response()->json([
            'status' => $status,
            'error' => $error,
        ]);
    }
    public function reg_step_2(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'integer', 'max:10000000000', 'unique:users'],
            'code' => ['required', 'integer'],
        ], [
            'phone.unique' => 'با این شماره قبلا ثبت نام شده',
        ]);

        $status = 'wrong';
        $key = '';
        $code_req = DB::table('reg_req')->where('phone', $request->phone)->where('code', $request->code)->get();
        if (count($code_req)) {
            $key = str(rand(10000, 99999));
            DB::table('reg_req')->where('phone', $request->phone)->where('code', $request->code)->update([
                'status' => 1,
                'key' => $key
            ]);
            $status = 'success';
        }
        return response()->json(['status' => $status, 'key' => $key]);
    }
    public function reg_step_3(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:25'],
            'phone' => ['required', 'string', 'unique:users', 'min:10', 'unique:users'],
            // 'key' => ['required', 'string', 'max:5'],
            'password' => ['required', 'min:8', 'max:255'],

        ], [
            'name.required' => 'نام الزامی است ',
            'phone.required' => 'تلفن همراه الزامی است',
            'phone.unique' => 'با این شماره قبلا ثبت نام شده',
            'password.min' => 'رمز عبور حداقل باید 8 رقمی باشد',
        ]);
        // $loginRequest = DB::table('reg_req')->where('phone', $request->phone)->where('key', $request->key)->first();
        $loginRequest = true;
        if ($loginRequest) {
            User::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
            ]);
            // send_notif(0);
            $user = DB::table('users')->where('phone', $request->phone)->first();
            $user = User::find($user->id);
            if ($token = JWTAuth::fromUser($user)) {
                $userAgent = $request->header('User-Agent');
                // ایجاد Refresh Token
                $refreshToken = Str::random(60);
                UserToken::create([
                    'user_id' => $user->id,
                    'refresh_token' => $refreshToken,
                    'user_agent' => $userAgent,
                    'last_online_at' => now(),
                ]);
                return response()->json([
                    'status' => 'success',
                    'auth' => $user,
                    'retoken' => $refreshToken,
                    'token' => $token
                ]);
            }
        }
        return response()->json(['status' => 'failed']);
    }


    public function adminLogin(Request $request)
    {
        $credentials = $request->only('phone', 'password');
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['message' => 'wrong']);
            }
        } catch (JWTException $e) {
            return response()->json(['message' => 'Could not create token' , 'error' => $e]);
        }
        $user = JWTAuth::user();
        auth()->loginUsingId($user->id, true);
        return redirect()->intended('/');
    }



    // ورود با شماره و رمز
    public function jlogin(Request $request)
    {
        $credentials = $request->only('phone', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['message' => 'wrong']);
            }
        } catch (JWTException $e) {
            return response()->json(['message' => 'Could not create token']);
        }
        $user = JWTAuth::user();
        $userAgent = $request->header('User-Agent');
        $refreshToken = Str::random(60);
        UserToken::create([
            'user_id' => auth()->user()->id,
            'refresh_token' => $refreshToken,
            'user_agent' => $userAgent,
            'last_online_at' => now(), // ذخیره زمان ورود
        ]);
        return response()->json([
            'message' => 'success',
            'auth' => $user,
            'retoken' => $refreshToken,
            'token' => $token
        ]);
    }
    // چک کردن بودن اکانت از شماره
    public function phone(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
        ]);
        $user = DB::table('users')->where('phone', $request->phone)->get();

        if (count($user)) {
            return 1;
        } else {
            return 'noac';
        }
    }
    // در خواست کد یکبار مصرف جهت ورود
    public function code(Request $request)
    {
        $request->validate([
            'phone' => 'required|integer',
        ]);
        $user = DB::table('users')->where('phone', $request->phone)->get();

        if (count($user)) {
            $code = rand(10000, 99999);
            login_reqs::updateOrInsert(
                ['phone' => $request->phone],
                ['code' => $code]
            );


            $respons = send_ksms($request->phone, $code, 'code');
            $sms_res = json_decode($respons);

            return 1;
        } else {
            return 0;
        }
    }
    // برسی کد یکبار مصرف جهت ورود
    public function verify_otp(Request $request)
    {
        $request->validate([
            'phone' => 'required|integer',
            'code' => 'required|integer',
        ]);

        $loginRequest = DB::table('login_req')
            ->where('phone', $request->phone)->where('code', $request->code)
            ->first();
        // ->where('updated_at', '>=', now()->subMinutes(10))

        if ($loginRequest) {
            DB::table('login_req')->where('id', $loginRequest->id)->delete();
            $user = DB::table('users')->where('phone', $request->phone)->first();
            $user = User::find($user->id);
            // $token = JWTAuth::fromUser($user);
            try {
                if (!$token = JWTAuth::fromUser($user)) {
                    return response()->json(['error' => 'wrong']);
                }
            } catch (JWTException $e) {
                return response()->json(['error' => 'Could not create token'], 500);
            }
            $userAgent = $request->header('User-Agent');
            // ایجاد Refresh Token
            $refreshToken = Str::random(60);
            UserToken::create([
                'user_id' => $user->id,
                'refresh_token' => $refreshToken,
                'user_agent' => $userAgent,
                'last_online_at' => now(), // زمان فعلی
            ]);

            return response()->json([
                'message' => 'success',
                'auth' => $user,
                'retoken' => $refreshToken,
                'token' => $token
            ]);
        } else {
            return response()->json(['message' => 'invalid_code']);
        }
    }
}
