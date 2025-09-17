<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'id_karyawan' => 'required|exists:karyawan,id_karyawan',
            'email' => 'required|email',
        ]);

        $karyawan = Karyawan::where('id_karyawan', $request->id_karyawan)->first();

        // Cek apakah email yang dimasukkan sudah digunakan oleh karyawan lain
        $existingKaryawanWithEmail = Karyawan::where('email', $request->email)
            ->where('id_karyawan', '!=', $request->id_karyawan)
            ->first();

        if ($existingKaryawanWithEmail) {
            return response()->json([
                'message' => 'Login Gagal: Email sudah digunakan oleh karyawan lain.'
            ], 409); // Menggunakan kode status 409 Conflict
        }

        // Jika email berbeda dan belum ada yang menggunakan, perbarui
        if ($karyawan && $karyawan->email !== $request->email) {
            $karyawan->email = $request->email;
            $karyawan->save();
        }

        // Menggunakan Sanctum untuk membuat token
        $token = $karyawan->createToken('auth-token')->plainTextToken;

        // Periksa apakah pengguna adalah admin
        if ($karyawan->is_admin) {
             return response()->json([
                'message' => 'Login Admin Berhasil!',
                'api_token' => $token,
                'karyawan' => $karyawan
            ]);
        }

        // Ini adalah logika untuk karyawan biasa
        return response()->json([
            'message' => 'Login Karyawan Berhasil!',
            'api_token' => $token,
            'karyawan' => $karyawan
        ]);
    }

    // Fungsi baru untuk login admin dengan password
    public function loginAdmin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $karyawan = Karyawan::where('email', $request->email)->first();

        // Periksa email, peran, dan password
        if (!$karyawan || !$karyawan->is_admin || !Hash::check($request->password, $karyawan->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah, atau Anda bukan admin.'
            ], 401);
        }

        // Jika berhasil, buat token dan simpan
        $token = $karyawan->createToken('admin-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login Admin berhasil!',
            'api_token' => $token
        ]);
    }
}
