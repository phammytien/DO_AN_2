@extends('layouts.admin')

@section('content')
<style>
    body {
        background-color: #f5f7fa;
    }
    .page-header {
        background: white;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.04);
    }
    .filter-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.04);
    }
    .report-table {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0,0,0,0.04);
    }
    .table thead th {
        background-color: #f8f9fa;
        color: #6b7280;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        border: none;
        padding: 16px;
        white-space: nowrap;
    }
    .table tbody td {
        padding: 16px;
        vertical-align: middle;
        border-bottom: 1px solid #f3f4f6;
        font-size: 0.875rem;
        color: #374151;
    }
    .table tbody tr:last-child td {
        border-bottom: none;
    }
    .table tbody tr:hover {
        background-color: #f9fafb;
    }
    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        display: inline-block;
    }
    .status-approved {
        background-color: #d1fae5;
        color: #065f46;
    }
    .status-pending {
        background-color: #fef3c7;
        color: #92400e;
    }
    .status-rejected {
        background-color: #fee2e2;
        color: #991b1b;
    }
    .status-request {
        background-color: #dbeafe;
        color: #1e40af;
    }
    .btn-view {
        background: #4f46e5;
        color: white;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 600;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
    }
    .btn-view:hover {
        background: #4338ca;
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(79, 70, 229, 0.3);
    }
    .form-select, .form-control {
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        padding: 10px 14px;
        font-size: 0.875rem;
    }
    .form-select:focus, .form-control:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }
    .btn-filter {
        background: #4f46e5;
        color: white;
        padding: 10px 24px;
        border-radius: 8px;
        font-weight: 600;
        border: none;
        transition: all 0.2s;
    }
    .btn-filter:hover {
        background: #4338ca;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
    }
    .file-link {
        color: #4f46e5;
        text-decoration: none;
        font-weight: 500;
        display: inline-block;
        max-width: 200px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .file-link:hover {
        text-decoration: underline;
    }
</style>

<div class="container-fluid px-4 py-4">
    {{-- HEADER --}}
    <div class="page-header">
        <h4 class="mb-0 fw-bold text-dark">
            <i class="fas fa-file-alt text-primary me-2"></i>
            Danh sách báo cáo / tiến độ
        </h4>
    </div>

    {{-- ALERTS --}}
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4" style="border-radius: 12px;">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        </div>
    @endif

    {{-- FILTER SECTION --}}
    <div class="filter-card">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-semibold small text-muted">Đề tài</label>
                <select name="detai" class="form-select">
                    <option value="">Lọc theo đề tài...</option>
                    @foreach($detais as $dt)
                        <option value="{{ $dt->MaDeTai }}" {{ request('detai') == $dt->MaDeTai ? 'selected' : '' }}>
                            {{ Str::limit($dt->TenDeTai, 50) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold small text-muted">Sinh viên</label>
                <select name="sinhvien" class="form-select">
                    <option value="">Lọc theo sinh viên...</option>
                    @foreach($sinhviens as $sv)
                        <option value="{{ $sv->MaSV }}" {{ request('sinhvien') == $sv->MaSV ? 'selected' : '' }}>
                            {{ $sv->TenSV }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold small text-muted">Trạng thái</label>
                <select name="trangthai" class="form-select">
                    <option value="">Tất cả</option>
                    <option value="Chờ duyệt" {{ request('trangthai') == 'Chờ duyệt' ? 'selected' : '' }}>Chờ duyệt</option>
                    <option value="Đã duyệt" {{ request('trangthai') == 'Đã duyệt' ? 'selected' : '' }}>Đã duyệt</option>
                    <option value="Yêu cầu chỉnh sửa" {{ request('trangthai') == 'Yêu cầu chỉnh sửa' ? 'selected' : '' }}>Yêu cầu chỉnh sửa</option>
                    <option value="Xin nộp bổ sung" {{ request('trangthai') == 'Xin nộp bổ sung' ? 'selected' : '' }}>Xin nộp bổ sung</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-filter w-100">
                    <i class="fas fa-filter me-2"></i>Lọc
                </button>
            </div>
        </form>
    </div>

    {{-- TABLE --}}
    <div class="report-table">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Đề tài</th>
                        <th>Sinh viên</th>
                        <th>Tên file</th>
                        <th style="width: 140px;">Ngày nộp</th>
                        <th style="width: 80px;" class="text-center">Lần nộp</th>
                        <th style="width: 130px;">Trạng thái</th>
                        <th>Nhận xét</th>
                        <th style="width: 120px;" class="text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($baocaos as $index => $bc)
                    <tr>
                        <td class="fw-semibold text-muted">{{ $baocaos->firstItem() + $index }}</td>
                        
                        {{-- Tên đề tài --}}
                        <td>
                            <div class="fw-semibold text-dark" style="max-width: 250px;">
                                {{ Str::limit($bc->deTai->TenDeTai ?? 'N/A', 60) }}
                            </div>
                        </td>

                        {{-- Tên sinh viên --}}
                        <td>
                            <div class="fw-medium">{{ $bc->sinhVien->TenSV ?? 'Không có' }}</div>
                        </td>

                        {{-- File báo cáo --}}
                        <td>
                            @if($bc->fileBaoCao)
                                <a href="{{ Str::startsWith($bc->fileBaoCao->path, 'storage/') ? asset($bc->fileBaoCao->path) : asset('storage/' . $bc->fileBaoCao->path) }}" 
                                   target="_blank" 
                                   class="file-link"
                                   title="{{ $bc->fileBaoCao->name }}">
                                    {{ $bc->fileBaoCao->name }}
                                </a>
                            @elseif($bc->LinkFile)
                                <a href="{{ Str::startsWith($bc->LinkFile, 'storage/') ? asset($bc->LinkFile) : asset('storage/' . $bc->LinkFile) }}" 
                                   target="_blank" 
                                   class="file-link"
                                   title="{{ $bc->TenFile }}">
                                    {{ $bc->TenFile }}
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        {{-- Ngày nộp --}}
                        <td>
                            <div class="small">
                                {{ \Carbon\Carbon::parse($bc->NgayNop)->format('Y-m-d') }}<br>
                                <span class="text-muted">{{ \Carbon\Carbon::parse($bc->NgayNop)->format('H:i:s') }}</span>
                            </div>
                        </td>

                        {{-- Lần nộp --}}
                        <td class="text-center">
                            <span class="badge bg-secondary rounded-pill">{{ $bc->LanNop }}</span>
                        </td>

                        {{-- Trạng thái --}}
                        <td>
                            @if($bc->TrangThai == 'Đã duyệt')
                                <span class="status-badge status-approved">Đã duyệt</span>
                            @elseif($bc->TrangThai == 'Yêu cầu chỉnh sửa')
                                <span class="status-badge status-pending">Yêu cầu sửa</span>
                            @elseif($bc->TrangThai == 'Xin nộp bổ sung')
                                <span class="status-badge status-request">Xin nộp bù</span>
                            @elseif($bc->TrangThai == 'Được nộp bổ sung')
                                <span class="status-badge status-approved">Được nộp bù</span>
                            @elseif($bc->TrangThai == 'Từ chối nộp bù')
                                <span class="status-badge status-rejected">Từ chối</span>
                            @else
                                <span class="status-badge status-pending">Chờ duyệt</span>
                            @endif
                        </td>

                        {{-- Nhận xét --}}
                        <td>
                            <div style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" 
                                 title="{{ $bc->NhanXet }}">
                                {{ $bc->NhanXet ?? 'Chưa có nhận xét' }}
                            </div>
                        </td>

                        {{-- HÀNH ĐỘNG --}}
                        <td class="text-center">
                            @if($bc->fileBaoCao)
                                <a href="{{ Str::startsWith($bc->fileBaoCao->path, 'storage/') ? asset($bc->fileBaoCao->path) : asset('storage/' . $bc->fileBaoCao->path) }}" 
                                   target="_blank" 
                                   class="btn-view">
                                    <i class="fas fa-eye"></i>
                                    Xem file
                                </a>
                            @elseif($bc->LinkFile)
                                <a href="{{ Str::startsWith($bc->LinkFile, 'storage/') ? asset($bc->LinkFile) : asset('storage/' . $bc->LinkFile) }}" 
                                   target="_blank" 
                                   class="btn-view">
                                    <i class="fas fa-eye"></i>
                                    Xem file
                                </a>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-inbox fa-3x mb-3 opacity-25"></i>
                                <p class="mb-0">Không có báo cáo nào</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        @if($baocaos->hasPages())
            <div class="px-4 py-3 border-top">
                {{ $baocaos->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>

@endsection
