@extends('layouts.sinhvien')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid p-0">
    <div class="row g-3">
        {{-- LEFT COLUMN: STUDENT INFO --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom-0 pt-3 ps-3">
                    <h5 class="fw-bold text-primary mb-0">Thông tin sinh viên</h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-3 text-center mb-3 mb-md-0">
                            <div class="avatar-container mx-auto mb-2" style="width: 100px; height: 100px;">
                                @if(!empty($sinhvien->HinhAnh) && file_exists(public_path($sinhvien->HinhAnh)))
                                    <img src="{{ asset($sinhvien->HinhAnh) }}?v={{ time() }}" 
                                         class="rounded-circle object-fit-cover w-100 h-100" 
                                         style="border: 3px solid #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.1);"
                                         alt="Avatar">
                                @else
                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center w-100 h-100"
                                         style="border: 3px solid #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                                        <i class="bi bi-person-fill text-secondary" style="font-size: 50px;"></i>
                                    </div>
                                @endif
                            </div>
                            <a href="{{ route('sinhvien.profile.index') }}" class="d-block mt-2 text-decoration-none small">Xem chi tiết</a>
                        </div>
                        <div class="col-md-9">
                            <div class="row g-2">
                                <div class="col-sm-6">
                                    <p class="mb-1"><span class="text-muted fw-medium" style="width: 100px; display: inline-block;">MSSV:</span> <b>{{ $sinhvien->MaSV }}</b></p>
                                    <p class="mb-1"><span class="text-muted fw-medium" style="width: 100px; display: inline-block;">Họ tên:</span> <b>{{ $sinhvien->TenSV }}</b></p>
                                    <p class="mb-1"><span class="text-muted fw-medium" style="width: 100px; display: inline-block;">Giới tính:</span> {{ $sinhvien->GioiTinh }}</p>
                                    <p class="mb-1"><span class="text-muted fw-medium" style="width: 100px; display: inline-block;">Ngày sinh:</span> {{ \Carbon\Carbon::parse($sinhvien->NgaySinh)->format('d/m/Y') }}</p>
                                    <p class="mb-1"><span class="text-muted fw-medium" style="width: 100px; display: inline-block;">Nơi sinh:</span> {{ $sinhvien->NoiSinh }}</p>
                                </div>
                                <div class="col-sm-6">
                                    <p class="mb-1"><span class="text-muted fw-medium" style="width: 110px; display: inline-block;">Lớp học:</span> <b>{{ $sinhvien->lop->TenLop ?? '---' }}</b></p>
                                    <p class="mb-1"><span class="text-muted fw-medium" style="width: 110px; display: inline-block;">Khóa học:</span> <b>{{ $sinhvien->namhoc->TenNamHoc ?? '---' }}</b></p>
                                    <p class="mb-1"><span class="text-muted fw-medium" style="width: 110px; display: inline-block;">Bậc đào tạo:</span> {{ $sinhvien->BacDaoTao }}</p>
                                    <p class="mb-1"><span class="text-muted fw-medium" style="width: 110px; display: inline-block;">Loại hình ĐT:</span> {{ $sinhvien->LoaiHinhDaoTao ?? 'Chính quy' }}</p>
                                    <p class="mb-1"><span class="text-muted fw-medium" style="width: 110px; display: inline-block;">Ngành:</span> <b>{{ $sinhvien->nganh->TenNganh ?? '---' }}</b></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN: NOTIFICATIONS --}}
        <div class="col-lg-4">
            <div class="row g-3 h-100">
                {{-- Nhắc nhở --}}
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 small">Nhắc nhở mới, chưa xem</p>
                                <h2 class="fw-bold mb-0 text-secondary">{{ $reminders }}</h2>
                                <a href="{{ route('sinhvien.baocao.index') }}" class="small text-decoration-none">Xem chi tiết</a>
                            </div>
                            <div class="icon-box rounded-circle border border-secondary text-secondary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="bi bi-bell"></i>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- Hạn nộp sắp tới --}}
                <div class="col-6">
                    <div class="card border-0 shadow-sm bg-info bg-opacity-10 h-100">
                        <div class="card-body position-relative">
                            <p class="text-info mb-1 small">Hạn nộp sắp tới</p>
                            <h2 class="fw-bold text-info mb-0">0</h2>
                            <a href="{{ route('sinhvien.baocao.index') }}" class="small text-decoration-none text-info">Xem chi tiết</a>
                            <div class="position-absolute top-50 end-0 translate-middle-y me-3 text-info opacity-50">
                                <i class="bi bi-calendar-event fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- Lịch bảo vệ --}}
                <div class="col-6">
                    <div class="card border-0 shadow-sm bg-warning bg-opacity-10 h-100">
                        <div class="card-body position-relative">
                            <p class="text-warning mb-1 small">Lịch bảo vệ</p>
                            <h2 class="fw-bold text-warning mb-0">--</h2>
                            <a href="#" class="small text-decoration-none text-warning">Xem chi tiết</a>
                            <div class="position-absolute top-50 end-0 translate-middle-y me-3 text-warning opacity-50">
                                <i class="bi bi-shield-check fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- QUICK ACTIONS --}}
    <div class="row g-3 mt-1">
        @php
            $actions = [
                ['icon' => 'bi-pencil-square', 'text' => 'Đăng ký đề tài', 'link' => route('sinhvien.detai.index')],
                ['icon' => 'bi-cloud-upload', 'text' => 'Nộp báo cáo', 'link' => route('sinhvien.baocao.index')],
                ['icon' => 'bi-trophy', 'text' => 'Xem kết quả', 'link' => route('sinhvien.diem.index')],
                ['icon' => 'bi-bell', 'text' => 'Thông báo', 'link' => route('sinhvien.thongbao.index')],
                ['icon' => 'bi-person-circle', 'text' => 'Hồ sơ cá nhân', 'link' => route('sinhvien.profile.index')],
                ['icon' => 'bi-key', 'text' => 'Đổi mật khẩu', 'link' => route('sinhvien.profile.changePasswordView')]
            ];
        @endphp
        @foreach($actions as $action)
        <div class="col-6 col-md-4 col-lg-2">
            <a href="{{ $action['link'] }}" class="card border-0 shadow-sm text-center text-decoration-none h-100 py-3 action-card">
                <div class="card-body p-2">
                    <i class="bi {{ $action['icon'] }} fs-3 text-primary mb-2 d-block"></i>
                    <span class="small text-muted fw-medium">{{ $action['text'] }}</span>
                </div>
            </a>
        </div>
        @endforeach
    </div>

    {{-- BOTTOM ROW: CHARTS & TABLES --}}
    <div class="row g-3 mt-1">
        {{-- Kết quả học tập (Chart) --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-primary mb-0">Kết quả học tập</h6>
                    <select class="form-select form-select-sm w-auto border-0 bg-light">
                        <option>HK1 ({{ $sinhvien->namhoc->TenNamHoc ?? '2025-2026' }})</option>
                    </select>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    @if($diem)
                        <canvas id="gradeChart" style="max-height: 200px;"></canvas>
                    @else
                        <div class="text-center text-muted">
                            <i class="bi bi-bar-chart fs-1 opacity-25"></i>
                            <p class="small mt-2">Chưa có dữ liệu hiển thị</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Tiến độ học tập (Circle) --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom-0">
                    <h6 class="fw-bold text-primary mb-0">Tiến độ học tập</h6>
                </div>
                <div class="card-body text-center position-relative">
                    <div class="position-relative d-inline-block">
                        <canvas id="progressChart" width="180" height="180"></canvas>
                        <div class="position-absolute top-50 start-50 translate-middle">
                            <h4 class="fw-bold mb-0 text-primary">{{ $progressPercent }}%</h4>
                        </div>
                    </div>
                    <p class="mt-3 fw-bold text-dark">{{ $completedTasks }}/{{ $totalTasks }} <span class="text-muted fw-normal small">nhiệm vụ</span></p>
                </div>
            </div>
        </div>

        {{-- Lớp học phần (Table) --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-primary mb-0">Đề tài đã đăng ký</h6>
                    <span class="badge bg-light text-primary border">{{ $sinhvien->namhoc->TenNamHoc ?? '2025-2026' }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-muted small">
                                <tr>
                                    <th class="ps-3 border-0">Tên đề tài</th>
                                    <th class="text-end pe-3 border-0">GVHD</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($detai)
                                    <tr>
                                        <td class="ps-3 border-0">
                                            <div class="fw-bold text-primary">{{ $detai->MaDeTai }}</div>
                                            <div class="small text-muted text-truncate" style="max-width: 200px;">{{ $detai->TenDeTai }}</div>
                                        </td>
                                        <td class="text-end pe-3 border-0 fw-bold text-primary">
                                            {{ $detai->giangVien->TenGV ?? 'Chưa cập nhật' }}
                                        </td>
                                    </tr>
                                @else
                                    <tr>
                                        <td colspan="2" class="text-center py-4 text-muted small">
                                            Chưa đăng ký học phần nào
                                        </td>
                                    </tr>
                                @endif
                                {{-- Placeholder rows to match design look --}}
                                @if(!$detai)
                                    @for($i=0; $i<3; $i++)
                                    <tr>
                                        <td class="ps-3 border-0">
                                            <div class="fw-bold text-muted opacity-50">---</div>
                                            <div class="small text-muted opacity-50">---</div>
                                        </td>
                                        <td class="text-end pe-3 border-0 fw-bold opacity-50">0</td>
                                    </tr>
                                    @endfor
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Grade Chart
        @if($diem)
        const ctxGrade = document.getElementById('gradeChart').getContext('2d');
        new Chart(ctxGrade, {
            type: 'bar',
            data: {
                labels: ['Đồ án'],
                datasets: [{
                    label: 'Điểm',
                    data: [{{ $diem->DiemCuoi ?? $diem->Diem ?? 0 }}],
                    backgroundColor: 'rgba(13, 110, 253, 0.2)',
                    borderColor: '#0d6efd',
                    borderWidth: 1,
                    borderRadius: 5,
                    barThickness: 40
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, max: 10 }
                }
            }
        });
        @endif

        // Progress Chart (Doughnut)
        const ctxProgress = document.getElementById('progressChart').getContext('2d');
        new Chart(ctxProgress, {
            type: 'doughnut',
            data: {
                labels: ['Hoàn thành', 'Chưa hoàn thành'],
                datasets: [{
                    data: [{{ $progressPercent }}, {{ 100 - $progressPercent }}],
                    backgroundColor: ['#20c997', '#e9ecef'],
                    borderWidth: 0,
                    cutout: '75%'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { enabled: false } }
            }
        });
    });
</script>

<style>
    .action-card {
        transition: all 0.2s ease;
    }
    .action-card:hover {
        transform: translateY(-3px);
        background-color: #f8f9fa;
    }
    .avatar-container img {
        border: 3px solid #fff;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
</style>
@endsection