<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Karyawan;
use Illuminate\Support\Facades\Hash;

class KaryawanSeeder extends Seeder
{
    /**
     * Jalankan seed database.
     */
    public function run(): void
    {
        // Buat data untuk Admin
        Karyawan::create([
            'nama' => 'Admin Utama',
            'id_karyawan' => 'ADM-001',
            'unit' => 'Administrasi',
            'email' => 'admin@gmail.com',
            'is_admin' => true,
            'password' => Hash::make('admin1231'), // Ganti dengan password yang aman
        ]);

        // Buat data untuk Karyawan Biasa
        Karyawan::create([
            'nama' => 'Budi Santoso',
            'id_karyawan' => '12345678',
            'unit' => 'FBB Fulfillment',
            'email' => 'budi@gmail.com',
            'is_admin' => false,
            // Kolom password dikosongkan karena bersifat nullable
        ]);
    }
}
