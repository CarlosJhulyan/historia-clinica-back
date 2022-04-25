<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hospitalizaciones extends Model
{
    protected $guarded = [];
    protected $table = 'HCW_HOSPITALIZACION';
    protected $fillable = [
        'ID_HOSPITALIZACION',
        'COD_GRUPO_CIA',
        'COD_PACIENTE',
        'HOSPITALIZACION',
        'URGENCIA',
        'HISTORIA_CLINICA',
        'ASIGNADO',
        'MOTIVO_BAJA'
    ];

    protected $primaryKey = 'ID_HOSPITALIZACION';
}
