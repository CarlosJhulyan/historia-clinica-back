<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvolucionEnfermeria extends Model
{
    protected $guarded = [];
    protected $table = 'HCW_EVOLUCION_ENFERMERIA';
    protected $fillable = [
        'id',
        'narracion_estado',
        'cod_paciente',
        'nom_paciente',
        'cod_medico',
        'nom_medico',
        'nro_hc'
    ];
    protected $casts = [];

    public $timestamps = false;
    protected $primaryKey = 'id';
}
