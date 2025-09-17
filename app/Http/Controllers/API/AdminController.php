<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PermohonanCuti;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Mail;
use App\Mail\StatusCutiNotification;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $query = PermohonanCuti::with('karyawan');

        if ($request->has('unit') && $request->unit != '') {
            $query->whereHas('karyawan', function ($q) use ($request) {
                $q->where('unit', $request->unit);
            });
        }

        $permohonanCuti = $query->orderBy('created_at', 'desc')->get();
        return response()->json($permohonanCuti);
    }

    public function exportToExcel(Request $request)
    {
        $query = PermohonanCuti::with('karyawan');

        if ($request->has('unit') && $request->unit != '') {
            $query->whereHas('karyawan', function ($q) use ($request) {
                $q->where('unit', $request->unit);
            });
        }

        $permohonanCuti = $query->orderBy('created_at', 'desc')->get();

        $timestamp = now()->format('d-m-Y');
        $fileName = 'Riwayat cuti karyawan Telkom_' . $timestamp . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        $callback = function() use ($permohonanCuti) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Nama Karyawan',
                'ID Karyawan',
                'Unit',
                'Jenis Cuti',
                'Tanggal Mulai',
                'Tanggal Selesai',
                'Durasi (Hari)',
                'Alasan',
                'Alamat Cuti',
                'Status'
            ]);

            foreach ($permohonanCuti as $permohonan) {
                fputcsv($file, [
                    $permohonan->karyawan->nama ?? '',
                    $permohonan->karyawan->id_karyawan ?? '',
                    $permohonan->karyawan->unit ?? '',
                    $permohonan->jenis_cuti,
                    $permohonan->tanggal_mulai,
                    $permohonan->tanggal_selesai,
                    $permohonan->durasi,
                    $permohonan->alasan,
                    $permohonan->alamat_selama_cuti,
                    $permohonan->status
                ]);
            }
            fclose($file);
        };

        return response()->streamDownload($callback, $fileName, $headers);
    }

    public function changeStatus(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|exists:permohonan_cuti,id',
                'status' => 'required|in:Disetujui,Ditolak,Menunggu',
                'alasan_penolakan' => 'nullable|string'
            ]);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $permohonan = PermohonanCuti::findOrFail($request->id);
        $karyawan = $permohonan->karyawan;
        $previousStatus = $permohonan->status;
        $newStatus = $request->status;

        // Atur alasan penolakan hanya jika status baru adalah 'Ditolak'
        if ($newStatus === 'Ditolak') {
            if (!$request->alasan_penolakan) {
                return response()->json(['success' => false, 'message' => 'Alasan penolakan tidak boleh kosong.'], 400);
            }
            $permohonan->alasan_penolakan = $request->alasan_penolakan;
        } else {
            // Kosongkan alasan penolakan jika status diubah ke 'Disetujui' atau 'Menunggu'
            $permohonan->alasan_penolakan = null;
        }

        // Mengelola jatah cuti berdasarkan perubahan status
        if ($permohonan->jenis_cuti === 'Cuti Tahunan') {
            // Jika status sebelumnya adalah 'Disetujui' dan status baru bukan 'Disetujui', kembalikan jatah cuti
            if ($previousStatus === 'Disetujui' && $newStatus !== 'Disetujui') {
                $karyawan->jatah_cuti_tahunan += $permohonan->durasi;
            }
            // Jika status baru adalah 'Disetujui' dan status sebelumnya bukan 'Disetujui', kurangi jatah cuti
            if ($newStatus === 'Disetujui' && $previousStatus !== 'Disetujui') {
                if ($permohonan->durasi > $karyawan->jatah_cuti_tahunan) {
                    return response()->json(['success' => false, 'message' => 'Sisa cuti tidak mencukupi!'], 400);
                }
                $karyawan->jatah_cuti_tahunan -= $permohonan->durasi;
            }
            $karyawan->save();
        }

        $permohonan->status = $newStatus;
        $permohonan->save();

        // Kirim notifikasi email setelah status diperbarui
        if ($karyawan) {
            Mail::to($karyawan->email)->send(new StatusCutiNotification($permohonan));
        }

        return response()->json(['success' => true, 'message' => 'Status berhasil diperbarui dan notifikasi email telah dikirim.']);
    }
}
