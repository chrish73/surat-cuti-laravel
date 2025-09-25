<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('karyawan', function (Blueprint $table) {
            // Kolom password bisa nullable karena tidak semua karyawan punya password
            $table->string('password')->nullable()->after('email');
            $table->rememberToken(); // Untuk fitur "ingat saya"
        });
    }
    public function down(): void
    {
        Schema::table('karyawan', function (Blueprint $table) {
            $table->dropColumn(['password', 'remember_token']);
        });
    }
};
