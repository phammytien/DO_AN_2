<?php

namespace App\Http\Controllers\SinhVien;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\DeTai;

class DiemController extends Controller
{
    public function index()
    {
        $maSV = Auth::user()->MaSo;

        // 🔥 Lọc đề tài theo đúng bảng pivot: DeTai_SinhVien
        $detais = DeTai::whereHas('sinhViens', function ($q) use ($maSV) {
                $q->where('DeTai_SinhVien.MaSV', $maSV);
            })
            ->with([
                'sinhViens' => function($q) use ($maSV) {
                    $q->wherePivot('MaSV', $maSV);
                },
                'chamdiems.giangVien',
                'phancongs.giangVien'
            ])
            ->get();

        // 🔥 Mảng vai trò GV theo đề tài
        $vaiTroTheoDeTai = [];

        foreach ($detais as $dt) {
            foreach ($dt->phancongs as $pc) {
                $vaiTroTheoDeTai[$dt->MaDeTai][$pc->MaGV] = $pc->VaiTro;
            }
        }

        return view('sinhvien.diem.index', compact('detais', 'maSV', 'vaiTroTheoDeTai'));
    }
}