<?php

namespace App\Http\Controllers\SinhVien;

use App\Http\Controllers\Controller;
use App\Models\TienDo;
use Illuminate\Http\Request;
use App\Helpers\FileHelper;

class TienDoController extends Controller
{
    public function update(Request $request, $id)
    {
        $tiendo = TienDo::findOrFail($id);
        
        $request->validate([
            'file_baocao' => 'required_without:file_code|file|max:20480', // 20MB
            'file_code' => 'required_without:file_baocao|file|max:20480' // 20MB
        ]);

        // Check if final report is approved
        $maSV = \Illuminate\Support\Facades\Auth::user()->MaSo;
        $maDeTai = \Illuminate\Support\Facades\DB::table('DeTai_SinhVien')
                        ->where('MaSV', $maSV)
                        ->value('MaDeTai');
        
        $lastReport = \App\Models\BaoCao::where('MaSV', $maSV)
                        ->where('MaDeTai', $maDeTai)
                        ->orderBy('LanNop', 'desc')
                        ->first();

        if ($lastReport && $lastReport->TrangThai === 'Đã duyệt') {
            return back()->with('error', 'Đề tài đã hoàn thành. Không thể cập nhật tiến độ.');
        }

        // Check permission
        $isLate = $tiendo->Deadline && now()->gt($tiendo->Deadline);
        $isAllowed = $tiendo->TrangThai === 'Được nộp bổ sung';

        if ($isLate && !$isAllowed) {
            return back()->with('error', 'Đã quá hạn! Vui lòng xin nộp bổ sung.');
        }

        $updateData = [
            'NgayNop' => now(),
            'TrangThai' => 'Đã nộp'
        ];

        if ($request->hasFile('file_baocao')) {
            $file = $request->file('file_baocao');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('baocao', $fileName, 'public');
            
            $updateData['LinkFile'] = 'storage/baocao/' . $fileName;
            $updateData['TenFile'] = $fileName;
        }

        if ($request->hasFile('file_code')) {
            $fileCode = $request->file('file_code');
            $fileNameCode = time() . '_code_' . $fileCode->getClientOriginalName();
            $filePathCode = $fileCode->storeAs('baocao/code', $fileNameCode, 'public');
            
            $newFileCode = \App\Models\File::create([
                'name' => $fileNameCode,
                'path' => 'baocao/code/' . $fileNameCode,
                'type' => $fileCode->getMimeType(),
                'size' => $fileCode->getSize(),
                'extension' => $fileCode->getClientOriginalExtension(),
            ]);
            $updateData['FileCodeID'] = $newFileCode->id;
        }

        $tiendo->update($updateData);

        return back()->with('success', 'Nộp báo cáo thành công!');
    }

    public function requestLate($id)
    {
        $tiendo = TienDo::findOrFail($id);
        
        // Check if final report is approved
        $maSV = \Illuminate\Support\Facades\Auth::user()->MaSo;
        $maDeTai = \Illuminate\Support\Facades\DB::table('DeTai_SinhVien')
                        ->where('MaSV', $maSV)
                        ->value('MaDeTai');
        
        $lastReport = \App\Models\BaoCao::where('MaSV', $maSV)
                        ->where('MaDeTai', $maDeTai)
                        ->orderBy('LanNop', 'desc')
                        ->first();

        if ($lastReport && $lastReport->TrangThai === 'Đã duyệt') {
            return back()->with('error', 'Đề tài đã hoàn thành. Không thể gửi yêu cầu.');
        }

        if ($tiendo->TrangThai === 'Xin nộp bổ sung' || $tiendo->TrangThai === 'Đã duyệt') {
            return back()->with('error', 'Không thể gửi yêu cầu (Đã duyệt hoặc đang chờ duyệt).');
        }

        $tiendo->update(['TrangThai' => 'Xin nộp bổ sung']);
        return back()->with('success', 'Đã gửi yêu cầu xin nộp bổ sung.');
    }
}
