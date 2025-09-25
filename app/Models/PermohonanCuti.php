<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermohonanCuti extends Model
{
    use HasFactory;
    protected $table = 'permohonan_cuti';
    protected $fillable = ['karyawan_id', 'jenis_cuti', 'tanggal_mulai', 'tanggal_selesai', 'durasi', 'alasan', 'alamat_selama_cuti', 'lampiran_file', 'status', 'alasan_penolakan']; // Tambahkan 'alasan_penolakan'
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }
}
