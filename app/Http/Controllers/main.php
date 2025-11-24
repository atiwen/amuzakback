<?php

namespace App\Http\Controllers;

use ArrayObject;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\notif;
use App\Models\supp_mass;
use Jenssegers\Agent\Agent;
use Laravel\Jetstream\Jetstream;


class main extends Controller
{
    // not complate
    public function notifs(Request $request)
    {
        $alers = [];
        $notifications = DB::table('notifs')->where('user_id', $request->user()->id)->where('is_user', 1)->where('is_readed', 0)->get();
        $result = $notifications;
        return response()->json($result);
    }
     public function support(Request $request)
     {
         $massages = DB::table('supp_mass')->where('user_id', $request->user()->id)->get();
         DB::table('notifs')->where('user_id', $request->user()->id)->where('is_user', 1)->where('type', 2)->update([
             "is_readed" => 1
         ]);
 
         return Jetstream::inertia()->render($request, 'Other/Support', [
             "massages" => $massages
         ]);
     }
     public function add_sup_mass(Request $request)
     {
         $request->validate([
             'mass' => ['required', 'string', 'max:250'],
 
         ]);
         supp_mass::create([
             'user_id' => $request->user()->id,
             'mass' => $request->mass,
             'type' => 0
         ]);
 
         $mass = 'پیام جدید توسط ' . $request->user()->name;
         notif::create([
             'user_id' => $request->user()->id,
             'massage' => $mass,
             'type' => 2,
             'link' => '/adm/support',
         ]);
         return redirect()->route(route: 'other.support');
     }

     


   
    public function sessions(Request $request)
    {
        if (config('session.driver') !== 'database') {
            return collect();
        }

        return collect(
            DB::connection(config('session.connection'))->table(config('session.table', 'sessions'))
                ->where('user_id', $request->user()->getAuthIdentifier())
                ->orderBy('last_activity', 'desc')
                ->get()
        )->map(function ($session) use ($request) {
            $agent = $this->createAgent($session);

            return (object) [
                'agent' => [
                    'is_desktop' => $agent->isDesktop(),
                    'platform' => $agent->platform(),
                    'browser' => $agent->browser(),
                ],
                'ip_address' => $session->ip_address,
                'is_current_device' => $session->id === $request->session()->getId(),
                'last_active' => Carbon::createFromTimestamp($session->last_activity)->diffForHumans(),
            ];
        });
    }

    protected function createAgent($session)
    {
        return tap(new Agent, function ($agent) use ($session) {
            $agent->setUserAgent($session->user_agent);
        });
    }
}
