<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PermohonanCuti;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Mail;
use App\Mail\StatusCutiNotification;
use Illuminate\Validation\ValidationException;
use App\Models\Karyawan; // Impor model Karyawan
use Illuminate\Support\Facades\Hash;

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

    public function getKaryawan()
    {
        $karyawan = Karyawan::all();
        return response()->json($karyawan);
    }

    public function getKaryawanById($id)
    {
        $karyawan = Karyawan::findOrFail($id);
        return response()->json($karyawan);
    }

    public function saveKaryawan(Request $request, $id = null)
    {
        $is_admin = $request->is_admin ?? false;

        $rules = [
            'nama' => 'required|string|max:255',
            'unit' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'id_karyawan' => 'required|string|max:255',
            'is_admin' => 'required|boolean',
            'jatah_cuti_tahunan' => 'required|integer',
        ];

        if ($is_admin) {
            $rules['password'] = 'nullable|min:8';
        }

        if ($id) {
            $karyawan = Karyawan::findOrFail($id);
            $rules['id_karyawan'] = 'required|string|max:255|unique:karyawan,id_karyawan,'.$id;
            $rules['email'] = 'required|email|max:255|unique:karyawan,email,'.$id;

            $request->validate($rules);

            $karyawan->nama = $request->nama;
            $karyawan->id_karyawan = $request->id_karyawan;
            $karyawan->unit = $request->unit;
            $karyawan->email = $request->email;
            $karyawan->is_admin = $request->is_admin;
            $karyawan->jatah_cuti_tahunan = $request->jatah_cuti_tahunan;

            if ($request->filled('password') && $is_admin) {
                $karyawan->password = Hash::make($request->password);
            }
            $karyawan->save();
        } else {
            $request->validate($rules);

            $karyawanData = $request->only('nama', 'id_karyawan', 'unit', 'email', 'is_admin', 'jatah_cuti_tahunan');
            if ($request->filled('password') && $is_admin) {
                $karyawanData['password'] = Hash::make($request->password);
            }
            Karyawan::create($karyawanData);
        }

        return response()->json(['message' => 'Data karyawan berhasil disimpan!']);
    }

    public function deleteKaryawan($id)
    {
        $karyawan = Karyawan::findOrFail($id);
        $karyawan->delete();

        return response()->json(['message' => 'Data karyawan berhasil dihapus!']);
    }
}
