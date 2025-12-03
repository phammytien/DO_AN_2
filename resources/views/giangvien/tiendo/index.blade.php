@extends('layouts.giangvien')

@section('content')
<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-primary fw-bold">
            <i class="bi bi-list-check me-2"></i>Quản lý tiến độ đề tài
        </h3>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalThem">
            <i class="bi bi-plus-lg me-2"></i>Thêm mốc tiến độ
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success shadow-sm border-0">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger shadow-sm border-0">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary">
                        <tr>
                            <th class="ps-4 py-3">Đề tài</th>
                            <th class="py-3">Nội dung công việc</th>
                            <th class="py-3">Deadline</th>
                            <th class="py-3">Tình trạng nộp</th>
                            <th class="py-3">File đính kèm</th>
                            <th class="text-end pe-4 py-3">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tiendos as $td)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark">{{ $td->deTai->TenDeTai }}</div>
                                    <small class="text-muted">SV: {{ $td->deTai->sinhViens->first()->TenSV ?? 'N/A' }}</small>
                                </td>
                                <td>{{ $td->NoiDung }}</td>
                                <td>
                                    @if($td->Deadline)
                                        <span class="{{ \Carbon\Carbon::parse($td->Deadline)->isPast() && !$td->NgayNop ? 'text-danger fw-bold' : '' }}">
                                            {{ \Carbon\Carbon::parse($td->Deadline)->format('d/m/Y') }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($td->NgayNop)
                                        <span class="badge bg-success">Đã nộp</span>
                                        <div class="small text-muted mt-1">{{ \Carbon\Carbon::parse($td->NgayNop)->format('d/m/Y H:i') }}</div>
                                    @else
                                        <span class="badge bg-secondary">Chưa nộp</span>
                                    @endif
                                </td>
                                <td>
                                    @if($td->LinkFile)
                                        <div class="mb-1">
                                            <a href="{{ asset($td->LinkFile) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                                <i class="bi bi-file-earmark-text me-1"></i>Báo cáo
                                            </a>
                                        </div>
                                    @endif
                                    @if($td->fileCode)
                                        @php
                                            $pathCode = str_replace('\\', '/', $td->fileCode->path);
                                            $urlCode = Str::startsWith($pathCode, 'storage/') ? asset($pathCode) : asset('storage/' . $pathCode);
                                        @endphp
                                        <div>
                                            <a href="{{ $urlCode }}" target="_blank" class="btn btn-sm btn-outline-warning text-dark">
                                                <i class="bi bi-file-code me-1"></i>Code
                                            </a>
                                        </div>
                                    @endif
                                    @if(!$td->LinkFile && !$td->fileCode)
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-2">
                                        @if($td->TrangThai === 'Xin nộp bổ sung')
                                            <form action="{{ route('giangvien.tiendo.approveLate', $td->MaTienDo) }}" method="POST">
                                                @csrf
                                                <button class="btn btn-sm btn-warning text-dark" title="Duyệt nộp bổ sung">
                                                    <i class="bi bi-check-circle-fill me-1"></i>Duyệt nộp bù
                                                </button>
                                            </form>
                                        @endif

                                        <button class="btn btn-sm btn-outline-primary btn-edit" 
                                                data-id="{{ $td->MaTienDo }}"
                                                data-detai="{{ $td->MaDeTai }}"
                                                data-noidung="{{ $td->NoiDung }}"
                                                data-deadline="{{ $td->Deadline }}"
                                                title="Sửa">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        
                                        <button class="btn btn-sm btn-outline-danger btn-delete" 
                                                data-id="{{ $td->MaTienDo }}"
                                                data-noidung="{{ $td->NoiDung }}"
                                                title="Xóa">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-clipboard-x display-6 d-block mb-3"></i>
                                    Chưa có mốc tiến độ nào được tạo.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Thêm Mới -->
<div class="modal fade" id="modalThem" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Thêm mốc tiến độ mới</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('giangvien.tiendo.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Chọn đề tài</label>
                        <select name="MaDeTai" class="form-select" required>
                            @php
                                $detais = \App\Models\DeTai::where('MaGV', Auth::user()->MaSo)->get();
                            @endphp
                            @foreach($detais as $dt)
                                <option value="{{ $dt->MaDeTai }}">{{ $dt->TenDeTai }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nội dung công việc</label>
                        <textarea name="NoiDung" class="form-control" rows="3" required placeholder="Ví dụ: Nộp chương 1, Viết báo cáo tuần..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Hạn chót (Deadline)</label>
                        <input type="date" name="Deadline" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Lưu lại</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Chỉnh Sửa -->
<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 15px 15px 0 0;">
                <h5 class="modal-title text-white fw-bold">
                    <i class="bi bi-pencil-square me-2"></i>Chỉnh sửa mốc tiến độ
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">
                            <i class="bi bi-book me-2 text-primary"></i>Chọn đề tài
                        </label>
                        <select name="MaDeTai" id="editDeTai" class="form-select" required style="border-radius: 10px;">
                            @foreach($detais as $dt)
                                <option value="{{ $dt->MaDeTai }}">{{ $dt->TenDeTai }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">
                            <i class="bi bi-card-text me-2 text-success"></i>Nội dung công việc
                        </label>
                        <textarea name="NoiDung" id="editNoiDung" class="form-control" rows="3" required placeholder="Ví dụ: Nộp chương 1, Viết báo cáo tuần..." style="border-radius: 10px;"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">
                            <i class="bi bi-calendar-event me-2 text-danger"></i>Hạn chót (Deadline)
                        </label>
                        <input type="date" name="Deadline" id="editDeadline" class="form-control" required style="border-radius: 10px;">
                    </div>
                </div>
                <div class="modal-footer border-0 p-4">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal" style="border-radius: 10px;">
                        <i class="bi bi-x-circle me-2"></i>Hủy
                    </button>
                    <button type="submit" class="btn btn-primary px-4" style="border-radius: 10px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                        <i class="bi bi-check-circle me-2"></i>Cập nhật
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Xác Nhận Xóa -->
<div class="modal fade" id="deleteModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-body text-center p-5">
                <div class="mb-4">
                    <div class="warning-icon" style="width: 100px; height: 100px; margin: 0 auto; background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; animation: pulse 2s infinite;">
                        <i class="bi bi-exclamation-triangle" style="font-size: 50px; color: #dc2626;"></i>
                    </div>
                </div>
                <h4 class="fw-bold mb-3" style="color: #1f2937;">Bạn có chắc chắn muốn xóa?</h4>
                <p class="text-muted mb-2" id="deleteMessage" style="font-size: 1.1rem;"></p>
                <div class="alert alert-warning border-0 mt-3 mb-4" style="background: #fef3c7; color: #92400e; border-radius: 12px;">
                    <i class="bi bi-info-circle me-2"></i>
                    <small><strong>Lưu ý:</strong> Thao tác này không thể hoàn tác!</small>
                </div>
                <div class="d-flex gap-3 justify-content-center">
                    <button type="button" class="btn btn-light px-5 py-2 fw-semibold" data-bs-dismiss="modal" style="border-radius: 12px; border: 2px solid #e5e7eb;">
                        <i class="bi bi-x-circle me-2"></i>Hủy
                    </button>
                    <button type="button" class="btn btn-danger px-5 py-2 fw-semibold" id="confirmDeleteBtn" style="border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(220, 38, 38, 0.3);">
                        <i class="bi bi-trash me-2"></i>Xác nhận xóa
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Thông Báo Thành Công -->
<div class="modal fade" id="successModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-body text-center p-5">
                <div class="mb-4">
                    <div class="success-icon" style="width: 80px; height: 80px; margin: 0 auto; background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; animation: scaleIn 0.5s ease-out;">
                        <i class="bi bi-check-circle" style="font-size: 40px; color: #28a745;"></i>
                    </div>
                </div>
                <h4 class="fw-bold mb-3" id="successTitle">Thành công!</h4>
                <p class="text-muted mb-4" id="successMessage"></p>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-success btn-lg" onclick="window.location.reload()" style="border-radius: 10px;">
                        Quay lại danh sách
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

@keyframes scaleIn {
    0% { transform: scale(0); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editModal = new bootstrap.Modal('#modalEdit');
    const deleteModal = new bootstrap.Modal('#deleteModal');
    const successModal = new bootstrap.Modal('#successModal');
    
    let deleteId = null;
    let deleteForm = null;
    
    // Edit button click
    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const detai = this.dataset.detai;
            const noidung = this.dataset.noidung;
            const deadline = this.dataset.deadline;
            
            document.getElementById('editForm').action = `/giangvien/tiendo/${id}`;
            document.getElementById('editDeTai').value = detai;
            document.getElementById('editNoiDung').value = noidung;
            document.getElementById('editDeadline').value = deadline;
            
            editModal.show();
        });
    });
    
    // Delete button click
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function() {
            deleteId = this.dataset.id;
            const noidung = this.dataset.noidung;
            
            document.getElementById('deleteMessage').textContent = `Xóa mốc tiến độ: "${noidung}"?`;
            deleteModal.show();
        });
    });
    
    // Confirm delete
    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        if (!deleteId) return;
        
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/giangvien/tiendo/${deleteId}`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        
        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        
        deleteModal.hide();
        form.submit();
    });
});
</script>
@endsection
