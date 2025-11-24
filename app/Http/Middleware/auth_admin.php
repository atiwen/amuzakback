<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

class auth_admin
{

    public function handle(Request $request, Closure $next): Response
    {
        $currentRouteName = Route::currentRouteName();
        if ($currentRouteName && strpos($currentRouteName, 'admin.') === 0) {
            if ($request->user() != null) {
                if ($request->user()->role == 'admin') {
                    return $next($request);
                } else {
                    abort(403, 'َشما دسترسی به اینجا را ندارید');
                }
            } else {
                return redirect('adm/login');
            }
        } elseif($currentRouteName == 'login'){
            if ($request->user() == null) {
                return $next($request);
            } else {
                return redirect('panel');
            }
        } else {
            // abort(403, $currentRouteName);
            return $next($request);
        }
    }
}
