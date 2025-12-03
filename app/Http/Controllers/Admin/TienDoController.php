<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeTai;
use App\Models\SinhVien;
use App\Models\TienDo;
use Illuminate\Http\Request;

class TienDoController extends Controller
{
    // Trang danh sách + thống kê
    public function index(Request $request)
    {
        $query = TienDo::with(['deTai', 'sinhVien']);

        // Lọc theo đề tài (Tên đề tài)
        if ($request->filled('detai')) {
            $query->whereHas('deTai', function($q) use ($request) {
                $q->where('TenDeTai', 'like', '%' . $request->detai . '%');
            });
        }

        // Lọc theo sinh viên (Tên sinh viên)
        if ($request->filled('sinhvien')) {
            $query->whereHas('deTai.sinhViens', function($q) use ($request) {
                $q->where('TenSV', 'like', '%' . $request->sinhvien . '%');
            });
        }

        // Lọc theo trạng thái (Tính toán dựa trên Deadline và NgayNop)
        if ($request->filled('trangthai')) {
            $status = $request->trangthai;
            if ($status == 'Chưa nộp') {
                $query->whereNull('NgayNop');
            } elseif ($status == 'Trễ hạn') {
                $query->whereNotNull('NgayNop')->whereColumn('NgayNop', '>', 'Deadline');
            } elseif ($status == 'Đúng hạn') {
                $query->whereNotNull('NgayNop')->whereColumn('NgayNop', '<=', 'Deadline');
            }
        }

        $tiendos = $query->orderBy('NgayNop', 'desc')->paginate(15);
        $detais = DeTai::all();
        $sinhviens = SinhVien::all();

        return view('admin.tiendo.index', compact('tiendos', 'detais', 'sinhviens'));
    }

    // Trang xem chi tiết
public function show($MaTienDo)
{
    $t = TienDo::with('deTai')->findOrFail($MaTienDo);
    return view('admin.tiendo.show', compact('t'));
}

}