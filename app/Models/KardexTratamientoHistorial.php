<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KardexTratamientoHistorial extends Model
{
    protected $guarded = [];
    protected $table = 'HCW_KX_TRATAMIENTO_HISTORIAL';
    protected $fillable = [
        'ID',
        'ID_KARDEX',
        'HC',
        'COD_MED',
        'NOM_MED',
        'FECHA',
        'ACCION',
        'DETALLES',
    ];
    protected $casts = [];
    public $timestamps = false;
    protected $primaryKey = 'ID';
}
