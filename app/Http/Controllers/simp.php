<?php

namespace App\Http\Controllers;
use Laravel\Jetstream\Jetstream;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

class simp extends Controller
{

    public function simp(Request $request)
    {
        $inve = array(
            (object) ["value" => 0, "type" => 0, "name" => "تومن"],
            (object) ["value" => 0, "type" => 1, "name" => "تمام"],
            (object) ["value" => 0, "type" => 2, "name" => "نیم"],
            (object) ["value" => 0, "type" => 3, "name" => "ربع"],
            (object) ["value" => 0, "type" => 4, "name" => "USDT"],
            (object) ["value" => 0, "type" => 5, "name" => "NFX"],
        );
        $vahed = DB::table('vaheds')->get();
        return Jetstream::inertia()->render($request, 'Simp/Panel', [
            'inve' => $inve,
            'vaheds' => $vahed,
        ]);
    }
    
    public function wallet(Request $request)
    {
        $inve = array(
            (object) ["value" => 0, "type" => 0, "name" => "تومن"],
            (object) ["value" => 0, "type" => 1, "name" => "تمام"],
            (object) ["value" => 0, "type" => 2, "name" => "نیم"],
            (object) ["value" => 0, "type" => 3, "name" => "ربع"],
            (object) ["value" => 0, "type" => 4, "name" => "USDT"],
            (object) ["value" => 0, "type" => 5, "name" => "NFX"],
        );
        $vahed = DB::table('vaheds')->get();
        return Jetstream::inertia()->render($request, 'Simp/Wallet', [
            'inve' => $inve,
            'prices' => $vahed,
        ]);
    }
    
    public function history(Request $request)
    {
        $inve = array();
        $vahed = DB::table('vaheds')->get();
        return Jetstream::inertia()->render($request, 'Simp/History', [
            'trans' => $inve,
            'vaheds' => $vahed,
        ]);
    }
    
    public function profile(Request $request)
    {
        $inve = array(
            (object) ["value" => 0, "type" => 0, "name" => "تومن"],
            (object) ["value" => 0, "type" => 1, "name" => "تمام"],
            (object) ["value" => 0, "type" => 2, "name" => "نیم"],
            (object) ["value" => 0, "type" => 3, "name" => "ربع"],
            (object) ["value" => 0, "type" => 4, "name" => "USDT"],
            (object) ["value" => 0, "type" => 5, "name" => "NFX"],
        );
        $vahed = DB::table('vaheds')->get();
        return Jetstream::inertia()->render($request, 'Simp/Profile', [
            'inve' => $inve,
            'vaheds' => $vahed,
        ]);
    }
    
    public function card2card(Request $request)
    {
        $bank_acc = DB::table('hesabs')->get();
        $deposit_req = array();
        return Jetstream::inertia()->render($request, 'Simp/card2card', [
            'bank_acc' => $bank_acc,
            'deposits' => $deposit_req,
        ]);
    }
    public function debit(Request $request)
    {

        $deposit_req = array();
        $bank = array();
        $vahed = DB::table('vaheds')->get();
        $inve = array(
            (object) ["value" => 0, "type" => 0, "name" => "تومن"],
            (object) ["value" => 0, "type" => 1, "name" => "تمام"],
            (object) ["value" => 0, "type" => 2, "name" => "نیم"],
            (object) ["value" => 0, "type" => 3, "name" => "ربع"],
            (object) ["value" => 0, "type" => 4, "name" => "USDT"],
            (object) ["value" => 0, "type" => 5, "name" => "NFX"],
        );
        return Jetstream::inertia()->render($request, 'Simp/debit', [
           
            'debits' => $deposit_req,
            'inve' => $inve,
            'bank_info' => $bank,
            'vaheds' => $vahed,
        ]);
    }
    public function buy(Request $request)
    {
        $inve = array(
            (object) ["value" => 0, "type" => 0, "name" => "تومن"],
            (object) ["value" => 0, "type" => 1, "name" => "تمام"],
            (object) ["value" => 0, "type" => 2, "name" => "نیم"],
            (object) ["value" => 0, "type" => 3, "name" => "ربع"],
            (object) ["value" => 0, "type" => 4, "name" => "USDT"],
            (object) ["value" => 0, "type" => 5, "name" => "NFX"],
        );
        $vahed = DB::table('vaheds')->get();
        $buys = array();
        return Jetstream::inertia()->render($request, 'Simp/buy', [
            'inve' => $inve,
            'vaheds' => $vahed,
            'buys' => $buys,
        ]);
    }
    public function sell(Request $request)
    {
        $inve = array(
            (object) ["value" => 0, "type" => 0, "name" => "تومن"],
            (object) ["value" => 0, "type" => 1, "name" => "تمام"],
            (object) ["value" => 0, "type" => 2, "name" => "نیم"],
            (object) ["value" => 0, "type" => 3, "name" => "ربع"],
            (object) ["value" => 0, "type" => 4, "name" => "USDT"],
            (object) ["value" => 0, "type" => 5, "name" => "NFX"],
        );
        $sells = array();

        $vahed = DB::table('vaheds')->get();
        return Jetstream::inertia()->render($request, 'Simp/sell', [
            'inve' => $inve,
            'units' => $vahed,
            'sells' => $sells,
        ]);
    }
}
