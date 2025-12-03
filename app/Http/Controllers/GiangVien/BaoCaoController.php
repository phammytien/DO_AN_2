<?php

namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Models\BaoCao;
use App\Models\DeTai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BaoCaoController extends Controller
{
    public function index()
    {
        $maGV = Auth::user()?->MaSo;
        $baocaos = BaoCao::whereHas('deTai', function($q) use ($maGV){
            $q->where('MaGV', $maGV);
        })->with(['deTai', 'sinhVien'])->orderBy('NgayNop', 'desc')->paginate(15);

        return view('giangvien.baocao.index', compact('baocaos'));
    }

    public function approveLate($id)
    {
        $baocao = BaoCao::findOrFail($id);
        
        // Check ownership
        if ($baocao->deTai->MaGV !== Auth::user()->MaSo) {
             return back()->with('error', 'Bạn không có quyền duyệt báo cáo này.');
        }

        if ($baocao->TrangThai !== 'Xin nộp bổ sung') {
            return back()->with('error', 'Trạng thái không hợp lệ.');
        }

        $baocao->TrangThai = 'Được nộp bổ sung';
        $baocao->save();

        return back()->with('success', 'Đã duyệt yêu cầu nộp bổ sung.');
    }
    
    public function rejectLate($id)
    {
        $baocao = BaoCao::findOrFail($id);

        // Check ownership
        if ($baocao->deTai->MaGV !== Auth::user()->MaSo) {
             return back()->with('error', 'Bạn không có quyền từ chối báo cáo này.');
        }
        
        if ($baocao->TrangThai !== 'Xin nộp bổ sung') {
            return back()->with('error', 'Trạng thái không hợp lệ.');
        }

        $baocao->TrangThai = 'Từ chối nộp bù';
        $baocao->save();

        return back()->with('success', 'Đã từ chối yêu cầu nộp bổ sung.');
    }

    public function approve(Request $request, $id)
    {
        $baocao = BaoCao::findOrFail($id);
        
        // Check ownership
        if ($baocao->deTai->MaGV !== Auth::user()->MaSo) {
             return back()->with('error', 'Bạn không có quyền duyệt báo cáo này.');
        }

        if ($baocao->TrangThai !== 'Chờ duyệt') {
            return back()->with('error', 'Báo cáo này không ở trạng thái chờ duyệt.');
        }

        $baocao->TrangThai = 'Đã duyệt';
        if ($request->filled('NhanXet')) {
            $baocao->NhanXet = $request->NhanXet;
        }
        $baocao->save();

        return back()->with('success', 'Đã duyệt báo cáo thành công.');
    }

    public function comment(Request $request, $id)
    {
        $baocao = BaoCao::findOrFail($id);
        
        // Check ownership
        if ($baocao->deTai->MaGV !== Auth::user()->MaSo) {
             return back()->with('error', 'Bạn không có quyền nhận xét báo cáo này.');
        }

        $request->validate([
            'NhanXet' => 'required|string|max:1000'
        ]);

        $baocao->NhanXet = $request->NhanXet;
        $baocao->save();

        return back()->with('success', 'Đã lưu nhận xét thành công.');
    }

    public function destroy($id)
    {
        $baocao = BaoCao::findOrFail($id);
        
        // Check ownership
        if ($baocao->deTai->MaGV !== Auth::user()->MaSo) {
             return back()->with('error', 'Bạn không có quyền xóa báo cáo này.');
        }

        // Delete file if exists
        // Delete files if exist
        if ($baocao->fileBaoCao) {
            if (\Storage::disk('public')->exists(str_replace('storage/', '', $baocao->fileBaoCao->path))) {
                \Storage::disk('public')->delete(str_replace('storage/', '', $baocao->fileBaoCao->path));
            }
            $baocao->fileBaoCao->delete();
        }

        if ($baocao->fileCode) {
            if (\Storage::disk('public')->exists(str_replace('storage/', '', $baocao->fileCode->path))) {
                \Storage::disk('public')->delete(str_replace('storage/', '', $baocao->fileCode->path));
            }
            $baocao->fileCode->delete();
        }

        $baocao->delete();

        return back()->with('success', 'Đã xóa báo cáo thành công.');
    }
}
