<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Manajer;
use Illuminate\Support\Facades\DB;

class ManajerSeeder extends Seeder
{
    public function run(): void
    {
        $manajer1 = Manajer::create([
            'nama_manajer' => 'Alex Handoko',
            'id_manajer' => '001',
            'jabatan_manajer' => 'Manajer Operasional',
        ]);

        DB::table('manajer_unit')->insert([
            ['manajer_id' => $manajer1->id, 'unit' => 'FBB Fulfillment'],
            ['manajer_id' => $manajer1->id, 'unit' => 'Service Node'],
        ]);

        $manajer2 = Manajer::create([
            'nama_manajer' => 'Budi Santoso',
            'id_manajer' => '002',
            'jabatan_manajer' => 'Manajer Logistik',
        ]);

        DB::table('manajer_unit')->insert([
            ['manajer_id' => $manajer2->id, 'unit' => 'BGES Assurance'],
        ]);
    }
}
