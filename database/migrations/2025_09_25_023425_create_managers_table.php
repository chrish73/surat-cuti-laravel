<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manajer', function (Blueprint $table) {
            $table->id();
            $table->string('nama_manajer');
            $table->string('id_manajer')->unique();
            $table->string('jabatan_manajer');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manajer');
    }
};
