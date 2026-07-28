<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\AuditLog;

class AdminController extends Controller
{
    public function logs(Request $request)
    {
        $cari = $request->input('cari');
        $query = DB::table('logs_aktivitas');

        if ($request->filled('cari')) {
            $query->where(function($q) use ($cari) {
                $q->where('username', 'LIKE', '%' . $cari . '%')
                  ->orWhere('nama_pengguna', 'LIKE', '%' . $cari . '%')
                  ->orWhere('role', 'LIKE', '%' . $cari . '%')
                  ->orWhere('aktivitas', 'LIKE', '%' . $cari . '%');
            });
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(50);

        return view('admin.logs', compact('logs'));
    }

    public function backupIndex()
    {
        return view('admin.backup');
    }

    public function backupDownload()
    {
        AuditLog::write('Melakukan backup database');

        // Native SQL dumper
        $tables = [];
        $result = DB::select('SHOW TABLES');
        $dbNameKey = 'Tables_in_' . env('DB_DATABASE');

        foreach ($result as $row) {
            $tables[] = $row->$dbNameKey;
        }

        $sql = "-- SINALA Database Backup\n";
        $sql .= "-- Generated at: " . date('Y-m-d H:i:s') . "\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

        foreach ($tables as $table) {
            // Drop statement
            $sql .= "DROP TABLE IF EXISTS `" . $table . "`;\n";
            
            // Create Table structure
            $createTableResult = DB::select("SHOW CREATE TABLE `" . $table . "`");
            $createTableKey = 'Create Table';
            $sql .= $createTableResult[0]->$createTableKey . ";\n\n";

            // Table data
            $rows = DB::select("SELECT * FROM `" . $table . "`");
            foreach ($rows as $row) {
                $rowArr = (array) $row;
                $keys = array_map(function($k) { return "`" . $k . "`"; }, array_keys($rowArr));
                $values = array_map(function($v) {
                    if (is_null($v)) return "NULL";
                    return "'" . addslashes($v) . "'";
                }, array_values($rowArr));

                $sql .= "INSERT INTO `" . $table . "` (" . implode(', ', $keys) . ") VALUES (" . implode(', ', $values) . ");\n";
            }
            $sql .= "\n";
        }

        $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";

        $filename = 'backup_db_' . date('Ymd_His') . '.sql';

        return response($sql)
            ->withHeaders([
                'Content-Type' => 'application/sql',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
    }

    public function backupRestore(Request $request)
    {
        $request->validate([
            'file' => 'required|file'
        ]);

        $file = $request->file('file');
        
        // Simple extension validation
        if ($file->getClientOriginalExtension() !== 'sql') {
            return back()->with('gagal', 'File harus berupa dokumen SQL (.sql)');
        }

        $sqlContent = file_get_contents($file->getRealPath());

        try {
            DB::beginTransaction();
            DB::unprepared("SET FOREIGN_KEY_CHECKS = 0;");
            DB::unprepared($sqlContent);
            DB::unprepared("SET FOREIGN_KEY_CHECKS = 1;");
            DB::commit();

            AuditLog::write('Melakukan restore database');
            
            return back()->with('sukses', 'Database berhasil dikembalikan dari file backup.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('gagal', 'Proses restore gagal: ' . $e->getMessage());
        }
    }
}
