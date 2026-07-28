<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForcePasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            $user = auth()->user();
            // Redirect to change password if the flag is active, and they are not already navigating to ganti-password or logout
            if ($user->needs_password_change && !$request->is('ganti-password*') && !$request->is('logout*')) {
                return redirect('/ganti-password')->with('gagal', 'Anda wajib mengganti password default demi keamanan akun Anda.');
            }
        }

        return $next($request);
    }
}
