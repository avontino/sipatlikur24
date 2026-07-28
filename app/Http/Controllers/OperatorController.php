<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\OperatorExport;
use App\Imports\OperatorImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Guru;
use DB;

class OperatorController extends Controller
{
    public function operator(Request $request)
    {
        if ($request->ajax() || $request->has('draw')) {
            $type = $request->input('type', 'siswa');
            $query = \App\Models\User::query();

            if ($type === 'siswa') {
                $query->whereIn('role', ['siswa', 'ketuakelas']);
            } elseif ($type === 'admin') {
                $query->where('role', 'admin');
            } else {
                $query->whereNotIn('role', ['siswa', 'ketuakelas', 'admin']);
            }

            $totalRecords = $query->count();

            if ($searchValue = trim($request->input('search.value'))) {
                $cleanSearch = str_replace([' ', '_', '-'], '', strtolower($searchValue));
                $query->where(function($q) use ($searchValue, $cleanSearch) {
                    $q->where('name', 'LIKE', "%{$searchValue}%")
                      ->orWhere('username', 'LIKE', "%{$searchValue}%")
                      ->orWhere('role', 'LIKE', "%{$searchValue}%")
                      ->orWhere('role', 'LIKE', "%{$cleanSearch}%")
                      ->orWhere('additional_roles', 'LIKE', "%{$searchValue}%")
                      ->orWhere('additional_roles', 'LIKE', "%{$cleanSearch}%")
                      ->orWhere('walikelas_kelas', 'LIKE', "%{$searchValue}%");

                    if (str_contains(strtolower($searchValue), 'ketua')) {
                        $q->orWhere('role', 'ketuakelas')
                          ->orWhere('additional_roles', 'LIKE', '%ketuakelas%');
                    }

                    if (str_contains(strtolower($searchValue), 'wali')) {
                        $q->orWhere('role', 'walikelas')
                          ->orWhere('additional_roles', 'LIKE', '%walikelas%');
                    }
                });
            }

            $filteredRecords = $query->count();
            $start = intval($request->input('start', 0));
            $length = intval($request->input('length', 10));

            $data = $query->orderBy('created_at', 'desc')->skip($start)->take($length)->get();

            $canEdit = auth()->user()->hasPermission('operator_edit');
            $canDelete = auth()->user()->hasPermission('operator_delete');

            $resultData = [];
            foreach ($data as $operator) {
                $primaryRoleStr = $operator->role;
                if ($operator->role == 'ketuakelas') {
                    $primaryRoleStr = 'ketuakelas' . ($operator->walikelas_kelas ? ' (Ketua Kelas ' . $operator->walikelas_kelas . ')' : ' (Ketua Kelas)');
                } elseif ($operator->role == 'walikelas') {
                    $primaryRoleStr = 'walikelas' . ($operator->walikelas_kelas ? ' (Wali Kelas ' . $operator->walikelas_kelas . ')' : ' (Wali Kelas)');
                } elseif ($operator->role == 'guru' && $operator->walikelas_kelas) {
                    $primaryRoleStr = 'guru (Wali Kelas ' . $operator->walikelas_kelas . ')';
                }

                $roleHtml = e($primaryRoleStr);
                if ($operator->additional_roles) {
                    $roles = explode(',', $operator->additional_roles);
                    $displayRoles = [];
                    foreach ($roles as $r) {
                        $r = trim($r);
                        if ($r == 'ketuakelas') {
                            $displayRoles[] = 'ketuakelas' . ($operator->walikelas_kelas ? ' (Ketua Kelas ' . $operator->walikelas_kelas . ')' : ' (Ketua Kelas)');
                        } elseif ($r == 'walikelas') {
                            $displayRoles[] = 'walikelas' . ($operator->walikelas_kelas ? ' (Wali Kelas ' . $operator->walikelas_kelas . ')' : ' (Wali Kelas)');
                        } else {
                            $displayRoles[] = $r;
                        }
                    }
                    $roleHtml .= '<br><small class="text-muted">Peran Tambahan: ' . e(implode(', ', $displayRoles)) . '</small>';
                }

                $passHtml = $canEdit
                    ? '<form action="/operator/'.$operator->id.'/reset-password" method="POST" onsubmit="return confirm(\'Apakah Anda yakin ingin mereset password operator ini?\');" style="display:inline;">'.csrf_field().'<button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-sync-alt"></i> Reset</button></form>'
                    : '<span class="text-muted">-</span>';

                $aksiHtml = '';
                if ($canEdit) {
                    $aksiHtml .= '<button type="button" class="btn btn-warning btn-sm me-1" 
                        data-myid="'.$operator->id.'"
                        data-myrole="'.e($operator->role).'"
                        data-myname="'.e($operator->name).'"
                        data-myusername="'.e($operator->username).'"
                        data-mywalikelas_kelas="'.e($operator->walikelas_kelas).'"
                        data-myadditional_roles="'.e($operator->additional_roles).'"
                        data-mypassword=""
                        data-bs-toggle="modal" data-bs-target="#edit">Edit</button>';
                }
                if ($canDelete) {
                    $aksiHtml .= '<a href="/operator/'.$operator->id.'/delete" class="btn btn-danger btn-sm">Hapus</a>';
                }

                $resultData[] = [
                    'role' => $roleHtml,
                    'name' => e($operator->name),
                    'username' => e($operator->username),
                    'password' => $passHtml,
                    'aksi' => $aksiHtml
                ];
            }

            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $resultData
            ]);
        }

        $countSiswa = \App\Models\User::whereIn('role', ['siswa', 'ketuakelas'])->count();
        $countAdmin = \App\Models\User::where('role', 'admin')->count();
        $countGuru = \App\Models\User::whereNotIn('role', ['siswa', 'ketuakelas', 'admin'])->count();

        return view('operator', [
            'data_operator' => collect(),
            'countSiswa' => $countSiswa,
            'countGuru' => $countGuru,
            'countAdmin' => $countAdmin
        ]);
    }


    public function create(Request $request)
    {	
    	$user = new \App\Models\User;
    	$user->role=$request->role;
    	$user->name=$request->name;
    	$user->username=$request->username;
    	$user->password=bcrypt($request->password);
    	$user->needs_password_change = 0;
    	$user->remember_token=\Illuminate\Support\Str::random(60);
    	$user->walikelas_kelas=$request->walikelas_kelas;
        $additionalRoles = $request->input('additional_roles', []);
        $user->additional_roles = is_array($additionalRoles) ? implode(',', $additionalRoles) : '';
    	$user->save();

        if ($request->role == 'guru') {
            DB::table('gu_ru')->insert([
                'guru' => $request->name,
                'mapel' => '',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        \App\Helpers\AuditLog::write('Menambahkan operator baru: ' . $request->name . ' (Username: ' . $request->username . ', Role: ' . $request->role . ')');

    	return redirect('/operator')->with('sukses','Operator Berhasil Ditambahkan');
    }


    public function export() 
    {
        return Excel::download(new OperatorExport, 'Operator.xlsx');
    }

    public function edit($id)
    {   
        $kasus=\App\Models\Kasus::find($id);
        return view('kasus/edit',['kasus'=>$kasus]);
    }

    public function update(Request $request)
    {   
        $user1 = \App\Models\User::findorFail($request->opid);
        DB::table('gu_ru')->where('guru','LIKE',$user1->name)->update(['guru'=>$request->name]);

    	$user = \App\Models\User::findorFail($request->opid);
    	$user->role=$request->role;
    	$user->name=$request->name;
    	$user->username=$request->username;
        if ($request->filled('password')) {
            $user->password=bcrypt($request->password);
            $user->needs_password_change = 1;
        }
    	$user->remember_token=\Illuminate\Support\Str::random(60);
    	$user->walikelas_kelas=$request->walikelas_kelas;
        $additionalRoles = $request->input('additional_roles', []);
        $user->additional_roles = is_array($additionalRoles) ? implode(',', $additionalRoles) : '';
    	$user->save();

        \App\Helpers\AuditLog::write('Memperbarui data operator: ' . $request->name . ' (Username: ' . $request->username . ')');

        return redirect('/operator')->with('sukses','Operator Berhasil Diupdate');
    }

    public function delete($id)
    {
        $operator=\App\Models\User::find($id);

        \App\Helpers\AuditLog::write('Menghapus data operator: ' . $operator->name . ' (Username: ' . $operator->username . ')');

        $guru = DB::table('gu_ru')->where('guru','LIKE','%'.$operator->name.'%')->delete();

        $operator->delete();

        return redirect('/operator')->with('sukses','Operator Berhasil Dihapus');
    }

    public function import(Request $request) 
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:5120'
        ], [
            'file.required' => 'File Excel wajib diunggah.',
            'file.mimes' => 'Format file harus berupa .xlsx atau .xls.',
            'file.max' => 'Ukuran file tidak boleh lebih dari 5 MB.'
        ]);

        Excel::import(new OperatorImport, $request->file('file'));

        \App\Helpers\AuditLog::write('Mengimpor data operator via Excel');
        
        return redirect('/operator')->with('sukses','Operator Berhasil Diupload');
    }

    public function resetPassword($id)
    {
        $user = \App\Models\User::findOrFail($id);
        
        $tempPassword = \Illuminate\Support\Str::random(8);
        $user->password = bcrypt($tempPassword);
        $user->needs_password_change = 1;
        $user->save();
        
        \App\Helpers\AuditLog::write('Mereset password operator: ' . $user->name . ' (Username: ' . $user->username . ')');
        
        return redirect('/operator')->with('sukses_reset', 'Password untuk operator ' . $user->name . ' berhasil direset sementara menjadi: ' . $tempPassword);
    }

    public function downloadTemplate()
    {
        return \Excel::download(new \App\Exports\OperatorTemplateExport, 'template_import_operator.xlsx');
    }

    public function savePermissions(Request $request)
    {
        \App\Models\RolePermission::truncate();

        $permissions = $request->input('permissions', []);
        foreach ($permissions as $roleName => $perms) {
            foreach ($perms as $perm) {
                \App\Models\RolePermission::create([
                    'role' => $roleName,
                    'permission' => $perm
                ]);
            }
        }

        \App\Helpers\AuditLog::write('Memperbarui matriks perizinan hak akses peran.');

        return redirect('/operator')->with('sukses', 'Matriks Hak Akses Peran Berhasil Disimpan');
    }
}


