<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use DB;

class AuthController extends Controller
{
    public function login()
    {
        $active_semesters = DB::table('tahun_ajaran')->where('status', 1)->get();
        return view('auths.login', compact('active_semesters'));
    }

    public function postlogin(Request $request)
    {
        if (Auth::attempt($request->only('username', 'password'))) {
            // Reset active role override from previous session
            session()->forget('active_role');

            // Find selected tahun ajaran & semester
            $ta = DB::table('tahun_ajaran')->find($request->tahun_ajaran_id);
            if ($ta) {
                session(['tahun_ajaran' => $ta->tahun_ajaran]);
                session(['semester' => $ta->semester]);
                session(['tahun_ajaran_id' => $ta->id]);
            } else {
                // If somehow not found, fall back to the first active one
                $default = DB::table('tahun_ajaran')->where('status', 1)->first();
                if ($default) {
                    session(['tahun_ajaran' => $default->tahun_ajaran]);
                    session(['semester' => $default->semester]);
                    session(['tahun_ajaran_id' => $default->id]);
                }
            }
            
            \App\Helpers\AuditLog::write('Berhasil login ke sistem');
            
            return redirect('/dashboard');
        }
        
        // Return JSON response for failed login
        return response()->json(['error' => 'Username or password is incorrect.'], 401);
    }

    public function logout()
    {
        \App\Helpers\AuditLog::write('Melakukan logout dari sistem');
        if (auth()->check()) {
            \DB::table('users_fcm_tokens')->where('user_id', auth()->id())->delete();
        }
        Auth::logout();
        session()->forget('active_role');
        return redirect('/login');
    }

    public function updateFcmToken(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        if (auth()->check()) {
            \DB::table('users_fcm_tokens')->updateOrInsert(
                [
                    'user_id' => auth()->id(),
                    'fcm_token' => $request->fcm_token,
                ],
                [
                    'device_type' => $request->input('device_type', 'android'),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
            return response()->json(['status' => 'success', 'message' => 'FCM token updated successfully']);
        }

        return response()->json(['status' => 'error', 'message' => 'Unauthenticated'], 401);
    }

    public function gantiPassword()
    {
        return view('auths.ganti_password');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password_sekarang' => 'required',
            'password_baru' => 'required|min:6',
            'konfirmasi_password_baru' => 'required|same:password_baru',
        ], [
            'password_sekarang.required' => 'Password saat ini wajib diisi.',
            'password_baru.required' => 'Password baru wajib diisi.',
            'password_baru.min' => 'Password baru minimal harus 6 karakter.',
            'konfirmasi_password_baru.required' => 'Konfirmasi password baru wajib diisi.',
            'konfirmasi_password_baru.same' => 'Konfirmasi password baru tidak cocok dengan password baru.',
        ]);

        $user = auth()->user();

        if (!\Hash::check($request->password_sekarang, $user->password)) {
            return redirect()->back()->with('gagal', 'Password saat ini salah.');
        }

        $dbUser = \App\Models\User::find($user->id);
        $dbUser->password = bcrypt($request->password_baru);
        $dbUser->needs_password_change = 0;
        $dbUser->save();

        \App\Helpers\AuditLog::write('Berhasil memperbarui password akun');

        return redirect()->back()->with('sukses', 'Password Anda berhasil diperbarui!');
    }

    public function autoLogin(Request $request)
    {
        $token = $request->query('token');
        $redirect = $request->query('redirect', '/dashboard');
        $tahun_ajaran_id = $request->query('tahun_ajaran_id');

        \Illuminate\Support\Facades\Log::info("SINALA AutoLogin attempt", [
            'token' => $token ? substr($token, 0, 10) . '...' : null,
            'redirect' => $redirect,
            'tahun_ajaran_id' => $tahun_ajaran_id
        ]);

        if (!$token) {
            \Illuminate\Support\Facades\Log::warning("SINALA AutoLogin failed: Token is missing");
            return redirect('/login');
        }

        // Custom manual token resolution to support any Sanctum version
        $id = null;
        $plainTextToken = $token;
        if (str_contains($token, '|')) {
            [$id, $plainTextToken] = explode('|', $token, 2);
        }

        $hashedToken = hash('sha256', $plainTextToken);

        $accessToken = DB::table('personal_access_tokens')
            ->when($id, function($query) use ($id) {
                return $query->where('id', $id);
            })
            ->where('token', $hashedToken)
            ->first();

        if (!$accessToken) {
            \Illuminate\Support\Facades\Log::warning("SINALA AutoLogin failed: Hashed token not found in personal_access_tokens", [
                'id' => $id,
                'hashed' => $hashedToken
            ]);
            return redirect('/login');
        }

        $user = \App\Models\User::find($accessToken->tokenable_id);
        if (!$user) {
            \Illuminate\Support\Facades\Log::warning("SINALA AutoLogin failed: User not found for tokenable_id: {$accessToken->tokenable_id}");
            return redirect('/login');
        }

        // Log the user into the session
        Auth::login($user);

        // Check if there is an active role override request
        $asRole = $request->query('as_role');
        if ($asRole && $user->hasRole($asRole)) {
            session([
                'active_role' => $asRole
            ]);
        } else {
            session()->forget(['active_role']);
        }

        // Find selected tahun ajaran
        if ($tahun_ajaran_id) {
            $ta = DB::table('tahun_ajaran')->find($tahun_ajaran_id);
            if ($ta) {
                session(['tahun_ajaran' => $ta->tahun_ajaran]);
                session(['semester' => $ta->semester]);
                session(['tahun_ajaran_id' => $ta->id]);
            }
        } else {
            // Default to first active
            $default = DB::table('tahun_ajaran')->where('status', 1)->first();
            if ($default) {
                session(['tahun_ajaran' => $default->tahun_ajaran]);
                session(['semester' => $default->semester]);
                session(['tahun_ajaran_id' => $default->id]);
            }
        }

        \Illuminate\Support\Facades\Log::info("SINALA AutoLogin success: Logged in user ID {$user->id}");

        return redirect($redirect);
    }

    public function switchRole(Request $request)
    {
        $request->validate([
            'role' => 'required'
        ]);

        $user = auth()->user();
        if ($user && $user->hasRole($request->role)) {
            session(['active_role' => $request->role]);
            
            \App\Helpers\AuditLog::write('Beralih peran aktif menjadi: ' . $request->role);
            
            return redirect()->back()->with('sukses', 'Berhasil beralih peran ke ' . strtoupper($request->role));
        }

        return redirect()->back()->with('gagal', 'Anda tidak memiliki akses untuk peran tersebut.');
    }
}
