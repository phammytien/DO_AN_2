@extends('layouts.admin')

@section('content')

<style>
    body {
        background-color: #f5f7fa;
    }
    .page-header {
        background: #fff;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        margin-bottom: 20px;
    }
    .filter-bar {
        background: #fff;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        margin-bottom: 20px;
    }
    .accordion-item {
        border: none;
        margin-bottom: 15px;
        border-radius: 10px !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        overflow: hidden;
    }
    .accordion-header {
        background: #fff;
    }
    .accordion-button {
        background: #fff !important;
        color: #333 !important;
        font-weight: 600;
        padding: 20px;
        box-shadow: none !important;
    }
    .accordion-button::after {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23333'%3e%3cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3e%3c/svg%3e");
    }
    .accordion-button:not(.collapsed) {
        background: #f8f9fa !important;
    }
    .badge-class {
        background-color: #e0e7ff;
        color: #4338ca;
        font-weight: 500;
        border-radius: 6px;
        padding: 5px 10px;
        font-size: 0.85rem;
    }
    .badge-count {
        background-color: #dbeafe;
        color: #1e40af;
        border-radius: 20px;
        padding: 4px 12px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    .badge-major {
        background-color: #fef3c7;
        color: #92400e;
        border-radius: 20px;
        padding: 4px 12px;
        font-size: 0.8rem;
        font-weight: 500;
    }
    .badge-faculty {
        background-color: #d1fae5;
        color: #065f46;
        border-radius: 20px;
        padding: 4px 12px;
        font-size: 0.8rem;
        font-weight: 500;
    }
    
    /* Table Styling */
    .table thead th {
        background-color: #f9fafb;
        color: #6b7280;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        border-bottom: 1px solid #e5e7eb;
        padding: 12px 16px;
    }
    .table tbody td {
        padding: 16px;
        vertical-align: middle;
        border-bottom: 1px solid #f3f4f6;
        font-size: 0.875rem;
        color: #374151;
    }
    .student-name {
        font-weight: 600;
        color: #111827;
    }
    .student-id {
        color: #2563eb;
        font-weight: 500;
        text-decoration: none;
    }
    .badge-gender-nam {
        background-color: #dbeafe;
        color: #1e40af;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .badge-gender-nu {
        background-color: #fce7f3;
        color: #9d174d;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .badge-status {
        background-color: #dcfce7;
        color: #166534;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .action-btn {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-edit-icon { background: #fef3c7; color: #d97706; }
    .btn-delete-icon { background: #fee2e2; color: #dc2626; }
    .btn-detail-icon { background: #e0f2fe; color: #0284c7; }
    
    .btn-edit-icon:hover { background: #fde68a; }
    .btn-delete-icon:hover { background: #fecaca; }
    .btn-detail-icon:hover { background: #bae6fd; }

    .form-control, .form-select {
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        padding: 10px 15px;
    }
    .form-control:focus, .form-select:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }
</style>

<div class="container-fluid px-4">
    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-4">
        <h3 class="fw-bold text-dark mb-0">
            <i class="fas fa-user-graduate text-primary me-2"></i>Danh Sách Sinh Viên
        </h3>
        <button class="btn btn-primary px-4 py-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalThem" style="border-radius: 8px;">
            <i class="fas fa-plus me-2"></i>Thêm sinh viên
        </button>
    </div>

    {{-- Success/Error Modals triggered by JavaScript --}}
    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                showNotificationModal('success', 'Thành công!', '{{ session('success') }}');
            });
        </script>
    @endif
    @if(session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                showNotificationModal('error', 'Lỗi!', '{{ session('error') }}');
            });
        </script>
    @endif
    @if($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                showNotificationModal('error', 'Lỗi xác thực!', '{!! implode("<br>", $errors->all()) !!}');
            });
        </script>
    @endif

    {{-- FILTER BAR --}}
    <div class="filter-bar">
        <form method="GET" id="filterForm" class="row g-3">
            <div class="col-md-3">
                <select name="lop" class="form-select" onchange="document.getElementById('filterForm').submit()">
                    <option value="">-- Tất cả Lớp --</option>
                    @foreach($allLops as $l)
                        <option value="{{ $l->MaLop }}" {{ request('lop') == $l->MaLop ? 'selected' : '' }}>{{ $l->TenLop }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="trangthai" class="form-select" onchange="document.getElementById('filterForm').submit()">
                    <option value="">-- Tất cả Trạng thái --</option>
                    <option value="Đang học" {{ request('trangthai') == 'Đang học' ? 'selected' : '' }}>Đang học</option>
                    <option value="Bảo lưu" {{ request('trangthai') == 'Bảo lưu' ? 'selected' : '' }}>Bảo lưu</option>
                    <option value="Tốt nghiệp" {{ request('trangthai') == 'Tốt nghiệp' ? 'selected' : '' }}>Tốt nghiệp</option>
                </select>
            </div>
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search"></i></span>
                    <input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="Tìm tên SV / Mã SV..." class="form-control border-start-0 ps-0">
                </div>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-primary fw-semibold" style="border-radius: 8px; flex: 1;"><i class="fas fa-filter me-2"></i>Lọc</button>
                <a href="{{ route('admin.sinhvien.index') }}" class="btn btn-light fw-semibold text-muted" style="border-radius: 8px; border: 1px solid #e5e7eb; flex: 1;">Đặt lại</a>
            </div>
        </form>
        
        <div class="row g-2 mt-2 pt-3 border-top">
            <div class="col-auto">
                <!-- Export Dropdown -->
                <div class="btn-group">
                    <button type="button" class="btn btn-success fw-semibold px-4 dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" style="border-radius: 8px;">
                        <i class="fas fa-file-excel me-2"></i>Export Excel
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="{{ route('admin.sinhvien.export') }}">
                                <i class="fas fa-users me-2"></i>Tất cả sinh viên
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li class="dropdown-header">Xuất theo lớp</li>
                        @foreach($lops as $lop)
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.sinhvien.export-by-class', $lop->MaLop) }}">
                                    <i class="fas fa-user-graduate me-2"></i>{{ $lop->TenLop }}
                                    <span class="badge bg-secondary ms-2">{{ $lop->sinhviens_count }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <div class="col-auto">
                <button class="btn btn-primary fw-semibold px-4" onclick="document.getElementById('importForm').classList.toggle('d-none')" style="border-radius: 8px;">
                    <i class="fas fa-file-upload me-2"></i>Import Excel
                </button>
            </div>
            <div class="col-12">
                <form id="importForm" action="{{ route('admin.sinhvien.import') }}" method="POST" enctype="multipart/form-data" class="d-none">
                    @csrf
                    <div class="d-flex gap-2 align-items-center">
                        <input type="file" name="excel_file" class="form-control" required accept=".xlsx,.csv" style="max-width: 300px; border-radius: 8px;">
                        <button class="btn btn-primary fw-semibold px-4" style="border-radius: 8px;">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ACCORDION LIST --}}
    <div class="accordion" id="lopAccordion">
        @forelse($lops as $lop)
        <div class="accordion-item">
            <h2 class="accordion-header" id="heading{{ $lop->MaLop }}">
                <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" 
                        data-bs-target="#collapse{{ $lop->MaLop }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}">
                    <div class="d-flex align-items-center gap-3 w-100">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-users text-primary"></i>
                            <span class="fw-bold text-dark" style="font-size: 1.1rem;">{{ $lop->TenLop }}</span>
                        </div>
                        <span class="badge-count">{{ $lop->sinhviens_count }} sinh viên</span>
                        @if($lop->nganh)
                            <span class="badge-major">{{ $lop->nganh->TenNganh }}</span>
                        @endif
                    </div>
                </button>
            </h2>
            <div id="collapse{{ $lop->MaLop }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" 
                 data-bs-parent="#lopAccordion">
                <div class="accordion-body p-0">
                    @if($lop->sinhviens->count() > 0)
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead>
                                    <tr>
                                        <th>Mã SV</th>
                                        <th>Họ tên</th>
                                        <th>Giới tính</th>
                                        <th>Ngày sinh</th>
                                        <th>Email</th>
                                        <th>SĐT</th>
                                        <th>Trạng thái</th>
                                        <th class="text-end">Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($lop->sinhviens as $sv)
                                    <tr>
                                        <td><a href="#" class="student-id">{{ $sv->MaSV }}</a></td>
                                        <td><span class="student-name">{{ $sv->TenSV }}</span></td>
                                        <td>
                                            @if($sv->GioiTinh == 'Nam')
                                                <span class="badge-gender-nam">Nam</span>
                                            @else
                                                <span class="badge-gender-nu">Nữ</span>
                                            @endif
                                        </td>
                                        <td>{{ $sv->NgaySinh ? date('d/m/Y', strtotime($sv->NgaySinh)) : '-' }}</td>
                                        <td>{{ $sv->Email ?? '-' }}</td>
                                        <td>{{ $sv->SDT ?? '-' }}</td>
                                        <td>
                                            <span class="badge-status">{{ $sv->TrangThai }}</span>
                                        </td>
                                        <td class="text-end">
                                            <button class="action-btn btn-edit-icon btn-sua" data-id="{{ $sv->MaSV }}" title="Sửa">
                                                <i class="fas fa-pencil-alt"></i>
                                            </button>
                                            
                                            <form id="delete-form-{{ $sv->MaSV }}" action="{{ route('admin.sinhvien.destroy', $sv->MaSV) }}" method="POST" class="d-inline-block">
                                                @csrf @method('DELETE')
                                                <button type="button" class="action-btn btn-delete-icon" onclick="confirmDelete('{{ $sv->MaSV }}', '{{ $sv->TenSV }}')" title="Xóa">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>

                                            <button class="action-btn btn-detail-icon btn-detai" data-id="{{ $sv->MaSV }}" title="Đề tài & Điểm">
                                                <i class="fas fa-folder-open"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-4 text-center text-muted">
                            <i class="fas fa-inbox fa-2x mb-2 opacity-50"></i>
                            <p class="mb-0">Chưa có sinh viên nào trong lớp này.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @empty
            <div class="text-center py-5">
                <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="100" class="mb-3 opacity-50">
                <h5 class="text-muted">Không tìm thấy dữ liệu phù hợp</h5>
            </div>
        @endforelse
    </div>

    <div class="mt-4 d-flex justify-content-center">
        {{ $lops->links('pagination::bootstrap-5') }}
    </div>
</div>

{{-- ========== MODAL THÊM SINH VIÊN ========== --}}
<div class="modal fade" id="modalThem" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header bg-primary text-white border-0" style="border-radius: 16px 16px 0 0;">
                <h5 class="modal-title fw-bold"><i class="fas fa-user-plus me-2"></i>Thêm Sinh Viên Mới</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.sinhvien.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Tên sinh viên <span class="text-danger">*</span></label>
                            <input type="text" name="TenSV" class="form-control" required minlength="3" maxlength="200">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Giới tính <span class="text-danger">*</span></label>
                            <select name="GioiTinh" class="form-select" required>
                                <option value="Nam">Nam</option>
                                <option value="Nữ">Nữ</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Ngày sinh <span class="text-danger">*</span></label>
                            <input type="date" name="NgaySinh" class="form-control" required>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" name="Email" class="form-control" required pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}" title="Email chỉ được chứa chữ, số và ký tự @">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Số điện thoại <span class="text-danger">*</span></label>
                            <input type="text" name="SDT" class="form-control" required pattern="[0-9]{10}" maxlength="10" title="Số điện thoại phải có đúng 10 chữ số" placeholder="Nhập 10 số">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Mã CCCD <span class="text-danger">*</span></label>
                            <input type="text" name="MaCCCD" class="form-control" required pattern="[0-9]{12}" maxlength="12" title="CCCD phải có đúng 12 chữ số" placeholder="Nhập 12 số">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Lớp <span class="text-danger">*</span></label>
                            <input list="listLop" name="MaLop" class="form-control" required minlength="2" placeholder="Chọn hoặc nhập lớp mới">
                            <datalist id="listLop">
                                @foreach($allLops as $lop)
                                    <option value="{{ $lop->TenLop }}">
                                @endforeach
                            </datalist>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Ngành <span class="text-danger">*</span></label>
                            <input list="listNganh" name="MaNganh" class="form-control" required placeholder="Chọn hoặc nhập ngành mới">
                            <datalist id="listNganh">
                                @foreach($nganhs as $nganh)
                                    <option value="{{ $nganh->TenNganh }}">
                                @endforeach
                            </datalist>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Khoa <span class="text-danger">*</span></label>
                            <input list="listKhoa" name="MaKhoa" class="form-control" required placeholder="Chọn hoặc nhập khoa mới">
                            <datalist id="listKhoa">
                                @foreach($khoas as $khoa)
                                    <option value="{{ $khoa->TenKhoa }}">
                                @endforeach
                            </datalist>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Năm học <span class="text-danger">*</span></label>
                            <select name="MaNamHoc" class="form-select" required>
                                @foreach($namhocs as $nh)
                                    <option value="{{ $nh->MaNamHoc }}">{{ $nh->TenNamHoc }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Trạng thái</label>
                            <select name="TrangThai" class="form-select">
                                <option value="Đang học">Đang học</option>
                                <option value="Bảo lưu">Bảo lưu</option>
                                <option value="Tốt nghiệp">Tốt nghiệp</option>
                            </select>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label fw-semibold">Địa chỉ (HKTT)</label>
                            <input type="text" name="HKTT" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary px-4">Lưu sinh viên</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ========== MODAL SỬA SINH VIÊN ========== --}}
<div class="modal fade" id="modalSua" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header bg-warning text-white border-0" style="border-radius: 16px 16px 0 0;">
                <h5 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i>Cập Nhật Sinh Viên</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formSua" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Mã SV</label>
                            <input type="text" id="edit_MaSV" class="form-control bg-light" disabled>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Tên sinh viên <span class="text-danger">*</span></label>
                            <input type="text" name="TenSV" id="edit_TenSV" class="form-control" required minlength="3" maxlength="200">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Giới tính <span class="text-danger">*</span></label>
                            <select name="GioiTinh" id="edit_GioiTinh" class="form-select" required>
                                <option value="Nam">Nam</option>
                                <option value="Nữ">Nữ</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Ngày sinh <span class="text-danger">*</span></label>
                            <input type="date" name="NgaySinh" id="edit_NgaySinh" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" name="Email" id="edit_Email" class="form-control" required pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}" title="Email chỉ được chứa chữ, số và ký tự @">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">SĐT <span class="text-danger">*</span></label>
                            <input type="text" name="SDT" id="edit_SDT" class="form-control" required pattern="[0-9]{10}" maxlength="10" title="Số điện thoại phải có đúng 10 chữ số">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Lớp <span class="text-danger">*</span></label>
                            <input list="listLop" name="MaLop" id="edit_MaLop" class="form-control" required placeholder="Chọn hoặc nhập lớp mới">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Ngành <span class="text-danger">*</span></label>
                            <input list="listNganh" name="MaNganh" id="edit_MaNganh" class="form-control" required placeholder="Chọn hoặc nhập ngành mới">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Khoa <span class="text-danger">*</span></label>
                            <input list="listKhoa" name="MaKhoa" id="edit_MaKhoa" class="form-control" required placeholder="Chọn hoặc nhập khoa mới">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">CCCD <span class="text-danger">*</span></label>
                            <input type="text" name="MaCCCD" id="edit_MaCCCD" class="form-control" required pattern="[0-9]{12}" maxlength="12" title="CCCD phải có đúng 12 chữ số">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Năm học</label>
                            <select name="MaNamHoc" id="edit_MaNamHoc" class="form-select">
                                @foreach($namhocs as $nh)
                                    <option value="{{ $nh->MaNamHoc }}">{{ $nh->TenNamHoc }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Trạng thái</label>
                            <input type="text" name="TrangThai" id="edit_TrangThai" class="form-control">
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label fw-semibold">Địa chỉ (HKTT)</label>
                            <input type="text" name="HKTT" id="edit_HKTT" class="form-control">
                        </div>
                        
                        {{-- Hidden fields for extra data --}}
                        <input type="hidden" name="TonGiao" id="edit_TonGiao">
                        <input type="hidden" name="NoiSinh" id="edit_NoiSinh">
                        <input type="hidden" name="DanToc" id="edit_DanToc">
                        <input type="hidden" name="BacDaoTao" id="edit_BacDaoTao">
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-warning px-4 text-white">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ========== MODAL ĐỀ TÀI ========== --}}
<div class="modal fade" id="modalDeTai" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header bg-info text-white border-0" style="border-radius: 16px 16px 0 0;">
                <h5 class="modal-title fw-bold"><i class="fas fa-folder-open me-2"></i>Chi Tiết Đề Tài & Kết Quả</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="detaiContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-info" role="status">
                        <span class="visually-hidden">Đang tải...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-body text-center p-5">
                <div class="mb-4">
                    <div class="warning-icon" style="width: 100px; height: 100px; margin: 0 auto; background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; animation: pulse 2s infinite;">
                        <i class="fas fa-exclamation-triangle" style="font-size: 50px; color: #dc2626;"></i>
                    </div>
                </div>
                <h4 class="fw-bold mb-3" style="color: #1f2937;">Bạn có chắc chắn muốn xóa?</h4>
                <p class="text-muted mb-2" id="deleteMessage" style="font-size: 1.1rem;">Xóa sinh viên này?</p>
                <div class="alert alert-warning border-0 mt-3 mb-4" style="background: #fef3c7; color: #92400e; border-radius: 12px;">
                    <i class="fas fa-info-circle me-2"></i>
                    <small><strong>Lưu ý:</strong> Thao tác này sẽ xóa cả tài khoản liên quan và không thể hoàn tác!</small>
                </div>
                <div class="d-flex gap-3 justify-content-center">
                    <button type="button" class="btn btn-light px-5 py-2 fw-semibold" data-bs-dismiss="modal" style="border-radius: 12px; border: 2px solid #e5e7eb;">
                        <i class="fas fa-times me-2"></i>Hủy
                    </button>
                    <button type="button" class="btn btn-danger px-5 py-2 fw-semibold" id="confirmDeleteBtn" style="border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(220, 38, 38, 0.3);">
                        <i class="fas fa-trash-alt me-2"></i>Xác nhận xóa
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes pulse {
    0%, 100% {
        transform: scale(1);
        opacity: 1;
    }
    50% {
        transform: scale(1.05);
        opacity: 0.9;
    }
}
</style>

{{-- Notification Modal (Success/Error) --}}
<div class="modal fade" id="notificationModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-body text-center p-5">
                <div class="mb-4" id="notificationIcon"></div>
                <h4 class="fw-bold mb-3" id="notificationTitle">Thành công!</h4>
                <p class="text-muted mb-4" id="notificationMessage"></p>
                <div class="d-grid gap-2" id="notificationButtons"></div>
            </div>
        </div>
    </div>
</div>

<style>
.success-icon {
    width: 80px; height: 80px; margin: 0 auto;
    background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    border-radius: 50%; display: flex; align-items: center; justify-content: center;
    animation: scaleIn 0.5s ease-out;
}
.success-icon i { font-size: 40px; color: #28a745; }
.error-icon {
    width: 80px; height: 80px; margin: 0 auto;
    background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
    border-radius: 50%; display: flex; align-items: center; justify-content: center;
    animation: shake 0.5s ease-out;
}
.error-icon i { font-size: 40px; color: #dc3545; }
@keyframes scaleIn {
    0% { transform: scale(0); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-10px); }
    75% { transform: translateX(10px); }
}
</style>

<script>
function showNotificationModal(type, title, message) {
    const modal = new bootstrap.Modal(document.getElementById('notificationModal'));
    document.getElementById('notificationTitle').textContent = title;
    document.getElementById('notificationMessage').innerHTML = message;
    
    if (type === 'success') {
        document.getElementById('notificationIcon').innerHTML = '<div class="success-icon"><i class="fas fa-check"></i></div>';
        document.getElementById('notificationButtons').innerHTML = `
            <button type="button" class="btn btn-success btn-lg" onclick="window.location.reload()" style="border-radius: 10px;">
                Quay lại danh sách
            </button>
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" style="border-radius: 10px;">
                + Thêm mới
            </button>
        `;
        setTimeout(() => { modal.hide(); window.location.reload(); }, 2000);
    } else {
        document.getElementById('notificationIcon').innerHTML = '<div class="error-icon"><i class="fas fa-exclamation-triangle"></i></div>';
        document.getElementById('notificationButtons').innerHTML = `
            <button type="button" class="btn btn-danger btn-lg" data-bs-dismiss="modal" style="border-radius: 10px;">OK</button>
        `;
    }
    modal.show();
}

function confirmDelete(id, name) {
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    document.getElementById('deleteMessage').textContent = `Xóa sinh viên ${name}?`;
    
    document.getElementById('confirmDeleteBtn').onclick = function() {
        document.getElementById('delete-form-' + id).submit();
    };
    
    modal.show();
}
</script>

@endsection

@section('scripts')
<script>
// ==== NÚT SỬA ====
document.querySelectorAll('.btn-sua').forEach(btn => {
    btn.addEventListener('click', function() {
        const maSV = this.dataset.id;
        
        // Load dữ liệu qua AJAX
        fetch(`/admin/sinhvien/${maSV}/edit`)
            .then(res => res.json())
            .then(sv => {
                document.getElementById('edit_MaSV').value = sv.MaSV;
                document.getElementById('edit_TenSV').value = sv.TenSV;
                document.getElementById('edit_GioiTinh').value = sv.GioiTinh;
                document.getElementById('edit_NgaySinh').value = sv.NgaySinh;
                document.getElementById('edit_Email').value = sv.Email;
                document.getElementById('edit_SDT').value = sv.SDT;
                document.getElementById('edit_TrangThai').value = sv.TrangThai;
                document.getElementById('edit_MaCCCD').value = sv.MaCCCD;
                document.getElementById('edit_TonGiao').value = sv.TonGiao;
                document.getElementById('edit_NoiSinh').value = sv.NoiSinh;
                document.getElementById('edit_HKTT').value = sv.HKTT;
                document.getElementById('edit_DanToc').value = sv.DanToc;
                document.getElementById('edit_BacDaoTao').value = sv.BacDaoTao;
                
                // Select dropdowns (now inputs)
                if(sv.MaKhoa) document.getElementById('edit_MaKhoa').value = sv.khoa ? sv.khoa.TenKhoa : sv.MaKhoa;
                if(sv.MaNganh) document.getElementById('edit_MaNganh').value = sv.nganh ? sv.nganh.TenNganh : sv.MaNganh;
                if(sv.MaLop) document.getElementById('edit_MaLop').value = sv.lop ? sv.lop.TenLop : sv.MaLop;
                if(sv.MaNamHoc) document.getElementById('edit_MaNamHoc').value = sv.MaNamHoc;
                
                // Set action cho form
                document.getElementById('formSua').action = `/admin/sinhvien/${maSV}`;
                
                // Hiển thị modal
                new bootstrap.Modal(document.getElementById('modalSua')).show();
            })
            .catch(err => alert('Lỗi tải dữ liệu: ' + err));
    });
});

// ==== NÚT ĐỀ TÀI ====
document.querySelectorAll('.btn-detai').forEach(btn => {
    btn.addEventListener('click', function() {
        const maSV = this.dataset.id;
        
        // Hiển thị modal
        const modal = new bootstrap.Modal(document.getElementById('modalDeTai'));
        modal.show();
        
        // Load dữ liệu qua AJAX
        fetch(`/admin/sinhvien/${maSV}/detai`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            let html = `<div class="d-flex align-items-center mb-4">
                            <div class="avatar-circle bg-primary text-white d-flex align-items-center justify-content-center rounded-circle me-3" style="width: 50px; height: 50px; font-size: 20px; font-weight: bold;">
                                ${data.sv.TenSV.charAt(0)}
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold">${data.sv.TenSV}</h5>
                                <span class="text-muted small">${data.sv.MaSV}</span>
                            </div>
                        </div>`;
            
            if (data.deTai) {
                html += `
                    <div class="card mb-4 border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                        <div class="card-header bg-primary text-white py-3">
                            <h6 class="mb-0 fw-bold"><i class="fas fa-book me-2"></i>${data.deTai.TenDeTai}</h6>
                        </div>
                        <div class="card-body bg-light">
                            <div class="row">
                                <div class="col-md-6 mb-2"><strong>Lĩnh vực:</strong> ${data.deTai.LinhVuc || '-'}</div>
                                <div class="col-md-6 mb-2"><strong>Năm học:</strong> ${ (data.deTai.namHoc || data.deTai.nam_hoc) ? (data.deTai.namHoc || data.deTai.nam_hoc).TenNamHoc : '-' }</div>
                                <div class="col-md-6 mb-2"><strong>Người ra đề:</strong> ${ (data.deTai.giangVien || data.deTai.giang_vien) ? (data.deTai.giangVien || data.deTai.giang_vien).TenGV : ((data.deTai.canBo || data.deTai.can_bo) ? (data.deTai.canBo || data.deTai.can_bo).TenCB : 'Chưa có') }</div>
                                <div class="col-md-6 mb-2"><strong>Trạng thái:</strong> 
                                    <span class="badge bg-${data.deTai.TrangThai == 'Hoàn thành' ? 'success' : (data.deTai.TrangThai == 'Đang thực hiện' ? 'warning' : 'secondary')}">
                                        ${data.deTai.TrangThai}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <h6 class="text-primary fw-bold mb-3"><i class="fas fa-tasks me-2"></i>Tiến độ thực hiện</h6>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Nội dung</th>
                                    <th>Deadline</th>
                                    <th>Trạng thái</th>
                                    <th>File</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${data.tiendos && data.tiendos.length > 0 ? data.tiendos.map(t => `
                                    <tr>
                                        <td>${t.NoiDung}</td>
                                        <td>${t.Deadline ? new Date(t.Deadline).toLocaleDateString('vi-VN') : '-'}</td>
                                        <td>
                                            <span class="badge bg-${t.NgayNop ? 'success' : 'secondary'}">
                                                ${t.NgayNop ? 'Đã nộp' : 'Chưa nộp'}
                                            </span>
                                        </td>
                                        <td>
                                            ${t.LinkFile ? `<a href="/${t.LinkFile}" target="_blank">Xem file</a>` : '-'}
                                        </td>
                                    </tr>
                                `).join('') : '<tr><td colspan="4" class="text-center text-muted">Chưa có mốc tiến độ nào.</td></tr>'}
                            </tbody>
                        </table>
                    </div>

                    <h6 class="text-primary fw-bold mb-3"><i class="fas fa-file-alt me-2"></i>Báo cáo đã nộp</h6>
                `;
                
                if (data.baoCaos.length > 0) {
                    html += `
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tên file</th>
                                        <th>Ngày nộp</th>
                                        <th>Lần nộp</th>
                                        <th>Nhận xét</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;
                    data.baoCaos.forEach(bc => {
                        html += `
                            <tr>
                                <td><a href="${bc.LinkFile}" target="_blank" class="text-decoration-none"><i class="fas fa-paperclip me-1"></i>${bc.TenFile}</a></td>
                                <td>${bc.NgayNop}</td>
                                <td><span class="badge bg-secondary rounded-pill">${bc.LanNop}</span></td>
                                <td>${bc.NhanXet || 'Chưa có nhận xét'}</td>
                            </tr>
                        `;
                    });
                    html += `</tbody></table></div>`;
                } else {
                    html += `<div class="alert alert-light border text-center text-muted mb-4">Chưa có báo cáo nào.</div>`;
                }
                
                html += `<h6 class="text-primary fw-bold mb-3"><i class="fas fa-star me-2"></i>Kết quả chấm điểm</h6>`;
                
                if (data.diems.length > 0) {
                    html += `
                        <div class="table-responsive">
                            <table class="table table-striped align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Giảng viên</th>
                                        <th>Điểm</th>
                                        <th>Nhận xét</th>
                                        <th>Ngày chấm</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;
                    data.diems.forEach(d => {
                        html += `
                            <tr>
                                <td>${d.giangvien ? d.giangvien.TenGV : '-'}</td>
                                <td class="fw-bold text-danger">${d.Diem}</td>
                                <td>${d.NhanXet || 'Chưa có nhận xét'}</td>
                                <td>${d.NgayCham}</td>
                            </tr>
                        `;
                    });
                    html += `</tbody></table></div>`;
                } else {
                    html += `<div class="alert alert-light border text-center text-muted">Chưa có điểm chấm.</div>`;
                }
            } else {
                html += `<div class="alert alert-warning text-center"><i class="fas fa-exclamation-triangle me-2"></i>Sinh viên này chưa đăng ký đề tài.</div>`;
            }
            
            document.getElementById('detaiContent').innerHTML = html;
        })
        .catch(error => {
            document.getElementById('detaiContent').innerHTML = 
                `<div class="alert alert-danger">Lỗi khi tải dữ liệu: ${error.message}</div>`;
        });
    });
});
</script>
@endsection