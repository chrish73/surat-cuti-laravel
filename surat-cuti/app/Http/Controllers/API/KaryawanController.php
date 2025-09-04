<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PermohonanCuti;

class KaryawanController extends Controller
{
    public function getKaryawanInfo(Request $request)
    {
        return response()->json($request->user());
    }

    public function getLeaveHistory(Request $request)
    {
        $permohonan = $request->user()->permohonanCuti()->orderBy('created_at', 'desc')->get();
        return response()->json($permohonan);
    }

    public function ajukanPermohonan(Request $request)
    {
        $request->validate([
            'jenis_cuti' => 'required',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'alasan' => 'required',
            'alamat' => 'required'
        ]);

        $durasi = (new \Carbon\Carbon($request->tanggal_mulai))->diffInDays(new \Carbon\Carbon($request->tanggal_selesai)) + 1;

        PermohonanCuti::create([
            'karyawan_id' => $request->user()->id,
            'jenis_cuti' => $request->jenis_cuti,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'durasi' => $durasi,
            'alasan' => $request->alasan,
            'alamat_selama_cuti' => $request->alamat,
        ]);

        return response()->json(['message' => 'Permohonan berhasil diajukan!']);
    }
}
