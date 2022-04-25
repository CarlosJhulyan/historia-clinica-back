<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patologico extends Model
{
    protected $guarded = [];
    protected $table = 'CME_HC_ANTECEDENTE_PATOLO_DIAG';
    protected $fillable = [
        'COD_GRUPO_CIA',
        'COD_PACIENTE',
        'SEC_HC_ANTECEDENTES',
        'COD_DIAGNOSTICO',
        'IND_TIPO',
        'DESC_PARIENTES',
        'COD_LOCAL',
    ];
    protected $casts = [];

    public $timestamps = false;
    protected $primaryKey = 'COD_GRUPO_CIA';
}
