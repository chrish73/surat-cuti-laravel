<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PermohonanCuti;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Mail;
use App\Mail\StatusCutiNotification;

class AdminController extends Controller
{
    // CRUD Permohonan Cuti
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

        if ($newStatus === 'Ditolak') {
            if (!$request->alasan_penolakan) {
                return response()->json(['success' => false, 'message' => 'Alasan penolakan tidak boleh kosong.'], 400);
            }
            $permohonan->alasan_penolakan = $request->alasan_penolakan;
        } else {
            $permohonan->alasan_penolakan = null;
        }

        if ($permohonan->jenis_cuti === 'Cuti Tahunan') {
            if ($previousStatus === 'Disetujui' && $newStatus !== 'Disetujui') {
                $karyawan->jatah_cuti_tahunan += $permohonan->durasi;
            }
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

        if ($karyawan) {
            Mail::to($karyawan->email)->send(new StatusCutiNotification($permohonan));
        }

        return response()->json(['success' => true, 'message' => 'Status berhasil diperbarui dan notifikasi email telah dikirim.']);
    }

    // --- Fungsionalitas CRUD Karyawan Baru ---

    public function getKaryawan()
    {
        $karyawan = Karyawan::all();
        return response()->json($karyawan);
    }

    public function storeKaryawan(Request $request)
    {
        $request->validate([
            'id_karyawan' => 'required|string|unique:karyawan,id_karyawan',
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:karyawan,email',
            'unit' => 'required|string|max:255',
            'password' => 'required|string|min:6',
            'jatah_cuti_tahunan' => 'required|integer',
            'is_admin' => 'boolean'
        ]);

        $karyawan = Karyawan::create([
            'id_karyawan' => $request->id_karyawan,
            'nama' => $request->nama,
            'email' => $request->email,
            'unit' => $request->unit,
            'password' => Hash::make($request->password),
            'jatah_cuti_tahunan' => $request->jatah_cuti_tahunan,
            'is_admin' => $request->is_admin ?? false
        ]);

        return response()->json(['message' => 'Karyawan berhasil ditambahkan.', 'data' => $karyawan], 201);
    }

    public function showKaryawan($id)
    {
        $karyawan = Karyawan::find($id);
        if (!$karyawan) {
            return response()->json(['message' => 'Karyawan tidak ditemukan.'], 404);
        }
        return response()->json($karyawan);
    }

    public function updateKaryawan(Request $request, $id)
    {
        $karyawan = Karyawan::find($id);
        if (!$karyawan) {
            return response()->json(['message' => 'Karyawan tidak ditemukan.'], 404);
        }

        $request->validate([
            'id_karyawan' => 'required|string|unique:karyawan,id_karyawan,' . $id,
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:karyawan,email,' . $id,
            'unit' => 'required|string|max:255',
            'password' => 'nullable|string|min:6',
            'jatah_cuti_tahunan' => 'required|integer',
            'is_admin' => 'boolean'
        ]);

        $karyawan->id_karyawan = $request->id_karyawan;
        $karyawan->nama = $request->nama;
        $karyawan->email = $request->email;
        $karyawan->unit = $request->unit;
        if ($request->filled('password')) {
            $karyawan->password = Hash::make($request->password);
        }
        $karyawan->jatah_cuti_tahunan = $request->jatah_cuti_tahunan;
        $karyawan->is_admin = $request->is_admin ?? false;
        $karyawan->save();

        return response()->json(['message' => 'Data karyawan berhasil diperbarui.', 'data' => $karyawan]);
    }

    public function deleteKaryawan($id)
    {
        $karyawan = Karyawan::find($id);
        if (!$karyawan) {
            return response()->json(['message' => 'Karyawan tidak ditemukan.'], 404);
        }
        $karyawan->delete();
        return response()->json(['message' => 'Karyawan berhasil dihapus.']);
    }
}
