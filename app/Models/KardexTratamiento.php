<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KardexTratamiento extends Model
{
    protected $guarded = [];
    protected $table = 'HCW_KARDEX_TRATAMIENTO';
    protected $fillable = [
        'ID',
        'ID_KARDEX',
        'CODIGO_PRODUCTO',
        'PRODUCTO',
        'VIA_ADMINISTRACION',
        'ETIQUETA_VIA',
        'DOSIS',
        'CANTIDAD',
        'DURACION',
        'FRECUENCIA',
        'ESTADO',
    ];
    protected $casts = [];
    public $timestamps = false;
    protected $primaryKey = 'ID';
}
