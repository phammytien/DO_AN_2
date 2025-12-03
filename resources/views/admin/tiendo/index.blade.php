@extends('layouts.admin')

@section('content')
<style>
    body {
        background-color: #f5f7fa;
    }
    .page-header {
        background: white;
        border-radius: 12px;
        padding: 20px 24px;
        margin-bottom: 24px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.04);
        border-left: 4px solid #4f46e5;
    }
    .page-header h4 {
        margin: 0;
        color: #1f2937;
        font-size: 1.25rem;
        font-weight: 600;
    }
    .filter-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.04);
    }
    .progress-table {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0,0,0,0.04);
    }
    .table thead th {
        background-color: #f9fafb;
        color: #6b7280;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 16px;
        border-bottom: 2px solid #e5e7eb;
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
    .status-pending {
        background-color: #fef3c7;
        color: #92400e;
    }
    .status-late {
        background-color: #fee2e2;
        color: #991b1b;
    }
    .btn-detail {
        background: #06b6d4;
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
        text-decoration: none;
    }
    .btn-detail:hover {
        background: #0891b2;
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(6, 182, 212, 0.3);
    }
    .form-control, .form-select {
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        padding: 10px 14px;
        font-size: 0.875rem;
    }
    .form-control:focus, .form-select:focus {
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
        width: 100%;
    }
    .btn-filter:hover {
        background: #4338ca;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
    }
</style>

<div class="container-fluid px-4 py-4">
    {{-- HEADER --}}
    <div class="page-header">
        <h4>
            <i class="fas fa-file-alt me-2"></i>
            Danh sách báo cáo / tiến độ
        </h4>
    </div>

    {{-- FILTER SECTION --}}
    <div class="filter-card">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-semibold small text-muted">Tên đề tài</label>
                <input type="text" name="detai" class="form-control" placeholder="Nhập tên đề tài..." value="{{ request('detai') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold small text-muted">Sinh viên</label>
                <input type="text" name="sinhvien" class="form-control" placeholder="Nhập tên sinh viên..." value="{{ request('sinhvien') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold small text-muted">Trạng thái</label>
                <select name="trangthai" class="form-select">
                    <option value="">Tất cả</option>
                    <option value="Chưa nộp" {{ request('trangthai') == 'Chưa nộp' ? 'selected' : '' }}>Chưa nộp</option>
                    <option value="Trễ hạn" {{ request('trangthai') == 'Trễ hạn' ? 'selected' : '' }}>Trễ hạn</option>
                    <option value="Đúng hạn" {{ request('trangthai') == 'Đúng hạn' ? 'selected' : '' }}>Đúng hạn</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-filter">
                    <i class="fas fa-filter me-2"></i>Lọc
                </button>
            </div>
        </form>
    </div>

    {{-- TABLE --}}
    <div class="progress-table">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">ID</th>
                        <th>Tên đề tài</th>
                        <th>Sinh viên</th>
                        <th style="width: 140px;">Ngày nộp</th>
                        <th style="width: 130px;">Trạng thái</th>
                        <th style="width: 120px;" class="text-center">Xem</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tiendos as $index => $t)
                    <tr>
                        <td class="fw-semibold text-muted">{{ $tiendos->firstItem() + $index }}</td>
                        
                        {{-- Tên đề tài --}}
                        <td>
                            <div class="fw-semibold text-dark" style="max-width: 250px;">
                                {{ Str::limit($t->deTai->TenDeTai ?? '—', 60) }}
                            </div>
                        </td>

                        {{-- Sinh viên --}}
                        <td>
                            @if($t->deTai && $t->deTai->sinhViens->count() > 0)
                                @foreach($t->deTai->sinhViens as $sv)
                                    <div class="small">{{ $sv->TenSV }}</div>
                                @endforeach
                            @else
                                <span class="text-muted">Chưa cập nhật</span>
                            @endif
                        </td>

                        {{-- Ngày nộp --}}
                        <td>
                            @if($t->NgayNop)
                                <div class="small">
                                    {{ \Carbon\Carbon::parse($t->NgayNop)->format('d/m/Y H:i') }}
                                </div>
                            @else
                                <span class="text-muted">Chưa cập nhật</span>
                            @endif
                        </td>

                        {{-- Trạng thái --}}
                        <td>
                            @php
                                $status = $t->TrangThaiTuDong;
                                $badgeClass = match($status) {
                                    'Đúng hạn' => 'status-badge bg-success text-white',
                                    'Chưa nộp' => 'status-badge status-pending',
                                    'Trễ hạn' => 'status-badge status-late',
                                    default => 'status-badge bg-secondary text-white'
                                };
                            @endphp
                            <span class="{{ $badgeClass }}">{{ $status }}</span>
                        </td>

                        {{-- HÀNH ĐỘNG --}}
                        <td class="text-center">
                            <a href="{{ route('admin.tiendo.show', $t->MaTienDo) }}" class="btn-detail">
                                <i class="fas fa-eye"></i>
                                Chi tiết
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-inbox fa-3x mb-3 opacity-25"></i>
                                <p class="mb-0">Không có tiến độ nào</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        @if(method_exists($tiendos, 'links') && $tiendos->hasPages())
            <div class="px-4 py-3 border-top">
                {{ $tiendos->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>

@endsection
