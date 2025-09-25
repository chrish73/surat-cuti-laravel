<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed aplikasi database.
     */
    public function run(): void
    {
        $this->call([
            KaryawanSeeder::class,
        ]);
    }
}
