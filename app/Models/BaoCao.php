<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaoCao extends Model
{
    protected $table = 'BaoCao';
    protected $primaryKey = 'MaBC';
    public $timestamps = false;
    protected $fillable = [
        'MaDeTai', 'MaSV', 'FileID', 'FileCodeID',
        'NgayNop', 'LanNop', 'NhanXet', 'TrangThai', 'Deadline'
    ];

    // Quan hệ
    public function deTai()
    {
        return $this->belongsTo(DeTai::class, 'MaDeTai', 'MaDeTai');
    }

    public function sinhVien()
    {
        return $this->belongsTo(SinhVien::class, 'MaSV', 'MaSV');
    }

    public function fileBaoCao()
    {
        return $this->belongsTo(File::class, 'FileID');
    }

    public function fileCode()
    {
        return $this->belongsTo(File::class, 'FileCodeID');
    }
}