<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplySessionOverrides
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            if (session()->has('active_role')) {
                $user->role = session('active_role');
                if ($user->role === 'walikelas' || $user->role === 'ketuakelas') {
                    $user->name = $user->walikelas_kelas;
                }
            }
        }

        return $next($request);
    }
}
