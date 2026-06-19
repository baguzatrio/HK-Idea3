<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')
            ->orderBy('name')
            ->get()
            ->map(function ($role) {
                return [
                    'id'          => $role->id,
                    'nama'        => $role->name,
                    'permissions' => $role->permissions->map(fn($p) => [
                        'id' => $p->id,
                        'nama' => $p->name,
                    ]),
                ];
            });

        $permissions = Permission::orderBy('name')->get()->map(function ($p) {
            return [
                'id' => $p->id,
                'nama' => $p->name,
            ];
        });

        return response()->json([
            'roles'       => $roles,
            'permissions' => $permissions,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama'        => 'required|string|max:255|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:' . config('permission.table_names.permissions') . ',id',
        ]);

        $role = Role::create([
            'name' => $request->nama,
            'guard_name' => 'web'
        ]);

        if ($request->filled('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return response()->json(['message' => 'Role berhasil ditambahkan.']);
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'nama'        => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:' . config('permission.table_names.permissions') . ',id',
        ]);

        $role->update([
            'name' => $request->nama,
        ]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        } else {
            $role->syncPermissions([]);
        }

        return response()->json(['message' => 'Role berhasil diupdate.']);
    }

    public function destroy(Role $role)
    {
        $role->delete();

        return response()->json(['message' => 'Role berhasil dihapus.']);
    }
}
