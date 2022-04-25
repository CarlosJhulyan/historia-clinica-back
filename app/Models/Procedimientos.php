<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Procedimientos extends Model
{
    protected $guarded = [];
    protected $table = 'HCW_CONSULTA_PROCEDIMIENTO';
    protected $fillable = [
        'COD_GRUPO_CIA',
        'COD_PACIENTE',
        'COD_MEDICO',
        'RELATO_MEDICO',
        'CONCLUSION',
        'OBSERVACIONES',
        'NRO_ATENCION',
    ];
    protected $casts = [];
    public $timestamps = false;
    protected $primaryKey = 'ID_DATOS_CONSULTA';
}