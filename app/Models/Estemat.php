<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estemat extends Model
{    
    protected $guarded = [];
    protected $table = 'HCW_ESTOMATOLOGICO';
    protected $fillable = [
        'ID_DATOS_ESTOMATOLOGICO',
        'COD_PACIENTE',
        'COD_GRUPO_CIA',
        'COD_MEDICO',
        'FECHA',
        'CARA',
        'CUELLO',
        'PIEL',
        'GANGLIOS',
        'ATM',
        'LABIOS',
        'CARRILLOS',
        'FONDO_SURCO',
        'PERIODONTO',
        'ZONA_RETROMOLAR',
        'SALIVA',
        'GLANDULAS_SALIVALES',
        'LENGUA',
        'PALADAR_DURO',
        'PALADAR_BLANDO',
        'PISO_BOCA',
        'OROFARINGE',
        'INDICE_HIGIENE_ORAL',
        'HENDIDURA_GINGIVAL',
        'VITALIDAD_PALPAR',
        'ODUSION',
        'GUIA_ANTERIOR',
        'INTERFERENCIAS',
        'CONTACTO_PREMATURO',
        'REBORDES_ALVEOLARE',
        'TUBEROSIDADES',
        'NRO_ATENCION'
    ];
    protected $casts = [];
    
    public $timestamps = false;
    protected $primaryKey = 'ID_DATOS_ESTOMATOLOGICO';
}