<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

class AuthController extends Controller
{
    public function login()
    {
        return view('auths.login');
    }

    public function postlogin(Request $request)
    {
        if (Auth::attempt($request->only('username', 'password'))) {
            return redirect('/dashboard');
        }
        
        // Return JSON response for failed login
        return response()->json(['error' => 'Username or password is incorrect.'], 401);
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/login');
    }
}
