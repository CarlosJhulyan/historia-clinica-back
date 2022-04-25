<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReporteAuditoria extends Model
{
    protected $guarded = [];
    protected $table = 'HCW_REP_AUDITORIA';
    protected $fillable = [
        'ID',
        'COD_MEDICO',
        'NOM_MEDICO',
        'COD_PACIENTE',
        'NOM_PACIENTE',
        'HC',
        'COMPLETOS',
        'INCOMPLETOS',
        'FECHA',
    ];
    protected $casts = [];
    public $timestamps = false;
    protected $primaryKey = 'ID';
}
