<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Interconsultas extends Model
{
    protected $guarded = [];
    protected $table = 'HCW_INTERCONSULTAS';
    protected $fillable = [
       
        'ID_INTERCONSULTAS',
        'COD_GRUPO_CIA',
        'COD_PACIENTE',
        'COD_PROD',
        'DESC_PROD',
        'NOM_LAB',
        'RUC',
        'NRO_ATENCION',        
    ];

    protected $primaryKey = 'ID_INTERCONSULTAS';
}
