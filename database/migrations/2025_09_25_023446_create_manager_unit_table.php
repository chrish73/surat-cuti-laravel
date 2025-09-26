<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manajer_unit', function (Blueprint $table) {
            $table->foreignId('manajer_id')->constrained('manajer')->onDelete('cascade');
            $table->string('unit');
            $table->primary(['manajer_id', 'unit']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manajer_unit');
    }
};
