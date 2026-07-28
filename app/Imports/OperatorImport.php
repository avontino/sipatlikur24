<?php 
namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Facades\Hash;

class OperatorImport implements ToCollection
{
    // public function model(array $row)
    // {   
       
    //     return new User([
    //       'role' => $row[0],
    //       'name' => $row[1],
    //       'username' => $row[2],
    //       'password' => bcrypt($row[3]),
    //       'remember_token' => str_random(60)
           
    //     ]);
    // }
    
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) 
        {
            User::create([
              'role' => $row[0],
              'name' => $row[1],
              'username' => $row[2],
              'password' => bcrypt($row[3]),
              'needs_password_change' => 1
            ]);

            if ($row[0] == 'guru') {
                $exists = \Illuminate\Support\Facades\DB::table('gu_ru')->where('guru', $row[1])->exists();
                if (!$exists) {
                    \Illuminate\Support\Facades\DB::table('gu_ru')->insert([
                        'guru' => $row[1],
                        'mapel' => '',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
        }
    }
}
