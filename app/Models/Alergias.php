<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alergias extends Model
{
    protected $guarded = [];
    protected $table = 'HCW_ALERGIAS';
    protected $fillable = [
        'ID_ALERGIAS',
        'COD_GRUPO_CIA',
        'COD_PACIENTE',
        'ALERGIAS',
        'OTROS'
      
    ];

    protected $primaryKey = 'ID_ALERGIAS';
}
