<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Karyawan extends Authenticatable
{
    use HasFactory;
    protected $table = 'karyawan';
    protected $fillable = ['nama', 'id_karyawan', 'unit', 'is_admin', 'email', 'password', 'api_token'];
    protected $hidden = ['password', 'remember_token'];
    public function permohonanCuti()
    {
        return $this->hasMany(PermohonanCuti::class);
    }
}
