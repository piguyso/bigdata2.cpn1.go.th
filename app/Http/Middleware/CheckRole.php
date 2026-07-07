<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
 
    public function handle(Request $request, Closure $next, ...$roles)
        {
            // ถ้ายังไม่ได้ Login หรือ Role ของ User ไม่อยู่ในรายชื่อที่อนุญาต
            if (!Auth::check() || !in_array(Auth::user()->role, $roles)) {
                return redirect('/')->with('error', 'คุณไม่มีสิทธิ์เข้าถึงส่วนนี้');
            }

            return $next($request);
        }
}
