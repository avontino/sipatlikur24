<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id','role','name', 'username', 'password', 'needs_password_change', 'created_at','tidakhadir','walikelas_kelas','additional_roles','default_shift_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Get list of all available roles for the user.
     *
     * @return array
     */
    public function getAvailableRoles()
    {
        $roles = [$this->getOriginal('role') ?? $this->role];
        if (!empty($this->additional_roles)) {
            $extra = explode(',', $this->additional_roles);
            foreach ($extra as $r) {
                $r = trim($r);
                if (!empty($r)) {
                    $roles[] = $r;
                }
            }
        }
        return array_values(array_unique($roles));
    }

    /**
     * Check if user is assigned the given role.
     *
     * @param string $role
     * @return bool
     */
    public function hasRole($role)
    {
        return in_array($role, $this->getAvailableRoles());
    }

    /**
     * Get default permissions mapping.
     *
     * @return array
     */
    public static function getDefaultPermissions()
    {
        return [
            // Jurnal & Jadwal
            'jurnal_view' => ['admin', 'guru', 'kurikulum', 'tamu', 'lihat', 'ketuakelas', 'walikelas'],
            'jurnal_create' => ['admin', 'guru', 'ketuakelas', 'walikelas'],
            'jurnal_edit' => ['admin', 'guru', 'ketuakelas', 'walikelas'],
            'jurnal_delete' => ['admin'],
            'jurnal_export' => ['admin', 'guru', 'lihat'],
            'jurnal_import' => ['admin'],

            // Presensi
            'presensi_view' => ['admin', 'guru', 'lihat', 'ketuakelas', 'walikelas', 'siswa', 'tendik'],
            'presensi_create' => ['admin', 'guru', 'ketuakelas', 'siswa', 'tendik'],
            'presensi_edit' => ['admin', 'guru', 'ketuakelas'],
            'presensi_delete' => ['admin'],
            'presensi_export' => ['admin', 'guru'],
            'presensi_import' => ['admin'],

            // Izin
            'izin_view' => ['admin', 'guru', 'kepala', 'pembina', 'kurikulum', 'walikelas', 'kesehatan', 'siswa', 'satpam'],
            'izin_create' => ['admin', 'guru', 'siswa'],
            'izin_edit' => ['admin', 'guru', 'kepala'],
            'izin_delete' => ['admin'],
            'izin_export' => ['admin'],
            'izin_import' => ['admin'],

            // Kasus & Laporan (Poin)
            'poin_view' => ['admin', 'kesiswaan', 'pembina', 'walikelas', 'guru'],
            'poin_create' => ['admin', 'kesiswaan', 'pembina', 'guru'],
            'poin_edit' => ['admin', 'kesiswaan'],
            'poin_delete' => ['admin'],
            'poin_export' => ['admin'],
            'poin_import' => ['admin'],

            // Tagihan / Keuangan
            'tagihan_view' => ['admin', 'keuangan', 'siswa'],
            'tagihan_create' => ['admin', 'keuangan'],
            'tagihan_edit' => ['admin', 'keuangan'],
            'tagihan_delete' => ['admin'],
            'tagihan_export' => ['admin'],
            'tagihan_import' => ['admin'],

            // Data Master
            'master_view' => ['admin', 'kurikulum'],
            'master_create' => ['admin'],
            'master_edit' => ['admin'],
            'master_delete' => ['admin'],
            'master_export' => ['admin'],
            'master_import' => ['admin'],

            // Operator
            'operator_view' => ['admin'],
            'operator_create' => ['admin'],
            'operator_edit' => ['admin'],
            'operator_delete' => ['admin'],
            'operator_export' => ['admin'],
            'operator_import' => ['admin'],
        ];
    }

    /**
     * Check if user's active role has a given permission.
     *
     * @param string $permission
     * @param string|null $roleOverride
     * @return bool
     */
    public function hasPermission($permission, $roleOverride = null)
    {
        if ($roleOverride) {
            return $this->hasRolePermission($roleOverride, $permission);
        }

        foreach ($this->getAvailableRoles() as $role) {
            if ($this->hasRolePermission($role, $permission)) {
                return true;
            }
        }

        return false;
    }

    protected function hasRolePermission($role, $permission)
    {
        // Admin always has all permissions
        if ($role === 'admin') {
            return true;
        }

        // Check if there are any configured permissions in the database
        $hasConfigured = \App\Models\RolePermission::exists();

        if ($hasConfigured) {
            return \App\Models\RolePermission::where('role', $role)
                ->where('permission', $permission)
                ->exists();
        }

        // Fallback to default permissions map
        $defaults = self::getDefaultPermissions();
        if (isset($defaults[$permission])) {
            return in_array($role, $defaults[$permission]);
        }

        return false;
    }

    /**
     * Get all permission keys for a given role.
     *
     * @param string $role
     * @return array
     */
    public function getPermissionsForRole($role)
    {
        if ($role === 'admin') {
            return array_keys(self::getDefaultPermissions());
        }

        $hasConfigured = \App\Models\RolePermission::exists();

        if ($hasConfigured) {
            return \App\Models\RolePermission::where('role', $role)
                ->pluck('permission')
                ->toArray();
        }

        $permissions = [];
        foreach (self::getDefaultPermissions() as $permission => $roles) {
            if (in_array($role, $roles)) {
                $permissions[] = $permission;
            }
        }
        return $permissions;
    }

    /**
     * Send in-app notification to this user.
     *
     * @param string $title
     * @param string $message
     * @param string|null $actionUrl
     * @param string $category
     * @return bool
     */
    public function sendNotification($title, $message, $actionUrl = null, $category = 'system')
    {
        $inserted = \Illuminate\Support\Facades\DB::table('notifications')->insert([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\Notifications\SystemNotification',
            'notifiable_type' => 'App\Models\User',
            'notifiable_id' => $this->id,
            'data' => json_encode([
                'title' => $title,
                'message' => $message,
                'action_url' => $actionUrl,
                'category' => $category,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Triger push notification ke perangkat HP Android via FCM jika token terdaftar
        try {
            \App\Helpers\FcmHelper::sendToUser($this->id, $title, $message, [
                'action_url' => $actionUrl ?? '/dashboard',
                'category' => $category
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('FCM Push Notification failed: ' . $e->getMessage());
        }

        return $inserted;
    }

    /**
     * Get the class managed by this user (Wali Kelas, Ketua Kelas, or Guru with wali duties).
     *
     * @return string|null
     */
    public function getManagedClass()
    {
        // Prioritas 1: walikelas_kelas sudah diset secara eksplisit oleh admin
        if ($this->walikelas_kelas) {
            return $this->walikelas_kelas;
        }

        if ($this->hasRole('ketuakelas') || $this->hasRole('walikelas')) {
            // Prioritas 2: Cari kelas dari tabel siswa berdasarkan tahun ajaran AKTIF
            $rawTa = \Illuminate\Support\Facades\DB::table('tahun_ajaran')
                ->where('status', 1)
                ->value('tahun_ajaran');

            if ($rawTa) {
                $cleanTa = trim(preg_replace('/\s*\(.*\)/', '', $rawTa));
                $firstYear = explode('/', $cleanTa)[0] ?? $cleanTa;

                $siswaClass = \App\Models\Siswa::where(function ($q) {
                        $q->where('nis', $this->username)
                          ->orWhere('nama', $this->name);
                    })
                    ->where(function ($q) use ($rawTa, $cleanTa, $firstYear) {
                        $q->where('tahun_ajaran', $rawTa)
                          ->orWhere('tahun_ajaran', 'LIKE', '%' . $cleanTa . '%')
                          ->orWhere('tahun_ajaran', 'LIKE', '%' . $firstYear . '%')
                          ->orWhereNull('tahun_ajaran');
                    })
                    ->orderBy('id', 'desc')
                    ->value('kelas');

                if ($siswaClass) {
                    return $siswaClass;
                }
            }

            // Prioritas 3: Fallback — ambil data siswa terbaru (id terbesar)
            $siswaClass = \App\Models\Siswa::where(function ($q) {
                    $q->where('nis', $this->username)
                      ->orWhere('nama', $this->name);
                })
                ->orderBy('id', 'desc')
                ->value('kelas');

            if ($siswaClass) {
                return $siswaClass;
            }

            return $this->name;
        }

        if ($this->hasRole('guru') && $this->walikelas_kelas) {
            return $this->walikelas_kelas;
        }

        return null;
    }

    /**
     * Relationship to default shift.
     */
    public function defaultShift()
    {
        return $this->belongsTo(Shift::class, 'default_shift_id');
    }

    /**
     * Relationship to shift schedules (roster).
     */
    public function shiftSchedules()
    {
        return $this->hasMany(UserShiftSchedule::class, 'user_id');
    }

    /**
     * Get active shift for a given date (defaults to today).
     * Priority: 1) Scheduled Roster for date, 2) User default shift, 3) System default shift ID 1.
     *
     * @param string|null $tanggal (Y-m-d)
     * @return \App\Models\Shift
     */
    public function getActiveShift($tanggal = null)
    {
        $targetDate = $tanggal ?? \Carbon\Carbon::today()->toDateString();

        // 1. Check daily roster schedule
        $schedule = UserShiftSchedule::where('user_id', $this->id)
            ->where('tanggal', $targetDate)
            ->with('shift')
            ->first();

        if ($schedule && $schedule->shift) {
            return $schedule->shift;
        }

        // 2. Check user default_shift_id
        if ($this->default_shift_id && $this->defaultShift) {
            return $this->defaultShift;
        }

        // 3. Fallback to Shift ID 1 (Reguler Guru & Tendik)
        $fallbackShift = Shift::find(1);
        if ($fallbackShift) {
            return $fallbackShift;
        }

        // 4. Emergency in-memory fallback
        return new Shift([
            'id' => 0,
            'nama_shift' => 'Reguler Standard',
            'jam_masuk' => '07:00:00',
            'jam_pulang' => '16:00:00',
            'is_overnight' => false,
            'toleransi_terlambat' => 0
        ]);
    }
}
