<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecomendacionTratamiento extends Model
{
    protected $guarded = [];
    protected $table = 'HCW_RECOMENDACION_TRAT';
    protected $fillable = [
        'ID_REC_TRAT',
        'NRO_RECETA',
        'ATENCION_MEDICA',
        'COD_PROD',
        'RECOMENDACION'
    ];
    protected $casts = [];
    public $timestamps = false;
    protected $primaryKey = 'ID_REC_TRAT';
}