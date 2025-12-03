<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChamDiem;
use App\Models\DeTai;
use App\Models\GiangVien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChamDiemController extends Controller
{
    // ================================
    // DANH SÁCH
    // ================================
    public function index(Request $request)
    {
        $lops = \App\Models\Lop::all();
        $selectedLop = $request->get('lop_id', 'all');

        $query = DeTai::with(['sinhViens.lop','phancongs.giangVien','chamdiems.giangVien']);

        if ($selectedLop !== 'all') {
            $query->whereHas('sinhViens', function($q) use ($selectedLop) {
                $q->where('MaLop', $selectedLop);
            });
        }

        $detais = $query->orderBy('MaDeTai','desc')->paginate(10);

        return view('admin.chamdiem.index', ['cds'=>$detais, 'lops'=>$lops, 'selectedLop'=>$selectedLop]);
    }

    // ================================
    // CREATE
    // ================================
    public function create()
    {
        $detais = DeTai::all();
        $gvs    = GiangVien::all();

        return view('admin.chamdiem.create', compact('detais','gvs'));
    }

    // ================================
    // STORE
    // ================================
    public function store(Request $request)
    {
        $request->validate([
            'MaDeTai' => 'required|integer|exists:DeTai,MaDeTai',
            'MaGV'    => 'required|integer|exists:GiangVien,MaGV',
            'MaSV'    => 'required|integer',
            'Diem'    => 'required|numeric|min:0|max:10',
        ]);

        // Xác định vai trò GV
        $vaiTroDB = DB::table('PhanCong')
                        ->where('MaDeTai', $request->MaDeTai)
                        ->where('MaGV', $request->MaGV)
                        ->value('VaiTro');

        $vaiTro = $vaiTroDB === 'Hướng dẫn chính' ? 'GVHD' : 'GVPB';

        ChamDiem::create([
            'MaDeTai' => $request->MaDeTai,
            'MaGV'    => $request->MaGV,
            'MaSV'    => $request->MaSV,
            'Diem'    => $request->Diem,
            'NhanXet' => $request->NhanXet,
            'NgayCham'=> now(),
            'VaiTro'  => $vaiTro,
            'TrangThai' => 'Chờ duyệt',
            'DiemCuoi' => null,
        ]);

        return redirect()->route('admin.chamdiem.index')
            ->with('success','✅ Thêm chấm điểm thành công!');
    }

    // ================================
    // LẤY GVPB + GVHD THEO ĐỀ TÀI + SV
    // ================================
    private function getGVHD($MaDeTai, $MaSV)
    {
        return ChamDiem::where('MaDeTai', $MaDeTai)
                        ->where('MaSV', $MaSV)
                        ->where('VaiTro', 'GVHD')
                        ->first();
    }

    private function getGVPB($MaDeTai, $MaSV)
    {
        return ChamDiem::where('MaDeTai', $MaDeTai)
                        ->where('MaSV', $MaSV)
                        ->where('VaiTro', 'GVPB')
                        ->first();
    }

    // ================================
    // EDIT
    // ================================
    public function edit($id)
    {
        $cd = ChamDiem::with(['detai','sinhvien','giangVien'])->findOrFail($id);

        $gvhd = $this->getGVHD($cd->MaDeTai, $cd->MaSV);
        $gvpb = $this->getGVPB($cd->MaDeTai, $cd->MaSV);

        $detais = DeTai::all();

        return view('admin.chamdiem.edit', compact('cd','gvhd','gvpb','detais'));
    }

    // ================================
    // UPDATE
    // ================================
    public function update(Request $request, $id)
    {
        $cd = ChamDiem::findOrFail($id);
        $MaDeTai = $cd->MaDeTai;
        $MaSV    = $cd->MaSV;

        $gvhd = $this->getGVHD($MaDeTai, $MaSV);
        $gvpb = $this->getGVPB($MaDeTai, $MaSV);

        // Cập nhật GVHD
        if($gvhd){
            $gvhd->Diem = $request->DiemGVHD ?? $gvhd->Diem;
            $gvhd->NhanXet = $request->NhanXetGVHD ?? $gvhd->NhanXet;
            $gvhd->save();
        }

        // Cập nhật GVPB
        if($gvpb){
            $gvpb->Diem = $request->DiemGVPB ?? $gvpb->Diem;
            $gvpb->NhanXet = $request->NhanXetGVPB ?? $gvpb->NhanXet;
            $gvpb->save();
        }

        // Tính điểm TB - chỉ tính từ các điểm hợp lệ (> 0)
        $diemTB = collect([
            $gvhd && $gvhd->Diem > 0 ? $gvhd->Diem : null,
            $gvpb && $gvpb->Diem > 0 ? $gvpb->Diem : null
        ])->filter()->avg();

        // Nếu admin duyệt thì lưu DiemCuoi cho ALL record
        if($request->TrangThai === 'Đã duyệt'){
            ChamDiem::where('MaDeTai', $MaDeTai)
                    ->where('MaSV', $MaSV)
                    ->update([
                        'DiemCuoi' => $diemTB,
                        'TrangThai' => 'Đã duyệt'
                    ]);
            
            // Cập nhật trạng thái đề tài thành "Đã hoàn thành"
            $deTai = DeTai::find($MaDeTai);
            if ($deTai) {
                $deTai->TrangThai = 'Đã hoàn thành';
                $deTai->save();
            }
        } else {
            // Update ALL records with new status
            $newStatus = $request->TrangThai ?? $cd->TrangThai ?? 'Chưa xác nhận';
            ChamDiem::where('MaDeTai', $MaDeTai)
                    ->where('MaSV', $MaSV)
                    ->update([
                        'DiemCuoi' => null,
                        'TrangThai' => $newStatus
                    ]);
        }

        return redirect()->route('admin.chamdiem.index')
            ->with('success','✅ Cập nhật chấm điểm thành công!');
    }

    // ================================
    // DUYỆT
    // ================================
    public function approve($id)
    {
        $cd = ChamDiem::findOrFail($id);
        $MaDeTai = $cd->MaDeTai;
        $MaSV    = $cd->MaSV;

        $list = ChamDiem::where('MaDeTai', $MaDeTai)
                        ->where('MaSV', $MaSV)
                        ->get();

        // Chỉ tính trung bình từ các bản ghi có điểm (không null, không 0)
        $validScores = $list->where('Diem', '>', 0);
        $diemTB = $validScores->count() > 0 ? $validScores->avg('Diem') : 0;

        ChamDiem::where('MaDeTai', $MaDeTai)
                ->where('MaSV', $MaSV)
                ->update([
                    'DiemCuoi' => $diemTB,
                    'TrangThai' => 'Đã duyệt'
                ]);

        // Cập nhật trạng thái đề tài thành "Đã hoàn thành"
        $deTai = DeTai::find($MaDeTai);
        if ($deTai) {
            $deTai->TrangThai = 'Đã hoàn thành';
            $deTai->save();
        }

        return back()->with('success','✔ Điểm đã được duyệt!');
    }

    // ================================
    // UPDATE STATUS
    // ================================
    public function updateStatus(Request $request, $id)
    {
        $cd = ChamDiem::findOrFail($id);

        $MaDeTai = $cd->MaDeTai;
        $MaSV    = $cd->MaSV;

        $list = ChamDiem::where('MaDeTai', $MaDeTai)
                        ->where('MaSV', $MaSV)
                        ->get();

        // Chỉ tính trung bình từ các bản ghi có điểm (không null, không 0)
        $validScores = $list->where('Diem', '>', 0);
        $diemTB = $validScores->count() > 0 ? $validScores->avg('Diem') : 0;

        if ($request->TrangThai === 'Đã duyệt') {
            // Update ALL records for this student-project
            ChamDiem::where('MaDeTai', $MaDeTai)
                    ->where('MaSV', $MaSV)
                    ->update([
                        'DiemCuoi' => $diemTB,
                        'TrangThai' => 'Đã duyệt'
                    ]);
            
            // Cập nhật trạng thái đề tài thành "Đã hoàn thành"
            $deTai = DeTai::find($MaDeTai);
            if ($deTai) {
                $deTai->TrangThai = 'Đã hoàn thành';
                $deTai->save();
            }
        } else {
            // Update ALL records
            ChamDiem::where('MaDeTai', $MaDeTai)
                    ->where('MaSV', $MaSV)
                    ->update([
                        'DiemCuoi' => null,
                        'TrangThai' => $request->TrangThai
                    ]);
        }

        return back()->with('success', 'Cập nhật trạng thái thành công!');

    }

    // ================================
    // UPDATE ROLE
    // ================================
    public function updateRole(Request $request, $id)
    {
        $cd = ChamDiem::findOrFail($id);
        $cd->VaiTro = $request->VaiTro;
        $cd->save();

        return back()->with('success', '✅ Cập nhật vai trò thành công!');
    }

    // ================================
    // SHOW
    // ================================
    public function show($id)
    {
        $cd = ChamDiem::with(['detai','sinhvien','giangVien'])->findOrFail($id);

        $MaDeTai = $cd->MaDeTai;
        $MaSV    = $cd->MaSV;

        // LẤY FULL DANH SÁCH GIẢNG VIÊN CHẤM
        $listGV = ChamDiem::where('MaDeTai', $MaDeTai)
                    ->where('MaSV', $MaSV)
                    ->with('giangVien')
                    ->get();

        // Get PhanCong to determine roles
        $phancongs = \App\Models\PhanCong::where('MaDeTai', $MaDeTai)
                    ->with('giangVien')
                    ->get();
        
        // Map VaiTro from PhanCong to each ChamDiem record
        $listGV = $listGV->map(function($cham) use ($phancongs) {
            $phancong = $phancongs->firstWhere('MaGV', $cham->MaGV);
            if ($phancong) {
                $cham->VaiTroDisplay = $phancong->VaiTro;
            } else {
                $cham->VaiTroDisplay = $cham->VaiTro ?? 'N/A';
            }
            return $cham;
        });
        
        // Tách GVHD + GVPB based on PhanCong roles
        $gvhd = $listGV->first(function($cham) {
            return str_contains(strtolower($cham->VaiTroDisplay ?? ''), 'hướng dẫn');
        });
        
        $gvpb = $listGV->first(function($cham) {
            return str_contains(strtolower($cham->VaiTroDisplay ?? ''), 'phản biện');
        });

        // Điểm TB
        $diemTB = $listGV->avg('Diem');

        return view('admin.chamdiem.show', compact(
            'cd','listGV','gvhd','gvpb','diemTB'
        ));
    }


    // ================================
    // DELETE
    // ================================
    public function destroy($id)
    {
        ChamDiem::destroy($id);
        return redirect()->route('admin.chamdiem.index')
                         ->with('success','🗑️ Xóa chấm điểm thành công!');
    }
}