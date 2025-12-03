<?php

// namespace App\Http\Controllers\GiangVien;

// use App\Http\Controllers\Controller;
// use App\Models\TienDo;
// use App\Models\DeTai;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Auth;

// class TienDoController extends Controller
// {
//     public function index()
//     {
        
//         $maGV = Auth::user()?->MaSo;
//         $tiendos = TienDo::whereHas('deTai', function($q) use ($maGV){
//             $q->where('MaGV', $maGV);
//         })->with('deTai')->get();
//         return view('giangvien.tiendo.index', compact('tiendos'));
//     }

//     public function update(Request $request, $id)
//     {
//         $t = TienDo::findOrFail($id);
//         $request->validate(['TrangThai' => 'nullable|string|max:50','GhiChu' => 'nullable|string|max:300']);
//         $t->update($request->only('TrangThai','GhiChu'));
//         return back()->with('success','Cập nhật tiến độ thành công');
//     }
// }




namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Models\TienDo;
use App\Models\DeTai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TienDoController extends Controller
{
    public function index()
    {
        
        $maGV = Auth::user()?->MaSo;
        $tiendos = TienDo::whereHas('deTai', function($q) use ($maGV){
            $q->where('MaGV', $maGV);
        })->with(['deTai', 'fileCode'])->get();
        return view('giangvien.tiendo.index', compact('tiendos'));
    }
public function edit($id)
{
    $tiendo = TienDo::findOrFail($id);

    return view('giangvien.tiendo.edit', compact('tiendo'));
}

    public function store(Request $request)
    {
        $request->validate([
            'MaDeTai' => 'required|exists:DeTai,MaDeTai',
            'NoiDung' => 'required|string|max:500',
            'Deadline' => 'required|date',
        ]);

        TienDo::create([
            'MaDeTai' => $request->MaDeTai,
            'NoiDung' => $request->NoiDung,
            'Deadline' => $request->Deadline,
            'ThoiGianCapNhat' => now(),
            'TrangThai' => 'Chưa nộp'
        ]);

        return back()->with('success', 'Đã thêm mốc tiến độ mới');
    }

    public function destroy($id)
    {
        TienDo::destroy($id);
        return back()->with('success', 'Đã xóa mốc tiến độ');
    }

    public function update(Request $request, $id)
    {
        $t = TienDo::findOrFail($id);
        $request->validate([
            'NoiDung' => 'nullable|string|max:500',
            'Deadline' => 'nullable|date',
            'TrangThai' => 'nullable|string|max:50',
            'GhiChu' => 'nullable|string|max:300'
        ]);
        $t->update($request->only('NoiDung', 'Deadline', 'TrangThai', 'GhiChu'));
        return redirect()->route('giangvien.tiendo.index')->with('success','Cập nhật tiến độ thành công');
    }

    public function approveLate($id)
    {
        $tiendo = TienDo::findOrFail($id);
        $tiendo->update(['TrangThai' => 'Được nộp bổ sung']);
        return back()->with('success', 'Đã cho phép sinh viên nộp bổ sung.');
    }
}