<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterRecomendacion extends Model
{
    protected $guarded = [];
    protected $table = 'HCW_INTER_RECOMENDACION';
    protected $fillable = [
        'COD_GRUPO_CIA',
        'COD_PACIENTE',
        'NRO_ATENCION',
        'RECOMENDACION'
    ];

    // protected $primaryKey = 'ID_INTERCONSULTAS';
}
