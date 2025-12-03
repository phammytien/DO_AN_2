<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BaoCao;
use App\Models\DeTai;
use App\Models\SinhVien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class BaoCaoController extends Controller
{
    public function index(Request $request)
    {
        $query = BaoCao::with(['deTai', 'sinhVien']);

        // Lọc theo đề tài
        if ($request->filled('detai')) {
            $query->where('MaDeTai', $request->detai);
        }

        // Lọc theo sinh viên
        if ($request->filled('sinhvien')) {
            $query->where('MaSV', $request->sinhvien);
        }

        // Lọc theo trạng thái
        if ($request->filled('trangthai')) {
            $query->where('TrangThai', $request->trangthai);
        }

        $baocaos = $query->orderBy('NgayNop', 'desc')->paginate(15);
        $detais = DeTai::all();
        $sinhviens = SinhVien::all();

        return view('admin.baocao.index', compact('baocaos', 'detais', 'sinhviens'));
    }

    public function create()
    {
        $detais = DeTai::all();
        return view('admin.baocao.create', compact('detais'));
    }

    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'MaDeTai' => 'required|integer|exists:DeTai,MaDeTai',
    //         'file' => 'required|file|max:51200'
    //     ]);

    //     $maSV = Auth::user()->sinhVien->MaSV;

    //     if (!$maSV) {
    //         return back()->with('error', 'Không tìm thấy mã sinh viên của tài khoản này.');
    //     }

    //     $file = $request->file('file');
    //     $fileName = time() . '_' . $file->getClientOriginalName();
    //     $path = $file->storeAs('baocao', $fileName, 'public');

    //     BaoCao::create([
    //         'MaDeTai' => $request->MaDeTai,
    //         'MaSV' => $maSV,
    //         'TenFile' => $file->getClientOriginalName(),
    //         'LinkFile' => $path,
    //         'NgayNop' => now(),
    //         'LanNop' => 1,
    //         'NhanXet' => null,
    //         'TrangThai' => 'Chờ duyệt'
    //     ]);

    //     return redirect()->route('admin.baocao.index')->with('success','Nộp báo cáo thành công');
    // }

    public function edit($id)
    {
        $bc = BaoCao::findOrFail($id);
        $detais = DeTai::all();
        return view('admin.baocao.edit', compact('bc','detais'));
    }

    public function update(Request $request, $id)
    {
        $bc = BaoCao::findOrFail($id);

        $request->validate([
            'MaDeTai' => 'required|integer|exists:DeTai,MaDeTai',
            'file' => 'nullable|file|max:51200'
        ]);

        // Nếu có file mới thì cập nhật file
        if ($request->hasFile('file')) {

            // Xóa file cũ
            if ($bc->LinkFile) {
                Storage::disk('public')->delete($bc->LinkFile);
            }

            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('baocao', $fileName, 'public');

            $bc->TenFile = $file->getClientOriginalName();
            $bc->LinkFile = $path;
            $bc->NgayNop = now();
        }

        $bc->MaDeTai = $request->MaDeTai;
        $bc->LanNop = $request->LanNop ?? $bc->LanNop;
        $bc->NhanXet = $request->NhanXet ?? $bc->NhanXet;

        $bc->save();

        return redirect()->route('admin.baocao.index')->with('success','Cập nhật báo cáo thành công');
    }

    public function duyet($id)
    {
        $bc = BaoCao::findOrFail($id);
        $bc->TrangThai = 'Đã duyệt';
        $bc->save();

        return redirect()->route('admin.baocao.index')->with('success','Đã duyệt báo cáo.');
    }

    public function yeuCauChinhSua(Request $request, $id)
    {
        $bc = BaoCao::findOrFail($id);

        $request->validate([
            'NhanXet' => 'required|string'
        ]);

        $bc->NhanXet = $request->NhanXet;
        $bc->TrangThai = 'Yêu cầu chỉnh sửa';
        $bc->save();

        return redirect()->route('admin.baocao.index')->with('success','Đã gửi yêu cầu chỉnh sửa.');
    }

    public function destroy($id)
    {
        $bc = BaoCao::findOrFail($id);

        if ($bc->LinkFile) {
            Storage::disk('public')->delete($bc->LinkFile);
        }

        $bc->delete();

        return redirect()->route('admin.baocao.index')->with('success','Xóa báo cáo thành công');
    }


}
