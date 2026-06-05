<?php

namespace App\Http\Controllers;

use App\Models\Divisi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class DivisiController extends Controller
{
    public function index()
    {
        $divisis = Divisi::orderBy('no_urut')->get()->map(function ($divisi) {
            return [
                'id'      => $divisi->id,
                'kode'    => $divisi->kode,
                'nama'    => $divisi->nama,
                'lantai'  => $divisi->lantai,
                'logo'    => $divisi->logo,

                'no_urut' => $divisi->no_urut,
            ];
        });

        return response()->json([
            'divisis' => $divisis,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode'    => 'required|string|max:50|unique:divisi,kode',
            'nama'    => 'required|string|max:255',
            'lantai'  => 'nullable|integer',
            'logo'    => 'nullable|image|max:2048',

            'no_urut' => 'nullable|integer',
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('divisi/logo', 'public');
        }

        Divisi::create([
            'kode'    => $request->kode,
            'nama'    => $request->nama,
            'lantai'  => $request->lantai,
            'logo'    => $logoPath,

            'no_urut' => $request->no_urut ?? 0,
        ]);

        return response()->json(['message' => 'Divisi berhasil ditambahkan.']);
    }

    public function update(Request $request, Divisi $divisi)
    {
        $request->validate([
            'kode'    => 'required|string|max:50|unique:divisi,kode,' . $divisi->id,
            'nama'    => 'required|string|max:255',
            'lantai'  => 'nullable|integer',
            'logo'    => 'nullable|image|max:2048',

            'no_urut' => 'nullable|integer',
        ]);

        $logoPath = $divisi->logo;
        if ($request->hasFile('logo')) {
            // Hapus logo lama
            if ($divisi->logo) {
                Storage::disk('public')->delete($divisi->logo);
            }
            $logoPath = $request->file('logo')->store('divisi/logo', 'public');
        }

        $divisi->update([
            'kode'    => $request->kode,
            'nama'    => $request->nama,
            'lantai'  => $request->lantai,
            'logo'    => $logoPath,

            'no_urut' => $request->no_urut ?? 0,
        ]);

        return response()->json(['message' => 'Divisi berhasil diupdate.']);
    }

    public function destroy(Divisi $divisi)
    {
        if ($divisi->logo) {
            Storage::disk('public')->delete($divisi->logo);
        }

        $divisi->delete();

        return response()->json(['message' => 'Divisi berhasil dihapus.']);
    }
}