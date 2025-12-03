<?php

namespace App\Http\Controllers\SinhVien;

use App\Http\Controllers\Controller;
use App\Models\ThongBao;

class ThongBaoController extends Controller
{
    public function index()
    {
        $maSV = auth()->user()->MaSo;
        
        $thongBao = ThongBao::where(function($query) use ($maSV) {
            $query->whereIn('DoiTuongNhan', ['SV', 'TatCa'])
                  ->whereNull('MaNguoiNhan'); // Thông báo chung
        })
        ->orWhere(function($query) use ($maSV) {
            $query->where('MaNguoiNhan', $maSV); // Thông báo riêng cho sinh viên này
        })
        ->orderByDesc('TGDang')
        ->get();

        // DEBUG: Uncomment dòng dưới để xem dữ liệu
        // dd([
        //     'MaSV_DangNhap' => $maSV,
        //     'SoLuongThongBao' => $thongBao->count(),
        //     'DanhSachThongBao' => $thongBao->toArray()
        // ]);

        return view('sinhvien.thongbao.index', compact('thongBao'));
    }
}