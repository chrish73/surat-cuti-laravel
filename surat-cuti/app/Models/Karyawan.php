<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens; // Impor HasApiTokens

class Karyawan extends Authenticatable
{
    use HasFactory, HasApiTokens; // Tambahkan HasApiTokens di sini
    protected $table = 'karyawan';
    protected $fillable = ['nama', 'id_karyawan', 'unit', 'is_admin', 'email', 'password', 'api_token', 'jatah_cuti_tahunan'];
    protected $hidden = ['password', 'remember_token', 'api_token']; // Sembunyikan api_token dari JSON
    public function permohonanCuti()
    {
        return $this->hasMany(PermohonanCuti::class);
    }
}
