<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KardexInterconsulta extends Model
{
    protected $guarded = [];
    protected $table = 'HCW_KARDEX_INTERCONSULTA';
    protected $fillable = [
        'ID',
        'ID_KARDEX',
        'CODIGO_PRODUCTO',
        'PRODUCTO',
        'NOMBRE_LABORATORIO',
        'RUC',
        'TIPO',
        'ESTADO',
        'FECHA_TOMA',
        'FECHA_ENTREGA',
    ];
    protected $casts = [];
    public $timestamps = false;
    protected $primaryKey = 'ID';
}
