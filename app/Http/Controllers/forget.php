<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Laravel\Jetstream\Jetstream;

class forget extends Controller
{
    public function forget_1(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'integer'],
        ]);
        $res = (object) array('code' => 00, 'mass' => 'no phone');
        $phone = DB::table('users')->where('phone', $request->phone)->get();
        if (count($phone)) {
            $response = "";
            $reg_req_err = '';
            $step = 1;
            $id_code = rand(100000, 999999);
            $code = rand(10000, 99999);


            $respons = send_ksms($request->phone, $code, 'code');
            $sms_res = json_decode($respons);

            if ($sms_res->return->status == 200) {
                try {
                    DB::table('forgot_pass_reqs')->updateOrInsert(
                        ['phone' => $request->phone],
                        [
                            'id' => $id_code,
                            'code' => $code
                        ]
                    );
                    $res->code = 10;
                    $res->mass = $id_code;
                } catch (\Throwable $e) {
                    $res->code = 21;
                    $res->mass = $e->getMessage();
                }
            } else {
                $res->code = 21;
                $res->mass = $response;
            }
        } else {
            $res->code = 1;
            $res->mass = "no Phone";
        }
        return $res;
    }
    public function forget_2(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'integer'],
            'code' => ['required', 'integer'],
            'key_id' => ['required', 'integer'],
        ]);
        $res = (object) array('code' => 31, 'mass' => 'no access');
        $code_req = DB::table('forgot_pass_reqs')->where('id', $request->key_id)->where('code', $request->code)->where('phone', $request->phone)->get();
        if (count($code_req)) {
            $res->code = 10;
        } else {
            $res->code = 21;
        }
        return $res;
    }
    public function forget_3(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'integer'],
            'code' => ['required', 'integer'],
            'key_id' => ['required', 'integer'],
            'password' => ['required', 'string', 'min:8'],
        ]);
        $status = 'error';
        $phone = '';
        $pass = '';
        $req = DB::table('forgot_pass_reqs')->where('id', $request->key_id)->where('code', $request->code)->where('phone', $request->phone)->get();
        if (count($req)) {
            DB::table('users')->where('phone', $request->phone)->update(['password' => Hash::make($request->password)]);
            DB::table('forgot_pass_reqs')->where('id', $request->key_id)->where('code', $request->code)->where('phone', $request->phone)->delete();
            $status = 'succes';
            $phone = $request->phone;
            $pass = $request->password;
        }
        $user = DB::table('users')->where('phone', $request->phone)->get();
        return response()->json([
            'status' => $status,
            // 'phone' => $phone,
            // 'pass' => $pass
        ]);
    }

    public function forget_e_1(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string'],
        ]);

        $res = (object) array('code' => 00, 'mass' => 'no email');
        $user = DB::table('users')->where('email', $request->email)->get();
        if (count($user)) {
            $response = "";
            $id_code = rand(100000, 999999);
            $code = rand(10000, 99999);
            $subject = 'نوینکس(فراموشی رمزعبور)';
            $body = "ّکد تایید جهت فراموشی رمزعبور: " . $code;
            $to = $request->email;

            try {
                Mail::raw($body, function ($message) use ($to, $subject) {
                    $message->to($to)->subject($subject)->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                });
                $response = "success";
            } catch (\Throwable $e) {
                $error = "failed";
                $res->mass = $e->getMessage();
            }
            if ($response == "success") {
                try {
                    DB::table('forgot_pass_reqs')->updateOrInsert(
                        ['email' => $request->email],
                        [
                            'id' => $id_code,
                            'code' => $code
                        ]
                    );
                    $res->code = 10;
                    $res->mass = $id_code;
                } catch (\Throwable $e) {
                    $res->code = 21;
                    $res->mass = $e->getMessage();
                }
            } else {
                // $res->mass = 'cant send email';
            }
        } else {
            $res->code = 1;
            $res->mass = "no email";
        }
        return $res;
    }
    public function forget_e_2(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string'],
            'code'  => ['required', 'integer'],
            'key_id' => ['required', 'integer'],
        ]);
        $res = (object) array('code' => 31, 'mass' => 'no access');
        $code_req = DB::table('forgot_pass_reqs')->where('id', $request->key_id)->where('code', $request->code)->where('email', $request->email)->get();
        if (count($code_req)) {
            $res->code = 10;
        } else {
            $res->code = 21;
        }
        return $res;
    }
    public function forget_e_3(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string'],
            'code'  => ['required', 'integer'],
            'key_id' => ['required', 'integer'],
            'password' => ['required', 'string', 'min:8'],
        ]);
        $status = 'error';
        $email = '';
        $pass = '';
        $req = DB::table('forgot_pass_reqs')->where('id', $request->key_id)->where('code', $request->code)->where('email', $request->email)->get();
        if (count($req)) {
            DB::table('users')->where('email', $request->email)->update([
                'password' => Hash::make($request->password),
            ]);
            DB::table('forgot_pass_reqs')->where('id', $request->key_id)->where('code', $request->code)->where('email', $request->email)->delete();
            $status = 'succes';
            $email = $request->email;
            $pass = $request->password;
        }
        return response()->json([
            'status' => $status,
            // 'email' => $email,
            // 'pass' => $pass
        ]);
    }
}
