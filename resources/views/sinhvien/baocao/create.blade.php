@extends('layouts.sinhvien')
@section('title','Nộp báo cáo')
@section('content')
    <h1>Nộp báo cáo</h1>
    <form action="{{ route('sinhvien.baocao.store') }}" method="post" enctype="multipart/form-data">
        @csrf
        <div>
            <label>Đề tài</label>
            <select name="MaDeTai" required>
                @foreach($detais as $d)
                    <option value="{{ $d->MaDeTai }}">{{ $d->TenDeTai }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label>File (pdf/doc/zip)</label>
            <input type="file" name="file" required>
        </div>
        <div>
            <label>Lần nộp</label>
            <input type="number" name="LanNop" value="1" required>
        </div>
        <button type="submit">Nộp</button>
    </form>
@endsection
