<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PermohonanCuti;
use App\Models\Karyawan; // Import model Karyawan

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
            'alamat' => 'required',
            'lampiran_file' => 'nullable|file|max:2048' // Aturan validasi baru untuk file (max: 2MB)
        ]);

        $durasi = (new \Carbon\Carbon($request->tanggal_mulai))->diffInDays(new \Carbon\Carbon($request->tanggal_selesai)) + 1;
        $karyawan = Karyawan::find($request->user()->id);

        if ($request->jenis_cuti === 'Cuti Tahunan') {
            if ($durasi > $karyawan->jatah_cuti_tahunan) {
                return response()->json(['message' => 'Sisa cuti tahunan tidak mencukupi.'], 400);
            }
        }

        $filePath = null;
        if ($request->hasFile('lampiran_file')) {
            $filePath = $request->file('lampiran_file')->store('lampiran_cuti', 'public');
        }

        PermohonanCuti::create([
            'karyawan_id' => $request->user()->id,
            'jenis_cuti' => $request->jenis_cuti,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'durasi' => $durasi,
            'alasan' => $request->alasan,
            'alamat_selama_cuti' => $request->alamat,
            'lampiran_file' => $filePath,
        ]);

        return response()->json(['message' => 'Permohonan berhasil diajukan!']);
    }
}
