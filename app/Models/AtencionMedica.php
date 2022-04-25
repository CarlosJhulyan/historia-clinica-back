<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AtencionMedica extends Model
{
    protected $guarded = [];
    protected $table = 'CME_ATENCION_MEDICA';
    protected $fillable = [
        'COD_GRUPO_CIA',
        'COD_CIA',
        'COD_LOCAL',
        'NUM_ATEN_MED',
        'COD_MEDICO',
        'COD_PACIENTE',
        'ESTADO',
        'FEC_CREA',
        'USU_CREA',
        'FEC_MOD',
        'IND_ANULADO'
    ];
    protected $casts = [
    ];

    public $timestamps = false;
    protected $primaryKey = 'COD_MEDICO';
}
