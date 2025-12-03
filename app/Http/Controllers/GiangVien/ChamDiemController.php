<?php

namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChamDiem;
use App\Models\DeTai;
use App\Models\BaoCao;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ChamDiemController extends Controller
{
    public function index()
    {
        $giangVien = Auth::user()->giangVien;

        if (!$giangVien) {
            return redirect()->route('login')->with('error', 'Không xác định được giảng viên!');
        }

        /*--------------------------------------------------------------
        | 1. Lấy tất cả đề tài kèm sinh viên – KHÔNG sửa vào đây
        --------------------------------------------------------------*/
        $detai = DeTai::with(['sinhviens'])
            ->whereHas('phancongs', function ($q) use ($giangVien) {
                $q->where('MaGV', $giangVien->MaGV);
            })
            ->get();

        /*--------------------------------------------------------------
        | 2. TẠO BẢN SAO cho danh sách CHƯA CHẤM
        |    => tránh làm mất dữ liệu gốc
        --------------------------------------------------------------*/
        $detaiChuaCham = $detai->map(function ($dt) use ($giangVien) {
            $clone = clone $dt;  // Quan trọng: tạo bản sao

            $clone->sinhviens = $dt->sinhviens->filter(function ($sv) use ($giangVien, $dt) {
                return !ChamDiem::where('MaDeTai', $dt->MaDeTai)
                    ->where('MaSV', $sv->MaSV)
                    ->where('MaGV', $giangVien->MaGV)
                    ->exists();
            });

            return $clone;
        })->filter(function ($dt) {
            return $dt->sinhviens->count() > 0;
        });

        /*--------------------------------------------------------------
        | 3. Lấy danh sách sinh viên đã chấm
        --------------------------------------------------------------*/
        $chamdiem = ChamDiem::with(['detai', 'sinhvien', 'giangvien'])
            ->where('MaGV', $giangVien->MaGV)
            ->orderByDesc('NgayCham')
            ->get();

        /*--------------------------------------------------------------
        | 4. Tối ưu lấy điểm TB & báo cáo mới nhất – KHÔNG truy vấn lặp lại
        --------------------------------------------------------------*/
        $diemTrungBinh = [];
        $latestReports = [];

        // Lấy toàn bộ bảng báo cáo 1 lần
        $allReports = BaoCao::with(['fileBaoCao', 'fileCode'])->orderByDesc('NgayNop')->get()->groupBy(function ($item) {
            return $item->MaDeTai . '-' . $item->MaSV;
        });

        // Lấy toàn bộ điểm 1 lần
        $allScores = ChamDiem::selectRaw('MaDeTai, MaSV, AVG(Diem) as tb')
            ->groupBy('MaDeTai', 'MaSV')
            ->get()
            ->keyBy(function ($item) {
                return $item->MaDeTai . '-' . $item->MaSV;
            });

        // Gán vào mảng 2 chiều
        foreach ($detai as $dt) {
            // Lấy tiến độ mới nhất của đề tài này (chung cho cả nhóm)
            $latestTienDo = \App\Models\TienDo::with(['fileBaoCao', 'fileCode'])
                                ->where('MaDeTai', $dt->MaDeTai)
                                ->whereNotNull('NgayNop')
                                ->orderByDesc('NgayNop')
                                ->first();

            foreach ($dt->sinhviens as $sv) {

                $key = $dt->MaDeTai . '-' . $sv->MaSV;

                $diemTrungBinh[$dt->MaDeTai][$sv->MaSV] =
                    $allScores[$key]->tb ?? null;

                $latestBaoCao = $allReports[$key][0] ?? null;

                // So sánh xem cái nào mới hơn thì lấy
                if ($latestBaoCao && $latestTienDo) {
                    $latestReports[$dt->MaDeTai][$sv->MaSV] = 
                        ($latestBaoCao->NgayNop > $latestTienDo->NgayNop) ? $latestBaoCao : $latestTienDo;
                } elseif ($latestBaoCao) {
                    $latestReports[$dt->MaDeTai][$sv->MaSV] = $latestBaoCao;
                } else {
                    $latestReports[$dt->MaDeTai][$sv->MaSV] = $latestTienDo;
                }
            }
        }

        return view('giangvien.chamdiem.index', compact(
            'detaiChuaCham',
            'chamdiem',
            'diemTrungBinh',
            'latestReports'
        ));
    }

    /*--------------------------------------------------------------
    | Lưu điểm
    --------------------------------------------------------------*/
    public function store(Request $request)
    {
        $request->validate([
            'MaDeTai' => 'required|exists:DeTai,MaDeTai',
            'MaSV' => 'required|exists:SinhVien,MaSV',
            'Diem' => 'required|numeric|min:0|max:10',
            'NhanXet' => 'nullable|string|max:500',
        ]);

        ChamDiem::updateOrCreate(
            [
                'MaDeTai' => $request->MaDeTai,
                'MaSV' => $request->MaSV,
                'MaGV' => Auth::user()->giangVien->MaGV,
            ],
            [
                'Diem' => $request->Diem,
                'NhanXet' => $request->NhanXet,
                'NgayCham' => Carbon::now(),
                'TrangThai' => 'Chờ duyệt',
                'DiemCuoi' => null,
            ]
        );

        return back()->with('success', 'Chấm điểm sinh viên thành công!');
    }

    /*--------------------------------------------------------------
    | Cập nhật điểm
    --------------------------------------------------------------*/
    public function update(Request $request, $id)
    {
        $request->validate([
            'Diem' => 'required|numeric|min:0|max:10',
            'NhanXet' => 'nullable|string|max:500'
        ]);

        $chamdiem = ChamDiem::findOrFail($id);

        if ($chamdiem->DiemCuoi !== null) {
            return redirect()->back()->with('error', 'Điểm đã được duyệt, không thể chỉnh sửa!');
        }

        $chamdiem->update([
            'Diem' => $request->Diem,
            'NhanXet' => $request->NhanXet,
            'NgayCham' => Carbon::now(),
            'TrangThai' => 'Chờ duyệt',
            'DiemCuoi' => null
        ]);

        return redirect()->back()->with('success', 'Cập nhật điểm sinh viên thành công!');
    }
}