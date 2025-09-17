<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PermohonanCuti;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Mail; // Tambahkan ini
use App\Mail\StatusCutiNotification; // Tambahkan ini
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
        $request->validate([
            'id' => 'required|exists:permohonan_cuti,id',
            'status' => 'required|in:Disetujui,Ditolak',
        ]);

        $permohonan = PermohonanCuti::findOrFail($request->id);
        $karyawan = $permohonan->karyawan;

        // Logika untuk mengurangi jatah cuti hanya jika disetujui dari status Menunggu
        if ($permohonan->status === 'Menunggu' && $request->status === 'Disetujui' && $permohonan->jenis_cuti === 'Cuti Tahunan') {
            if ($permohonan->durasi > $karyawan->jatah_cuti_tahunan) {
                return response()->json(['success' => false, 'message' => 'Sisa cuti tidak mencukupi!'], 400);
            }
            $karyawan->jatah_cuti_tahunan -= $permohonan->durasi;
            $karyawan->save();
        }

        $permohonan->status = $request->status;
        $permohonan->save();

        // Kirim notifikasi email setelah status diperbarui
        if ($karyawan) {
            Mail::to($karyawan->email)->send(new StatusCutiNotification($permohonan));
        }

        return response()->json(['success' => true, 'message' => 'Status berhasil diperbarui dan notifikasi email telah dikirim.']);
    }

    public function revertStatus(Request $request)
    {
        $request->validate(['id' => 'required|exists:permohonan_cuti,id']);
        $permohonan = PermohonanCuti::findOrFail($request->id);
        $karyawan = $permohonan->karyawan;

        // Logika untuk mengembalikan jatah cuti hanya jika status sebelumnya adalah Disetujui
        if ($permohonan->jenis_cuti === 'Cuti Tahunan' && $permohonan->status === 'Disetujui') {
            $karyawan->jatah_cuti_tahunan += $permohonan->durasi;
            $karyawan->save();
        }

        $permohonan->status = 'Menunggu';
        $permohonan->save();

        return response()->json(['success' => true, 'message' => 'Status berhasil dikembalikan.']);
    }

    // public function viewFile(Request $request, $fileName)
    // {
    //     $filePath = 'public/' . $fileName;

    //     if (Storage::exists($filePath)) {
    //         return response()->file(Storage::path($filePath));
    //     }

    //     return response()->json(['message' => 'File tidak ditemukan.'], 404);
    // }

        public function rejectPermohonan(Request $request, $id)
    {
        try {
            $request->validate(['alasan_penolakan' => 'required|string']);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $permohonan = PermohonanCuti::with('karyawan')->find($id);

        if (!$permohonan || $permohonan->status !== 'Menunggu') {
            return response()->json(['message' => 'Permohonan tidak ditemukan atau tidak dalam status Menunggu.'], 404);
        }

        $permohonan->status = 'Ditolak';
        $permohonan->alasan_penolakan = $request->alasan_penolakan;
        $permohonan->save();

        Mail::to($permohonan->karyawan->email)->send(new StatusCutiNotification($permohonan));

        return response()->json(['message' => 'Permohonan berhasil ditolak.']);
    }

}
