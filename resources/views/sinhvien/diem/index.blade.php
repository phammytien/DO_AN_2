@extends('layouts.sinhvien')

@section('content')
<style>
    .score-card {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 30px;
    }
    
    .score-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 8px;
    }
    
    .score-header .icon {
        width: 40px;
        height: 40px;
        background: #4285f4;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 20px;
    }
    
    .score-header h4 {
        margin: 0;
        font-size: 20px;
        font-weight: 600;
        color: #202124;
    }
    
    .score-description {
        color: #5f6368;
        font-size: 14px;
        margin-left: 52px;
        margin-bottom: 20px;
    }
    
    .results-table {
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .results-table table {
        margin: 0;
        border: none;
    }
    
    .results-table thead th {
        background: #f8f9fa;
        color: #5f6368;
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 16px;
        border: none;
    }
    
    .results-table tbody td {
        padding: 16px;
        border-top: 1px solid #e8eaed;
        border-left: none;
        border-right: none;
        color: #202124;
        vertical-align: middle;
    }
    
    .results-table tbody tr:first-child td {
        border-top: none;
    }
    
    .lecturer-name {
        font-weight: 500;
        color: #202124;
    }
    
    .role-text {
        color: #5f6368;
        font-size: 14px;
    }
    
    .score-value {
        font-weight: 600;
        font-size: 16px;
        color: #202124;
    }
    
    .not-published {
        color: #d93025;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    
    .status-badge {
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    
    .status-approved {
        background: #e6f4ea;
        color: #1e8e3e;
    }
    
    .status-pending {
        background: #fef7e0;
        color: #f9ab00;
    }
    
    .summary-section {
        display: flex;
        gap: 20px;
        margin-top: 20px;
    }
    
    .summary-card {
        flex: 1;
        padding: 20px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 16px;
    }
    
    .summary-card.average {
        background: #e8f0fe;
    }
    
    .summary-card.final {
        background: #e6f4ea;
    }
    
    .summary-icon {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
    }
    
    .summary-card.average .summary-icon {
        background: #4285f4;
        color: white;
    }
    
    .summary-card.final .summary-icon {
        background: #34a853;
        color: white;
    }
    
    .summary-content h6 {
        margin: 0 0 4px 0;
        color: #5f6368;
        font-size: 13px;
        font-weight: 500;
    }
    
    .summary-content .score {
        font-size: 28px;
        font-weight: 700;
        margin: 0;
    }
    
    .summary-card.average .score {
        color: #1967d2;
    }
    
    .summary-card.final .score {
        color: #1e8e3e;
    }
</style>

<div class="container mt-4">
    @foreach($detais as $dt)
        <div class="score-card">
            <div class="score-header">
                <div class="icon">
                    📊
                </div>
                <h4>Kết Quả Chấm Điểm</h4>
            </div>
            <div class="score-description">
                Hệ thống quản lý đồ án.
            </div>

            @php
                // lấy tất cả chấm điểm của 1 sinh viên
                $list = $dt->chamdiems->where('MaSV', $maSV);

                // Tính trung bình
                $diemTB = $list->avg('Diem');

                // Điểm cuối = chỉ có khi 1 dòng nào đó đã duyệt
                $isApproved = $list->contains(fn($i) => $i->TrangThai === 'Đã duyệt');
                $diemCuoi = $isApproved ? $diemTB : null;
            @endphp

            <div class="results-table">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>GIẢNG VIÊN</th>
                            <th>VAI TRÒ</th>
                            <th>ĐIỂM</th>
                            <th>NHẬN XÉT</th>
                            <th>NGÀY CHẤM</th>
                            <th>TRẠNG THÁI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($list as $cd)
                            @php
                                $vaiTro = $vaiTroTheoDeTai[$dt->MaDeTai][$cd->MaGV] ?? '-';
                            @endphp
                            <tr>
                                <td class="lecturer-name">{{ $cd->giangVien->TenGV ?? '-' }}</td>
                                <td class="role-text">{{ $vaiTro }}</td>
                                <td>
                                    @if($cd->TrangThai === 'Đã duyệt')
                                        <span class="score-value">{{ number_format($cd->Diem, 2) }}</span>
                                    @else
                                        <span class="not-published">⛔ Chưa công bố</span>
                                    @endif
                                </td>
                                <td class="role-text">
                                    @if($cd->TrangThai === 'Đã duyệt')
                                        {{ $cd->NhanXet ?? '-' }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="role-text">
                                    {{ $cd->NgayCham ? \Carbon\Carbon::parse($cd->NgayCham)->format('d/m/Y H:i') : '-' }}
                                </td>
                                <td>
                                    @if($cd->TrangThai === 'Đã duyệt')
                                        <span class="status-badge status-approved">
                                            ✅ Đã duyệt
                                        </span>
                                    @else
                                        <span class="status-badge status-pending">
                                            ⏳ Chờ duyệt
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="summary-section">
                <div class="summary-card average">
                    <div class="summary-icon">⭐</div>
                    <div class="summary-content">
                        <h6>Điểm trung bình</h6>
                        <div class="score">
                            @if($diemCuoi !== null)
                                {{ number_format($diemTB, 2) }}
                            @else
                                -
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="summary-card final">
                    <div class="summary-icon">🏆</div>
                    <div class="summary-content">
                        <h6>Điểm cuối (chính thức)</h6>
                        <div class="score">
                            @if($diemCuoi !== null)
                                {{ number_format($diemCuoi, 2) }}
                            @else
                                -
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
