<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DatosOdontograma extends Model
{
    protected $guarded = [];
    protected $table = 'HCW_ODONTOGRAMA';
    protected $fillable = [
        'ID_DATOS',
        'ID_OPCIONES',
        'ID_HISTORIAL',
        'DIAGNOSTICO',
        'ESTADO',
        'DIENTE',
        'DIENTE_FIN',
        'TIPO',
        'PARTES'
    ];
    protected $casts = [
        'diente' => 'integer',
        'diente_fin' => 'integer',
        'estado' => 'integer'
    ];

    public $timestamps = false;
    protected $primaryKey = 'ID_DATOS';
}
