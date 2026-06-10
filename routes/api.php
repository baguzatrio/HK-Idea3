<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\DivisiController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Models\Divisi;

Route::middleware('auth:sanctum')->group(function() {
    Route::get('/user', function (Request $request) {
        return $request->user()->load('roles');
    });

    Route::get('/dashboard', function () {
        $user = auth()->user();

        $divisis = Divisi::with(['permissions'])
            ->orderBy('no_urut')
            ->get()
            ->map(function ($d) use ($user) {
                // Filter izin
                $allowedPermissions = $d->permissions->filter(function ($p) use ($user) {
                    return $user->hasRole('super_admin') || $user->hasPermissionTo($p->name);
                });

                return [
                    'id'      => $d->id,
                    'kode'    => $d->kode,
                    'nama'    => $d->nama,
                    'logo'    => $d->logo,
                    'no_urut' => $d->no_urut,
                    'permissions' => $allowedPermissions->values()->map(fn($p) => [
                        'id'             => $p->id,
                        'nama'           => $p->name ?? $p->nama,
                        'nama_report'    => $p->nama_report,
                        'judul_report'   => $p->judul_report,
                        'link_dashboard' => $p->link_dashboard,
                    ])->toArray(),
                ];
            })->filter(function ($d) {
                return count($d['permissions']) > 0;
            })->values();

        return response()->json([
            'divisis' => $divisis,
        ]);
    });

    Route::middleware(['role:super_admin'])->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('divisis', DivisiController::class)->except(['create', 'edit', 'show']);
        Route::resource('roles', RoleController::class)->except(['create', 'edit', 'show']);
        Route::resource('permissions', PermissionController::class)->except(['create', 'edit', 'show']);
    });
});
