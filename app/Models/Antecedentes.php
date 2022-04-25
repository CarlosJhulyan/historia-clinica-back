<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Antecedentes extends Model
{
    protected $guarded = [];
    protected $table = 'HCW_ANTECEDENTES';
    protected $fillable = [
        'ID_DATOS_ANTECEDENTES',
        'COD_PACIENTE',
        'COD_GRUPO_CIA',
        'COD_MEDICO',
        'FECHA',
        'DIABETES',
        'FIEBRE_REUMATICA',
        'ENFERMEDAD_HEPATICAS',
        'HEMORRAGIAS',
        'TUBERCULOSIS',
        'ENFERMEDAD_CARDIOVASCULAR',
        'REACCION_ANORMAL_LOCAL',
        'ALERGIA_PENECILINA',
        'ANEMIA',
        'ENFERMEDAD_RENAL',
        'REACCION_ANORMAL_DROGAS',
        'OTRAS',
    ];
    protected $casts = [];

    public $timestamps = false;
    protected $primaryKey = 'ID_DATOS_ANTECEDENTES';
}