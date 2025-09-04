<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permohonan_cuti', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')->constrained('karyawan');
            $table->string('jenis_cuti');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->integer('durasi');
            $table->text('alasan');
            $table->text('alamat_selama_cuti');
            $table->string('lampiran_file')->nullable();
            $table->enum('status', ['Menunggu', 'Disetujui', 'Ditolak'])->default('Menunggu');
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('permohonan_cuti');
    }
};
