<?php
use App\Models\Transaction;
use App\Models\User;
use App\Models\Vahed;
use App\Models\Inventory;
use App\Models\transforms;
use App\Models\logs;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;


if (!function_exists('aban_req')) {
    function aban_req($method, $endpoint, $body = [], $queryParams = [])
    {
        $baseUrl = "https://api.abanprime.com";
        $apiKey = '7Ai0sEo6x3y90dPu';
        $secretKey = 'AAaCE9e3UC9u6f5Arzeyqj1EQLmmKxDl';
        $timeStamp = round(microtime(true) * 1000); // گرفتن timestamp بر حسب میلی‌ثانیه
        $apiSignBeforeHash = "{$endpoint}{$method}{$apiKey}{$timeStamp}";
        // هش کردن مقدار ورودی
        $apiSign = base64_encode(hash_hmac("sha256", $apiSignBeforeHash, $secretKey, true));
        $url = $baseUrl . $endpoint;
        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }
        // مقداردهی اولیه cURL
        $ch = curl_init($url);
        $headers = [
            "API-SIGN: $apiSign",
            "TIMESTAMP: $timeStamp",
            "API-KEY: $apiKey",
            "Content-Type: application/json"
        ];

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // تنظیم متد درخواست (GET, POST, PUT, DELETE)
        switch (strtoupper($method)) {
            case "POST":
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
                break;
            case "PUT":
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
                break;
            case "DELETE":
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                break;
        }
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (curl_errno($ch)) {
            return ['success' => false, 'error' => 'خطا در ارتباط با سرور ثانی'.curl_error($ch)];
        }
        curl_close($ch);
        return ['success' => true, 'res' => $response];

    }
}


if (!function_exists('send_ksms')) {
    function send_ksms($reciver_phone ,$token ,$template)
    {
        $apiKey = "68514D763339754C356D594F7A67417A6F486F75506A56347777754D503268496C73614A374A6E2B7162453D"; 
        $url = "https://api.kavenegar.com/v1/{$apiKey}/verify/lookup.json";  
        $postFields = http_build_query([
            'receptor' => $reciver_phone,
            'token' => $token,
            'template' => $template,
            'type' => 'sms',
        ]);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
}
if (!function_exists('send_sms')) {
    function send_sms($text, $phone_to)
    {
        $url = "https://login.niazpardaz.ir/SMSInOutBox/SendSms?username=t.09354482018&password=qwer@@1234&from=10009611&to=" . $phone_to . "&text=" . $text;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
        ));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30000);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        $sms_res = curl_exec($ch);
        curl_close($ch);
    }
}


if (!function_exists('variz')) {
    function variz($id,$vahed_id, $amout, $who_ver_deposit)
    {
        $user = DB::table('users')->where('id', $id)->get();
        $vahed = DB::table('vaheds')->where('type', $vahed_id)->get();
        $inv = DB::table('invertory')->where('user_id', $id)->where('type', $vahed[0]->type)->get();
        $all = $inv[0]->value + $amout;
        transforms::create([
            "user_id" => $user[0]->id,
            "type" => 0,
            "price" => $amout,
            "verify" => 1,
            "time" => date("Y/m/d h:i:s "),
            "value_type" =>  $vahed[0]->type
        ]);
        try {
            logs::create([
                "who_ver_deposit" => $who_ver_deposit,
                "who_deposit" => $user[0]->id,
                "type" => 0,
                "value" => $amout,
            ]);
            DB::table('invertory')->where('user_id', $id)->where('type', $vahed[0]->type)->update(['value' => $all]);
            $res = "succ";
        } catch (\Throwable $th) {
            $res = $th;
        };
        return $res;
    }
}
if (!function_exists('barda')) {
    function barda($item, $who_ver_debit)
    {
        $vahed_id = $item->vahed;
        $amout = $item->value;
        $user = DB::table('users')->where('id', $item->user_id)->get();
        $vahed = DB::table('vaheds')->where('type', $vahed_id)->get();
        try {
            transforms::create([
                "user_id" => $user[0]->id,
                "type" => 1,
                "price" => $amout,
                "verify" => 1,
                "time" => date("Y/m/d h:i:s "),
                "value_type" =>  $vahed[0]->type
            ]);
            logs::create([
                "who_ver_deposit" => $who_ver_debit,
                "who_deposit" => $user[0]->id,
                "type" => 1,
                "value" => $amout,
            ]);
            $res = "succ";
        } catch (\Throwable $th) {
            $res = $th;
        };
        return $res;
    }
}
if (!function_exists('unbarda')) {
    function unbarda($item, $who_ver_debit) {
        $id = $item->user_id;
        $vahed_id = $item->vahed;
        $amout = $item->value;

        $user = DB::table('users')->where('id', $id)->get();
        $vahed = DB::table('vaheds')->where('type', $vahed_id)->get();
        $inv = DB::table('invertory')->where('user_id', $id)->where('type', $vahed[0]->type)->get();
        $all = $inv[0]->value + $amout;
        try {
            logs::create([
                "who_ver_deposit" => $who_ver_debit,
                "who_deposit" => $user[0]->id,
                "type" => 11,
                "value" => $amout,
            ]);
            DB::table('invertory')->where('user_id', $id)->where('type', $vahed[0]->type)->update(['value' => $all]);
            $res = "succ";
        } catch (\Throwable $th) {
            $res = $th;
        };
        return $res;
    }
}

if (!function_exists('buy')) {
    function buy($req_id, $who)
    {
        $de = DB::table('verify_req')->where('type', 2)->where('id', $req_id)->get();
        $id = $de[0]->user_id;
        $buy_unit = $de[0]->vahed;
        $amout = $de[0]->value;
        $price = $de[0]->reason;
        $who_ver_buy = $who;
        $value_type = $de[0]->vahed;


        $user = DB::table('users')->where('id', $id)->get();
        $user_inv = DB::table('invertory')->where('user_id', $id)->where('type', $buy_unit)->get();
        $all = $user_inv[0]->value + $amout;
        try {
            transforms::create([
                "user_id" => $user[0]->id,
                "type" => 2,
                "price" => $price,
                "value" => $amout,
                "value_type" => $value_type,
                "verify" => 1,
                "time" => date("Y/m/d h:i:s "),

            ]);
            logs::create([
                "who_ver_deposit" => $who_ver_buy,
                "who_deposit" => $user[0]->id,
                "type" => 2,
                "unit_type" => $value_type,
                "value" => $amout,
            ]);
            DB::table('invertory')->where('user_id', $id)->where('type', $buy_unit)->update(['value' => $all]);
            $res = "succ";
        } catch (\Throwable $th) {
            $res = $th;
        };
        return $res;
    }
}
if (!function_exists('unbuy')) {
    function unbuy($id, $buy_unit, $amout, $price, $who_ver_buy)
    {
        $user = DB::table('users')->where('id', $id)->get();
        $user_inv = DB::table('invertory')->where('user_id', $id)->where('type', 0)->get();
        $all = $user_inv[0]->value + $amout;
        try {
            logs::create([
                "who_ver_deposit" => $who_ver_buy,
                "who_deposit" => $user[0]->id,
                "type" => 22,
                "value" => $amout,
            ]);
            DB::table('invertory')->where('user_id', $id)->where('type', 0)->update(['value' => $all]);
            $res = "succ";
        } catch (\Throwable $th) {
            $res = $th;
        };
        return $res;
    }
}

if (!function_exists('sell')) {
    function sell($id, $sell_unit, $amout, $price, $who_ver_sell, $value_type)
    {
        $user = DB::table('users')->where('id', $id)->get();
        $user_inv = DB::table('invertory')->where('user_id', $id)->where('type', 0)->get();
        $all = $user_inv[0]->value + ($amout * $price);
        try {
            transforms::create([
                "user_id" => $user[0]->id,
                "type" => 3,
                "price" => $price,
                "value" => $amout,
                "verify" => 1,
                "value_type" => $value_type,
                "time" => date("Y/m/d h:i:s "),

            ]);
            logs::create([
                "who_ver_deposit" => $who_ver_sell,
                "who_deposit" => $user[0]->id,
                "type" => 3,
                "value" => $amout,
                "unit_type" => $value_type,

            ]);
            DB::table('invertory')->where('user_id', $id)->where('type', 0)->update(['value' => $all]);
            $res = "succ";
        } catch (\Throwable $th) {
            $res = $th;
        };
        return $res;
    }
}
if (!function_exists('unsell')) {
    function unsell($id, $sell_unit, $amout, $price, $who_ver_sell)
    {
        $user = DB::table('users')->where('id', $id)->get();
        $user_inv = DB::table('invertory')->where('user_id', $id)->where('type', $sell_unit)->get();
        $all = $user_inv[0]->value + $amout;
        try {

            logs::create([
                "who_ver_deposit" => $who_ver_sell,
                "who_deposit" => $user[0]->id,
                "type" => 33,
                "value" => $amout,
            ]);
            DB::table('invertory')->where('user_id', $id)->where('type', $sell_unit)->update(['value' => $all]);
            $res = "succ";
        } catch (\Throwable $th) {
            $res = $th;
        };
        return $res;
    }
}



if (!function_exists('man_dep')) {
    function man_dep($id, $unit_type, $amout, $price, $who_dep)
    {
        $user = DB::table('users')->where('id', $id)->get();
        $user_inv = DB::table('invertory')->where('user_id', $id)->where('type', $unit_type)->get();
        $all = $user_inv[0]->value + $amout;
        try {
            transforms::create([
                "user_id" => $user[0]->id,
                "type" => 1101,
                "price" => $price,
                "value" => $amout,
                "verify" => 1,
                "value_type" => $unit_type,
                "time" => date("Y/m/d h:i:s "),
            ]);
            logs::create([
                "who_ver_deposit" => $who_dep,
                "who_deposit" => $user[0]->id,
                "type" => 1101,
                "value" => $amout,
            ]);
            DB::table('invertory')->where('user_id', $id)->where('type', $unit_type)->update(['value' => $all]);
            $res = "succ";
        } catch (\Throwable $th) {$res = $th;}
        return $res;
    }
}
if (!function_exists('man_deb')) {
    function man_deb($id, $unit_type, $amout, $price, $who_dep)
    {
        $user = DB::table('users')->where('id', $id)->get();
        $user_inv = DB::table('invertory')->where('user_id', $id)->where('type', $unit_type)->get();
        $all = $user_inv[0]->value - $amout;
        try {
            transforms::create([
                "user_id" => $user[0]->id,
                "type" => 1102,
                "price" => $price,
                "value" => $amout,
                "value_type" => $unit_type,
                "verify" => 0,
                "time" => date("Y/m/d h:i:s "),
            ]);
            logs::create([
                "who_ver_deposit" => $who_dep,
                "who_deposit" => $user[0]->id,
                "type" => 1102,
                "value" => $amout,
            ]);
            DB::table('invertory')->where('user_id', $id)->where('type', $unit_type)->update(['value' => $all]);
            $res = "succ";
        } catch (\Throwable $th) {
            $res = $th;
        };
        return $res;
    }
}
if (!function_exists('rest_wallet')) {
    function rest_wallet()
    {
        $users = DB::table('users')->pluck('id')->toArray();
        $vaheds = DB::table('vaheds')->get();
        foreach ($users as $user) {
            foreach ($vaheds as $vahed) {
                $existingRecord = DB::table('invertory')
                    ->where('user_id', $user)
                    ->where('type', $vahed->type)
                    ->first();
                if (!$existingRecord) {
                    Inventory::create([
                        'user_id' => $user,
                        'value' => 0,
                        'type' => $vahed->type,
                    ]);
                }
            }
        }
        return true;
    }
}
if (!function_exists('send_notif')) {
    function send_notif($type)
    {
        if ($type == 0) {
            $mass = "ثبت%20نام%20جدید";
        } else if ($type == 1) {
            $mass = "واریز%20جدید";
        } else if ($type == 2) {
            $mass = "برداشت%20جدید";
        } else if ($type == 3) {
            $mass = "خرید%20جدید";
        } else if ($type == 4) {
            $mass = "فروش%20جدید";
        } else if ($type == 5) {
            $mass = "حساب%20بانکی%20جدید";
        }
        // $mass= "کد%20ورود%20شما:" . $type . "%20نوینکس";
        $phone = DB::table('options')->where('type', 3)->get();

        for ($i = 0; $i < count($phone); $i++) {

            $url = "https://login.niazpardaz.ir/SMSInOutBox/SendSms?username=t.09354482018&password=qwer@@1234&from=10009611&to=" . $phone[$i]->value . "&text=$mass";
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
            ));
            curl_setopt($ch, CURLOPT_TIMEOUT, 30000);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            $sms_res = curl_exec($ch);
            curl_close($ch);
        }
        if ($sms_res == "SendWasSuccessful") {
            return 'succ';
        } else {
            return $sms_res;
        }
    }
}
if (!function_exists('send_email')) {
    function send_email($to,$subject,$body)
    {
        try {
            Mail::raw($body, function ($message) use ($to, $subject) {
                $message->to($to)->subject($subject)->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
            });
            return "success";
        } catch (\Throwable $th) {
            return $th;
        }
    }
}
