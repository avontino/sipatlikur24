<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AuditLog
{
    public static function write($activity)
    {
        $userId = null;
        $username = null;
        $name = null;
        $role = null;

        if (Auth::check()) {
            $user = Auth::user();
            $userId = $user->id;
            $username = $user->username;
            $name = $user->name;
            $role = $user->role;
        } else {
            $username = 'guest';
            $name = 'Guest User';
            $role = 'guest';
        }

        try {
            DB::table('logs_aktivitas')->insert([
                'user_id' => $userId,
                'username' => $username,
                'nama_pengguna' => $name,
                'role' => $role,
                'aktivitas' => $activity,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('AuditLog error: ' . $e->getMessage());
        }
    }
}
