<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\JadwalImport;
use App\Exports\JadwalExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Mapel;
use App\Models\Guru;
use App\Models\Kelas;
use DB;
use DateTime;


class JadwalController extends Controller
{

    public function index(Request $request)
    {	
        $ma_pel=Mapel::all();
        $gu_ru=Guru::all();
        $ke_las=Kelas::all();

        if ($request->ajax() || $request->has('draw')) {
            $query = \App\Models\Jadwal::where('tahun_ajaran', session('tahun_ajaran'))
                ->where('semester', session('semester'));

            if (auth()->user()->role == 'siswa') {
                $siswa = \App\Models\Siswa::where('tahun_ajaran', session('tahun_ajaran'))
                    ->where(function($q) {
                        $q->where('nama', auth()->user()->name)
                          ->orWhere('nis', auth()->user()->username);
                    })->first();
                $kelasSiswa = $siswa ? $siswa->kelas : null;
                if ($kelasSiswa) {
                    $query->where('kelas', $kelasSiswa);
                } else {
                    $query->whereRaw('1 = 0');
                }
            }

            $totalRecords = $query->count();

            if ($searchValue = $request->input('search.value')) {
                $query->where(function($q) use ($searchValue) {
                    $q->where('kelas', 'LIKE', "%{$searchValue}%")
                      ->orWhere('jamke', 'LIKE', "%{$searchValue}%")
                      ->orWhere('jumlahjam', 'LIKE', "%{$searchValue}%")
                      ->orWhere('mapel', 'LIKE', "%{$searchValue}%")
                      ->orWhere('guru', 'LIKE', "%{$searchValue}%")
                      ->orWhere('hari', 'LIKE', "%{$searchValue}%");
                });
            }

            // Order by column header click, or default to Day of Week (Monday..Sunday / Senin..Minggu) then JamKe
            if ($request->has('order') && count($request->input('order')) > 0) {
                $order = $request->input('order');
                $columnIndex = intval($order[0]['column']);
                $columnDir = strtolower($order[0]['dir']) === 'desc' ? 'desc' : 'asc';
                $columnName = $request->input("columns.{$columnIndex}.name");

                $columnsMap = [
                    'kelas'     => 'kelas',
                    'jamke'     => 'CAST(jamke AS UNSIGNED)',
                    'jumlahjam' => 'CAST(jumlahjam AS UNSIGNED)',
                    'mapel'     => 'mapel',
                    'guru'      => 'guru',
                    'hari'      => "CASE LOWER(hari) 
                        WHEN 'monday' THEN 1 WHEN 'senin' THEN 1 
                        WHEN 'tuesday' THEN 2 WHEN 'selasa' THEN 2 
                        WHEN 'wednesday' THEN 3 WHEN 'rabu' THEN 3 
                        WHEN 'thursday' THEN 4 WHEN 'kamis' THEN 4 
                        WHEN 'friday' THEN 5 WHEN 'jumat' THEN 5 
                        WHEN 'saturday' THEN 6 WHEN 'sabtu' THEN 6 
                        WHEN 'sunday' THEN 7 WHEN 'minggu' THEN 7 
                        ELSE 8 END",
                ];

                if ($columnName && isset($columnsMap[$columnName])) {
                    $query->orderByRaw("{$columnsMap[$columnName]} {$columnDir}");
                }
            } else {
                $query->orderByRaw("CASE LOWER(hari) 
                    WHEN 'monday' THEN 1 WHEN 'senin' THEN 1 
                    WHEN 'tuesday' THEN 2 WHEN 'selasa' THEN 2 
                    WHEN 'wednesday' THEN 3 WHEN 'rabu' THEN 3 
                    WHEN 'thursday' THEN 4 WHEN 'kamis' THEN 4 
                    WHEN 'friday' THEN 5 WHEN 'jumat' THEN 5 
                    WHEN 'saturday' THEN 6 WHEN 'sabtu' THEN 6 
                    WHEN 'sunday' THEN 7 WHEN 'minggu' THEN 7 
                    ELSE 8 END ASC")
                ->orderByRaw("CAST(jamke AS UNSIGNED) ASC");
            }

            $filteredRecords = $query->count();
            $start = intval($request->input('start', 0));
            $length = intval($request->input('length', 10));

            if (auth()->user()->role == 'siswa' || $length < 0) {
                $data = $query->get();
            } else {
                $data = $query->skip($start)->take($length)->get();
            }

            $isAdminOrKurikulum = (auth()->user()->role=='admin' || auth()->user()->role=='kurikulum');

            $hariMap = [
                'monday'    => 'Senin',
                'tuesday'   => 'Selasa',
                'wednesday' => 'Rabu',
                'thursday'  => 'Kamis',
                'friday'    => 'Jumat',
                'saturday'  => 'Sabtu',
                'sunday'    => 'Minggu',
                'senin'     => 'Senin',
                'selasa'    => 'Selasa',
                'rabu'      => 'Rabu',
                'kamis'     => 'Kamis',
                'jumat'     => 'Jumat',
                'sabtu'     => 'Sabtu',
                'minggu'    => 'Minggu',
            ];

            $resultData = [];
            foreach ($data as $jadwal) {
                $row = [];

                if ($isAdminOrKurikulum) {
                    $row['checkbox'] = '<input type="checkbox" name="ids[]" value="'.$jadwal->id.'" class="checkItem">';
                }

                $row['kelas'] = e($jadwal->kelas);
                $row['jamke'] = e($jadwal->jamke);
                $row['jumlahjam'] = e($jadwal->jumlahjam);
                $row['mapel'] = e($jadwal->mapel);
                $row['guru'] = e($jadwal->guru);
                $hLower = strtolower(trim($jadwal->hari ?? ''));
                $row['hari'] = e(isset($hariMap[$hLower]) ? $hariMap[$hLower] : $jadwal->hari);

                if ($isAdminOrKurikulum) {
                    $row['aksi'] = '<button type="button" class="btn btn-warning btn-sm edit-btn text-dark me-1" 
                        data-myid="'.$jadwal->id.'"
                        data-mykelas="'.e($jadwal->kelas).'"
                        data-myjamke="'.e($jadwal->jamke).'"
                        data-myjumlahjam="'.e($jadwal->jumlahjam).'"
                        data-mymapel="'.e($jadwal->mapel).'"
                        data-myguru="'.e($jadwal->guru).'"
                        data-myhari="'.e($jadwal->hari).'"
                        data-myj1="'.e($jadwal->j1).'"
                        data-myj2="'.e($jadwal->j2).'"
                        data-myj3="'.e($jadwal->j3).'"
                        data-myj4="'.e($jadwal->j4).'"
                        data-myj5="'.e($jadwal->j5).'"
                        data-myj6="'.e($jadwal->j6).'"
                        data-myj7="'.e($jadwal->j7).'"
                        data-myj8="'.e($jadwal->j8).'"
                        data-myj9="'.e($jadwal->j9).'"
                        data-myj10="'.e($jadwal->j10).'"
                        data-myj11="'.e($jadwal->j11).'"
                        data-bs-toggle="modal" 
                        data-bs-target="#editjadwalpel">Edit</button>'.
                        '<a href="/jadwal/'.$jadwal->id.'/delete" class="btn btn-danger btn-sm" onclick="return confirm(\'Apakah Anda yakin ingin menghapus jadwal ini?\')">Hapus</a>';
                }

                $resultData[] = $row;
            }

            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $resultData
            ]);
        }

        $data_jadwal = collect();

        return view('jadwal.index',['data_jadwal' => $data_jadwal],compact('ma_pel','gu_ru','ke_las'));
    }

    public function create(Request $request)
    {   
        $data = $request->all();
        $data['tahun_ajaran'] = session('tahun_ajaran');
        $data['semester'] = session('semester');

        \App\Models\Jadwal::create($data);

        \App\Helpers\AuditLog::write('Menambahkan jadwal baru: ' . $request->hari . ', Jam Ke: ' . $request->jamke . ', Kelas: ' . $request->kelas . ' (' . $request->mapel . ')');

        return redirect('/jadwal')->with('sukses','Jadwal Berhasil Ditambahkan');
    }

    public function update(Request $request)
    {
        $jadwal=\App\Models\Jadwal::findorFail($request->jadwalid);
        $jadwal->update($request->all());

        \App\Helpers\AuditLog::write('Memperbarui jadwal: ' . $request->hari . ', Jam Ke: ' . $request->jamke . ', Kelas: ' . $request->kelas . ' (' . $request->mapel . ')');

        return redirect('/jadwal')->with('sukses','Jadwal Berhasil Diupdate');
    }

   

    public function delete($id)
    {
        $jadwal=\App\Models\Jadwal::find($id);
        if ($jadwal) {
            \App\Helpers\AuditLog::write('Menghapus jadwal: ' . $jadwal->hari . ', Jam Ke: ' . $jadwal->jamke . ', Kelas: ' . $jadwal->kelas . ' (' . $jadwal->mapel . ')');
            $jadwal->delete();
        }
        return redirect('/jadwal')->with('sukses','Jadwal Berhasil Dihapus');
    }

    public function deleteMultiple(Request $request)
    {
        $ids = $request->input('ids');
        if (!empty($ids)) {
            \App\Models\Jadwal::whereIn('id', $ids)->delete();
            \App\Helpers\AuditLog::write('Menghapus beberapa jadwal terpilih');
            return redirect('/jadwal')->with('sukses', 'Jadwal pelajaran terpilih berhasil dihapus');
        }
        return redirect('/jadwal')->with('gagal', 'Tidak ada jadwal yang dipilih');
    }

    public function deleteAll()
    {
        \App\Models\Jadwal::truncate();
        \App\Helpers\AuditLog::write('Membasmi / Mengosongkan seluruh jadwal pelajaran');
        return redirect('/jadwal')->with('sukses', 'Seluruh jadwal pelajaran berhasil dikosongkan');
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

        Excel::import(new JadwalImport, $request->file('file'));

        \App\Helpers\AuditLog::write('Mengimpor data jadwal pelajaran via Excel');
        
        return redirect('/jadwal')->with('sukses','Jadwal Berhasil Diupload');
    }

    public function export() 
    {
        return Excel::download(new JadwalExport, 'Jadwal.xlsx');
    }

    public function downloadTemplate()
    {
        return Excel::download(new \App\Exports\JadwalTemplateExport, 'template_import_jadwal.xlsx');
    }
}
