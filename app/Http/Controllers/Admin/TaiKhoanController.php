<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TaiKhoan;
use App\Models\ThongBao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TaiKhoanController extends Controller
{
    public function index(Request $request)
    {
        $query = TaiKhoan::query()
            ->leftJoin('sinhvien', 'taikhoan.MaTK', '=', 'sinhvien.MaTK')
            ->leftJoin('giangvien', 'taikhoan.MaTK', '=', 'giangvien.MaTK')
            ->leftJoin('canboql', 'taikhoan.MaTK', '=', 'canboql.MaTK')
            ->select(
                'taikhoan.*',
                'sinhvien.TenSV',
                'giangvien.TenGV',
                'canboql.TenCB'
            );

        // Lọc theo vai trò (quan trọng!)
        if ($request->filled('vaitro')) {
            $query->where('taikhoan.VaiTro', $request->vaitro);
        }

        // Tìm kiếm
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function($q) use ($keyword) {
                $q->where('taikhoan.MaSo', 'like', "%{$keyword}%")
                  ->orWhere('sinhvien.TenSV', 'like', "%{$keyword}%")
                  ->orWhere('giangvien.TenGV', 'like', "%{$keyword}%")
                  ->orWhere('canboql.TenCB', 'like', "%{$keyword}%");
            });
        }

        $taikhoans = $query->paginate(15)->appends($request->all());
        $vaitros = TaiKhoan::distinct()->pluck('VaiTro')->toArray();

        return view('admin.taikhoan.index', compact('taikhoans', 'vaitros'));
    }



public function ajaxSearch(Request $request)
{
    $keyword = $request->keyword;

    $data = TaiKhoan::leftJoin('SinhVien', 'SinhVien.MaSV', '=', 'TaiKhoan.MaSo')
        ->leftJoin('GiangVien', 'GiangVien.MaGV', '=', 'TaiKhoan.MaSo')
        ->leftJoin('CanBoQL', 'CanBoQL.MaCB', '=', 'TaiKhoan.MaSo')
        ->select(
            'TaiKhoan.*',
            'SinhVien.TenSV',
            'GiangVien.TenGV',
            'CanBoQL.TenCB'
        )
        ->when($keyword, function ($q) use ($keyword) {
            $q->where('TaiKhoan.MaSo', 'LIKE', "%$keyword%")
              ->orWhere('SinhVien.TenSV', 'LIKE', "%$keyword%")
              ->orWhere('GiangVien.TenGV', 'LIKE', "%$keyword%")
              ->orWhere('CanBoQL.TenCB', 'LIKE', "%$keyword%");
        })
        ->orderBy('MaTK', 'desc')
        ->limit(20)
        ->get();

    return response()->json($data);
}


    public function create()
    {
        return view('admin.taikhoan.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'MaSo'   => 'required|unique:TaiKhoan,MaSo',
            'MatKhau'=> 'required|min:6',
            'VaiTro' => 'required|in:SinhVien,GiangVien,CanBo,Admin'
        ]);

        TaiKhoan::create([
            'MaSo'     => $request->MaSo,
            'MatKhau'  => Hash::make($request->MatKhau),
            'VaiTro'   => $request->VaiTro,
            'TrangThai'=> 'active',
        ]);

        return redirect()->route('admin.taikhoan.index')
            ->with('success', 'Tạo tài khoản thành công.');
    }

    public function edit($id)
    {
        $taikhoan = TaiKhoan::findOrFail($id);
        return view('admin.taikhoan.edit', compact('taikhoan'));
    }

    public function update(Request $request, $id)
    {
        $taikhoan = TaiKhoan::findOrFail($id);

        $request->validate([
            'MaSo'  => 'required|unique:TaiKhoan,MaSo,' . $id . ',MaTK',
            'VaiTro'=> 'required|in:SinhVien,GiangVien,CanBo,Admin'
        ]);

        $data = [
            'MaSo'  => $request->MaSo,
            'VaiTro'=> $request->VaiTro
        ];

        if ($request->MatKhau) {
            $data['MatKhau'] = Hash::make($request->MatKhau);
        }

        $taikhoan->update($data);

        return redirect()->route('admin.taikhoan.index')
            ->with('success', 'Cập nhật tài khoản thành công.');
    }

    // KHÓA / MỞ KHÓA TÀI KHOẢN
    public function toggleStatus($id)
    {
        $tk = TaiKhoan::findOrFail($id);
        $tk->TrangThai = $tk->TrangThai === 'active' ? 'locked' : 'active';
        $tk->save();

        return back()->with('success', 'Cập nhật trạng thái thành công!');
    }


    public function resetPassword($id)
{
    $tk = TaiKhoan::findOrFail($id);

    // Mật khẩu mặc định
    $newPassword = '123456';

    $tk->MatKhau = Hash::make($newPassword);
    $tk->save();

    // Tạo thông báo cho người dùng
    $doiTuong = '';
    $tenNguoiDung = '';
    
    if ($tk->VaiTro === 'sinhvien') {
        $doiTuong = 'SV';
        $sinhVien = $tk->sinhVien;
        $tenNguoiDung = $sinhVien ? $sinhVien->TenSV : 'Sinh viên';
    } elseif ($tk->VaiTro === 'giangvien') {
        $doiTuong = 'GV';
        $giangVien = $tk->giangVien;
        $tenNguoiDung = $giangVien ? $giangVien->TenGV : 'Giảng viên';
    }

    // Chỉ tạo thông báo cho sinh viên và giảng viên
    if ($doiTuong) {
        ThongBao::create([
            'NoiDung' => "Mật khẩu của bạn đã được Admin reset lại. Mật khẩu mới: {$newPassword}. Vui lòng đổi mật khẩu sau khi đăng nhập.",
            'TGDang' => now(),
            'MaCB' => null,
            'TenFile' => null,
            'DoiTuongNhan' => $doiTuong,
            'MucDo' => 'QuanTrong',
            'MaNguoiNhan' => $tk->MaSo
        ]);
    }

    return back()->with('success', 'Mật khẩu mới: ' . $newPassword);
}


    // XÓA VĨNH VIỄN
    public function destroy($id)
    {
        TaiKhoan::findOrFail($id)->forceDelete();

        return redirect()->route('admin.taikhoan.index')
            ->with('success', 'Xóa tài khoản vĩnh viễn thành công.');
    }

    // ================== ĐỔI MẬT KHẨU ADMIN ==================
    public function changePasswordView()
    {
        return view('admin.profile.change_password');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ], [
            'new_password.confirmed' => 'Mật khẩu xác nhận không khớp.',
            'new_password.min' => 'Mật khẩu mới phải có ít nhất 6 ký tự.'
        ]);

        $user = \Illuminate\Support\Facades\Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Mật khẩu hiện tại không đúng.']);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return back()->with('success', 'Đổi mật khẩu thành công!');
    }
}