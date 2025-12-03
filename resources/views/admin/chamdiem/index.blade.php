@extends('layouts.admin')

@section('title', 'Quản lý chấm điểm')

@section('content')
<style>
    body {
        background: #f5f7fa !important;
    }

    .grading-container {
        padding: 2rem;
        max-width: 1400px;
        margin: 0 auto;
    }

    .page-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .header-icon {
        width: 48px;
        height: 48px;
        background: #e0e7ff;
        color: #4338ca;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .page-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1f2937;
        margin: 0;
    }

    .grading-table-container {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
    }

    .grading-table {
        width: 100%;
        border-collapse: collapse;
    }

    .grading-table th {
        background: #fff;
        padding: 1.25rem 1rem;
        text-align: left;
        font-size: 0.75rem;
        font-weight: 700;
        color: #6b7280;
        text-transform: uppercase;
        border-bottom: 1px solid #f3f4f6;
        white-space: nowrap;
    }

    .grading-table td {
        padding: 1.25rem 1rem;
        border-bottom: 1px solid #f3f4f6;
        font-size: 0.875rem;
        color: #374151;
        vertical-align: middle;
    }

    .grading-table tbody tr:hover {
        background: #f9fafb;
    }

    .grading-table tbody tr:last-child td {
        border-bottom: none;
    }

    .score-bold {
        font-weight: 700;
        color: #1f2937;
    }

    .score-final {
        font-weight: 700;
        color: #4f46e5;
    }

    .text-muted-custom {
        color: #9ca3af;
    }

    .badge-status {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .badge-approved {
        background: #d1fae5;
        color: #065f46;
    }

    .badge-pending {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-rejected {
        background: #fee2e2;
        color: #991b1b;
    }

    .badge-none {
        background: #f3f4f6;
        color: #6b7280;
    }

    /* Custom select for status inside table */
    .status-select {
        border: none;
        background: transparent;
        font-size: 0.75rem;
        font-weight: 600;
        cursor: pointer;
        padding: 0;
        width: 100%;
    }
    
    .status-select:focus {
        outline: none;
    }

    .pagination-container {
        margin-top: 1.5rem;
        display: flex;
        justify-content: center;
    }
</style>

<div class="grading-container">
    <div class="page-header">
        <div class="header-icon">
            <i class="fas fa-star"></i>
        </div>
        <h1 class="page-title">Danh sách chấm điểm</h1>
    </div>

    {{-- Filter Trigger Button --}}
    <div class="mb-4 d-flex justify-content-end">
        <button class="btn btn-white shadow-sm border d-flex align-items-center gap-2 px-3 py-2" 
                type="button" data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas" aria-controls="filterOffcanvas"
                style="border-radius: 10px; font-weight: 500; color: #374151;">
            <i class="bi bi-funnel-fill text-primary"></i> Bộ lọc tìm kiếm
            @if($selectedLop !== 'all')
                <span class="badge bg-primary rounded-pill ms-1">1</span>
            @endif
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-4" style="border-radius: 12px;">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        </div>
    @endif

    <div class="grading-table-container">
        <table class="grading-table">
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th style="width: 25%;">ĐỀ TÀI</th>
                    <th style="width: 15%;">SINH VIÊN</th>
                    <th style="width: 10%;">LỚP</th>
                    <th style="width: 15%;">GVHD</th>
                    <th class="text-center">ĐIỂM<br>GVHD</th>
                    <th style="width: 15%;">GVPB</th>
                    <th class="text-center">ĐIỂM<br>GVPB</th>
                    <th class="text-center">ĐIỂM<br>TB</th>
                    <th class="text-center">ĐIỂM<br>CUỐI</th>
                    <th style="width: 150px;">TRẠNG THÁI<br>CHUNG</th>
                    <th class="text-center" style="width: 100px;">HÀNH ĐỘNG</th>
                </tr>
            </thead>
            <tbody>
            @foreach($cds as $deTai)
                @php
                    $gvhd = $deTai->phancongs->first(fn($pc) => str_contains(strtolower($pc->VaiTro),'hướng dẫn'));
                    $gvpb = $deTai->phancongs->first(fn($pc) => str_contains(strtolower($pc->VaiTro),'phản biện'));
                @endphp

                @foreach($deTai->sinhViens as $index => $sv)
                    @php
                        $allCham = $deTai->chamdiems->where('MaSV', $sv->MaSV);
                        $cham = $allCham->first();
                        
                        $diemGVHD = $gvhd ? optional($allCham->firstWhere('MaGV',$gvhd->MaGV))->Diem : null;
                        $diemGVPB = $gvpb ? optional($allCham->firstWhere('MaGV',$gvpb->MaGV))->Diem : null;
                        
                        $diemTB = (is_numeric($diemGVHD) && is_numeric($diemGVPB))
                            ? ($diemGVHD + $diemGVPB) / 2
                            : ($diemGVHD ?? $diemGVPB ?? null);
                            
                        $diemCuoi = $cham?->DiemCuoi ?? null;
                        $maCham = $cham?->MaCham;
                    @endphp

                    <tr>
                        <td class="text-muted-custom">{{ $loop->parent->iteration }}.{{ $index + 1 }}</td>
                        <td>
                            <div style="font-weight: 600; color: #111827;">{{ $deTai->TenDeTai }}</div>
                        </td>
                        <td>{{ $sv->TenSV }}</td>
                        <td>{{ $sv->lop->TenLop ?? 'Chưa cập nhật' }}</td>
                        
                        <td class="text-muted-custom">{{ $gvhd ? $gvhd->giangVien->TenGV : 'Chưa cập nhật' }}</td>
                        <td class="text-center score-bold">{{ is_numeric($diemGVHD) ? number_format($diemGVHD, 2) : 'Chưa cập nhật' }}</td>
                        
                        <td class="text-muted-custom">{{ $gvpb ? $gvpb->giangVien->TenGV : 'Chưa cập nhật' }}</td>
                        <td class="text-center score-bold">{{ is_numeric($diemGVPB) ? number_format($diemGVPB, 2) : 'Chưa cập nhật' }}</td>
                        
                        <td class="text-center score-bold">{{ $diemTB !== null ? number_format($diemTB, 2) : 'Chưa cập nhật' }}</td>
                        <td class="text-center score-final">{{ $diemCuoi !== null ? number_format($diemCuoi, 2) : 'Chưa cập nhật' }}</td>

                        <td>
                            @if($cham)
                                <form action="{{ route('admin.chamdiem.updateStatus', $maCham) }}" method="POST" id="form-status-{{ $maCham }}">
                                    @csrf
                                    @php
                                        $statusClass = match($cham->TrangThai) {
                                            'Đã duyệt' => 'badge-approved',
                                            'Chờ duyệt' => 'badge-pending',
                                            'Từ chối' => 'badge-rejected',
                                            default => 'badge-none'
                                        };
                                        $icon = match($cham->TrangThai) {
                                            'Đã duyệt' => 'fa-check-circle',
                                            'Chờ duyệt' => 'fa-hourglass-half',
                                            'Từ chối' => 'fa-times-circle',
                                            default => 'fa-circle'
                                        };
                                    @endphp
                                    
                                    <div class="badge-status {{ $statusClass }}" style="cursor: pointer; position: relative;">
                                        <i class="fas {{ $icon }}"></i>
                                        <select name="TrangThai" class="status-select" 
                                                onchange="document.getElementById('form-status-{{ $maCham }}').submit()"
                                                style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer;">
                                            <option value="Chưa xác nhận" {{ $cham->TrangThai=='Chưa xác nhận' ? 'selected' : '' }}>Chưa xác nhận</option>
                                            <option value="Chờ duyệt" {{ $cham->TrangThai=='Chờ duyệt' ? 'selected' : '' }}>Chờ duyệt</option>
                                            <option value="Đã duyệt" {{ $cham->TrangThai=='Đã duyệt' ? 'selected' : '' }}>Đã duyệt</option>
                                            <option value="Từ chối" {{ $cham->TrangThai=='Từ chối' ? 'selected' : '' }}>Từ chối</option>
                                        </select>
                                        <span>{{ $cham->TrangThai }}</span>
                                    </div>
                                </form>
                            @else
                                <span class="badge-status badge-none">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($cham)
                                <button type="button" class="btn btn-light text-danger btn-sm shadow-sm border-0" 
                                        onclick="confirmDelete('{{ $maCham }}', '{{ $sv->TenSV }}')"
                                        title="Xóa điểm">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="pagination-container">
        {{ $cds->links('pagination::bootstrap-4') }}
    </div>
</div>

{{-- Offcanvas Filter --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="filterOffcanvas" aria-labelledby="filterOffcanvasLabel" style="width: 350px;">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-bold" id="filterOffcanvasLabel">
            <i class="bi bi-funnel me-2"></i>Bộ lọc tìm kiếm
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body bg-light">
        <form action="{{ route('admin.chamdiem.index') }}" method="GET">
            <div class="mb-4">
                <label class="form-label fw-bold text-uppercase text-muted small mb-3">Lớp học</label>
                <div class="d-flex flex-column gap-2">
                    {{-- Option: Tất cả --}}
                    <label class="card shadow-sm border-0 cursor-pointer hover-shadow transition-all" style="cursor: pointer;">
                        <div class="card-body p-3 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                    <i class="bi bi-layers text-primary"></i>
                                </div>
                                <span class="fw-medium">Tất cả các lớp</span>
                            </div>
                            <input class="form-check-input" type="radio" name="lop_id" value="all" 
                                   {{ $selectedLop == 'all' ? 'checked' : '' }} style="transform: scale(1.2);" onchange="this.form.submit()">
                        </div>
                    </label>

                    {{-- Options: Các lớp --}}
                    @foreach($lops as $lop)
                        <label class="card shadow-sm border-0 cursor-pointer hover-shadow transition-all" style="cursor: pointer;">
                            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-white border d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                        <span class="small fw-bold text-muted">{{ substr($lop->TenLop, 0, 2) }}</span>
                                    </div>
                                    <span class="fw-medium">{{ $lop->TenLop }}</span>
                                </div>
                                <input class="form-check-input" type="radio" name="lop_id" value="{{ $lop->MaLop }}" 
                                       {{ $selectedLop == $lop->MaLop ? 'checked' : '' }} style="transform: scale(1.2);" onchange="this.form.submit()">
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

        </form>
    </div>
</div>

<style>
    .hover-shadow:hover {
        transform: translateY(-2px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
    }
    .transition-all {
        transition: all 0.2s ease-in-out;
    }
</style>
{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-body p-4 text-center">
                <div class="mb-3">
                    <div class="rounded-circle bg-danger bg-opacity-10 d-inline-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                        <i class="bi bi-trash-fill text-danger" style="font-size: 32px;"></i>
                    </div>
                </div>
                <h5 class="fw-bold mb-2">Xác nhận xóa điểm?</h5>
                <p class="text-muted mb-4">
                    Bạn có chắc chắn muốn xóa điểm của sinh viên <b id="deleteSvName" class="text-dark"></b> không?<br>
                    Hành động này không thể hoàn tác.
                </p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light px-4 fw-bold rounded-pill" data-bs-dismiss="modal">Hủy bỏ</button>
                    <form id="deleteForm" method="POST" action="">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger px-4 fw-bold rounded-pill">Xóa ngay</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmDelete(id, name) {
        document.getElementById('deleteSvName').innerText = name;
        document.getElementById('deleteForm').action = "/admin/chamdiem/" + id;
        var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        deleteModal.show();
    }
</script>

@endsection
