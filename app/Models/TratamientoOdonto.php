<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TratamientoOdonto extends Model
{
    protected $guarded = [];
    protected $table = 'HCW_TRATAMIENTO_ODONTO';    
    protected $fillable = [
        'ID_DATOS_TRATAMIENTO',
        'COD_PACIENTE',
        'COD_GRUPO_CIA',
        'COD_MEDICO',
        'FECHA',
        'PLAN_TRATAMIENTO',
        'DESCRIPCION_TRATAMIENTO',
        'NOMBRE_MEDICO',
        'ESPECIALIDAD',
        'NRO_ATENCION'
    ];
    protected $casts = [];

    public $timestamps = false;
    protected $primaryKey = 'ID_DATOS_TRATAMIENTO';
}
