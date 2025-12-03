<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NienKhoa;
use Illuminate\Http\Request;

class NienKhoaController extends Controller
{
    public function index()
    {
        $nienkhoas = NienKhoa::all();
        return view('admin.nienkhoa.index', compact('nienkhoas'));
    }

    public function create()
    {
        return view('admin.nienkhoa.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'TenNienKhoa' => 'nullable|string|max:100',
            'NamBatDau' => 'nullable|integer',
            'NamKetThuc' => 'nullable|integer'
        ]);

        NienKhoa::create($request->only('TenNienKhoa','NamBatDau','NamKetThuc'));
        return redirect()->route('admin.nienkhoa.index')->with('success','Thêm niên khóa thành công');
    }

    public function edit($id)
    {
        $nienkhoa = NienKhoa::findOrFail($id);
        return view('admin.nienkhoa.edit', compact('nienkhoa'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'TenNienKhoa' => 'nullable|string|max:100',
            'NamBatDau' => 'nullable|integer',
            'NamKetThuc' => 'nullable|integer'
        ]);

        $nienkhoa = NienKhoa::findOrFail($id);
        $nienkhoa->update($request->only('TenNienKhoa','NamBatDau','NamKetThuc'));
        return redirect()->route('admin.nienkhoa.index')->with('success','Cập nhật thành công');
    }

    public function destroy($id)
    {
        NienKhoa::destroy($id);
        return redirect()->route('admin.nienkhoa.index')->with('success','Xóa thành công');
    }
}
