<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Garjas extends Model
{
    protected $table = 'garjas';
    
    protected $fillable = [
        'nis', 'nama', 'kelas', 'bulan', 'tahun',
        'lari', 'nlari', 'up', 'nup', 
        'situp', 'nsitup', 'pushup', 'npushup', 
        'shuttle', 'nshuttle', 'nb', 'total'
    ];

    protected $casts = [
        'lari' => 'integer',
        'shuttle' => 'decimal:2',
        'nlari' => 'integer',
        'up' => 'integer',
        'nup' => 'integer',
        'situp' => 'integer',
        'nsitup' => 'integer',
        'pushup' => 'integer',
        'npushup' => 'integer',
        'nshuttle' => 'integer',
        'nb' => 'decimal:2',
        'total' => 'decimal:2',
        'bulan' => 'integer',
        'tahun' => 'integer',
    ];

    // Relasi ke model Siswa
    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'nis', 'nis');
    }

    // Event handler untuk auto calculate
    protected static function boot()
    {
        parent::boot();

        // Auto calculate saat data disimpan atau diupdate
        static::saving(function ($model) {
            $model->calculateGarjasB();
            $model->calculateTotal();
        });
    }

    // Method untuk menghitung Nilai Garjas B otomatis
    // Rumus: (nilai UP/CHIN + nilai SIT UP + nilai push up + nilai shuttle) / 4
    public function calculateGarjasB()
    {
        $nup = $this->nup ?? 0;
        $nsitup = $this->nsitup ?? 0;
        $npushup = $this->npushup ?? 0;
        $nshuttle = $this->nshuttle ?? 0;

        // Hitung rata-rata dari 4 nilai
        if ($nup > 0 || $nsitup > 0 || $npushup > 0 || $nshuttle > 0) {
            $this->nb = ($nup + $nsitup + $npushup + $nshuttle) / 4;
        } else {
            $this->nb = 0;
        }
        
        return $this->nb;
    }

    // Method untuk menghitung Total Nilai
    // Rumus: (nilai lari + nilai Garjas B) / 2
    public function calculateTotal()
    {
        $nlari = $this->nlari ?? 0;
        $nb = $this->nb ?? 0;

        // Hitung rata-rata dari nilai lari dan nilai garjas B
        if ($nlari > 0 || $nb > 0) {
            $this->total = ($nlari + $nb) / 2;
        } else {
            $this->total = 0;
        }
        
        return $this->total;
    }

    // Method untuk cek apakah field bisa diedit oleh siswa
    public function canEditByStudent($field)
    {
        $scoreFields = [
            'lari' => 'nlari',
            'up' => 'nup',
            'situp' => 'nsitup',
            'pushup' => 'npushup',
            'shuttle' => 'nshuttle'
        ];

        if (!array_key_exists($field, $scoreFields)) {
            return false;
        }

        $scoreField = $scoreFields[$field];
        $scoreValue = $this->attributes[$scoreField] ?? null;
        
        // Field dapat diedit jika nilai scorenya masih kosong atau 0
        return empty($scoreValue) || $scoreValue == 0;
    }

    // Method untuk mendapatkan status editable field
    public function getEditableStatus()
    {
        return [
            'lari' => $this->canEditByStudent('lari'),
            'up' => $this->canEditByStudent('up'),
            'situp' => $this->canEditByStudent('situp'),
            'pushup' => $this->canEditByStudent('pushup'),
            'shuttle' => $this->canEditByStudent('shuttle'),
        ];
    }

    // Accessor untuk format shuttle dengan 2 decimal places
    public function getShuttleAttribute($value)
    {
        return $value ? number_format($value, 2) : null;
    }

    // Mutator untuk shuttle
    public function setShuttleAttribute($value)
    {
        $this->attributes['shuttle'] = $value ? round($value, 2) : null;
    }

    // Accessor untuk format nb dengan 2 decimal places
    public function getNbAttribute($value)
    {
        return $value ? round($value, 2) : 0;
    }

    // Accessor untuk format total dengan 2 decimal places
    public function getTotalAttribute($value)
    {
        return $value ? round($value, 2) : 0;
    }

    // Scope untuk filter berdasarkan bulan dan tahun
    public function scopeByPeriod($query, $bulan, $tahun)
    {
        return $query->where('bulan', $bulan)->where('tahun', $tahun);
    }

    // Scope untuk filter berdasarkan kelas
    public function scopeByKelas($query, $kelas)
    {
        return $query->where('kelas', $kelas);
    }

    // Scope untuk data siswa tertentu
    public function scopeBySiswa($query, $nis)
    {
        return $query->where('nis', $nis);
    }

    // Method untuk mendapatkan grade berdasarkan total
    public function getGrade()
    {
        $total = $this->total ?? 0;
        
        if ($total >= 90) return 'A';
        if ($total >= 80) return 'B+';
        if ($total >= 70) return 'B';
        if ($total >= 60) return 'C+';
        if ($total >= 50) return 'C';
        if ($total >= 40) return 'D+';
        if ($total >= 30) return 'D';
        return 'E';
    }

    // Method untuk mendapatkan warna badge berdasarkan grade
    public function getGradeBadgeClass()
    {
        $grade = $this->getGrade();
        
        switch ($grade) {
            case 'A': return 'badge-success';
            case 'B+': case 'B': return 'badge-primary';
            case 'C+': case 'C': return 'badge-info';
            case 'D+': case 'D': return 'badge-warning';
            default: return 'badge-danger';
        }
    }

    // Method untuk validasi data siswa
    public function validateStudentData($field, $value)
    {
        $rules = [
            'lari' => ['integer', 'min:0', 'max:3600'], // max 60 menit
            'up' => ['integer', 'min:0', 'max:1000'],
            'situp' => ['integer', 'min:0', 'max:1000'],
            'pushup' => ['integer', 'min:0', 'max:1000'],
            'shuttle' => ['numeric', 'min:0', 'max:60'] // max 60 detik
        ];

        if (!isset($rules[$field])) {
            return false;
        }

        foreach ($rules[$field] as $rule) {
            if ($rule === 'integer' && !is_numeric($value)) {
                return false;
            }
            if ($rule === 'numeric' && !is_numeric($value)) {
                return false;
            }
            if (strpos($rule, 'min:') === 0) {
                $min = (float) substr($rule, 4);
                if ((float) $value < $min) {
                    return false;
                }
            }
            if (strpos($rule, 'max:') === 0) {
                $max = (float) substr($rule, 4);
                if ((float) $value > $max) {
                    return false;
                }
            }
        }

        return true;
    }

    // Method untuk validasi data pembina
    public function validateTeacherData($field, $value)
    {
        $scoreFields = ['nlari', 'nup', 'nsitup', 'npushup', 'nshuttle'];
        
        if (!in_array($field, $scoreFields)) {
            return false;
        }

        return is_numeric($value) && $value >= 0 && $value <= 100;
    }

    // Method untuk export data
    public function toExportArray()
    {
        return [
            'NIS' => $this->nis,
            'Nama' => $this->nama,
            'Kelas' => $this->kelas,
            'Lari (detik)' => $this->lari ?? '-',
            'Nilai Lari' => $this->nlari ?? 0,
            'UP (kali)' => $this->up ?? '-',
            'Nilai UP' => $this->nup ?? 0,
            'Sit Up (kali)' => $this->situp ?? '-',
            'Nilai Sit Up' => $this->nsitup ?? 0,
            'Push Up (kali)' => $this->pushup ?? '-',
            'Nilai Push Up' => $this->npushup ?? 0,
            'Shuttle (detik)' => $this->shuttle ?? '-',
            'Nilai Shuttle' => $this->nshuttle ?? 0,
            'Nilai Garjas B' => $this->nb ?? 0,
            'Total Nilai' => $this->total ?? 0,
            'Grade' => $this->getGrade()
        ];
    }
}