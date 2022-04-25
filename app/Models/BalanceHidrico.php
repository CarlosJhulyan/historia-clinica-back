<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BalanceHidrico extends Model
{
    protected $guarded = [];
    protected $table = 'HCW_BALANCE_HIDRICO';
    protected $fillable = [
        'ID_BALANCE_HIDRICO',
        'PACIENTE',
        'HISTORIA_CLINICA',
        'FECHA',
        'PESO',
        'BALANCE_HIDRICO',
        'ESTACION',
        'E_DIURESIS_0814',
        'E_DIURESIS_1420',
        'E_DIURESIS_2008',
        'E_DEPOSICION_0814',
        'E_DEPOSICION_1420',
        'E_DEPOSICION_2008',
        'E_TEMPERATURA_0814',
        'E_TEMPERATURA_1420',
        'E_TEMPERATURA_2008',
        'E_OPCIONAL',
        'E_VALOR_0814',
        'E_VALOR_1420',
        'E_VALOR_2008',
        'I_ORAL_0814',
        'I_ORAL_1420',
        'I_ORAL_2008',
        'I_PARENTAL_0814',
        'I_PARENTAL_1420',
        'I_PARENTAL_2008',
        'I_TRATAMIENTO_0814',
        'I_TRATAMIENTO_1420',
        'I_TRATAMIENTO_2008',
        'I_OPCIONAL',
        'I_VALOR_0814',
        'I_VALOR_1420',
        'I_VALOR_2008'
    ];
    protected $casts = [];

    public $timestamps = false;
    protected $primaryKey = '(ID_BALANCE_HIDRICO)';
}
