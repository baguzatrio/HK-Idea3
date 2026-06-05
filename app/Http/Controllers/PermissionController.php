<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use App\Models\Divisi;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::query()->get()
            ->map(function ($p) {
                // To avoid relation error if not defined on Spatie model, let's fetch divisi name via Divisi model
                $divisi = Divisi::query()->find($p->divisi_id);
                return [
                    'id'             => $p->id,
                    'nama'           => $p->name, // from Spatie column
                    'divisi_id'      => $p->divisi_id,
                    'nama_divisi'    => $divisi ? $divisi->nama : '-',
                    'judul_report'   => $p->judul_report,
                    'deskripsi'      => $p->deskripsi,
                    'link_dashboard' => $p->link_dashboard,
                ];
            });

        $divisis = Divisi::query()->orderBy('nama', 'asc')->get(['id', 'nama']);

        return response()->json([
            'permissions' => $permissions,
            'divisis'     => $divisis,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama'           => 'required|string|max:255|unique:' . config('permission.table_names.permissions') . ',name',
            'divisi_id'      => 'nullable|exists:divisi,id',
            'judul_report'   => 'nullable|string|max:255',
            'deskripsi'      => 'nullable|string',
            'link_dashboard' => 'nullable|url|max:1000',
        ]);

        Permission::create([
            'name'           => $request->input('nama'),
            'guard_name'     => 'web',
            'divisi_id'      => $request->input('divisi_id'),
            'judul_report'   => $request->input('judul_report'),
            'deskripsi'      => $request->input('deskripsi'),
            'link_dashboard' => $request->input('link_dashboard'),
        ]);

        return response()->json(['message' => 'Permission berhasil ditambahkan.']);
    }

    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'nama'           => 'required|string|max:255|unique:' . config('permission.table_names.permissions') . ',name,' . $permission->id,
            'divisi_id'      => 'nullable|exists:divisi,id',
            'judul_report'   => 'nullable|string|max:255',
            'deskripsi'      => 'nullable|string',
            'link_dashboard' => 'nullable|url|max:1000',
        ]);

        $permission->update([
            'name'           => $request->input('nama'),
            'divisi_id'      => $request->input('divisi_id'),
            'judul_report'   => $request->input('judul_report'),
            'deskripsi'      => $request->input('deskripsi'),
            'link_dashboard' => $request->input('link_dashboard'),
        ]);

        return response()->json(['message' => 'Permission berhasil diupdate.']);
    }

    public function destroy(Permission $permission)
    {
        $permission->deleteOrFail();

        return response()->json(['message' => 'Permission berhasil dihapus.']);
    }
}
