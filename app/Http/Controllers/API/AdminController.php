<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PermohonanCuti;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Mail;
use App\Mail\StatusCutiNotification;
use Illuminate\Validation\ValidationException;
use App\Models\Karyawan;
use App\Models\Manajer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $query = PermohonanCuti::with('karyawan');

        // Filter berdasarkan unit
        if ($request->has('unit') && $request->unit != '') {
            $query->whereHas('karyawan', function ($q) use ($request) {
                $q->where('unit', $request->unit);
            });
        }

        // Tambahkan filter nama
        if ($request->has('name') && $request->name != '') {
            $query->whereHas('karyawan', function ($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->name . '%');
            });
        }

        // Tambahkan filter rentang tanggal
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if ($startDate && $endDate) {
            $query->whereBetween('tanggal_mulai', [$startDate, $endDate]);
        } elseif ($startDate) {
            $query->where('tanggal_mulai', '>=', $startDate);
        } elseif ($endDate) {
            $query->where('tanggal_mulai', '<=', $endDate);
        }

        $permohonanCuti = $query->orderBy('created_at', 'desc')->get();
        return response()->json($permohonanCuti);
    }

    public function exportToExcel(Request $request)
    {
        // Ambil data dari database, filter berdasarkan unit, nama, dan tanggal jika ada
        $query = PermohonanCuti::with('karyawan');

        // Filter berdasarkan unit
        if ($request->has('unit') && $request->unit != '') {
            $query->whereHas('karyawan', function ($q) use ($request) {
                $q->where('unit', $request->unit);
            });
        }

        // Tambahkan filter nama
        if ($request->has('name') && $request->name != '') {
            $query->whereHas('karyawan', function ($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->name . '%');
            });
        }

        // Tambahkan filter rentang tanggal
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if ($startDate && $endDate) {
            $query->whereBetween('tanggal_mulai', [$startDate, $endDate]);
        } elseif ($startDate) {
            $query->where('tanggal_mulai', '>=', $startDate);
        } elseif ($endDate) {
            $query->where('tanggal_mulai', '<=', $endDate);
        }

        $permohonanCuti = $query->orderBy('created_at', 'desc')->get();

        // Tentukan nama file
        $timestamp = now()->format('Y-m-d_H-i-s');
        $fileName = 'riwayat_cuti_' . ($request->unit ? $request->unit . '_' : '') . $timestamp . '.csv';

        // Definisikan header respons untuk pengunduhan file
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        // Buat callback untuk menangani pembuatan dan penulisan file CSV
        $callback = function() use ($permohonanCuti) {
            $file = fopen('php://output', 'w');

            // Baris header
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

            // Loop melalui data dan tambahkan ke CSV
            foreach ($permohonanCuti as $permohonan) {
                fputcsv($file, [
                    $permohonan->karyawan->nama ?? 'Tidak Ada',
                    $permohonan->karyawan->id_karyawan ?? 'Tidak Ada',
                    $permohonan->karyawan->unit ?? 'Tidak Ada',
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

        // Mengembalikan respons streaming yang akan mengunduh file
        return new StreamedResponse($callback, 200, $headers);
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
            $rules['password'] = 'nullable|min:5';
        }

        if ($id) {
            $karyawan = Karyawan::findOrFail($id);
            $rules['id_karyawan'] = 'required|string|max:255|unique:karyawan,id_karyawan,' . $id;
            $rules['email'] = 'required|email|max:255|unique:karyawan,email,' . $id;

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

    public function getManajer()
    {
        $manajer = Manajer::all();
        $manajerData = [];

        foreach ($manajer as $m) {
            $units = DB::table('manajer_unit')->where('manajer_id', $m->id)->pluck('unit');
            $manajerData[] = [
                'id' => $m->id,
                'nama_manajer' => $m->nama_manajer,
                'id_manajer' => $m->id_manajer,
                'jabatan_manajer' => $m->jabatan_manajer,
                'units' => $units->toArray()
            ];
        }

        return response()->json(['manajer' => $manajerData], 200);
    }

    public function addManajer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_manajer' => 'required|string|max:255',
            'id_manajer' => 'required|string|unique:manajer,id_manajer|max:255',
            'jabatan_manajer' => 'required|string|max:255',
            'units' => 'array',
            'units.*' => 'string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $manajer = Manajer::create($request->only('nama_manajer', 'id_manajer', 'jabatan_manajer'));

        if ($request->has('units') && !empty($request->units)) {
            $units = collect($request->units)->map(function ($unit) use ($manajer) {
                return ['manajer_id' => $manajer->id, 'unit' => $unit];
            });
            DB::table('manajer_unit')->insert($units->toArray());
        }

        return response()->json(['message' => 'Manajer berhasil ditambahkan.', 'data' => $manajer], 201);
    }

    public function updateManajer(Request $request, $id)
    {
        $manajer = Manajer::find($id);

        if (!$manajer) {
            return response()->json(['message' => 'Manajer tidak ditemukan.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama_manajer' => 'string|max:255',
            'id_manajer' => 'string|unique:manajer,id_manajer,' . $id . '|max:255',
            'jabatan_manajer' => 'string|max:255',
            'units' => 'array',
            'units.*' => 'string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $manajer->update($request->only('nama_manajer', 'id_manajer', 'jabatan_manajer'));

        if ($request->has('units')) {
            // Hapus semua unit lama dan tambahkan yang baru
            DB::table('manajer_unit')->where('manajer_id', $manajer->id)->delete();
            if (!empty($request->units)) {
                $units = collect($request->units)->map(function ($unit) use ($manajer) {
                    return ['manajer_id' => $manajer->id, 'unit' => $unit];
                });
                DB::table('manajer_unit')->insert($units->toArray());
            }
        }

        return response()->json(['message' => 'Data manajer berhasil diperbarui.', 'data' => $manajer], 200);
    }

    public function deleteManajer($id)
    {
        $manajer = Manajer::find($id);

        if (!$manajer) {
            return response()->json(['message' => 'Manajer tidak ditemukan.'], 404);
        }

        $manajer->delete();

        return response()->json(['message' => 'Manajer berhasil dihapus.'], 200);
    }

    public function getUniqueUnits()
{
    $units = Karyawan::distinct()->pluck('unit');
    return response()->json($units);
}
}
