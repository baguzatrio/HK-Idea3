<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Divisi;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['divisi', 'roles'])
            ->get()
            ->map(function ($user) {
                return [
                    'id'        => $user->id,
                    'name'      => $user->name,
                    'email'     => $user->email,
                    'divisi'    => $user->divisi?->nama ?? '-',
                    'divisi_id' => $user->divisi_id,
                    'role'      => $user->roles->first()?->name ?? '-',
                ];
            });

        $divisis = Divisi::orderBy('nama')->get(['id', 'nama']);
        $roles = Role::orderBy('name')->get(['id', 'name as nama']); // For frontend consistency

        return response()->json([
            'users'   => $users,
            'divisis' => $divisis,
            'roles'   => $roles,
        ]);
    }

    public function store(Request $request)
    {

        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:8|confirmed',
            'divisi_id' => 'nullable|exists:divisi,id',
            'role'      => 'nullable|string|exists:roles,name',
        ]);

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'divisi_id' => $request->divisi_id ?? null,
        ]);

        if ($request->filled('role')) {
            $user->assignRole($request->role);
        }

        return response()->json(['message' => 'User berhasil ditambahkan.']);
    }

    public function update(Request $request, User $user)
    {

        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'password'  => 'nullable|string|min:8|confirmed',
            'divisi_id' => 'nullable|exists:divisi,id',
            'role'      => 'nullable|string|exists:roles,name',
        ]);

        $data = [
            'name'      => $request->name,
            'email'     => $request->email,
            'divisi_id' => $request->divisi_id ?? null,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        if ($request->filled('role')) {
            $user->syncRoles([$request->role]);
        } else {
            $user->syncRoles([]);
        }

        return response()->json(['message' => 'User berhasil diupdate.']);
    }

    public function destroy(User $user)
    {

        $user->delete();

        return response()->json(['message' => 'User berhasil dihapus.']);
    }
}