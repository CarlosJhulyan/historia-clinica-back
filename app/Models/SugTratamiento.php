<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SugTratamiento extends Model
{
    protected $guarded = [];
    protected $table = 'HCW_SUG_TRAT';
    protected $fillable = [
        'key',
        'cantidad',
        'codprod',
        'rucempresa',
        'valfrac',
        'unidvta',
        'viaadministracion',
        'etiquetaVia',
        'frecuencia',
        'duracion',
        'dosis',
        'recomendacionAplicar',
        'tratamiento'
    ];
    protected $casts = [];

    public $timestamps = false;
    protected $primaryKey = 'key';
}
