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

        Karyawan::create([
            'nama' => 'Diana Adinda',
            'id_karyawan' => '12445678',
            'unit' => 'Service Node',
            'email' => 'diana@gmail.com',
            'is_admin' => false,
            // Kolom password dikosongkan karena bersifat nullable
        ]);

        Karyawan::create([
            'nama' => 'Bayu Pratama',
            'id_karyawan' => '28211',
            'unit' => 'BGES Assurance',
            'email' => 'satu@gmail.com',
            'is_admin' => false,
            // Kolom password dikosongkan karena bersifat nullable
        ]);

        Karyawan::create([
            'nama' => 'Gepol',
            'id_karyawan' => '282111',
            'unit' => 'FBB Assurance',
            'email' => 'satuss@gmail.com',
            'is_admin' => false,
            // Kolom password dikosongkan karena bersifat nullable
        ]);

        Karyawan::create([
            'nama' => 'Citra Emilia Fitriani',
            'id_karyawan' => '28220',
            'unit' => 'Wifi',
            'email' => 'dua@gmail.com',
            'is_admin' => false,
            // Kolom password dikosongkan karena bersifat nullable
        ]);

        Karyawan::create([
            'nama' => 'Muhammad Syafi Nasution',
            'id_karyawan' => '28200',
            'unit' => 'Surveillance',
            'email' => 'tiga@gmail.com',
            'is_admin' => false,
            // Kolom password dikosongkan karena bersifat nullable
        ]);

        Karyawan::create([
            'nama' => 'Abdur Rahman',
            'id_karyawan' => '28204',
            'unit' => 'FBB Assurance',
            'email' => 'empat@gmail.com',
            'is_admin' => false,
            // Kolom password dikosongkan karena bersifat nullable
        ]);

        Karyawan::create([
            'nama' => 'Ririn Kurniasih',
            'id_karyawan' => '31851',
            'unit' => 'Performance',
            'email' => 'lima@gmail.com',
            'is_admin' => false,
            // Kolom password dikosongkan karena bersifat nullable
        ]);

    }
}
