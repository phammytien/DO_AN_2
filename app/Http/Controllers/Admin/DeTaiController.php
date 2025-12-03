<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeTai;
use App\Models\GiangVien;
use App\Models\CanBoQL;
use App\Models\NamHoc;
use App\Models\Nganh;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\CauHinhHeThong;

class DeTaiController extends Controller
{
    /**
     * Cập nhật trạng thái đề tài dựa trên thời gian đăng ký
     * Nên gọi trước khi hiển thị danh sách hoặc qua cron job
     */
private function capNhatTrangThaiTheoThoiGian()
{
    $now = now();

    // Lấy tất cả đề tài đang mở đăng ký
    $detais = DeTai::where('TrangThai', 'Mở đăng ký')->get();

    foreach ($detais as $dt) {

        // Lấy cấu hình theo năm học của đề tài
        $config = CauHinhHeThong::where('MaNamHoc', $dt->MaNamHoc)->first();
        if (!$config) continue;

        // Nếu quá hạn → đổi sang ĐÃ DUYỆT
        if ($now->gt($config->ThoiGianDongDangKy)) {
            $dt->update(['TrangThai' => 'Đã duyệt']);
        }
    }
}


    /**
     * Hiển thị danh sách đề tài (lọc theo trạng thái)
     */
    public function index(Request $request)
{
    $this->capNhatTrangThaiTheoThoiGian();

    $trangThai = $request->get('trangthai');
    $query = DeTai::with(['giangVien', 'canBo', 'sinhViens', 'namHoc']);

    if ($trangThai) {
        $query->where('TrangThai', $trangThai);
    }

    $detais = $query->orderByDesc('MaDeTai')->paginate(10);
    $thoigian = DB::table('CauHinhHeThong')->first();
    
    // THÊM CÁC BIẾN NÀY
    $gvs = GiangVien::all();
    $cbs = CanBoQL::all();
    $namHocs = NamHoc::all();
    $nganhs = Nganh::all();

    return view('admin.detai.index', compact('detais', 'trangThai', 'thoigian', 'gvs', 'cbs', 'namHocs', 'nganhs'));
}

// THÊM METHOD MỚI - Load form sửa qua AJAX

    // ================= Tạo, Lưu, Sửa, Cập nhật, Duyệt, Hủy, Xóa đề tài =================

    public function create()
    {
        $gvs = GiangVien::all();
        $cbs = CanBoQL::all();
        $namHocs = NamHoc::all();
        $nganhs = Nganh::all();

        return view('admin.detai.create', compact('gvs', 'cbs', 'namHocs', 'nganhs'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'TenDeTai' => 'required|string|min:10|max:500',
            'MoTa' => 'nullable|string',
            'LinhVuc' => 'required|string',
            'LoaiDeTai' => 'nullable|string',
            'MaNamHoc' => 'required|exists:namhoc,MaNamHoc',
            'MaGV' => 'nullable|exists:giangvien,MaGV',
            'MaCB' => 'nullable|exists:canboql,MaCB'
        ], [
            'TenDeTai.required' => 'Tên đề tài không được để trống',
            'TenDeTai.min' => 'Tên đề tài phải có ít nhất 10 ký tự',
            'TenDeTai.max' => 'Tên đề tài không được vượt quá 500 ký tự',
            'LinhVuc.required' => 'Lĩnh vực không được để trống',
            'MaNamHoc.required' => 'Năm học không được để trống'
        ]);

        // Tự động sinh MaDeTai
        $lastDeTai = DeTai::orderBy('MaDeTai', 'desc')->first();
        $nextId = $lastDeTai ? intval(substr($lastDeTai->MaDeTai, 2)) + 1 : 1;
        $data['MaDeTai'] = 'DT' . str_pad($nextId, 3, '0', STR_PAD_LEFT);

        // Kiểm tra vai trò người tạo
        $user = auth()->user();
        
        // Nếu Admin hoặc Cán bộ tạo -> tự động duyệt
        // Nếu Giảng viên tạo -> cần duyệt
        if ($user && in_array($user->VaiTro, ['Admin', 'CanBo'])) {
            $data['TrangThai'] = 'Đang thực hiện';
        } else {
            $data['TrangThai'] = 'Chưa duyệt';
        }

        DeTai::create($data);

        return redirect()->route('admin.detai.index')
            ->with('success', 'Thêm đề tài thành công!');
    }

public function edit($id)
{
    $detai = DeTai::findOrFail($id);
    $gvs = GiangVien::all();
    $cbs = CanBoQL::all();
    $namhocs = NamHoc::all();
    $nganhs = Nganh::all();

    // Nếu là AJAX request, trả về partial view
    if (request()->ajax()) {
        return view('admin.detai.edit_form', compact('detai', 'gvs', 'cbs', 'namhocs', 'nganhs'));
    }

    return view('admin.detai.edit', compact('detai', 'gvs', 'cbs', 'namhocs', 'nganhs'));
}

    

    public function update(Request $request, $id)
    {
        $detai = DeTai::findOrFail($id);

        $request->validate([
            'TenDeTai' => 'required|string|max:300',
            'LinhVuc' => 'required|string|max:100',
            'LoaiDeTai' => 'required|string|max:50',
            'MaNamHoc' => 'required|integer|exists:NamHoc,MaNamHoc',
        ]);

        $detai->update([
            'TenDeTai' => $request->TenDeTai,
            'MoTa' => $request->MoTa,
            'LinhVuc' => $request->LinhVuc,
            'LoaiDeTai' => $request->LoaiDeTai,
            'TrangThai' => $request->TrangThai ?? $detai->TrangThai,
            'MaGV' => $request->MaGV,
            'MaCB' => $request->MaCB,
            'MaNamHoc' => $request->MaNamHoc,
        ]);

        return redirect()->route('admin.detai.index')->with('success', '📝 Cập nhật đề tài thành công!');
    }

public function approve($id)
{
    $detai = DeTai::findOrFail($id);

    // Tự động khớp cấu hình theo năm học của đề tài
    $config = CauHinhHeThong::where('MaNamHoc', $detai->MaNamHoc)->first();

    if (!$config) {
        return back()->with('error', 'Năm học này chưa được thiết lập cấu hình thời gian!');
    }

    $detai->update(['TrangThai' => 'Mở đăng ký']);

    return back()->with('success', 'Đã mở đăng ký theo đúng năm học!');
}

/**
 * Duyệt nhiều đề tài cùng lúc và thiết lập thời gian đăng ký
 */
public function approveMultiple(Request $request)
{
    $request->validate([
        'detai_ids' => 'required|string',
        'ThoiGianMoDangKy' => 'required|date',
        'ThoiGianDongDangKy' => 'required|date|after:ThoiGianMoDangKy',
    ], [
        'ThoiGianDongDangKy.after' => 'Ngày đóng đăng ký phải sau ngày mở đăng ký!'
    ]);

    // Chuyển chuỗi ID thành mảng
    $detaiIds = explode(',', $request->detai_ids);
    
    // Lấy danh sách đề tài
    $detais = DeTai::whereIn('MaDeTai', $detaiIds)->get();
    
    if ($detais->isEmpty()) {
        return back()->with('error', 'Không tìm thấy đề tài nào!');
    }

    // Nhóm đề tài theo năm học
    $namHocGroups = $detais->groupBy('MaNamHoc');
    
    // Cập nhật hoặc tạo cấu hình cho từng năm học
    foreach ($namHocGroups as $maNamHoc => $detaisInYear) {
        CauHinhHeThong::updateOrCreate(
            ['MaNamHoc' => $maNamHoc],
            [
                'ThoiGianMoDangKy' => $request->ThoiGianMoDangKy,
                'ThoiGianDongDangKy' => $request->ThoiGianDongDangKy,
            ]
        );
    }

    // Cập nhật trạng thái tất cả đề tài
    DeTai::whereIn('MaDeTai', $detaiIds)->update(['TrangThai' => 'Mở đăng ký']);

    return back()->with('success', "✅ Đã duyệt {$detais->count()} đề tài và thiết lập thời gian đăng ký!");
}


    public function complete($id)
    {
        $detai = DeTai::findOrFail($id);
        $detai->update(['TrangThai' => 'Hoàn thành']);
        return back()->with('success', '🎯 Đề tài đã hoàn thành!');
    }

    public function cancel($id)
    {
        $detai = DeTai::findOrFail($id);
        $detai->update(['TrangThai' => 'Hủy']);
        return back()->with('success', '❌ Đề tài đã bị hủy!');
    }

    public function destroy($id)
    {
        $detai = DeTai::findOrFail($id);

        // Xóa các bảng liên quan trước
        // 1. Xóa Báo cáo
        \App\Models\BaoCao::where('MaDeTai', $id)->delete();
        
        // 2. Xóa Chấm điểm
        \App\Models\ChamDiem::where('MaDeTai', $id)->delete();
        
        // 3. Xóa Phân công
        \App\Models\PhanCong::where('MaDeTai', $id)->delete();
        
        // 4. Xóa Tiến độ
        \App\Models\TienDo::where('MaDeTai', $id)->delete();

        // 5. Xóa Sinh viên tham gia (Pivot table)
        $detai->sinhViens()->detach();

        // Cuối cùng xóa Đề tài
        $detai->delete();

        return redirect()->route('admin.detai.index')->with('success', '🗑️ Xóa đề tài và dữ liệu liên quan thành công!');
    }

    public function capNhatThoiGianDangKy(Request $request)
    {
        $request->validate([
            'ThoiGianMo' => 'required|date',
            'ThoiGianDong' => 'required|date|after:ThoiGianMo',
        ]);

        DB::table('CauHinhHeThong')->updateOrInsert(
            ['id' => 1],
            [
                'ThoiGianMoDangKy' => $request->ThoiGianMo,
                'ThoiGianDongDangKy' => $request->ThoiGianDong,
                'updated_at' => now()
            ]
        );

        return back()->with('success', '🕒 Cập nhật thời gian đăng ký thành công!');
    }

    /**
     * Export danh sách sinh viên đăng ký đề tài
     */
    public function exportDangKy()
    {
        $detais = DeTai::with(['giangVien', 'sinhViens.lop'])->get();
        
        $data = [];
        $counter = 1;
        
        foreach ($detais as $detai) {
            foreach ($detai->sinhViens as $sinhvien) {
                $data[] = [
                    'STT' => $counter++,
                    'Mã đề tài' => $detai->MaDeTai,
                    'Tên đề tài' => $detai->TenDeTai,
                    'Giảng viên hướng dẫn' => $detai->giangVien->TenGV ?? 'Chưa gán',
                    'Tên sinh viên' => $sinhvien->HoTen ?? $sinhvien->TenSV ?? 'Chưa có tên',
                    'Lớp' => $sinhvien->lop->TenLop ?? 'N/A',
                ];
            }
        }
        
        // Tạo file Excel
        $filename = 'Danh_sach_sinh_vien_dang_ky_de_tai_' . date('Y-m-d_His') . '.xlsx';
        
        return \Maatwebsite\Excel\Facades\Excel::download(
            new class($data) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
                protected $data;
                
                public function __construct($data) {
                    $this->data = collect($data);
                }
                
                public function collection() {
                    return $this->data;
                }
                
                public function headings(): array {
                    return ['STT', 'Mã đề tài', 'Tên đề tài', 'Giảng viên hướng dẫn', 'Tên sinh viên', 'Lớp'];
                }
            },
            $filename
        );
    }
}