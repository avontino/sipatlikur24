<?php
namespace App\Imports;

use App\Models\Siswa;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SiswaImport implements ToModel
{
    public function model(array $row)
    {
        // Skip empty rows
        if (empty($row[0]) && empty($row[1]) && empty($row[2])) {
            return null;
        }

        $col0 = strtolower(trim((string)$row[0]));
        $col1 = strtolower(trim((string)$row[1]));
        $col2 = strtolower(trim((string)$row[2]));

        // Skip header row
        if ($col0 === 'nama' || $col0 === 'nis' || $col1 === 'nama' || $col2 === 'nis') {
            return null;
        }

        // Detect column order:
        // Format A: [nis, nama, kelas]
        // Format B: [nama, kelas, nis]
        if (is_numeric(trim((string)$row[0])) && !is_numeric(trim((string)$row[2]))) {
            $nis = trim((string)$row[0]);
            $nama = trim((string)$row[1]);
            $kelas = trim((string)$row[2]);
        } else {
            $nama = trim((string)$row[0]);
            $kelas = trim((string)$row[1]);
            $nis = trim((string)$row[2]);
        }

        if (empty($nis) || empty($nama)) {
            return null;
        }

        // Sync to users table (auto-create login account with password = NIS)
        $exists = DB::table('users')->where('username', $nis)->exists();
        if (!$exists) {
            DB::table('users')->insert([
                'role' => 'siswa',
                'name' => $nama,
                'username' => $nis,
                'password' => Hash::make($nis), // Password = NIS
                'needs_password_change' => 0,
                'remember_token' => Str::random(60),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } else {
            DB::table('users')
                ->where('username', $nis)
                ->update([
                    'name' => $nama,
                    'updated_at' => now()
                ]);
        }

        $tahunAjaran = session('tahun_ajaran') ?: '2026/2027';
        $semester = session('semester') ?: 'Ganjil';

        // Check if student already exists in the current academic year
        $existingSiswa = Siswa::where('nis', $nis)
            ->where('tahun_ajaran', $tahunAjaran)
            ->first();

        if ($existingSiswa) {
            $existingSiswa->update([
                'nama' => $nama,
                'kelas' => $kelas,
                'semester' => $semester,
            ]);
            return null;
        }

        return new Siswa([
           'nis' => $nis,
           'nama' => $nama,
           'kelas' => $kelas,
           'tahun_ajaran' => $tahunAjaran,
           'semester' => $semester,
        ]);
    }
}
