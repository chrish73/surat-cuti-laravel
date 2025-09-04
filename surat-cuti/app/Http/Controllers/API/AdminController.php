<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Models\PermohonanCuti;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        $permohonanCuti = PermohonanCuti::with('karyawan')->orderBy('created_at', 'desc')->get();
        return response()->json($permohonanCuti);
    }

    public function changeStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:permohonan_cuti,id',
            'status' => 'required|in:Disetujui,Ditolak',
        ]);

        $permohonan = PermohonanCuti::findOrFail($request->id);

        if ($permohonan->status === 'Menunggu' && $request->status === 'Disetujui' && $permohonan->jenis_cuti === 'Cuti Tahunan') {
            $karyawan = $permohonan->karyawan;
            if ($permohonan->durasi > $karyawan->jatah_cuti_tahunan) {
                return response()->json(['success' => false, 'message' => 'Sisa cuti tidak mencukupi!'], 400);
            }
            $karyawan->jatah_cuti_tahunan -= $permohonan->durasi;
            $karyawan->save();
        }

        $permohonan->status = $request->status;
        $permohonan->save();

        return response()->json(['success' => true, 'message' => 'Status berhasil diperbarui.']);
    }

    public function revertStatus(Request $request)
    {
        $request->validate(['id' => 'required|exists:permohonan_cuti,id']);
        $permohonan = PermohonanCuti::findOrFail($request->id);

        if ($permohonan->jenis_cuti === 'Cuti Tahunan' && $permohonan->status === 'Disetujui') {
            $karyawan = $permohonan->karyawan;
            $karyawan->jatah_cuti_tahunan += $permohonan->durasi;
            $karyawan->save();
        }

        $permohonan->status = 'Menunggu';
        $permohonan->save();

        return response()->json(['success' => true, 'message' => 'Status berhasil dikembalikan.']);
    }
}
