<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PermohonanCuti;
use App\Models\Karyawan;
use Barryvdh\DomPDF\Facade\Pdf;
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
        $karyawan = Karyawan::find($request->user()->id);

        if ($request->jenis_cuti === 'Cuti Tahunan') {
            if ($durasi > $karyawan->jatah_cuti_tahunan) {
                return response()->json(['message' => 'Sisa cuti tahunan tidak mencukupi.'], 400);
            }
        }

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

    public function downloadSuratPersetujuan(Request $request, $id)
    {
        $permohonan = PermohonanCuti::with('karyawan')->find($id);

        if (!$permohonan || $permohonan->karyawan->id !== $request->user()->id || $permohonan->status !== 'Disetujui') {
            return response()->json(['message' => 'Surat tidak ditemukan atau belum disetujui.'], 404);
        }

        $data = [
            'permohonan' => $permohonan,
            'karyawan' => $permohonan->karyawan,
            'tanggal_cetak' => now()->format('d F Y')
        ];

        $pdf = PDF::loadView('surat-persetujuan-cuti', $data);

        $fileName = 'Surat_Persetujuan_Cuti_' . $permohonan->karyawan->id_karyawan . '_' . $permohonan->tanggal_mulai . '.pdf';
        return $pdf->download($fileName);
    }
}
