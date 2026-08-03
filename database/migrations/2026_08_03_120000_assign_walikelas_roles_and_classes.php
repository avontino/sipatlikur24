<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $walis = [
            // Kelas 7
            '7A' => ['Dwi Rahmawati'],
            '7B' => ['Elsye', 'Sandra'],
            '7C' => ['Widyatama'],
            '7D' => ['Erri Endah', 'Listiani'],
            '7E' => ['Dyah Amelia'],
            '7F' => ['Fernanda'],
            '7G' => ['Sriatin'],

            // Kelas 8
            '8A' => ['Siti Rohmawati'],
            '8B' => ['Made Argita', 'Argita'],
            '8C' => ['Lina Setyaningrum'],
            '8D' => ['Titik Dewi'],
            '8E' => ['Ainur Romlah'],
            '8F' => ['Noveriana'],
            '8G' => ['Umi Farah'],

            // Kelas 9
            '9A' => ['Maria Ignatia'],
            '9B' => ['Ida Fitriyah'],
            '9C' => ['Wega'],
            '9D' => ['Sri Hartati'],
            '9E' => ['Endah Suci'],
            '9F' => ['Muflihatul', 'Habibah', "A'im"],
            '9G' => ['Vita Arwidiah', 'Vita'],
        ];

        foreach ($walis as $kelas => $keywords) {
            $user = null;
            foreach ($keywords as $kw) {
                $user = DB::table('users')
                    ->where('role', 'guru')
                    ->where('name', 'LIKE', '%' . $kw . '%')
                    ->first();
                if (!$user) {
                    $user = DB::table('users')
                        ->where('name', 'LIKE', '%' . $kw . '%')
                        ->first();
                }
                if ($user) {
                    break;
                }
            }

            if ($user) {
                $existingRoles = array_filter(array_map('trim', explode(',', $user->additional_roles ?? '')));
                if (!in_array('walikelas', $existingRoles)) {
                    $existingRoles[] = 'walikelas';
                }
                $newAdditionalRoles = implode(',', array_unique($existingRoles));

                DB::table('users')->where('id', $user->id)->update([
                    'walikelas_kelas' => $kelas,
                    'additional_roles' => $newAdditionalRoles,
                ]);

                Log::info("Wali Kelas Assigned: [{$kelas}] {$user->name} (ID: {$user->id})");
            } else {
                Log::warning("Wali Kelas Not Found in DB for [{$kelas}] with keywords: " . implode(', ', $keywords));
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No revert necessary
    }
};
