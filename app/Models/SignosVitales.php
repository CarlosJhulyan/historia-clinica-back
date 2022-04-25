<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SignosVitales extends Model
{
    protected $guarded = [];
    protected $table = 'HCW_SIGNOS_VITALES';
    protected $fillable = [
        'ID_SIGNOS_VITALES',
        'PACIENTE',
        'HISTORIA_CLINICA',
        'FECHA',
        'TURNO',
        'RESPIRACION',
        'P_A',
        'PULSO',
        'TEMPERATURA',
    ];
    protected $casts = [];

    public $timestamps = false;
    protected $primaryKey = '(ID_SIGNOS_VITALES)';
}
