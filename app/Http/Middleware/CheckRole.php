<?php

namespace App\Http\Middleware;

use Closure;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, ...$roles)
    {
        $user = $request->user();
        if (!$user) {
            return redirect('/login');
        }

        // 1. Direct role check matching
        $userRoles = method_exists($user, 'getAvailableRoles') ? $user->getAvailableRoles() : [$user->role];
        if ($user->walikelas_kelas) {
            $userRoles[] = 'walikelas';
        }
        if (array_intersect($userRoles, $roles)) {
            return $next($request);
        }

        // 2. Resolve target action based on request path and method
        $path = strtolower($request->path());
        $action = 'view';
        if ($request->isMethod('post')) {
            $action = 'create'; // default POST
        }
        
        if (strpos($path, 'create') !== false || strpos($path, 'tambah') !== false || strpos($path, 'store') !== false || strpos($path, 'submit') !== false) {
            $action = 'create';
        } elseif (strpos($path, 'update') !== false || strpos($path, 'edit') !== false || strpos($path, 'save') !== false || strpos($path, 'approve') !== false || strpos($path, 'reject') !== false || strpos($path, 'verifikasi') !== false || strpos($path, 'reset-password') !== false) {
            $action = 'edit';
        } elseif (strpos($path, 'delete') !== false || strpos($path, 'destroy') !== false || strpos($path, 'remove') !== false) {
            $action = 'delete';
        } elseif (strpos($path, 'import') !== false) {
            $action = 'import';
        } elseif (strpos($path, 'export') !== false || strpos($path, 'cetak') !== false) {
            $action = 'export';
        }

        // 3. Dynamic permission mapping check
        $requiredPermissions = [];
        foreach ($roles as $role) {
            if ($role === 'kurikulum') {
                $requiredPermissions[] = "jurnal_{$action}";
                $requiredPermissions[] = "master_{$action}";
            } elseif ($role === 'keuangan') {
                $requiredPermissions[] = "tagihan_{$action}";
            } elseif ($role === 'kesiswaan') {
                $requiredPermissions[] = "poin_{$action}";
            } elseif ($role === 'walikelas' || $role === 'guru') {
                $requiredPermissions[] = "jurnal_{$action}";
                $requiredPermissions[] = "presensi_{$action}";
                $requiredPermissions[] = "izin_{$action}";
            } elseif ($role === 'siswa') {
                $requiredPermissions[] = "presensi_{$action}";
                $requiredPermissions[] = "izin_{$action}";
                $requiredPermissions[] = "tagihan_{$action}";
            }
        }

        foreach ($requiredPermissions as $perm) {
            if ($user->hasPermission($perm)) {
                return $next($request);
            }
        }

        return redirect('/dashboard')->with('gagal', 'Anda tidak memiliki hak akses untuk halaman tersebut.');
    }
}
