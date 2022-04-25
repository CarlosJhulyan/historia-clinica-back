<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SugerenciaDetalle extends Model
{
    protected $guarded = [];
    protected $table = 'HCW_DET_SUG';
    protected $fillable = [
        'key',
        'tipoSugerencia',
        'codDiagnostico',
        'idDetalleSugerencia',    
        'cod_medico'    
    ];
    protected $casts = [];

    public $timestamps = false;
    protected $primaryKey = 'idDetalleSugerencia';
}
