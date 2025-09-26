<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Manajer extends Model
{
    use HasFactory;

    protected $table = 'manajer';

    protected $fillable = [
        'nama_manajer',
        'id_manajer',
        'jabatan_manajer',
    ];
}
