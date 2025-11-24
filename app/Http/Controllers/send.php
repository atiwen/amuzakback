<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

class send extends Controller
{
    public function check_send(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'min:9', 'max:10'],
        ]);
        $obj = (object) array(
            'isreg' => false,
            'name' => false,
        );
        $user = DB::table('users')->where('phone', $request->phone)->get();
        if (count($user)) {
            $obj->isreg = true;
            $obj->name = $user[0]->name;
        }
        return $obj;
    }
    public function check_sende(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string'],
        ]);
        $obj = (object) array(
            'isreg' => false,
            'name' => false,
        );
        $user = DB::table('users')->where('email', $request->email)->get();
        if (count($user)) {
            $obj->isreg = true;
            $obj->name = $user[0]->name;
        }
        return $obj;
    }
}
