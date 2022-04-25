<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Anexo extends Model
{
    protected $guarded = [];
    protected $table = 'CME_ATENCION_MEDICA_ANEXO';
    protected $fillable = [
        'COD_GRUPO_CIA',
        'COD_LOCAL',
        'COD_ANEXO',
        'NOM_ANEXO',
        'OBS_ANEXO',
        'RUTA_LOCAL',
        'RUTA_SERVIDOR',
        'NOM_FILE',
        'EXT_FILE',
        'USU_CREA',
        'FEC_CREA',
        'USU_MOD',
        'FEC_MOD',
        'EST_ANEXO',
        'COD_CIA',
        'NUM_ATEN_MED'
    ];
}
